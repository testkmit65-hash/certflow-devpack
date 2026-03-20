<?php
declare(strict_types=1);
require_once __DIR__ . '/../code/app/config.php';

header('Cache-Control: private, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: text/html; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

/* ---------------- I18N ---------------- */
(function () {
  $projI18n = dirname(__DIR__) . '/code/app/i18n.php';
  if (is_file($projI18n)) { @require_once $projI18n; }

  if (!function_exists('demo_get_lang')) {
    function demo_get_lang(): string {
      $c = strtolower((string)($_COOKIE['demo_lang'] ?? 'en'));
      return in_array($c, ['en','de'], true) ? $c : 'en';
    }
  }
  if (!function_exists('t')) {
    function t(string $key): string {
      static $T = null;
      if ($T === null) {
        $en = [
          'success.heading'      => 'Congratulations,',
          'success.subtext'      => 'Great work! Keep going.',
          'name.fallback'        =>  'Friend',
          'btn.download_cert'    => 'Download Certificate',
          'btn.download_bonus'   => 'Download Secret Bonus',
          'success.links_expire' => 'Links expire in 30 minutes. You can resubmit the form later to refresh links.',
          'link.back'            => 'Back to start',
          'title'                => 'Success — DemoBrand',
        ];
        $de = [
          'success.heading'      => 'Glückwunsch,',
          'success.subtext'      => 'Tolle Leistung! Weiter so.',
          'name.fallback'        => 'Freund',
          'btn.download_cert'    => 'Zertifikat herunterladen',
          'btn.download_bonus'   => 'Geheimen Bonus herunterladen',
          'success.links_expire' => 'Links verfallen in 30 Minuten. Du kannst das Formular später erneut senden, um die Links zu erneuern.',
          'link.back'            => 'Zurück zum Start',
          'title'                => 'Erfolg — DemoBrand',
        ];
        $lang = demo_get_lang();
        $T = ($lang === 'de') ? array_replace($en, $de) : $en;
      }
      return $T[$key] ?? $key;
    }
  }
})();

$lang = demo_get_lang();

/* ---- helpers (adjusted) ---- */
@require_once __DIR__ . '/../code/app/lib/Utils.php';
@require_once __DIR__ . '/../code/app/lib/Token.php';

$rid = $_GET['rid'] ?? null;
$row = ($rid && function_exists('get_request_by_rid')) ? (get_request_by_rid($rid) ?: null) : null;
$PROJ_ROOT = dirname(__DIR__);

function demo_display_first_name(?string $s): string {
  $s = trim((string)$s);
if ($s === '') return function_exists('t') ? t('name.fallback') : 'Friend';

  // normalize whitespace
  $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

  // Keep user's casing if it looks intentional (mixed-case)
  $lower = mb_strtolower($s, 'UTF-8');
  $upper = mb_strtoupper($s, 'UTF-8');
  if ($s !== $lower && $s !== $upper) return $s;

  // Otherwise apply gentle title-casing but keep common particles lower-case
  $particles = ['da','de','del','della','der','den','di','do','dos','das','du','el','la','le','van','von','zu','zur','zum','al','bin','ibn'];
  $words = preg_split('/\s+/u', $lower);
  foreach ($words as $i => $w) {
    if ($i > 0 && in_array($w, $particles, true)) {
      $words[$i] = $w; // keep lower
    } else {
      $words[$i] = mb_convert_case($w, MB_CASE_TITLE, 'UTF-8');
    }
  }
  return implode(' ', $words);
}

/* Recursively find first regex match within scalars of an array/object */
function demo_find_regex($haystack, string $pattern) {
  if (is_string($haystack)) {
    if (preg_match($pattern, $haystack, $m)) return $m[0];
    return null;
  }
  if (is_array($haystack) || is_object($haystack)) {
    $arr = is_object($haystack) ? get_object_vars($haystack) : $haystack;
    foreach ($arr as $v) {
      $found = demo_find_regex($v, $pattern);
      if ($found !== null) return $found;
    }
  }
  return null;
}

/* Find first value by keys in nested structures */
function demo_find_recursive($haystack, array $keys) {
  if (is_array($haystack) || is_object($haystack)) {
    $arr = is_object($haystack) ? get_object_vars($haystack) : $haystack;
    $lk  = array_map('strtolower', $keys);
    foreach ($arr as $k => $v) {
      $kl = is_string($k) ? strtolower($k) : $k;
      if (is_string($kl) && in_array($kl, $lk, true) && is_string($v) && trim($v) !== '') return trim($v);
      if (is_array($v) || is_object($v)) {
        $found = demo_find_recursive($v, $keys);
        if (is_string($found) && $found !== '') return $found;
      }
    }
  }
  return null;
}
function demo_pick_from_sources(array $sources, array $keys = [], ?string $regex = null): ?string {
  foreach ($sources as $src) {
    if ($src === null) continue;
    if ($keys)  { $v = demo_find_recursive($src, $keys); if (is_string($v) && $v !== '') return $v; }
    if ($regex) { $v = demo_find_regex($src, $regex);    if (is_string($v) && $v !== '') return $v; }
  }
  return null;
}
function demo_fs_resolve(string $p, string $root): ?string {
  $p = trim($p); if ($p === '') return null;
  $c = [$p];
  if ($p[0] === '/' && str_starts_with($p, '/storage/')) $c[] = $root . $p;
  if ($p[0] !== '/') $c[] = $root . '/' . ltrim($p, '/');
  foreach ($c as $cand) if (is_file($cand)) return $cand;
  return null;
}
function demo_find_pdf_path_pref(array|object|string|null $hay, string $prefer, string $root): ?string {
  $pref = strtolower($prefer); $found = null;
  $walk = function($n) use (&$walk, &$found, $pref){
    if ($found !== null) return;
    if (is_string($n)) {
      $s = trim($n);
      if ($s === '' || str_starts_with($s, 'http')) return;
      if (stripos($s, '.pdf') !== false) {
        $sl = strtolower($s);
        $isB = (str_contains($sl, '/bonus/') || str_contains($sl, 'bonus'));
        $isC = (str_contains($sl, '/certs/') || str_contains($sl, 'cert'));
        if ($pref === 'bonus' && $isB) { $found = $s; return; }
        if ($pref === 'cert'  && $isC && !$isB) { $found = $s; return; }
        if (!$isB && $pref === 'cert') { $found = $s; return; }
        if ($pref === 'bonus') { $found = $s; return; }
      }
      return;
    }
    if (is_array($n) || is_object($n)) {
      $arr = is_object($n) ? get_object_vars($n) : $n;
      foreach ($arr as $v) { $walk($v); if ($found !== null) return; }
    }
  };
  $walk($hay);
  return ($found !== null) ? demo_fs_resolve($found, $root) : null;
}

/* Assemble sources & personalize */
$flash = $_SESSION['flash'] ?? []; unset($_SESSION['flash']);
$inlineMsg = $flash['error'] ?? ($flash['success'] ?? null);

$stores = [$flash, $_SESSION['last_request'] ?? null, $_SESSION['request'] ?? null,
           $_SESSION['form'] ?? null, $_SESSION['form_data'] ?? null,
           $_SESSION['payload'] ?? null, $_SESSION['cert'] ?? null, $row, $_SESSION];

$firstName = demo_display_first_name(
  demo_pick_from_sources($stores, ['first_name','firstname','given_name','first','fname','name','full_name','customer_firstname','participant_firstname'])
);

/* Resolve download links */
$certUrl  = $certUrl  ?? $certificateUrl   ?? $downloadCertUrl  ?? $cert_link  ?? null;
$bonusUrl = $bonusUrl ?? $bonusUrlPublic   ?? $downloadBonusUrl ?? $bonus_link ?? null;

if (!$certUrl) {
  $certUrl = demo_pick_from_sources($stores, [
    'cert_url','certificate_url','download_certificate_url','download_cert_url','certlink','certificate_link','link_cert','certificate','cert'
  ], '#/download\.php\?[^"\']*(?:[&?]f=cert)\b[^"\']*#i');
}
if (!$bonusUrl) {
  $bonusUrl = demo_pick_from_sources($stores, [
    'bonus_url','download_bonus_url','secret_bonus_url','bonuslink','bonus_link','link_bonus','bonus'
  ], '#/download\.php\?[^"\']*(?:[&?]f=bonus)\b[^"\']*#i');
}

if (function_exists('make_token')) {
  $expires = time() + 30*60;
  if (!$certUrl) {
    $raw  = demo_pick_from_sources($stores, ['cert_pdf','certificate_path','certificate','cert','pdf','pdf_path','cert_file','cert_filepath','certificate_pdf']);
    $cert = $raw ? demo_fs_resolve($raw, $PROJ_ROOT) : null;
    if (!$cert) $cert = demo_find_pdf_path_pref($stores, 'cert', $PROJ_ROOT);
    if ($cert)  $certUrl  = '/download.php?t=' . rawurlencode(make_token($cert,  $expires));
  }
  if (!$bonusUrl) {
    $raw   = demo_pick_from_sources($stores, ['bonus_pdf','bonus_path','bonus','bonus_file','bonus_filepath']);
    $bonus = $raw ? demo_fs_resolve($raw, $PROJ_ROOT) : null;
    if (!$bonus && defined('BONUS_DIR') && is_dir(BONUS_DIR)) { foreach (glob(BONUS_DIR.'/*.pdf') as $cand) { $bonus = $cand; break; } }
    if (!$bonus) $bonus = demo_find_pdf_path_pref($stores, 'bonus', $PROJ_ROOT);
    if ($bonus) $bonusUrl = '/download.php?t=' . rawurlencode(make_token($bonus, $expires));
  }
}

$backHref = '/index.php';
?>
<!doctype html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= htmlspecialchars(t('title'), ENT_QUOTES, 'UTF-8') ?></title>
<link rel="prefetch" href="/privacy.php">
<style>
  :root{
    --ink-blue:#0F3558; --ink-orange:#FE8215; --white:#FFFFFF;
    --card-w:740px; --card-h:430px; --card-pad:20px;
    --footer-raise:38px;
    --fs-title:40px; --fs-motiv:18px; --fs-btn:32px; --fs-expiry:14px; --fs-back:18px;
    --card-offset-y: 55px;         /* + down / - up (card only) */

        /* Instant background placeholders (prevent blue first paint) */
    --bg-lqip-desktop: url("data:image/webp;base64,UklGRpgAAABXRUJQVlA4IIwAAACwBACdASpAACQAPzGGvleuqCYkqBqqqdAmCUAZjmv4AFS0Zi1u7k7bANk/Ih+AAPbiVue6h/oV3Z8Rdn/bzmz28bx/qvNfDmkW5gG1oP7ln9Uuhh+dvvq9WFzf1DrBuZhBQ8mMXyI4bOaO8nD16rZdk1CbWRB7hGwVG4QfS4zKQBHGiZZ/JICpMgAAAA==");
    --bg-lqip-mobile:  url("data:image/webp;base64,UklGRrYAAABXRUJQVlA4IKoAAABwBQCdASokAE4APzGCt1SuqCWjLNgMcdAmCWMAv7AAwxygPWHFpHLD3xupJ+Yqv5FTMTQAAPcDvToZzbX9CvWX302P+onYjxgTkjGijV050Jl6Xk9aXpIVfis6itElg4CvB4Ywz7OvWqKSaM60ViAKdTXPqmxliAnbrqYM45hQddUC9MSqXB7taYcxczhSuiDSaqB1SzvQ/HirHTZgK50/mw8hZxcSeggAAA==");
  }
  html[lang="de"] :root { --fs-motiv:17px; --fs-btn:31px; }

  html{
    background-color: #f7f9fc;
    overflow-x: hidden;
    -webkit-text-size-adjust: 100%;
    background-image: repeating-linear-gradient(
      -45deg,
      #f7f9fc,
      #f7f9fc 18px,
      #e2e8f0 18px,
      #e2e8f0 20px
    );
  }

  body{
    font-family:Helvetica,Arial,sans-serif; margin:0; padding:0;
    min-height:100vh; background:transparent; color:#fff;
    display:flex; flex-direction:column; overflow-x:hidden; position:relative; z-index:1;
  }
  @supports (min-height:100dvh){ body{ min-height:100dvh; } }

  .page-bg{
    position:fixed; inset:0;
    width: 100%; height: 100vh;
    height: 100dvh;
    background-color: #f7f9fc;
    background-image: repeating-linear-gradient(
      -45deg,
      #f7f9fc,
      #f7f9fc 18px,
      #e2e8f0 18px,
      #e2e8f0 20px
    );
    z-index:0; pointer-events:none; transform:translateZ(0);
  }

  .page-shell{ min-height:100vh; display:block; }
  .page-center{
    min-height: calc(100vh - 110px);
    display:grid; place-items:center;
    padding:16px; box-sizing:border-box; position:relative; z-index:1;
  }

  .inline-msg{
    position:fixed; top:16px; left:50%; transform:translateX(-50%);
    background:rgba(0,0,0,.65); border:1px solid rgba(255,255,255,.25);
    color:#fff; padding:10px 14px; border-radius:10px; font-size:15px; z-index:20;
    max-width:min(92vw,820px); text-align:center;
  }

  .success-card{
    position:relative; width:var(--card-w); height:var(--card-h);
    border-radius:30px; background:rgba(15,53,88,.70);
    padding:var(--card-pad); box-sizing:border-box;
    box-shadow:0 10px 50px rgba(0,0,0,.20);
    display:flex; flex-direction:column; align-items:center; gap:6px; text-align:center; isolation:isolate;
    transform: translateY(var(--card-offset-y));
  }
  .success-card > *{ position:relative; z-index:1; }
  .card-watermark{ z-index:0; mix-blend-mode:normal; }

  .title{ margin:0; color:#fff; font-weight:700; font-size:var(--fs-title); line-height:1.1; letter-spacing:.2px; }
  .motivation{ margin:0 0 22px 0; color:#fff; font-weight:400; font-size:var(--fs-motiv); line-height:1.3; }

  .actions{ display:grid; grid-auto-rows:70px; grid-template-columns:1fr; justify-items:center; gap:10px; width:100%; }
  .btn{
    display:inline-grid; place-items:center; width:510px; height:70px;
    background:var(--ink-orange); border:2pt solid var(--ink-blue); border-radius:6px;
    color:#fff; font-weight:700; font-size:var(--fs-btn); line-height:1; text-decoration:none; cursor:pointer;
    box-shadow:0 6px 16px rgba(0,0,0,.25); transition:transform .08s ease, box-shadow .08s ease;
  }
  .btn:hover{ transform:translateY(-1px); box-shadow:0 8px 20px rgba(0,0,0,.30); }
  .btn:active{ transform:translateY(0);    box-shadow:0 4px 10px rgba(0,0,0,.20); }

  /* NEW: prevent ghost-drag on buttons + back link */
  .actions .btn,
  .back{
    -webkit-user-drag: none;
    user-select: none;
  }
  
  .expiry{ margin-top:20px; font:400 var(--fs-expiry)/1.35 Helvetica,Arial,sans-serif; opacity:.97; }

  .back{ position:absolute; left:24px; bottom:16px; font:400 var(--fs-back)/1.1 Helvetica,Arial,sans-serif; color:#FE8215; text-decoration:underline; z-index:1; }

  .card-watermark{ position:absolute; right:18px; bottom:18px; height:65px; width:auto; opacity:.9; filter:drop-shadow(0 1px 2px rgba(0,0,0,.35)); pointer-events:none; user-select:none; }

  .site-footer{
    text-align:center; padding:12px 16px 24px;
    color:#fff; font-weight:700; font-size:22px; line-height:1.2;
    text-shadow:0 1px 2px rgba(0,0,0,.25); z-index:1;
    transform: translateY(var(--footer-raise)); will-change: transform;
  }

/*--------------------------------------------------------
  T27.2 — Tablet & laptop viewport audit — Success page
  (iPad Mini/Air + small laptops)
---------------------------------------------------------- */
@media (min-width: 700px) and (max-width: 1600px) and (min-height: 700px){

  html, body{
    height: 100%;
    overflow: hidden;
    overscroll-behavior: none;
  }

  :root{
    /* Card: size + geometry */
    --t27s-card-w: 650px;
    --t27s-card-h: 390px;
    --t27s-card-pad: 18px;
    --t27s-card-radius: 24px;
    --t27s-card-center-y: 0px; /* negative = move UP, positive = move DOWN */

    /* Title/subtext spacing */
    --t27s-title-mt: 0px;              /* card top -> heading */
    --t27s-gap-title-to-sub: 0px;      /* heading -> subheading */
    --t27s-gap-sub-to-actions: 20px;   /* subheading -> buttons */

    /* Buttons layout */
    --t27s-actions-gap: 6px;          /* spacing between buttons */
    --t27s-actions-row-h: 70px;        /* row height for buttons container */

    /* Buttons: size + geometry */
    --t27s-btn-w: 510px;
    --t27s-btn-h: 65px;
    --t27s-btn-radius: 6px;

    /* Buttons -> expiry spacing */
    --t27s-gap-actions-to-expiry: 12px;

    /* Font sizes */
    --t27s-fs-title: 36px;
    --t27s-fs-motiv: 18px;
    --t27s-fs-btn: 30px;
    --t27s-fs-expiry: 13px;
    --t27s-fs-back: 16px;

    /* “Close & Return” equivalent: the .back link (fully positionable) */
    --t27s-back-left: 18px;
    --t27s-back-right: auto;
    --t27s-back-top: auto;
    --t27s-back-bottom: 18px;

    /* “Logo” on Success = watermark image */
    --t27s-wm-h: 54px;
    --t27s-wm-right: 18px;
    --t27s-wm-bottom: 18px;

    /* Footer: fixed position independent of card height */
    --t27s-footer-fs: 18px;         /* ≈ 16pt */
    --t27s-footer-bottom: 14.5px;        /* distance to viewport bottom */
    --t27s-footer-pad-t: 12px;
    --t27s-footer-pad-lr: 16px;

    /* Wire into existing base vars used by the page */
    --card-w: var(--t27s-card-w);
    --card-h: var(--t27s-card-h);
    --card-pad: var(--t27s-card-pad);

    --fs-title: var(--t27s-fs-title);
    --fs-motiv: var(--t27s-fs-motiv);
    --fs-btn: var(--t27s-fs-btn);
    --fs-expiry: var(--t27s-fs-expiry);
    --fs-back: var(--t27s-fs-back);
  }

  .success-card{
    border-radius: var(--t27s-card-radius);
    transform: translateY(var(--t27s-card-center-y));
  }

  /* True vertical centering in viewport (not “vh-110px”) */
  .page-center{
    min-height: 100vh;
  }
  @supports (min-height:100dvh){
    .page-center{ min-height: 100dvh; }
  }

  .title{
    margin-top: var(--t27s-title-mt);
    margin-bottom: var(--t27s-gap-title-to-sub);
  }

  .motivation{
    margin: 0 0 var(--t27s-gap-sub-to-actions) 0;
  }

  .actions{
    gap: var(--t27s-actions-gap);
    grid-auto-rows: var(--t27s-actions-row-h);
  }

  .btn{
    width: var(--t27s-btn-w);
    height: var(--t27s-btn-h);
    border-radius: var(--t27s-btn-radius);
    border:2pt solid var(--ink-blue);
  }

  .expiry{
    margin-top: var(--t27s-gap-actions-to-expiry);
  }

  .back{
    left: var(--t27s-back-left);
    right: var(--t27s-back-right);
    top: var(--t27s-back-top);
    bottom: var(--t27s-back-bottom);
    font-size: var(--fs-back);
  }

  .card-watermark{
    height: var(--t27s-wm-h);
    right: var(--t27s-wm-right);
    bottom: var(--t27s-wm-bottom);
  }

  /* Footer must NOT move when card height changes */
  .site-footer{
    position: fixed;
    left: 0;
    right: 0;
    bottom: var(--t27s-footer-bottom);

    font-size: var(--t27s-footer-fs);
    padding: var(--t27s-footer-pad-t) var(--t27s-footer-pad-lr) 0;
    transform: none;
  }
}

/*-------------------------------------------------------------------------------
  Success page – Split-screen / multi-window bridge (481–699px width) – PORTRAIT
  Prevents desktop fallback (740px card) in mid-width windows
---------------------------------------------------------------------------------*/
@media (min-width: 481px) and (max-width: 699px) and (orientation: portrait){

  html, body{
    height: 100%;
    overflow: hidden;
    overscroll-behavior: none;
  }

  :root{
    /* Card (independent knobs) */
    --b1s-card-w: 540px;
    --b1s-card-h: 330px;
    --b1s-card-pad: 16px;
    --b1s-card-offset-y: 55px;

    --card-w: var(--b1s-card-w);
    --card-h: var(--b1s-card-h);
    --card-pad: var(--b1s-card-pad);
    --card-offset-y: var(--b1s-card-offset-y);

    /* Title/subtext spacing */
    --b1s-gap-title-to-sub: 5px;      /* heading -> subheading */
    --b1s-gap-sub-to-actions: 12px;   /* subheading -> buttons */

    /* Footer */
    --b1s-footer-raise: 65px;
    --b1s-footer-fs: 14px;

    /* Type */
    --fs-title: 28px;
    --fs-motiv: 18px;
    --fs-btn: 23px;
    --fs-expiry: 11px;
    --fs-back: 16px;

    /* Button */
    --b1s-btn-w: 380px;
    --b1s-btn-h: 54px;
    --b1s-btn-radius: 6px;
    --b1s-actions-gap: 8px;          /* spacing between buttons */
    --b1s-actions-row-h: var(--b1s-btn-h); /* row height must match button height */

    /* Expiry spacing */
    --expiry-gap-top: 13px;
  }

  .title{
    margin-bottom: var(--b1s-gap-title-to-sub);
  }

  .motivation{
    margin: 0 0 var(--b1s-gap-sub-to-actions) 0;
  }

  .btn{
    width: var(--b1s-btn-w);
    height: var(--b1s-btn-h);
    border-radius: var(--b1s-btn-radius);
    font-size: var(--fs-btn);
    border-radius:6px;
    border: 1.5pt solid var(--ink-blue);
  }

  .actions{
    gap: var(--b1s-actions-gap);
    grid-auto-rows: var(--b1s-actions-row-h);
  }

  .expiry{
    margin-top: var(--expiry-gap-top);
    margin-bottom: 0;
    line-height: 1.3;
  }

  .card-watermark {
      height: 48px;
      right: 16px;
      bottom: 16px;
    }

    .back{
    left: 16px;
    bottom: 16px;
  }

  .site-footer{
    font-size: var(--b1s-footer-fs);
    --footer-raise: var(--b1s-footer-raise);
  }
}

/* -------------------------------------------------------------------
  CH9 — Foldable / split-screen (557×720), PORTRAIT — Success page
  Card horizontally centered + card width adjustable (this viewport only)
----------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 557px) and (height: 720px){

  :root{
    /* Card width knob (557×720 portrait only) */
    --ch9f557-pt-card-w: 510px;   /* + wider / - narrower */

    /* Small safety padding so card never clips */
    --ch9f557-pt-page-pad: 10px;

    /* wire into existing base var used by .success-card */
    --card-w: var(--ch9f557-pt-card-w);
  }

  .page-center{
    padding: var(--ch9f557-pt-page-pad);
  }

  /* hard guard against overflow while keeping true centering */
  .success-card{
    width: var(--card-w);
    max-width: calc(100vw - 2 * var(--ch9f557-pt-page-pad));
    box-sizing: border-box;
  }
}

/* ------------------------------------------------------
   Common phones (≈390/393px), PORTRAIT – Success page
   ------------------------------------------------------*/
@media (min-width: 360px) and (max-width: 480px) and (orientation: portrait) {

  /* Drag-scroll allowed to hide URL bar */
  html, body{
    /* let Safari naturally hide toolbars */
  }

  :root{
    /* Card */
    --card-w: calc(100vw - 32px);
    --card-h: 330px;          /* fixed: prevents EN/DE height jump */
    --card-pad: 12px;
    --card-pt-card-offset-y: 27px;   /* + down / - up */


    /* Footer */
    --footer-raise: 0px;

    /* Type */
    --fs-title: 25px;
    --fs-motiv: 16px;
    --fs-btn: 21px;
    --fs-expiry: 10px;
    --fs-back: 14px;

    /* Buttons + spacing */
    --btn-h: 54px;
    --btn-max-w: 340px;
    --actions-gap: 8px;

    /* Reserve space for absolute back link + watermark */
    --card-bottom-reserve: 60px;

    /* Expiry spacing */
    --expiry-gap-top: 16px;
  }

  /* Override the more-specific global DE overrides */
  html[lang="de"] :root{
    --fs-motiv: 15px;
    --fs-btn: 20px;
  }

  /* Dedicated CSS texture to eliminate baked-in watermarks */
  html,
  .page-bg{
    background-color: #f7f9fc;
    background-image: repeating-linear-gradient(
      -45deg,
      #f7f9fc,
      #f7f9fc 18px,
      #e2e8f0 18px,
      #e2e8f0 20px
    );
  }

  .page-center{
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: calc(100vh - 56px);
    padding: 0 12px 6px;
    box-sizing: border-box;
  }

  .page-center::before,
  .page-center::after {
    content: "";
    display: block;
    flex-grow: 1;
  }

  .page-center::before {
    min-height: 40px;
    flex-shrink: 0;
  }

  @supports (min-height:100dvh){
    .page-center{ min-height: calc(100dvh - 56px); }
  }

  .success-card{
    width: var(--card-w);
    height: var(--card-h);
    border-radius: 24px;
    padding: var(--card-pad);
    padding-bottom: calc(var(--card-pad) + var(--card-bottom-reserve));
    margin: 0 auto;
    flex-shrink: 0;
    transform: translateY(var(--card-pt-card-offset-y));
    gap: 4px;
  }

  .title{ margin: 8px 0 6px; }
  .motivation{ margin: 0 0 12px 0; }

  .actions{
    grid-auto-rows: var(--btn-h);
    gap: var(--actions-gap);
  }

  .actions .btn{
    width: 100%;
    max-width: var(--btn-max-w);
    height: var(--btn-h);
    font-size: var(--fs-btn);
    border-radius:6px;
    border: 1.5pt solid var(--ink-blue);
  }

  .expiry{
    margin-top: var(--expiry-gap-top);
    margin-bottom: 0;
    line-height: 1.3;
  }

  .back{
    left: 16px;
    bottom: 14px;
    font-size: var(--fs-back);
  }

  .card-watermark{
    right: 12px;
    bottom: 12px;
    height: 40px;
  }

  .site-footer{
    font-size: 12px;
    padding: 25px 8px 12px;
    transform: translateY(0);
  }
}

/* ------------------------------------------------------
  T28.2 — iPhone X (375×812), PORTRAIT — Success page
---------------------------------------------------------*/
@media (orientation: portrait)
  and (min-width: 375px) and (max-width: 389px)
  and (min-height: 780px) and (max-height: 830px){

  :root{
    /* ===== knobs ===== */
    --t28x-pt-card-h: 330px;        /* card height (was 380px) */
    --t28x-pt-card-offset-y: 30px;   /* + down / - up */

    --t28x-pt-fs-title: 26px;
    --t28x-pt-fs-motiv: 15px;
    --t28x-pt-fs-btn: 20px;
    --t28x-pt-fs-expiry: 10px;
    --t28x-pt-fs-back: 13px;

    --t28x-pt-btn-h: 48px;
    --t28x-pt-btn-max-w: 330px;     /* button width cap */
    --t28x-pt-actions-gap: 8px;

    /* ===== wire into existing vars used by the page ===== */
    --card-h: var(--t28x-pt-card-h);

    --fs-title: var(--t28x-pt-fs-title);
    --fs-motiv: var(--t28x-pt-fs-motiv);
    --fs-btn: var(--t28x-pt-fs-btn);
    --fs-expiry: var(--t28x-pt-fs-expiry);
    --fs-back: var(--t28x-pt-fs-back);

    --btn-h: var(--t28x-pt-btn-h);
    --btn-max-w: var(--t28x-pt-btn-max-w);
    --actions-gap: var(--t28x-pt-actions-gap);
  }

  /* keep everything else identical; only apply knobs */
  .success-card{
    transform: translateY(var(--t28x-pt-card-offset-y));
  }
}

/* --------------------------------------------------------------------
  T28.3 — iPhone XR (414×896), PORTRAIT — Success page
-----------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 414px) and (height: 896px){

  :root{
    --t28xr-pt-card-offset-y: 25px; /* + down / - up */
  }

  .success-card{
    transform: translateY(var(--t28xr-pt-card-offset-y));
  }
}

