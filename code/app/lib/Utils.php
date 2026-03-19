<?php
declare(strict_types=1);

/* FIX: ensure PROJ_ROOT points to the project root when config.php
 * hasn't been loaded yet (lib → app → code → project root = 3 levels) */
if (!defined('PROJ_ROOT')) {
    define('PROJ_ROOT', dirname(__DIR__, 3));   // <- was ../../ (wrong)
}

require_once PROJ_ROOT . '/vendor/autoload.php';
use Dompdf\Dompdf;

// ---------------------------------------------------------------------
// Canonical paths (only define if not already defined by config.php)
// ---------------------------------------------------------------------
if (!defined('STORAGE_DIR'))    define('STORAGE_DIR',    PROJ_ROOT . '/storage');
if (!defined('LOGS_DIR'))       define('LOGS_DIR',       STORAGE_DIR . '/logs');
if (!defined('TMP_DIR'))        define('TMP_DIR',        STORAGE_DIR . '/tmp');

if (!defined('BOOK_ID'))        define('BOOK_ID',        'Demo Challenge');

// Certificates storage
if (!defined('CERTS_DIR'))      define('CERTS_DIR',      STORAGE_DIR . '/certs');
if (!defined('CERTS_BOOK_DIR')) define('CERTS_BOOK_DIR', CERTS_DIR . '/' . BOOK_ID);

// Requests storage
if (!defined('REQUESTS_DIR'))   define('REQUESTS_DIR',   STORAGE_DIR . '/requests/' . BOOK_ID);

// Email attempts storage
if (!defined('EMAILS_DIR'))     define('EMAILS_DIR',     STORAGE_DIR . '/emails/' . BOOK_ID);

// Bonus storage (for reference)
if (!defined('BONUS_DIR'))      define('BONUS_DIR',      STORAGE_DIR . '/bonus/' . BOOK_ID);
if (!defined('BONUS_FILE'))     define('BONUS_FILE',     BONUS_DIR   . '/bonus.pdf');

// Base URL & generator URL (fallbacks for local dev)
if (!defined('BASE_URL'))            define('BASE_URL',            'http://localhost:8000');
if (!defined('CERT_GENERATOR_URL'))  define('CERT_GENERATOR_URL',  rtrim(BASE_URL, '/') . '/cert_engine.php?name=');

// Ensure essential folders exist (idempotent)
// Ensure essential folders exist (idempotent)
foreach ([STORAGE_DIR, LOGS_DIR, TMP_DIR, CERTS_DIR, CERTS_BOOK_DIR, REQUESTS_DIR, BONUS_DIR, EMAILS_DIR] as $d) {
    if (!is_dir($d)) { @mkdir($d, 0775, true); }
}

// ---------------------------------------------------------------------
// Utility helpers (define only if not already defined by config.php)
// ---------------------------------------------------------------------
if (!function_exists('validate_key_code')) {
    function validate_key_code(string $code): bool {
        // 15–35 chars A–Z 0–9 only
        return (bool) preg_match('/^[A-Za-z0-9]{15,35}$/', $code);
    }
}

if (!function_exists('sanitize_name')) {
    function sanitize_name(?string $s): string {
        $s = trim((string)$s);
        // keep letters, spaces, apostrophes, hyphens (Unicode-safe)
        $s = preg_replace("/[^\p{L} \-']/u", '', $s) ?? '';
        $s = preg_replace('/\s+/', ' ', $s) ?? '';
        return $s;
    }
}

if (!function_exists('slug_ascii')) {
    function slug_ascii(string $s): string {
        $x = $s;
        if (function_exists('iconv')) {
            $x = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $x);
        }
        $x = strtolower((string)$x);
        $x = preg_replace('/[^a-z0-9]+/', '-', $x) ?? '';
        return trim($x, '-');
    }
}

