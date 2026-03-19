<?php
declare(strict_types=1);

// ---- Minimal .env loader (reads <project>/.env) ----
$__root = dirname(__DIR__, 2);             // project root from /code/app/config.php
$__env  = $__root . '/.env';
if (is_file($__env) && is_readable($__env)) {
    foreach (file($__env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $__line) {
        if ($__line === '' || $__line[0] === '#') continue;
        if (strpos($__line, '=') === false) continue;
        [$__k, $__v] = array_map('trim', explode('=', $__line, 2));
        $__v = trim($__v, "\"'");          // strip optional quotes
        putenv("$__k=$__v");
        $_ENV[$__k]    = $__v;
        $_SERVER[$__k] = $__v;
    }
}
unset($__root, $__env, $__line, $__k, $__v);

/*
|--------------------------------------------------------------------------
| Project – minimal config (paths, email, SMTP)
| File: /code/app/config.php
|--------------------------------------------------------------------------
| Source of truth for:
| - Project paths under /storage (single canonical tree)
| - Per-book config (BOOK_ID, bonus file)
| - App settings (cooldown, branding)
| - SMTP settings (from environment; do NOT hard-code secrets)
|--------------------------------------------------------------------------
*/

// ==============================
// Project root & storage paths
// ==============================

// Project root = <repo root> (…/demo-certflow_v2025-10-08), not /code or /public
if (!defined('PROJ_ROOT')) {
    define('PROJ_ROOT', realpath(__DIR__ . '/../../'));
}

// Canonical storage root under project root
if (!defined('STORAGE_DIR')) {
    define('STORAGE_DIR', PROJ_ROOT . '/storage');
}
if (!defined('LOGS_DIR')) {
    define('LOGS_DIR', STORAGE_DIR . '/logs');
}
if (!defined('TMP_DIR')) {
    define('TMP_DIR', STORAGE_DIR . '/tmp');
}

// ==============================
// Per-book configuration
// ==============================

if (!defined('BOOK_ID')) {
    $envBookId = getenv('BOOK_ID');
    define('BOOK_ID', ($envBookId !== false && $envBookId !== '') ? $envBookId : 'demo-challenge-001');
}

if (!defined('CHALLENGE_NAME')) {
    $envChallenge = getenv('CHALLENGE_NAME');
    if ($envChallenge === false || $envChallenge === '') {
        // Backward compatibility: allow old BOOK_NAME in .env
        $envChallenge = getenv('BOOK_NAME');
    }
    define('CHALLENGE_NAME', ($envChallenge !== false && $envChallenge !== '') ? $envChallenge : 'Demo Challenge');
}

// Backward compatibility for existing templates/pages:
if (!defined('BOOK_NAME')) {
    define('BOOK_NAME', CHALLENGE_NAME);
}

if (!defined('COPYRIGHT_YEAR')) {
    $envYear = getenv('COPYRIGHT_YEAR');
    define('COPYRIGHT_YEAR', ($envYear !== false && $envYear !== '') ? (int)$envYear : (int)date('Y'));
}

if (!defined('SECRET_KEY_CODE')) {
    $env = getenv('SECRET_KEY_CODE');
    $env = is_string($env) ? $env : '';
    // normalize: remove spaces, uppercase
    $env = strtoupper(preg_replace('/\s+/', '', $env));
    define('SECRET_KEY_CODE', $env);
}

// ==============================
// Storage subpaths (derived)
// ==============================

// Certificates root + per-book dir (canonical!)
if (!defined('CERTS_DIR'))      { define('CERTS_DIR',      STORAGE_DIR . '/certs'); }
if (!defined('CERTS_BOOK_DIR')) { define('CERTS_BOOK_DIR', CERTS_DIR . '/' . BOOK_ID); }

// Requests + Bonus (under same canonical storage root)
if (!defined('REQUESTS_DIR')) { define('REQUESTS_DIR', STORAGE_DIR . '/requests/' . BOOK_ID); }
if (!defined('BONUS_DIR'))    { define('BONUS_DIR',    STORAGE_DIR . '/bonus/' . BOOK_ID); }
if (!defined('BONUS_FILENAME')) { define('BONUS_FILENAME', getenv('BONUS_FILENAME') ?: 'bonus.pdf'); }
if (!defined('BONUS_FILE'))     { define('BONUS_FILE', BONUS_DIR . '/' . BONUS_FILENAME); }

// Create needed folders (idempotent)
foreach ([STORAGE_DIR, LOGS_DIR, TMP_DIR, CERTS_DIR, CERTS_BOOK_DIR, REQUESTS_DIR, BONUS_DIR] as $d) {
    if (!is_dir($d)) { @mkdir($d, 0775, true); }
}

// ==============================
// App / Branding
// ==============================

if (!defined('BRAND_NAME')) {
    $envBrand = getenv('BRAND_NAME');
    define('BRAND_NAME', ($envBrand !== false && $envBrand !== '') ? $envBrand : 'DemoBrand');
}
/// Adjust BASE_URL per environment (dev/prod)
if (!defined('BASE_URL')) {
    $env = getenv('BASE_URL');
    define('BASE_URL', $env !== false ? rtrim($env, '/') : 'http://localhost:8000');
}
if (!defined('BRAND_LOGO_URL')) { define('BRAND_LOGO_URL', BASE_URL . '/assets/img/logo-email.png'); }

// Certificate generator endpoint (public URL)
// Prefer explicit env override; else fall back to BASE_URL
if (!defined('CERT_GENERATOR_URL')) {
    $envGen = getenv('CERT_GENERATOR_URL');
    if ($envGen !== false && $envGen !== '') {
        define('CERT_GENERATOR_URL', $envGen); // should already end with ?name=
    } else {
        define('CERT_GENERATOR_URL', BASE_URL . '/cert_engine.php?name=');
    }
}

// Cooldown (can be overridden by env COOLDOWN_MINUTES)
if (!defined('COOLDOWN_MINUTES')) {
    $envCooldown = getenv('COOLDOWN_MINUTES');
    define('COOLDOWN_MINUTES', $envCooldown !== false ? (int)$envCooldown : 30);
}

// ==============================
// Privacy policy metadata
// ==============================
if (!defined('PRIVACY_LAST_UPDATED')) {
    // Canonical ISO date – change this ONE value when the policy changes
    $envPriv = getenv('PRIVACY_LAST_UPDATED');
    define('PRIVACY_LAST_UPDATED', ($envPriv !== false && $envPriv !== '') ? $envPriv : '2025-12-15');
}

/**
 * Format the privacy "Last updated" date according to language.
 *
 * @param string $lang 'en' or 'de'
 */
if (!function_exists('format_privacy_last_updated')) {
    function format_privacy_last_updated(string $lang = 'en'): string
    {
        if (!defined('PRIVACY_LAST_UPDATED')) {
            return '';
        }

        try {
            $dt = new DateTimeImmutable(PRIVACY_LAST_UPDATED);
        } catch (Throwable $e) {
            // Fallback: if parsing fails, just show the raw constant
            return (string) PRIVACY_LAST_UPDATED;
        }

        if ($lang === 'de') {
            // German style, e.g. 12.12.2025
            return $dt->format('d.m.Y');
        }

        // Default English, e.g. December 12, 2025
        return $dt->format('F j, Y');
    }
}

// ==============================
// Certificate rendering (engine & output)
// ==============================
if (!defined('PDF_ENGINE')) {
    $envPdf = getenv('PDF_ENGINE');
    // Allowed: dompdf | imagick | placeholder
    define('PDF_ENGINE', $envPdf !== false ? strtolower($envPdf) : 'dompdf');
}
if (!defined('CERT_OUTPUT_FORMAT')) {
    $envFmt = getenv('CERT_OUTPUT_FORMAT');
    // Allowed: pdf | png
    define('CERT_OUTPUT_FORMAT', $envFmt !== false ? strtolower($envFmt) : 'pdf');
}

// ==============================
// App secret for tokens (required by /code/app/lib/Token.php)
// ==============================

if (!defined('APP_SECRET')) {
    // Prefer environment variable; fall back to a dev-safe default (change on production!)
    $envSecret = getenv('APP_SECRET');
    define('APP_SECRET', $envSecret !== false ? $envSecret : 'change-me-dev-secret-please');
}

// ==============================
// Unsubscribe / Marketing suppression (GLOBAL)
// ==============================

// Public unsubscribe URL (used in email footer as fallback).
// If not set, keep the existing universal fallback behavior.
if (!defined('UNSUBSCRIBE_URL')) {
    $envUnsubUrl = getenv('UNSUBSCRIBE_URL');
    define(
        'UNSUBSCRIBE_URL',
        ($envUnsubUrl !== false && $envUnsubUrl !== '') ? rtrim($envUnsubUrl, '/') : 'https://example.test/unsubscribe'
    );
}

// Dedicated secret for unsubscribe tokens (HMAC).
// If not set, fall back to APP_SECRET so local dev still works.
if (!defined('UNSUBSCRIBE_SECRET')) {
    $envUnsubSecret = getenv('UNSUBSCRIBE_SECRET');
    define(
        'UNSUBSCRIBE_SECRET',
        ($envUnsubSecret !== false && $envUnsubSecret !== '') ? $envUnsubSecret : (defined('APP_SECRET') ? APP_SECRET : 'change-me-dev-secret-please')
    );
}

// Global marketing suppression list storage (append-only).
if (!defined('SUPPRESSION_FILE')) {
    $envSuppFile = getenv('SUPPRESSION_FILE');
    define(
        'SUPPRESSION_FILE',
        ($envSuppFile !== false && $envSuppFile !== '') ? $envSuppFile : (STORAGE_DIR . '/suppression/marketing_suppression.jsonl')
    );
}
if (!defined('SUPPRESSION_DIR')) {
    define('SUPPRESSION_DIR', dirname(SUPPRESSION_FILE));
}

// Ensure suppression folder exists (idempotent)
if (!is_dir(SUPPRESSION_DIR)) { @mkdir(SUPPRESSION_DIR, 0775, true); }


// ==============================
// Email / SMTP (read from environment; supports legacy keys)
// ==============================

if (!defined('SMTP_USE')) {
    $envUse = getenv('SMTP_USE');
    define('SMTP_USE', $envUse !== false ? filter_var($envUse, FILTER_VALIDATE_BOOLEAN) : true);
}

// HOST / PORT / SECURE
$envHost   = getenv('SMTP_HOST')   ?: 'smtp.hostinger.com';
$envPort   = getenv('SMTP_PORT')   ?: 465;         // default 465
$envSecure = getenv('SMTP_SECURE') ?: 'ssl';       // 'ssl' or 'tls'

// USER / PASS (with legacy fallbacks)
$envUser = getenv('SMTP_USERNAME');
if ($envUser === false || $envUser === '') { $envUser = getenv('SMTP_USER') ?: ''; }

$envPass = getenv('SMTP_PASSWORD');
if ($envPass === false || $envPass === '') { $envPass = getenv('SMTP_PASS') ?: ''; }

// FROM NAME / FROM ADDRESS (with legacy fallback)
$envFromName = getenv('SMTP_FROM_NAME') ?: (defined('BRAND_NAME') ? BRAND_NAME : 'DemoBrand');

$envEmailFrom = getenv('EMAIL_FROM');
if ($envEmailFrom === false || $envEmailFrom === '') { $envEmailFrom = getenv('SMTP_FROM') ?: 'challenge@example.test'; }

if (!defined('SMTP_HOST'))      define('SMTP_HOST',      $envHost);
if (!defined('SMTP_PORT'))      define('SMTP_PORT',      (int)$envPort);
if (!defined('SMTP_SECURE'))    define('SMTP_SECURE',    $envSecure);
if (!defined('SMTP_USERNAME'))  define('SMTP_USERNAME',  $envUser);
if (!defined('SMTP_PASSWORD'))  define('SMTP_PASSWORD',  $envPass);
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', $envFromName);

if (!defined('EMAIL_FROM'))     define('EMAIL_FROM',     $envEmailFrom);
if (!defined('EMAIL_SUBJECT'))  define('EMAIL_SUBJECT',  getenv('EMAIL_SUBJECT') ?: "You're all set — thanks!!");

// Optional language-specific subjects (used by bilingual emails)
$__subEn = getenv('EMAIL_SUBJECT_EN');
if (!defined('EMAIL_SUBJECT_EN') && $__subEn !== false && $__subEn !== '') {
    define('EMAIL_SUBJECT_EN', $__subEn);
}

$__subDe = getenv('EMAIL_SUBJECT_DE');
if (!defined('EMAIL_SUBJECT_DE') && $__subDe !== false && $__subDe !== '') {
    define('EMAIL_SUBJECT_DE', $__subDe);
}
unset($__subEn, $__subDe);

if (!defined('SUPPORT_EMAIL'))  define('SUPPORT_EMAIL',  getenv('SUPPORT_EMAIL') ?: 'support@example.test');

// ==============================
// Quotes used on success page
// ==============================

$QUOTES = [
    'secret' => [
        'Proud moments deserve proof.',
        'Every solved mystery is a story earned.',
        'Great work! Keep going.',
    ],
];

// ==============================
// Helpers
// ==============================

function validate_key_code(string $code): bool {
    // 15–35 chars A–Z 0–9 only
    return (bool) preg_match('/^[A-Za-z0-9]{15,35}$/', $code);
}

function sanitize_name(?string $s): string {
    $s = trim((string)$s);
    // keep letters, spaces, apostrophes, hyphens (Unicode-safe)
    $s = preg_replace("/[^\p{L} \-']/u", '', $s) ?? '';
    $s = preg_replace('/\s+/', ' ', $s) ?? '';
    return $s;
}

function slug_ascii(string $s): string {
    $x = $s;
    if (function_exists('iconv')) {
        $x = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $x);
    }
    $x = strtolower((string)$x);
    $x = preg_replace('/[^a-z0-9]+/', '-', $x) ?? '';
    return trim($x, '-');
}

function email_key(string $email): string {
    // stable, URL-safe key from email (lowercased)
    $email = strtolower(trim($email));
    return rtrim(strtr(base64_encode($email), '+/', '-_'), '=');
}