/* ---------------------------------------------------------------------
  T28.4 — iPhone 14 Pro Max (430×932), PORTRAIT — Success page
------------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 430px) and (height: 932px){

  :root{
    /* ===== CARD knobs (independent) ===== */
    --t284pm-pt-card-h: 340px;          /* fixed: prevents EN/DE jump */
    --t284pm-pt-card-offset-y: 25px;     /* + down / - up (card only) */

    /* ===== FOOTER knobs (independent) ===== */
    --t284pm-pt-footer-raise: 18px;     /* smaller = higher, bigger = lower */
    --t284pm-pt-footer-offset-y: 0px;   /* + down / - up (footer only) */

    /* wire into existing vars */
    --card-h: var(--t284pm-pt-card-h);
    --footer-raise: var(--t284pm-pt-footer-raise);
  }

  .success-card{
    height: var(--card-h);
    transform: translateY(var(--t284pm-pt-card-offset-y));
  }

  .site-footer{
    transform: translateY(var(--t284pm-pt-footer-offset-y));
  }
}

/* -----------------------------------------------------------
  T28.5 — Samsung Galaxy S8+ (360×740), PORTRAIT — Success page
--------------------------------------------------------------*/
@media (orientation: portrait) and (width: 360px) and (height: 740px){

  :root{
    /* knobs */
    /* ===== CARD knobs (independent) ===== */
    --t285s8-card-h: 310px;          /* fixed: prevents EN/DE jump */

    --t285s8-pt-fs-title: 22px; /* font size heading */
    --t285s8-pt-fs-btn: 18px;   /* font size action buttons */
    --t285s8-pt-btn-h: 48px;    /* hight action buttons */

    /* wire into existing vars */
    --fs-title: var(--t285s8-pt-fs-title);
    --fs-btn: var(--t285s8-pt-fs-btn);
    --btn-h: var(--t285s8-pt-btn-h);
  }

  .success-card{
    height: var(--t285s8-card-h);
  }

  /* ensure DE overrides from earlier blocks don't win */
  html[lang="de"] :root{
    --fs-title: var(--t285s8-pt-fs-title);
    --fs-btn: var(--t285s8-pt-fs-btn);
  }
}