if (!function_exists('email_key')) {
    function email_key(string $email): string {
        // stable, URL-safe key from email (lowercased)
        $email = strtolower(trim($email));
        return rtrim(strtr(base64_encode($email), '+/', '-_'), '=');
        
    }
}
if (!function_exists('log_email_attempt')) {
    /**
     * Log every email attempt to /storage/emails/<BOOK_ID>/<email_key>.json.
     * Records the latest status while keeping a running attempt_count and first_seen_at.
     */
    function log_email_attempt(string $email, string $status, string $reason, string $lang): void {
        $email = strtolower(trim($email));
        if ($email === '') {
            return; // nothing to log
        }

        // Resolve base directory for email logs
        $dir = defined('EMAILS_DIR')
            ? EMAILS_DIR
            : (defined('STORAGE_DIR')
                ? STORAGE_DIR . '/emails/' . (defined('BOOK_ID') ? BOOK_ID : 'default')
                : __DIR__ . '/../../storage/emails');

        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $key  = email_key($email);
        $path = $dir . '/' . $key . '.json';
        $now  = date('c');

        $row = [
            'email'         => $email,
            'book_id'       => defined('BOOK_ID') ? BOOK_ID : null,
            'status'        => $status,
            'reason'        => $reason,
            'lang'          => $lang,
            'attempt_count' => 1,
            'first_seen_at' => $now,
            'last_seen_at'  => $now,
        ];

        if (is_file($path)) {
            $existing = json_decode((string) file_get_contents($path), true);
            if (is_array($existing)) {
                if (!empty($existing['first_seen_at'])) {
                    $row['first_seen_at'] = $existing['first_seen_at'];
                }
                if (isset($existing['attempt_count'])) {
                    $row['attempt_count'] = (int)$existing['attempt_count'] + 1;
                }
            }
        }

        $json = json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        @file_put_contents($path, $json, LOCK_EX);
    }
}

// ---------------------------------------------------------------------
// Persistence helpers (JSON rows under /storage/requests/<BOOK_ID>)
// ---------------------------------------------------------------------
function save_request(array $row): bool {
    $rid = email_key($row['email']);
    $path = REQUESTS_DIR . '/' . $rid . '.json';
    $row['updated_at'] = date('c');
    if (!isset($row['created_at'])) $row['created_at'] = date('c');
    $json = json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return (bool) file_put_contents($path, $json, LOCK_EX);
}

function get_request_by_email(string $email): ?array {
    $rid = email_key($email);
    $path = REQUESTS_DIR . '/' . $rid . '.json';
    if (!is_file($path)) return null;
    return json_decode((string) file_get_contents($path), true);
}

function get_request_by_rid(string $rid): ?array {
    $path = REQUESTS_DIR . '/' . $rid . '.json';
    if (!is_file($path)) return null;
    return json_decode((string) file_get_contents($path), true);
}

// ---------------------------------------------------------------------
// PDF helpers (PNG fetch from generator -> wrap to PDF with Dompdf)
// ---------------------------------------------------------------------

/**
 * Download a PNG preview from the local /cert_engine.php and save to a temp file.
 * Returns the path to the temp PNG, or false on failure.
 */
function ink_fetch_cert_png(string $name, ?string $date = null) {
    $date = $date ?: date('Y-m-d');

    // Detect language for certificate artwork (session first, then cookie, default EN)
    $lang = 'en';

    if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['lang'])) {
        $c = strtolower(trim((string)$_SESSION['lang']));
        if (in_array($c, ['en', 'de'], true)) {
            $lang = $c;
        }
    } elseif (!empty($_COOKIE['demo_lang'])) {
        $c = strtolower(trim((string)$_COOKIE['demo_lang']));
        if (in_array($c, ['en', 'de'], true)) {
            $lang = $c;
        }
    }

    $url  = CERT_GENERATOR_URL
          . rawurlencode($name)
          . '&date=' . rawurlencode($date)
          . '&preview=1'
          . '&lang=' . rawurlencode($lang);

    // Use cURL (more robust than file_get_contents)
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => ['Accept: image/png'],
        ]);
        $png = curl_exec($ch);
        $ok  = !curl_errno($ch) && (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE) === 200;
        curl_close($ch);
        if (!$ok || !$png) return false;
    } else {
        // Fallback
        if (!ini_get('allow_url_fopen')) return false;
        $context = stream_context_create(['http' => ['timeout' => 15]]);
        $png     = @file_get_contents($url, false, $context);
        if ($png === false) return false;
    }

    // Verify PNG signature
    if (substr($png, 0, 8) !== "\x89PNG\x0D\x0A\x1A\x0A") {
        return false;
    }

    $tmp = TMP_DIR . '/cert-' . uniqid('', true) . '.png';
    if (@file_put_contents($tmp, $png) === false) return false;
    return $tmp;
}