/* ------------------------------------------------------------
   T28.1 — 375×667 PORTRAIT (short phones) — Success page
   -----------------------------------------------------------*/
@media (orientation: portrait)
  and (min-width: 360px) and (max-width: 430px)
  and (max-height: 700px){

  :root{
    /* Card */
    --card-h: 310px;
    --card-pad: 12px;

    /* Type */
    --fs-title: 23px;
    --fs-motiv: 14px;
    --fs-btn: 20px;
    --fs-expiry: 10px;
    --fs-back: 13px;

    /* Buttons + spacing */
    --btn-h: 44px;
    --btn-max-w: 330px;
    --actions-gap: 8px;

    /* Footer knobs (independent) */
    --t28s-footer-h: 22px;        /* footer height (adjustable) */
    --t28s-footer-space: 0px;    /* reserved space for centering (adjustable) */
    --t28s-footer-bottom: 10px;   /* distance to viewport bottom */
    --t28s-footer-fs: 12px;       /* footer font size */

    /* Reserve for back link + watermark */
    --card-bottom-reserve: 56px;

    /* Expiry spacing */
    --expiry-gap-top: 14px;

    /* Fine centering */
    --t28s-pt-card-offset-y: 0px; /* + down / - up */
  }

  .page-center{
    min-height: calc(100vh - var(--t28s-footer-space));
  }
  @supports (min-height:100dvh){
    .page-center{ min-height: calc(100dvh - var(--t28s-footer-space)); }
  }

  .success-card{
    transform: translateY(var(--t28s-pt-card-offset-y));
    border-radius: 24px;
  }

  .site-footer{
    position: fixed;
    left: 0;
    right: 0;
    bottom: var(--t28s-footer-bottom);
    height: var(--t28s-footer-h);

    padding: 0;               /* no side padding */
    margin: 0;
    transform: none;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: var(--t28s-footer-fs);
    line-height: 1;
    z-index: 5;
  }
}

    /*------------------------------------
    iPhone SE (320×568) – portrait only 
    --------------------------------------*/
  @media (max-width: 340px) {

    /* SE-only typography & card sizing via variables */
    :root{
      /* Card size & spacing */
      --card-w: calc(100vw - 32px);
      --card-h: auto;
      --card-pad: 12px;
      --card-pt-card-offset-y: 20px;   /* + down / - up */

      --footer-raise: 0px;

      /* Typography – scaled down for SE */
      --fs-title: 22px;
      --fs-motiv: 15px;
      --fs-btn: 16px;
      --fs-expiry: 11px;
      --fs-back: 13px;

      /* NEW: fine control for expiry spacing */
      --expiry-gap-top: 10px;      /* Abstand zum Buttonblock */
      --expiry-gap-bottom: 35px;   /* Abstand zum Kartenrand/Logo/Back */
    }

    /* Slightly smaller font for DE text too */
    html[lang="de"] :root {
      --fs-motiv: 13px;
      --fs-btn: 17px;
    }

    /* Dedicated CSS texture to eliminate baked-in watermarks */
    html,
    .page-bg {
      background-color: #f7f9fc;
      background-image: repeating-linear-gradient(
        -45deg,
        #f7f9fc,
        #f7f9fc 18px,
        #e2e8f0 18px,
        #e2e8f0 20px
      );
    }

    /* Lock the page height so card + footer sit inside the viewport */
    body{
      overflow-y: hidden;   /* avoid page scrolling on SE */
    }

    .page-shell{
      min-height: 100vh;
    }

    .page-center{
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: calc(100vh - 40px);     /* leaves room for footer line */
      padding: 0 8px 8px;
      box-sizing: border-box;
    }

    .page-center::before,
    .page-center::after {
      content: "";
      display: block;
      flex-grow: 1;
    }

    .page-center::before {
      min-height: 20px;
      flex-shrink: 0;
    }

    .success-card{
      width: var(--card-w);
      height: var(--card-h);              /* becomes auto */
      max-height: calc(100vh - 80px);     /* safety margin for very small heights */
      border-radius: 24px;
      margin: 0 auto;
      flex-shrink: 0;
      transform: translateY(var(--card-pt-card-offset-y));
      padding: var(--card-pad);
    }

    /* Bring title a bit closer to top, tighten vertical rhythm */
    .title{
      margin-top: 6px;
      margin-bottom: 6px;
    }

    .motivation{
      margin-bottom: 12px;
    }

    /* Button block – smaller, full-width buttons */
    .actions{
      grid-auto-rows: 40px;
      gap: 8px;
      width: 100%;
      justify-items: center;
    }

    .actions .btn{
      width: 100%;
      max-width: 260px;
      height: 40px;
      border: 1.5pt solid var(--ink-blue);
    }

    /* Expiry text – adjustable gaps above and below */
    .expiry{
      margin-top: var(--expiry-gap-top);
      margin-bottom: var(--expiry-gap-bottom);
    }

    /* Back link & watermark tucked neatly into lower corners */
    .back{
      left: 16px;
      bottom: 12px;
      font-size: var(--fs-back);
    }

    .card-watermark{
      right: 12px;
      bottom: 12px;
      height: 32px;
    }

    /* Footer – one line, comfortable but compact */
    .site-footer{
      font-size: 12px;
      padding: 10px 8px 10px;
      transform: translateY(0);  /* no extra lift on SE */
    }
  }

  /*-------------------------------------------------------------------------------
  Success page – Split-screen / short-height window (461–699px tall) – LANDSCAPE
  Covers the gap between phone-landscape (<=460h) and T27 (>=700h)
  ---------------------------------------------------------------------------------*/
  @media (orientation: landscape)
  and (min-width: 700px) and (max-width: 1600px)
  and (min-height: 461px) and (max-height: 699px){

  html, body{
    height: 100%;
    overflow: hidden;
    overscroll-behavior: none;
  }

  :root{
    /* Card (independent knobs) */
    --sls-card-w: 540px;
    --sls-card-h: 330px;
    --sls-card-pad: 16px;
    --sls-card-offset-y: 55px;

    --card-w: var(--sls-card-w);
    --card-h: var(--sls-card-h);
    --card-pad: var(--sls-card-pad);
    --card-offset-y: var(--sls-card-offset-y);

    /* Title/subtext spacing */
    --sls-gap-title-to-sub: 5px;      /* heading -> subheading */
    --sls-gap-sub-to-actions: 12px;   /* subheading -> buttons */

    /* Type */
    --fs-title: 28px;
    --fs-motiv: 18px;
    --fs-btn: 23px;
    --fs-expiry: 11px;
    --fs-back: 16px;

    /* Button knobs */
    --sls-btn-w: 380px;
    --sls-btn-h: 54px;
    --sls-btn-radius: 6px;
    --sls-actions-gap: 8px;          /* spacing between buttons */
    --sls-actions-row-h: var(--sls-btn-h); /* row height must match button height */

    /* Expiry spacing */
    --expiry-gap-top: 13px;

    /* Footer (make it fixed + visible, no scroll dependency) */
    --sls-footer-fs: 14px;
    --sls-footer-bottom: 15px;
    --sls-footer-pad-t: 10px;
    --sls-footer-pad-lr: 16px;
  }

  title{
    margin-bottom: var(--sls-gap-title-to-sub);
  }

  .motivation{
    margin: 0 0 var(--sls-gap-sub-to-actions) 0;
  }

  .btn{
    width: min(var(--sls-btn-w), calc(100vw - 80px));
    height: var(--sls-btn-h);
    border-radius: var(--sls-btn-radius);
    font-size: var(--fs-btn);
    border-radius:6px;
    border: 1.5pt solid var(--ink-blue);
  }

  .actions{
    gap: var(--sls-actions-gap);
    grid-auto-rows: var(--sls-actions-row-h);
  }

  .expiry{
    margin-top: var(--expiry-gap-top);
    margin-bottom: 0;
    line-height: 1.3;
  }

  .card-watermark {
      height: 48px;
      right: 16px;
      bottom: 16px;
    }

  .site-footer{
    position: fixed;
    left: 0;
    right: 0;
    bottom: var(--sls-footer-bottom);
    font-size: var(--sls-footer-fs);
    padding: var(--sls-footer-pad-t) var(--sls-footer-pad-lr) 0;
    transform: none;
  }

  .back{
    left: 16px;
    bottom: 16px;
    font-size: var(--fs-back);
  }
}

    /* ----------------------------------------------------------------------------------------
    T26.2 — Phone group iPhone 12/13/14 (844×390) & Pixel 5 (851×393), LANDSCAPE (Success page)
    -------------------------------------------------------------------------------------------*/
  @media (orientation: landscape)
    and (min-width: 640px) and (max-width: 1024px)
    and (min-height: 361px) and (max-height: 460px) {

    /* prevent tiny rounding scroll + keep stable */
    html, body{ overscroll-behavior-x: none; overflow-y: auto; }

    /* prevent background flicker on EN/DE flag click (soft swap) */
    html.demo-swap-freeze{ contain: none !important; }

    :root,
    html[lang="en"] :root,
    html[lang="de"] :root{
      --card-w: min(calc(100vw - 20px), 550px);
      --card-h: 315px;          /* fixed: prevents EN/DE height jump */
      --card-pad: 12px;
      --card-pt-card-offset-y: 7px;   /* + down / - up */
      --ls-top-band: 30px;

      /* spacing controls (ALL effective) */
      --gap-title-sub: 6px;         /* title -> subheading */
      --gap-sub-actions: 20px;      /* subheading -> actions (TOTAL)  (12 + old 10) */
      --actions-gap: 8px;           /* between the 2 action buttons */
      --gap-actions-expiry: 20px;   /* actions -> expiry */
      
      --fs-title: 30px;
      --fs-body: 16px;
      --fs-btn: 21px;

      --btn-h: 54px;
      --btn-max-w: 340px;

      --fs-expiry: 11px;

      --fs-back: 14px;
      --back-left: 12px;
      --back-bottom: 12px;

      --footer-raise: 0px;
    }

    .page-center{
      padding: var(--ls-top-band) 10px 6px;
      place-items: center;
    }

    .success-card{
      width: var(--card-w);
      height: var(--card-h);
      border-radius: 24px;
      padding: var(--card-pad);
      transform: translateY(var(--card-pt-card-offset-y));
      gap: 0;
    }

    /* title + title/sub gap (single source of truth) */
    .success-card h1,
    .title{
      font-size: var(--fs-title);
      margin: 6px 0 var(--gap-title-sub) 0;
    }

    /* subheading -> actions spacing (now always works) */
    .success-card p.motivation{
      margin: 0 0 var(--gap-sub-actions) 0;
    }

    /* buttons */
    .btn,
    .btn-primary,
    .btn-secondary{
      height: var(--btn-h);
      max-width: var(--btn-max-w);
      font-size: var(--fs-btn);
      padding: 0 18px;
      line-height: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      border: 1.5pt solid var(--ink-blue);
    }

    /* actions: remove hidden row-slack + make gap adjustable */
    .actions{
      margin-top: 0; /* now controlled ONLY by .motivation margin-bottom */
      gap: var(--actions-gap);

      /* kills the “gap won’t shrink” problem when rows were larger than the button */
      grid-auto-rows: var(--btn-h);
      grid-template-rows: repeat(2, var(--btn-h));
    }

    /* if your markup uses these on some layouts, keep them predictable */
    .btn-row,
    .button-row{
      margin-top: 0;
      gap: var(--actions-gap);
    }

    /* actions -> expiry spacing + expiry text size (now always works) */
    .success-card p.expiry{
      margin-top: var(--gap-actions-expiry);
      font-size: var(--fs-expiry);
    }

    .back{
      left: var(--back-left);
      bottom: var(--back-bottom);
      font: 400 var(--fs-back)/1.1 Helvetica,Arial,sans-serif;
    }

    .card-watermark{
      height: 40px;
      right: 12px;
      bottom: 12px;
    }

    .lang-switch{
      position: fixed;
      top: 8px;
      right: 10px;
      gap: 6px;
      z-index: 50;
    }
    .lang-switch a img{ width: 44px; height: 28px; }
    .lang-switch .lang-label{ font-size: 11px; margin-top: 0; }

    .site-footer{
      font-size: 12px;
      padding: 13px 8px 10px;
      transform: none;
    }

    .processing-card{
      max-width: min(460px, 100%);
      padding: 18px 16px;
    }
    .processing-card h2{ font-size: 20px; }
    .processing-card p{ font-size: 13px; margin-bottom: 14px; }
  }