/**
 * Wrap a PNG into a one-page A4 PDF via Dompdf.
 * Returns true on success.
 */
function ink_png_to_pdf(string $tmpPng, string $destPdf): bool {
    if (!class_exists(Dompdf::class)) return false;

    $imgData = @file_get_contents($tmpPng);
    if ($imgData === false) return false;
    $b64 = 'data:image/png;base64,' . base64_encode($imgData);

    $html = <<<HTML
<!doctype html><html><head><meta charset="utf-8">
<style>@page{margin:0;size:A4 portrait}html,body{margin:0;padding:0}img{width:100%;height:100%;display:block}</style>
</head><body><img src="$b64" alt="certificate"></body></html>
HTML;

    $dompdf = new Dompdf(['isRemoteEnabled' => true]);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $ok = (bool) @file_put_contents($destPdf, $dompdf->output());
    @unlink($tmpPng);
    return $ok;
}

// ---------------------------------------------------------------------
// High-level generator (no placeholder): PNG -> PDF
// ---------------------------------------------------------------------

/**
 * Generate and save a certificate PDF for the given full name.
 * - Fetches PNG from /cert_engine.php (same artwork as preview)
 * - Wraps it into a proper A4 PDF via Dompdf
 * - Stores under /storage/certs/<BOOK_ID>/
 *
 * @return string|null Absolute path to saved PDF or null on failure
 */
function generate_certificate_pdf(string $full_name): ?string {
    if (!is_dir(CERTS_BOOK_DIR)) {
        if (!@mkdir(CERTS_BOOK_DIR, 0775, true) && !is_dir(CERTS_BOOK_DIR)) {
            error_log('Utils: cannot create certificate directory: ' . CERTS_BOOK_DIR);
            return null;
        }
    }

    $tmpPng = ink_fetch_cert_png($full_name, null);
    if ($tmpPng === false) {
        error_log('Utils: could not fetch certificate PNG from generator.');
        return null;
    }

    $safe     = slug_ascii($full_name) ?: 'certificate';
    $finalPdf = CERTS_BOOK_DIR . '/' . sprintf('%s_%s-%s.pdf', BOOK_ID, $safe, date('Ymd-His'));

    if (!ink_png_to_pdf($tmpPng, $finalPdf)) {
        error_log('Utils: failed to convert PNG to PDF.');
        return null;
    }
    return $finalPdf;
}

if (!function_exists('send_confirmation_email')) {
function send_confirmation_email(string $to, string $first_name, string $request_type = 'cert_bonus', string $rid = ''): void
{

    // Ensure the same source of truth as the Access page (/public/index.php)
    // Fixes cases where Utils.php is used standalone (e.g., sandbox email test) and CHALLENGE_NAME would otherwise fall back.
    if (!defined('CHALLENGE_NAME')) {
        $cfg = PROJ_ROOT . '/code/app/config.php';
        if (is_file($cfg)) {
            require_once $cfg;
        }
    }

        // --- BASE_URL safety guard (email links must never point to localhost in production) ---
    $baseUrl = defined('BASE_URL') ? trim((string) BASE_URL) : '';
    $allowLocalLinks = false;

    // Allow local links only when explicitly enabled (for VS Code testing)
    if (defined('ALLOW_LOCAL_EMAIL_LINKS')) {
        $allowLocalLinks = (ALLOW_LOCAL_EMAIL_LINKS === '1' || ALLOW_LOCAL_EMAIL_LINKS === 1 || ALLOW_LOCAL_EMAIL_LINKS === true);
    } else {
        $envAllow = getenv('ALLOW_LOCAL_EMAIL_LINKS');
        if ($envAllow !== false) {
            $allowLocalLinks = ($envAllow === '1' || strtolower((string)$envAllow) === 'true');
        }
    }

    // Default live base used only if BASE_URL is missing or unsafe.
    // Can be overridden via .env for Hostinger pathing.
    $liveBaseUrl = '';

    if (defined('EMAIL_LIVE_BASE_URL')) {
        $liveBaseUrl = trim((string) EMAIL_LIVE_BASE_URL);
    } else {
        $envLive = getenv('EMAIL_LIVE_BASE_URL');
        if ($envLive !== false) {
            $liveBaseUrl = trim((string) $envLive);
        }
    }

    if ($liveBaseUrl === '') {
        // Hostinger often serves the app under /public unless the document root is set to /public
        $liveBaseUrl = 'http://localhost:8000';
    }

    $liveBaseUrl = rtrim($liveBaseUrl, '/');

    if ($baseUrl === '') {
        $baseUrl = $liveBaseUrl;
    }

    $lower = strtolower($baseUrl);
    if ((strpos($lower, 'localhost') !== false || strpos($lower, '127.0.0.1') !== false) && !$allowLocalLinks) {
        $baseUrl = $liveBaseUrl;
    }

    $baseUrl = rtrim($baseUrl, '/');

    $brandName = defined('BRAND_NAME') ? BRAND_NAME : 'DemoBrand';

    $logoUrl = defined('BRAND_LOGO_EMAIL_URL') ? BRAND_LOGO_EMAIL_URL
             : (defined('BRAND_LOGO_URL') ? BRAND_LOGO_URL : '/assets/img/logo-email.png');
        if (isset($logoUrl[0]) && $logoUrl[0] === '/') {
        $logoUrl = $baseUrl . $logoUrl;
    }

     // Email banner background image (must be one of the landing backgrounds)
      $bannerBgUrl = '/assets/img/landing-bg-desktop.webp';
           if (isset($bannerBgUrl[0]) && $bannerBgUrl[0] === '/') {
          $bannerBgUrl = $baseUrl . $bannerBgUrl;
      }
    
    // Fixed header (banner) height in px (independent knob)
    $headerBannerHeight = 65;

    // Header inner vertical padding (top/bottom) in px (independent knob)
    $headerPadY = 12;

    // Logo height in px (independent knob)
    $headerLogoHeight = 50;

    // Wordmark font size + vertical offset in px (independent knobs)
    $headerWordmarkFontSize = 20;
    $headerWordmarkOffsetY  = 37;

    // --- Email spacing knobs (px) ---
    $gapHeadingSalutation   = 26; // H1 ↔ salutation
    $gapThankYouBox         = 26; // thank-you/copy ↔ info box
    $gapBoxButton           = 26; // info box ↔ button
    $gapButtonLink          = 8;  // button ↔ fallback link text
    $gapLinkInfo            = 14; // fallback link ↔ information text
    $gapInfoSignature       = 30; // information text ↔ signature
    $gapSignatureFooter     = 16; // signature ↔ footer

    // --- Signature logo knobs ---
    $sigLogoHeight     = 19;  // px
    $sigLogoOffsetY    = -9;  // px (negative lifts it up)
    $sigLogoOffsetX    = 0;   // px (left padding inside logo cell)

    $support       = defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'support@example.test';
    $challengeName = defined('CHALLENGE_NAME') ? CHALLENGE_NAME : (defined('BOOK_NAME') ? BOOK_NAME : 'Demo Challenge');
    $unsubscribeUrl = defined('UNSUBSCRIBE_URL') ? UNSUBSCRIBE_URL : 'https://example.test/unsubscribe';

    // Language detection: session first, then cookie, default EN
    $lang = 'en';
    if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['en', 'de'], true)) {
        $lang = $_SESSION['lang'];
    } elseif (isset($_COOKIE['demo_lang']) && in_array($_COOKIE['demo_lang'], ['en', 'de'], true)) {
        $lang = $_COOKIE['demo_lang'];
    }

    // Privacy Policy URLs (language-specific)
    $privacyUrlEn = $baseUrl . '/privacy-policy.php';
    $privacyUrlDe = $baseUrl . '/privacy-policy-de.php';
    $privacyUrl   = ($lang === 'de') ? $privacyUrlDe : $privacyUrlEn;

    // Professional link text (don’t show long URLs in the email)
    $privacyLinkText = ($lang === 'de') ? 'Datenschutzerklärung' : 'Privacy Policy';

    // Language detection: session first, then cookie, default EN
    $lang = 'en';
    if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['en', 'de'], true)) {
        $lang = $_SESSION['lang'];
    } elseif (isset($_COOKIE['demo_lang']) && in_array($_COOKIE['demo_lang'], ['en', 'de'], true)) {
        $lang = $_COOKIE['demo_lang'];
    }

    // Subject defaults (backwards-compatible with existing EMAIL_SUBJECT)
    $subjectEn = defined('EMAIL_SUBJECT_EN')
        ? EMAIL_SUBJECT_EN
        : "Your {$brandName} participation confirmation ✅";

    $subjectDe = defined('EMAIL_SUBJECT_DE')
        ? EMAIL_SUBJECT_DE
        : "Deine {$brandName} Teilnahmebestätigung ✅";

    $safeFirst = htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8');

    $successUrl = rtrim($baseUrl, '/');