/* -------------------------------------------------------------------
  T26.2b — CH8 — Pixel 8 Pro (e.g. ~998×448), LANDSCAPE — Success page
----------------------------------------------------------------------*/
@media (orientation: landscape)
  and (min-width: 951px) and (max-width: 1024px)
  and (min-height: 431px) and (max-height: 460px){

  :root{
    /* Card: vertical offset knob (independent) */
    --t262b-card-offset-y: 1px;                 /* + down / - up */

    /* Footer: independent vertical offset knob */
    --t262b-footer-offset-y: 0px;         /* + down / - up */
  }

  /* Make the card truly centered in the usable area */
  .page-center{
    place-items: center;
    height: calc(100vh - 40px);           /* keep footer room stable */
    min-height: calc(100vh - 40px);
    transform: translateY(var(--t262b-card-offset-y));

  }

  /* Footer: allow independent tuning (does NOT affect card) */
  .site-footer{
    transform: translateY(var(--t262b-footer-offset-y));
  }
}

  /* ----------------------------------------------------
    T28.2 — iPhone X (812×375), LANDSCAPE — Success page
  -------------------------------------------------------*/
  @media (orientation: landscape)
    and (min-width: 800px) and (max-width: 830px)
    and (max-height: 380px){

    html, body{
      overscroll-behavior: none;
      overflow-y: auto;
      overflow-x: hidden;
    }

    :root{
      /* ===== knobs ===== */
      --t28x-ls-card-w-max: 540px;
      --t28x-ls-card-h: 310px;
      --t28x-ls-card-offset-y: 10px;    /* + down / - up */

      --t28x-ls-top-band: 18px;

      --t28x-ls-footer-h: 17px;        /* footer height (independent) */
      --t28x-ls-footer-space: 36px;    /* reserved space for centering (independent) */
      --t28x-ls-footer-bottom: 8px;   /* distance to viewport bottom */
      --t28x-ls-footer-fs: 12px;

      /* wire into existing vars */
      --card-w: min(calc(100vw - 20px), var(--t28x-ls-card-w-max));
      --card-h: var(--t28x-ls-card-h);
      --card-pad: 12px;
    }

    /* reserve footer space so the card can be truly centered */
    .page-center{
      min-height: calc(100vh - var(--t28x-ls-footer-space));
      padding: var(--t28x-ls-top-band) 10px 6px;
      box-sizing: border-box;
      place-items: center;
    }
    @supports (min-height:100dvh){
      .page-center{ min-height: calc(100dvh - var(--t28x-ls-footer-space)); }
    }

    .success-card{
      width: var(--card-w);
      height: var(--card-h); /* FIXED */
      transform: translateY(var(--t28x-ls-card-offset-y));
    }

    /* footer must NOT be clipped and must be independent of card */
    .site-footer{
      position: fixed;
      left: 0;
      right: 0;
      bottom: var(--t28x-ls-footer-bottom);
      height: var(--t28x-ls-footer-h);

      padding: 0;
      margin: 0;
      transform: none;

      display: flex;
      align-items: center;
      justify-content: center;

      font-size: var(--t28x-ls-footer-fs);
      line-height: 1;
      z-index: 5;
    }
  }