$inkBlue   = '#0F3558';
$inkOrange = '#FE8215';
$bgSoft    = '#f3f6fb';
$border    = '#dbe5f2';

$cardBorder = $inkOrange; // outer card + banner outline

// --- Card frame knobs Border Style---
$cardBorderWidth = 1;     // px (e.g., 1, 2, 3)
$cardBorderAlpha = 0.40;  // 0.00–1.00 (e.g., 0.85 = slightly transparent)

// Use rgba for clients that support it, but keep solid fallback for clients that don’t.
$cardBorderColor = 'rgba(254,130,21,' . $cardBorderAlpha . ')';

    if ($lang === 'de') {
        $subject = $subjectDe;
       $bodyHtml = <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body style="margin:0;padding:0;background:{$bgSoft};">

    <!-- Preheader (email preview text) -->
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    Zugangsseite nochmal öffnen? Ein Klick reicht. 🎉
  </div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:{$bgSoft};padding:24px 0;">
    <tr>
      <td align="center" style="padding:0 12px;">
        <table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;">

          <!-- Header bar -->
          <tr>
            <td height="{$headerBannerHeight}" background="{$bannerBgUrl}" bgcolor="{$inkBlue}"
                style="height:{$headerBannerHeight}px;background-color:{$inkBlue};background-image:url('{$bannerBgUrl}');background-position:center;background-repeat:no-repeat;background-size:cover;border:{$cardBorderWidth}px solid {$inkOrange};border-color:{$cardBorderColor};border-bottom:0;border-radius:16px 16px 0 0;overflow:hidden;background-clip:padding-box;">
              <table role="presentation" width="100%" height="{$headerBannerHeight}" cellpadding="0" cellspacing="0" border="0"
                    style="width:100%;height:{$headerBannerHeight}px;background-color:rgba(15,53,88,0.25);border-radius:16px 16px 0 0;overflow:hidden;">
                <tr>
                  <td height="{$headerBannerHeight}" style="height:{$headerBannerHeight}px;padding:{$headerPadY}px 18px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td style="vertical-align:middle;">
                          <img alt="{$brandName}" src="{$logoUrl}" height="{$headerLogoHeight}"
                              style="height:{$headerLogoHeight}px;width:auto;display:block;line-height:0;border:0;outline:none;text-decoration:none;">
                        </td>

                        <td style="vertical-align:middle;text-align:right;white-space:nowrap;">
                          <table role="presentation" align="right" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                              <td height="{$headerWordmarkOffsetY}" style="font-size:1px;line-height:1px;">&nbsp;</td>
                            </tr>
                            <tr>
                              <td style="vertical-align:middle;text-align:right;font-family:Helvetica,Arial,sans-serif;font-size:{$headerWordmarkFontSize}px;line-height:1;white-space:nowrap;">
                                <span style="font-weight:900;letter-spacing:.2px;">
                                  <span style="color:#ffffff;">ink</span><span style="color:#FE8215;">IQ</span><span style="color:#ffffff;opacity:.95;"> Challenge</span>
                                </span>
                              </td>
                            </tr>
                          </table>
                        </td>

                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Body card -->
          <tr>
            <td style="background:#ffffff;border:{$cardBorderWidth}px solid {$inkOrange};border-color:{$cardBorderColor};border-top:0;border-radius:0 0 16px 16px;padding:22px 22px 18px 22px;">
              <h1 style="margin:0 0 {$gapHeadingSalutation}px 0;font-family:Helvetica,Arial,sans-serif;font-size:22px;line-height:1.25;color:#000000;font-weight:800;">
                Danke fürs Mitmachen — du hast’s geschafft! 🎉
              </h1>

              <p style="margin:0 0 12px 0;font-family:Helvetica,Arial,sans-serif;font-size:16px;line-height:1.55;color:#000000;">
                Hallo {$safeFirst},
              </p>

              <p style="margin:0 0 12px 0;font-family:Helvetica,Arial,sans-serif;font-size:16px;line-height:1.55;color:#000000;">
                mega cool, dass du dich für ein <strong>DemoBrand Rätselbuch</strong> entschieden und bei der <strong>„{$challengeName}“</strong> mitgemacht hast.
              </p>

              <p style="margin:0 0 12px 0;font-family:Helvetica,Arial,sans-serif;font-size:16px;line-height:1.55;color:#000000;">
                Die meisten holen sich Zertifikat + Bonus sofort ab. Wenn du das schon getan hast: top. Wenn nicht: kein Problem — die <strong>Zugangsseite</strong> ist nur einen Klick entfernt.
              </p>

              <p style="margin:0;font-family:Helvetica,Arial,sans-serif;font-size:16px;line-height:1.55;color:#000000;">
                Und wenn du gerade warmgelaufen bist: Das nächste <strong>DemoBrand Rätselbuch</strong> wartet schon mit neuen Herausforderungen auf dich. 🧩
              </p>

              <!-- Callout -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:{$gapThankYouBox}px 0 0 0;">
                <tr>
                  <td style="background:#f8fafc;border:1px solid {$border};border-radius:12px;padding:14px;">
                    <p style="margin:0;font-family:Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5;color:#000000;">
                      <strong style="color:{$inkBlue};">Gut zu wissen:</strong>
                      Die Download-Links verfallen nach ca. <strong>30 Minuten</strong>. Du kannst die Zugangsseite aber jederzeit erneut öffnen, um neue Links zu generieren und deine Belohnung herunterzuladen.
                    </p>
                  </td>
                </tr>
              </table>
              
              <!-- Button DE -->
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:{$gapBoxButton}px auto {$gapButtonLink}px auto;">                
                <tr>
                  <td align="center" bgcolor="{$inkOrange}" style="border:1.5px solid {$inkBlue};border-radius:8px;">
                    <a href="{$successUrl}" style="display:inline-block;padding:12px 18px;font-family:Helvetica,Arial,sans-serif;font-size:18px;font-weight:800;color:#ffffff;text-decoration:none;border-radius:12px;">
                      Zugangsseite öffnen
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 14px 0;font-family:Helvetica,Arial,sans-serif;font-size:15px;line-height:1.55;color:#000000;">
                Wenn du diese Anfrage nicht gestellt hast, kannst du diese E-Mail ignorieren oder uns kontaktieren.
              </p>
              
              <!-- Signature -->
             <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:{$gapInfoSignature}px 0 0 0;">
              <tr>
                <td style="font-family:Helvetica,Arial,sans-serif;font-size:15px;line-height:1.4;color:#000000;padding:0 6px 0 0;vertical-align:middle;white-space:nowrap;">
                  — Dein
                </td>
                <td style="padding:0 0 0 {$sigLogoOffsetX}px;vertical-align:middle;white-space:nowrap;">
                  <img alt="DemoBrand" src="{$logoUrl}"
                      style="height:{$sigLogoHeight}px;width:auto;display:inline-block;vertical-align:middle;margin-top:{$sigLogoOffsetY}px;border:0;outline:none;text-decoration:none;">
                </td>
                <td style="font-family:Helvetica,Arial,sans-serif;font-size:15px;line-height:1.4;color:#000000;padding:0 0 0 6px;vertical-align:middle;white-space:nowrap;">
                  Team
                </td>
              </tr>
            </table>

              <p style="margin:{$gapSignatureFooter}px 0 0 0;font-family:Helvetica,Arial,sans-serif;font-size:12px;line-height:1.6;color:#000000;">
                Hilfe nötig? {$support}<br>
                Datenschutz: <a href="{$privacyUrl}" style="color:#000000;">{$privacyLinkText}</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

    } else {
        $subject = $subjectEn;
       $bodyHtml = <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body style="margin:0;padding:0;background:{$bgSoft};">

  <!-- Preheader (email preview text) -->
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    Need the Access Page again? One click gets you back in. 🎉
  </div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:{$bgSoft};padding:24px 0;">
    <tr>
      <td align="center" style="padding:0 12px;">
        <table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;">

        <!-- Header bar -->
        <tr>
          <td height="{$headerBannerHeight}" background="{$bannerBgUrl}" bgcolor="{$inkBlue}"
              style="height:{$headerBannerHeight}px;background-color:{$inkBlue};background-image:url('{$bannerBgUrl}');background-position:center;background-repeat:no-repeat;background-size:cover;border:{$cardBorderWidth}px solid {$inkOrange};border-color:{$cardBorderColor};border-bottom:0;border-radius:16px 16px 0 0;overflow:hidden;background-clip:padding-box;">
            <table role="presentation" width="100%" height="{$headerBannerHeight}" cellpadding="0" cellspacing="0" border="0"
                  style="width:100%;height:{$headerBannerHeight}px;background-color:rgba(15,53,88,0.25);border-radius:16px 16px 0 0;overflow:hidden;">
              <tr>
                <td height="{$headerBannerHeight}" style="height:{$headerBannerHeight}px;padding:{$headerPadY}px 18px;">
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td style="vertical-align:middle;">
                        <img alt="{$brandName}" src="{$logoUrl}" height="{$headerLogoHeight}"
                            style="height:{$headerLogoHeight}px;width:auto;display:block;line-height:0;border:0;outline:none;text-decoration:none;">
                      </td>

                      <td style="vertical-align:middle;text-align:right;white-space:nowrap;">
                        <table role="presentation" align="right" cellpadding="0" cellspacing="0" border="0">
                          <tr>
                            <td height="{$headerWordmarkOffsetY}" style="font-size:1px;line-height:1px;">&nbsp;</td>
                          </tr>
                          <tr>
                            <td style="vertical-align:middle;text-align:right;font-family:Helvetica,Arial,sans-serif;font-size:{$headerWordmarkFontSize}px;line-height:1;white-space:nowrap;">
                              <span style="font-weight:900;letter-spacing:.2px;">
                                <span style="color:#ffffff;">ink</span><span style="color:#FE8215;">IQ</span><span style="color:#ffffff;opacity:.95;"> Challenge</span>
                              </span>
                            </td>
                          </tr>
                        </table>
                      </td>

                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>

          <!-- Body card -->
          <tr>
            <td style="background:#ffffff;border:{$cardBorderWidth}px solid {$inkOrange};border-color:{$cardBorderColor};border-top:0;border-radius:0 0 16px 16px;padding:22px 22px 18px 22px;">
              <h1 style="margin:0 0 {$gapHeadingSalutation}px 0;font-family:Helvetica,Arial,sans-serif;font-size:22px;line-height:1.25;color:#000000;font-weight:800;">
                Thanks for joining — you did it! 🎉
              </h1>

              <p style="margin:0 0 12px 0;font-family:Helvetica,Arial,sans-serif;font-size:16px;line-height:1.55;color:#000000;">
                Hi {$safeFirst},
              </p>

              <p style="margin:0 0 12px 0;font-family:Helvetica,Arial,sans-serif;font-size:16px;line-height:1.55;color:#000000;">
                really cool that you chose an <strong>DemoBrand activity book</strong> and jumped into the <strong>“{$challengeName}”</strong>.
              </p>

              <p style="margin:0 0 12px 0;font-family:Helvetica,Arial,sans-serif;font-size:16px;line-height:1.55;color:#000000;">
                Most people grab their Certificate + Bonus right away. If you already did: awesome. If not: no stress — the <strong>Access Page</strong> is one click away.
              </p>

              <p style="margin:0;font-family:Helvetica,Arial,sans-serif;font-size:16px;line-height:1.55;color:#000000;">
                And if you’re already warmed up: the next <strong>exciting DemoBrand challenge</strong> is waiting between the covers. 🧩
              </p>

              <!-- Callout -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:{$gapThankYouBox}px 0 0 0;">
                <tr>
                  <td style="background:#f8fafc;border:1px solid {$border};border-radius:12px;padding:14px;">
                    <p style="margin:0;font-family:Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5;color:#000000;">
                     <strong style="color:{$inkBlue};">Good to know:</strong>
                      Download links expire after about <strong>30 minutes</strong>, but you can reopen the Access Page anytime to generate fresh links and download your reward.
                    </p>
                  </td>
                </tr>
              </table>

              <!-- Button EN -->
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:{$gapBoxButton}px auto {$gapButtonLink}px auto;">                
                <tr>
                  <td align="center" bgcolor="{$inkOrange}" style="border:1.5px solid {$inkBlue};border-radius:8px;">
                    <a href="{$successUrl}" style="display:inline-block;padding:12px 18px;font-family:Helvetica,Arial,sans-serif;font-size:18px;font-weight:800;color:#ffffff;text-decoration:none;border-radius:12px;">
                      Open Access Page
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 14px 0;font-family:Helvetica,Arial,sans-serif;font-size:15px;line-height:1.55;color:#000000;">
                If you didn’t request this, you can ignore this email or contact us.
              </p>

              <!-- Signature -->
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:{$gapInfoSignature}px 0 0 0;">
                <tr>
                  <td style="font-family:Helvetica,Arial,sans-serif;font-size:15px;line-height:1.4;color:#000000;padding:0 6px 0 0;vertical-align:middle;white-space:nowrap;">
                    — Your
                  </td>
                  <td style="padding:0 0 0 {$sigLogoOffsetX}px;vertical-align:middle;white-space:nowrap;">
                    <img alt="DemoBrand" src="{$logoUrl}"
                        style="height:{$sigLogoHeight}px;width:auto;display:inline-block;vertical-align:middle;margin-top:{$sigLogoOffsetY}px;border:0;outline:none;text-decoration:none;">
                  </td>
                  <td style="font-family:Helvetica,Arial,sans-serif;font-size:15px;line-height:1.4;color:#000000;padding:0 0 0 6px;vertical-align:middle;white-space:nowrap;">
                    Team
                  </td>
                </tr>
              </table>

              <p style="margin:{$gapSignatureFooter}px 0 0 0;font-family:Helvetica,Arial,sans-serif;font-size:12px;line-height:1.6;color:#000000;">
                Need help? {$support}<br>
                Privacy: <a href="{$privacyUrl}" style="color:#000000;">{$privacyLinkText}</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    // --- unchanged sending logic below ---

    // --- DEV PACK: disable outbound email when EMAIL_DISABLE=1 ---
    $disableEmail = false;
    if (defined('EMAIL_DISABLE')) {
        $disableEmail = (EMAIL_DISABLE === '1' || EMAIL_DISABLE === 1 || EMAIL_DISABLE === true);
    } else {
        $envDisable = getenv('EMAIL_DISABLE');
        if ($envDisable !== false) {
            $envDisable = strtolower(trim((string)$envDisable));
            $disableEmail = ($envDisable === '1' || $envDisable === 'true' || $envDisable === 'yes');
        }
    }
    if ($disableEmail) {
        $dir = (defined('STORAGE_DIR') ? rtrim((string)STORAGE_DIR,'/') : (defined('PROJ_ROOT') ? rtrim((string)PROJ_ROOT,'/') . '/storage' : __DIR__)) . '/emails/' . (defined('BOOK_ID') ? BOOK_ID : 'dev');
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $stamp = date('Ymd-His');
        $fn = $dir . '/email_' . $stamp . '_' . substr(sha1($to . '|' . $subject), 0, 10) . '.html';
        $meta = "<!-- DEV PACK EMAIL\nTo: {$to}\nSubject: {$subject}\nGenerated: " . date('c') . "\n-->\n";
        @file_put_contents($fn, $meta . $bodyHtml);
        return;
    }


    $useSmtp  = defined('SMTP_USE') ? (bool)SMTP_USE : true;
    $hasCreds = defined('SMTP_HOST') && SMTP_HOST !== ''
             && defined('SMTP_USERNAME') && SMTP_USERNAME !== ''
             && defined('SMTP_PASSWORD') && SMTP_PASSWORD !== '';

    if ($useSmtp && $hasCreds) {
        if (!function_exists('smtp_send')) {
            @require_once PROJ_ROOT . '/code/app/lib/Smtp.php';
        }
        $fromAddr = defined('EMAIL_FROM') ? EMAIL_FROM : 'demo@example.test';
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : $brandName;
        $replyTo  = 'no-reply@example.test';
        @smtp_send($to, $subject, $bodyHtml, $fromAddr, $fromName, $replyTo, 'text/html');
        return;
    }

    // Fallback to PHP mail()
    $fromAddr = defined('EMAIL_FROM') ? EMAIL_FROM : 'demo@example.test';
    $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : $brandName;
    $headers = [
        'From: ' . ($fromName ? "{$fromName} <{$fromAddr}>" : $fromAddr),
        'Reply-To: no-reply@example.test',
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];
    @mail($to, $subject, $bodyHtml, implode("\r\n", $headers));
}}