/* ----------------------------------------------------------------------
  T28.3 — iPhone XR (896×414), LANDSCAPE — Success page
-------------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 896px) and (height: 414px){

  html, body{ overscroll-behavior: none; overflow-y: auto; overflow-x: hidden; }

  :root{
    /* ===== knobs ===== */
    --t28xr-ls-card-w-max: 540px;
    --t28xr-ls-card-h: 320px;          /* fixed: prevents EN/DE height jump */
    --t28xr-ls-card-offset-y: 17px;     /* + down / - up (card only) */

    --t28xr-ls-footer-h: 40px;         /* footer element height */
    --t28xr-ls-footer-space: 34px;     /* reserved space for centering (independent) */
    --t28xr-ls-footer-fs: 12px;

    --t28xr-ls-pad-x: 10px;
    --t28xr-ls-pad-y: 8px;

    /* wire into existing vars */
    --card-w: min(calc(100vw - 20px), var(--t28xr-ls-card-w-max));
    --card-h: var(--t28xr-ls-card-h);
    --card-pad: 12px;

    --footer-space: var(--t28xr-ls-footer-space);
  }

  /* true centering box */
  .page-center{
    height: calc(100svh - var(--footer-space));
    height: calc(100vh  - var(--footer-space)); /* fallback */
    padding: var(--t28xr-ls-pad-y) var(--t28xr-ls-pad-x);
    box-sizing: border-box;
    display: grid;
    place-items: center;
  }

  .success-card{
    width: var(--card-w);
    height: var(--card-h);
    transform: translateY(var(--t28xr-ls-card-offset-y));
  }

  /* fixed footer (decoupled) */
  .site-footer{
    position: fixed;
    left: 0; right: 0; bottom: 0;
    height: var(--t28xr-ls-footer-h);
    padding: 0;
    margin: 0;
    transform: none;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: var(--t28xr-ls-footer-fs);
    line-height: 1;
    z-index: 5;
  }
}

/* -----------------------------------------------------------------
  T28.4 — iPhone 14 Pro Max (932×430), LANDSCAPE — Success page
--------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 932px) and (height: 430px){

  html, body{ overscroll-behavior: none; overflow-y: auto; overflow-x: hidden; }

  :root{
    /* ===== CARD knobs (independent) ===== */
    --t284pm-ls-card-w-max: 560px;
    --t284pm-ls-card-h: 320px;          /* fixed: prevents EN/DE jump */
    --t284pm-ls-card-offset-y: 14px;     /* + down / - up (card only) */

    /* ===== FOOTER knobs (independent) ===== */
    --t284pm-ls-footer-h: 32px;         /* footer element height */
    --t284pm-ls-footer-space: 28px;     /* reserved space for centering (card-only) */
    --t284pm-ls-footer-fs: 12px;
    --t284pm-ls-footer-offset-y: -4px;   /* + down / - up (footer only) */

    /* padding */
    --t284pm-ls-pad-x: 10px;
    --t284pm-ls-pad-y: 8px;

    /* wire into existing vars */
    --card-w: min(calc(100vw - 20px), var(--t284pm-ls-card-w-max));
    --card-h: var(--t284pm-ls-card-h);
    --card-pad: 12px;

    --footer-space: var(--t284pm-ls-footer-space);
  }

  /* true centering box */
  .page-center{
    height: calc(100svh - var(--footer-space));
    height: calc(100vh  - var(--footer-space)); /* fallback */
    padding: var(--t284pm-ls-pad-y) var(--t284pm-ls-pad-x);
    box-sizing: border-box;
    display: grid;
    place-items: center;
  }

  .success-card{
    width: var(--card-w);
    height: var(--card-h);
    transform: translateY(var(--t284pm-ls-card-offset-y));
  }

  /* fixed footer (decoupled) */
  .site-footer{
    position: fixed;
    left: 0; right: 0; bottom: 0;
    height: var(--t284pm-ls-footer-h);
    padding: 0;
    margin: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: var(--t284pm-ls-footer-fs);
    line-height: 1;
    transform: translateY(var(--t284pm-ls-footer-offset-y));
    z-index: 5;
  }
}

/* -------------------------------------------------------------------
  CH9 — Samsung Galaxy A51/A71 (914×412), LANDSCAPE — Success page
  Goal: card vertically centered + card Y adjustable, footer independent
----------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 914px) and (height: 412px){

  html, body{ overscroll-behavior: none; overflow-y: auto; overflow-x: hidden; }

  :root{
    /* ===== CARD knobs (card-only) ===== */
    --ch9a51-ls-success-card-offset-y: 14px;    /* + down / - up */

    /* ===== FOOTER knobs (footer-only) ===== */
    --ch9a51-ls-success-footer-h: 40px;
    --ch9a51-ls-success-footer-space: 28px;    /* reserve space so card can center */
    --ch9a51-ls-success-footer-fs: 12px;
    --ch9a51-ls-success-footer-offset-y: 2px;  /* + down / - up */

    /* padding */
    --ch9a51-ls-pad-x: 10px;
    --ch9a51-ls-pad-y: 8px;
  }

  /* true centering box (independent from footer position) */
  .page-center{
    height: calc(100svh - var(--ch9a51-ls-success-footer-space));
    height: calc(100vh  - var(--ch9a51-ls-success-footer-space)); /* fallback */
    padding: var(--ch9a51-ls-pad-y) var(--ch9a51-ls-pad-x);
    box-sizing: border-box;
    display: grid;
    place-items: center;
  }

  /* card fine-tuning (card-only) */
  .success-card{
    transform: translateY(var(--ch9a51-ls-success-card-offset-y));
  }

  /* fixed footer (footer-only) */
  .site-footer{
    position: fixed;
    left: 0; right: 0; bottom: 0;

    height: var(--ch9a51-ls-success-footer-h);
    padding: 0;
    margin: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: var(--ch9a51-ls-success-footer-fs);
    line-height: 1;

    transform: translateY(var(--ch9a51-ls-success-footer-offset-y));
    z-index: 5;
  }
}

/* --------------------------------------------------------------------
  T28.6 — Samsung Galaxy S20 Ultra (915×412), LANDSCAPE — Success page
-----------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 915px) and (height: 412px){

  html, body{ overscroll-behavior: none; overflow-y: auto; overflow-x: hidden; }

  :root{
    /* ===== CARD knobs (do NOT affect footer position) ===== */
    --t286s20-ls-card-offset-y: 14px;       /* + down / - up (card only) */

    /* ===== FOOTER knobs (do NOT affect card position) ===== */
    --t286s20-ls-footer-h: 32px;           /* footer element height */
    --t286s20-ls-footer-space: 28px;       /* reserved centering space (card-only) */
    --t286s20-ls-footer-fs: 12px;
    --t286s20-ls-footer-offset-y: 0px;     /* + down / - up (footer only) */

    /* padding */
    --t286s20-ls-pad-x: 10px;
    --t286s20-ls-pad-y: 8px;
  }

  /* true centering box (independent from footer position) */
  .page-center{
    height: calc(100svh - var(--t286s20-ls-footer-space));
    height: calc(100vh  - var(--t286s20-ls-footer-space)); /* fallback */
    padding: var(--t286s20-ls-pad-y) var(--t286s20-ls-pad-x);
    box-sizing: border-box;
    display: grid;
    place-items: center;
  }

  /* card fine-tuning (card-only) */
  .success-card{
    transform: translateY(var(--t286s20-ls-card-offset-y));
  }

  /* fixed footer (footer-only) */
  .site-footer{
    position: fixed;
    left: 0; right: 0; bottom: 0;

    height: var(--t286s20-ls-footer-h);
    padding: 0;
    margin: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: var(--t286s20-ls-footer-fs);
    line-height: 1;

    transform: translateY(var(--t286s20-ls-footer-offset-y));
    z-index: 5;
  }
}

  /* -----------------------------------------------------------------------
    T28.1 — 667×375 LANDSCAPE (medium landscape phones) — Success page
  --------------------------------------------------------------------------*/
  @media (orientation: landscape)
    and (min-width: 640px) and (max-width: 799px)
    and (min-height: 361px) and (max-height: 430px){

    html, body{ overscroll-behavior: none; overflow-y: auto; overflow-x: hidden; }

    :root{
      /* footer (decoupled) */
      --t28s-ls-footer-h: 35px;       /* adjust freely */
      --t28s-ls-footer-space: 0px;   /* adjust to avoid overlap / tune centering */
      --t28s-ls-footer-fs: 12px;

      /* centering + padding */
      --t28s-ls-pad-x: 10px;
      --t28s-ls-pad-y: 8px;
      --t28s-ls-card-offset-y: 0px;   /* + down / - up */

      /* size down for 667×375 */
      --card-w: min(calc(100vw - (2 * var(--t28s-ls-pad-x))), 500px);
      --card-h: 260px;
      --card-pad: 12px;

      --gap-title-sub: 5px;
      --gap-sub-actions: 12px;
      --actions-gap: 8px;
      --gap-actions-expiry: 12px;

      --fs-title: 24px;
      --fs-body: 14px;
      --fs-btn: 20px;
      --fs-expiry: 10px;
      --fs-back: 13px;

      --btn-h: 44px;
      --btn-max-w: 320px;

      --back-left: 12px;
      --back-bottom: 12px;

      --footer-space: var(--t28s-ls-footer-space);
    }

    /* true centering box */
    .page-center{
      height: calc(100svh - var(--footer-space));
      height: calc(100vh  - var(--footer-space)); /* fallback */
      padding: var(--t28s-ls-pad-y) var(--t28s-ls-pad-x);
      box-sizing: border-box;
      display: grid;
      place-items: center;
    }

    .success-card{
      width: var(--card-w);
      height: var(--card-h);
      padding: var(--card-pad);
      border-radius: 24px;
      transform: translateY(var(--t28s-ls-card-offset-y));
      gap: 0;
    }

    /* keep spacing controls effective */
    .success-card h1,
    .title{
      font-size: var(--fs-title);
      margin: 6px 0 var(--gap-title-sub) 0;
    }
    .success-card p.motivation{
      font-size: var(--fs-body);
      margin: 0 0 var(--gap-sub-actions) 0;
    }

    .actions{
      gap: var(--actions-gap);
      grid-auto-rows: var(--btn-h);
      grid-template-rows: repeat(2, var(--btn-h));
    }

    .btn,
    .btn-primary,
    .btn-secondary{
      height: var(--btn-h);
      max-width: var(--btn-max-w);
      font-size: var(--fs-btn);
      padding: 0 14px;
      line-height: 1;
    }

    .success-card p.expiry{
      margin-top: var(--gap-actions-expiry);
      font-size: var(--fs-expiry);
    }

    .back{
      left: var(--back-left);
      bottom: var(--back-bottom);
      font: 400 var(--fs-back)/1.1 Helvetica,Arial,sans-serif;
    }

    .card-watermark{
      height: 38px;
      right: 10px;
      bottom: 10px;
    }

    /* fixed footer (height independent from card position) */
    .site-footer{
      position: fixed;
      left: 0;
      right: 0;
      bottom: 0;
      height: var(--t28s-ls-footer-h);
      padding: 0;              /* no side padding */
      margin: 0;
      transform: none;

      display: flex;
      align-items: center;
      justify-content: center;

      font-size: var(--t28s-ls-footer-fs);
      line-height: 1;
      z-index: 5;
    }
  }

   /* ------------------------------------------------------------
     iPhone SE (568×320), LANDSCAPE – Success page
     -----------------------------------------------------------*/
  @media (max-width: 950px) and (max-height: 360px) and (orientation: landscape) {

    /* reduce tiny edge “rubber-band” feel */
    html, body { overscroll-behavior: none; }

    :root,
    html[lang="en"] :root,
    html[lang="de"] :root{
      /* Card sizing */
      --card-w: min(calc(100vw - 16px), 400px);
      --card-h: auto;
      --card-pad: 10px;
      --card-pt-card-offset-y: 7px;   /* + down / - up */

      /* Footer should not be lifted on SE landscape */
      --footer-raise: 0px;

      /* Typography */
      --fs-title: 22px;
      --fs-motiv: 15px;
      --fs-btn: 18px;
      --fs-expiry: 11px;
      --fs-back: 13px;

      /* Buttons */
      --btn-h: 40px;
      --btn-max-w: 300px;
      --actions-gap: 8px;

      /* Spacing */
      --expiry-gap-top: 10px;

      /* IMPORTANT: reserve space for absolute back link + watermark */
      --card-bottom-reserve: 35px;
    }

    .page-center{
      min-height: calc(100vh - 22px);
      padding: 10px 6px 6px;
      place-items: center;
    }

    .success-card{
      width: var(--card-w);
      height: var(--card-h); /* becomes auto via variable */
      border-radius: 24px;
      padding: var(--card-pad);
      padding-bottom: calc(var(--card-pad) + var(--card-bottom-reserve));
      transform: translateY(var(--card-pt-card-offset-y));
      gap: 4px;
    }

    .title{ margin: 2px 0 2px; }
    .motivation{ margin: 0 0 8px 0; }

    .actions{
      grid-auto-rows: var(--btn-h);
      gap: var(--actions-gap);
    }

    .actions .btn{
      width: 100%;
      max-width: var(--btn-max-w);
      height: var(--btn-h);
      font-size: var(--fs-btn);
      border: 1.5pt solid var(--ink-blue);
    }

    .expiry{
      margin-top: var(--expiry-gap-top);
      margin-bottom: 0;
      line-height: 1.25;
    }

    .back{
      left: 14px;
      bottom: 12px;
      font-size: var(--fs-back);
    }

    .card-watermark{
      right: 12px;
      bottom: 12px;
      height: 32px;
    }

    .site-footer{
      font-size: 12px;
      padding: 0px 8px 10px;
      transform: translateY(0);  /* no extra lift on SE */
    }

    /* If a flash message shows, keep it from eating the whole screen */
    .inline-msg{
      top: 8px;
      font-size: 12px;
      padding: 8px 10px;
    }

    /* --- LOCK SCROLL (softly) --- */
    html, body{
      overscroll-behavior: none;
    }
  } 
  
  /* Feature Request: add some space at the bottom of the text "Links expire..." only for landscape */
  @media (orientation: landscape) and (max-height: 700px) {
    .success-card p.expiry {
      margin-bottom: 20px !important;
    }
    .success-card {
      height: auto !important;
      min-height: var(--card-h) !important;
    }
  }
</style>
</head>
<body>

<div class="page-bg" aria-hidden="true"></div>

<?php if (!empty($inlineMsg)): ?>
  <div class="inline-msg"><?= htmlspecialchars((string)$inlineMsg, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="page-shell">
  <main class="page-center">
    <section class="success-card" role="region" aria-label="Success">
      <h1 class="title"><?= htmlspecialchars(t('success.heading') . ' ' . $firstName . '!', ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="motivation"><?= htmlspecialchars(t('success.subtext'), ENT_QUOTES, 'UTF-8') ?></p>

      <div class="actions">
        <?php if (!empty($certUrl)): ?>
          <a class="btn" href="<?= htmlspecialchars($certUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(t('btn.download_cert'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php endif; ?>
        <?php if (!empty($bonusUrl)): ?>
          <a class="btn" href="<?= htmlspecialchars($bonusUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(t('btn.download_bonus'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php endif; ?>
      </div>

      <p class="expiry"><?= htmlspecialchars(t('success.links_expire'), ENT_QUOTES, 'UTF-8') ?></p>
      <a class="back" href="/index.php"><?= htmlspecialchars(t('link.back'), ENT_QUOTES, 'UTF-8') ?></a>

      <img class="card-watermark" src="/assets/img/logo.svg" alt="" aria-hidden="true">
    </section>
  </main>

  <footer class="site-footer" role="contentinfo">
    Copyright <?= (int)COPYRIGHT_YEAR ?> <?= htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') ?>, All rights reserved.
  </footer>
</div>
<script src="/assets/js/lang-soft-nav.js?v=2025-11-08" defer></script>

<script>
(function(){
  // On leaving the success page (including bfcache navigation), mark next Access visit to reset the form.
  window.addEventListener('pagehide', function(){
    try { sessionStorage.setItem('demo_clear_claim_form', '1'); } catch(e){}
  });
})();
</script>
</body>
</html>