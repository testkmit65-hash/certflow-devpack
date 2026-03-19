<?php
declare(strict_types=1);

require_once __DIR__ . '/../code/app/config.php';

header('Cache-Control: private, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

/* -------------------------------------------------
 I18N bootstrap (cookie first, then helpers)
 ------------------------------------------------- */
(function () {
  $allowed = ['en','de']; $default = 'en';
  $chosen = null;

  if (isset($_GET['lang'])) {
    $g = strtolower(trim((string)$_GET['lang']));
    if (in_array($g, $allowed, true)) $chosen = $g;
  }
  if ($chosen === null && !empty($_COOKIE['demo_lang'])) {
    $c = strtolower(trim((string)$_COOKIE['demo_lang']));
    if (in_array($c, $allowed, true)) $chosen = $c;
  }
  if ($chosen === null) $chosen = $default;

  if (!isset($_COOKIE['demo_lang']) || $_COOKIE['demo_lang'] !== $chosen) {
    if (!headers_sent()) {
      setcookie('demo_lang', $chosen, [
        'expires'  => time() + 60*60*24*180,  // 180 days
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => false,
        'samesite' => 'Lax',
      ]);
    }
  }

  $GLOBALS['_DEMO_LANG'] = $chosen;
  unset($_GET['lang']);

  $projI18n = dirname(__DIR__) . '/code/app/i18n.php';
  if (is_file($projI18n)) { @require_once $projI18n; }

  if (!function_exists('demo_get_lang')) {
    function demo_get_lang(): string { return $GLOBALS['_DEMO_LANG'] ?? 'en'; }
    }
    if (!function_exists('t')) {
    function t(string $key): string {
        static $T = null;
        if ($T === null) {
          $en = [
          'access.subheading'       => 'Enter your details below to claim your free Certificate & Secret Bonus.',
          'field.first_name'        => 'First name *',
          'field.last_name'         => 'Last name *',
          'field.email'             => 'Email *',
          'field.secret_code'       => 'Secret Key Code *',
            'placeholder.secret_code' => 'Enter your 15-character code (A–Z, 0–9)',
          'helper.required'         => 'Kindly complete all required fields marked with *.',
          'btn.submit'              => 'Submit',
          'notice.privacy'          => 'We use the contact details you provide to respond to your requests and to operate our services; marketing emails are sent only if you opt in and you can unsubscribe at any time.',
          'notice.privacy.link'     => 'Privacy Information',
          'val.all_required'        => 'Please fill all required fields.',
          'val.email'               => 'Please enter a valid email address.',
          'val.secret'              => 'Invalid or missing Secret Key Code, please try again.',
          'val.cooldown'            => 'You can resubmit your form in {n} minutes.',
          'overlay.heading'         => 'Your well-deserved reward is being prepared...',
          'overlay.body'            => 'We are preparing your certificate and secret bonus. You will be redirected to the Success page in just a moment.',
          ];
          $de = [
          'access.subheading'       => 'Gib unten deine Daten ein, um dein gratis Zertifikat & Geheimen Bonus zu erhalten.',
          'field.first_name'        => 'Vorname *',
          'field.last_name'         => 'Nachname *',
          'field.email'             => 'E-Mail *',
          'field.secret_code'       => 'Geheimer Schlüsselcode *',
            'placeholder.secret_code' => 'Gib deinen 15-stelligen Code ein (A–Z, 0–9)',
          'helper.required'         => 'Bitte fülle alle mit * gekennzeichneten Pflichtfelder aus.',
          'btn.submit'              => 'Senden',
          'notice.privacy'          => 'Wir verwenden deine angegebenen Kontaktdaten, um auf deine Anfragen zu reagieren und unsere Dienste zu betreiben; Marketing-E-Mails senden wir nur, wenn du zugestimmt hast, und du kannst dich jederzeit abmelden.',
          'notice.privacy.link'     => 'Datenschutzhinweis',
          'val.all_required'        => 'Bitte fülle alle Pflichtfelder aus.',
          'val.email'               => 'Bitte gib eine gültige E-Mail-Adresse ein.',
          'val.secret'              => 'Ungültiger oder fehlender geheimer Schlüsselcode. Bitte versuche es erneut.',
          'val.cooldown'            => 'Du kannst das Formular in {n} Minuten erneut senden.',
          'overlay.heading'         => 'Deine wohlverdiente Belohnung wird vorbereitet...',
          'overlay.body'            => 'Wir bereiten dein Zertifikat und deinen geheimen Bonus vor. Du wirst in wenigen Augenblicken zur Erfolgsseite weitergeleitet.',
          ];
          $lang = demo_get_lang();
          $T = ($lang === 'de') ? array_replace($en, $de) : $en;
        }
        return $T[$key] ?? $key;
      }
    }
    if (!function_exists('tp')) {
    function tp(string $key, array $vars): string {
        $s = t($key);
      foreach ($vars as $k => $v) $s = str_replace('{'.$k.'}', (string)$v, $s);
        return $s;
      }
    }
  })();

$lang = demo_get_lang();

/* ---- flash/old form data ---- */
$flash = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);

$inlineMsg       = $flash['error']    ?? null;
$cooldownMessage = $flash['cooldown'] ?? null;

/* Localize cooldown if minutes are present */
$cooldownDisplay = $cooldownMessage;
if (is_string($cooldownMessage) && preg_match('/(\d+)/', $cooldownMessage, $m)) {
  $n = (int)$m[1];
  $cooldownDisplay = tp('val.cooldown', ['n' => $n]);
}

$old = [
  'first_name' => $flash['old']['first_name'] ?? '',
  'last_name'  => $flash['old']['last_name']  ?? '',
  'email'      => $flash['old']['email']      ?? '',
  'key'        => $flash['old']['key']        ?? '',
];

if (defined('DEMO_NO_INLINE_MSG')) {
  $inlineMsg = null;
  $cooldownDisplay = null;
}

if (!function_exists('e')) {
  function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
?>
<!doctype html>
<html lang="<?= e($lang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
  <link rel="preload" as="image" href="/assets/img/landing-bg-desktop.webp" type="image/webp" fetchpriority="high">
  <link rel="preload" as="image" href="/assets/img/landing-bg-mobile.webp" type="image/webp" media="(max-width: 480px)" fetchpriority="high">
  <link rel="prefetch" href="/privacy.php">
  <title><?= e(defined('CHALLENGE_NAME') ? CHALLENGE_NAME : 'Demo Challenge') ?></title>

  <style>
  :root{
    --ink-blue: #0F3558;
    --ink-orange: #FE8215;
    --white: #FFFFFF;

    /* Instant background placeholders (prevent blue first paint) */
    --bg-lqip-desktop: url("data:image/webp;base64,UklGRpgAAABXRUJQVlA4IIwAAACwBACdASpAACQAPzGGvleuqCYkqBqqqdAmCUAZjmv4AFS0Zi1u7k7bANk/Ih+AAPbiVue6h/oV3Z8Rdn/bzmz28bx/qvNfDmkW5gG1oP7ln9Uuhh+dvvq9WFzf1DrBuZhBQ8mMXyI4bOaO8nD16rZdk1CbWRB7hGwVG4QfS4zKQBHGiZZ/JICpMgAAAA==");
    --bg-lqip-mobile:  url("data:image/webp;base64,UklGRrYAAABXRUJQVlA4IKoAAABwBQCdASokAE4APzGCt1SuqCWjLNgMcdAmCWMAv7AAwxygPWHFpHLD3xupJ+Yqv5FTMTQAAPcDvToZzbX9CvWX302P+onYjxgTkjGijV050Jl6Xk9aXpIVfis6itElg4CvB4Ywz7OvWqKSaM60ViAKdTXPqmxliAnbrqYM45hQddUC9MSqXB7taYcxczhSuiDSaqB1SzvQ/HirHTZgK50/mw8hZxcSeggAAA==");

    --card-w: 740px;
    --card-h: 680px;
    --card-pad: 20px;
    --card-offset-y: 54px;   /* + down / - up */
    --col-w: 500px;
    --col-w-notice: 663px;

    --fs-title: 40px;
    --fs-intro: 18px;
    --fs-label: 16px;
    --fs-input: 16px;
    --fs-btn: 32px;
    --fs-small: 14px;
    --fs-error: 16px;

    --gap-title-intro: 15px;
    --gap-intro-form: 25px;
    --gap-meta: 6px;
    
    /* spacing controls for helper / error / privacy */
    --gap-helper-error: 12px;    /* space between the helper line and the error slot */
    --gap-error-notice: 12px;    /* space between the error slot and the privacy paragraph */
    --error-min-height: 28px;    /* reserved height for the error area (prevents layout jump) */

    /* Button pinning (adjust these) */
    --btn-bottom: 20px;     /* distance from card bottom to button */
    --btn-reserved: 140px;  /* reserved space inside card for the button area */

    --input-filled-bg: #EEF1F5;
    --footer-raise: 38px;
  }

  /* DE-only: slightly narrower notice column to reduce rivers */
  html[lang="de"] :root{ --col-w-notice: 640px; --fs-intro: 17px; --fs-label: 15px; --fs-small: 12px; }

  /* EN-only: match German alignment */
  html[lang="en"] :root{ --col-w-notice: 640px; }  /* same line width as DE */
  html[lang="en"] .notice{ text-align-last: left; } /* no stretched last line */

  /* Modern browsers: nicer line breaks */
  @supports (text-wrap: pretty) { html[lang="de"] .notice { text-wrap: pretty; } }

    html{
    background-color: var(--ink-blue);
    background-image: url('/assets/img/landing-bg-desktop.webp'), var(--bg-lqip-desktop);
    background-position: center center, center center;
    background-size: cover, cover;
    background-repeat: no-repeat, no-repeat;
  }

  body{
    font-family: Helvetica, Arial, sans-serif;
    margin:0; padding:0; min-height:100vh;
    background:transparent; color:#111;
    display:flex; flex-direction:column; overflow-x:hidden;
    position:relative; z-index:1;
  }
  @supports (min-height:100dvh){ body{ min-height:100dvh; } }

  .page-bg{
    position:fixed; inset:0;
    width: 100vw; height: 100vh;
    height: 100dvh;
    background-color: var(--ink-blue);
    background-image: url('/assets/img/landing-bg-desktop.webp'), var(--bg-lqip-desktop);
    background-position: center center, center center;
    background-size: cover, cover;
    background-repeat: no-repeat, no-repeat;
    z-index:0; pointer-events:none; transform:translateZ(0);
  }

    /* Flags (top-right) — hover on IMG only */
  .lang-switch {
    position: absolute;
    top: 16px;
    right: 16px;
    display: flex;
    gap: 12px;
    z-index: 10;
  }

  .lang-switch a {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
  }

  .lang-switch a img {
    width: 80px;
    height: 55px;
    display: block;
    transition: transform .08s ease;
    will-change: transform;
    transform: translateZ(0);
  }

  .lang-switch a:hover img {
    transform: translateY(-1px);
  }

  .lang-switch a:active img {
    transform: translateY(0);
  }

  .lang-switch .lang-label {
    margin-top: 4px;
    font-size: 24px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.6);
  }

  .page-center{
    min-height: calc(100vh - 110px);
    display:grid;
    place-items:center;
    padding: 16px;
    box-sizing:border-box;
    position:relative;
    z-index: 1; /* normal state: flags can sit above if they need to */
  }

  .page-center.processing-active{
    z-index: 20; /* only while overlay is active: above .lang-switch (z:10) and footer */
  }

  .access-card{
    position:relative;
    width: var(--card-w); height: var(--card-h);
    border-radius: 30px;
    background: rgba(15, 53, 88, 0.70);
    padding: var(--card-pad);
    padding-bottom: calc(var(--card-pad) + var(--btn-reserved)); /* reserve space for pinned button */
    transform: translateY(var(--card-offset-y));
    box-sizing: border-box;
    box-shadow: 0 10px 50px rgba(0,0,0,.20);
    isolation: isolate;
    display:flex; flex-direction:column; align-items:center; gap: 6px;
  }

  /* IMPORTANT: .stack is NOT positioned, so .actions anchors to .access-card */
  .meta-block { position: relative; z-index: 1; }
  .col input { position: relative; z-index: 2; }
  .card-watermark { z-index: 0; mix-blend-mode: normal; }

  .card-watermark{
    position:absolute; right: 18px; bottom: 18px;
    height: 65px; width:auto; opacity:.9;
    filter: drop-shadow(0 1px 2px rgba(0,0,0,.35));
    pointer-events:none; user-select:none;
  }

  .stack { width: 100%; display:flex; flex-direction:column; align-items:center; }
  .col   { width: var(--col-w); display:flex; flex-direction:column; gap:8px; }

  .title{
    margin: 0 0 var(--gap-title-intro) 0;
    text-align:center; color:var(--white);
    font-weight:700; font-size: var(--fs-title); line-height:1.1; letter-spacing:.2px;
  }
  .intro{
    margin: 0 0 var(--gap-intro-form) 0;
    text-align:center; color:var(--white);
    font-weight:400; font-size: var(--fs-intro); line-height:1.3;
  }

  label{ color:var(--white); font-weight:400; font-size: var(--fs-label); margin: 4px 0 2px 0; }
  input[type="text"], input[type="email"]{
    width: 100%; height: 40px; background:#fff; border:none; border-radius:6px;
    padding:0 12px; font-size: var(--fs-input); box-sizing:border-box; color:#111;
    -webkit-appearance:none; appearance:none; background-clip: padding-box;
  }
  input.has-value{ background-color: var(--input-filled-bg) !important; }
  #key_code{ background-color:#fff; color:#111; }
  #key_code.has-value{ background-color: var(--input-filled-bg) !important; }

  input:-webkit-autofill{
    -webkit-text-fill-color: #111;
    -webkit-box-shadow: 0 0 0 1000px var(--input-filled-bg) inset;
    box-shadow: 0 0 0 1000px var(--input-filled-bg) inset;
  }

  .meta-block{
    left: 50%; transform: translateX(-50%);
    width: min(var(--col-w-notice), calc(var(--card-w) - (2 * var(--card-pad))));
    display:flex; flex-direction:column; align-items:center; gap: 0; margin-top: 6px;
    }

  .helper, .inline-slot{ width: var(--col-w-notice); }

  .helper{
    margin: 0 0 var(--gap-helper-error) 0;        /* controls space below helper */
  }

  .inline-slot{
    min-height: var(--error-min-height);          /* reserve space even when empty */
    margin: 0 0 var(--gap-error-notice) 0;        /* controls space below error area */
  }
  
  .inline-msg{
    margin:0; color: var(--ink-orange); font-size: var(--fs-error); font-weight:700; text-align:center;
    white-space: nowrap;
  }
  .helper{
    color: var(--white);
    font-weight: 400;
    font-size: var(--fs-small);
    text-align: center;
    margin: 0 0 var(--gap-helper-error) 0; /* << now uses the variable */
  }

  /* Justified privacy block with sensible last-line handling */
  .notice{
    width:100%; margin:0; color:var(--white); font-weight:400; font-size: var(--fs-small);
    line-height:1.35;
    text-align: justify;
    text-justify: inter-word;
    text-align-last: left;        /* default: good for DE */
    hyphens: auto;
    overflow-wrap: break-word;
    word-break: normal;
  }
  .notice a{ color:#fff; font-weight:700; text-decoration:underline; }

  /* Pinned actions (Submit button) */
  .actions{
    position: absolute;
    left: 50%;
    bottom: var(--btn-bottom);
    transform: translateX(-50%);
    display: flex;
    justify-content: center;
    width: 100%;
    margin-top: 0;
    z-index: 3;
  }

  .btn-submit{
    width:175px; height:70px; background: var(--ink-orange); border: 2pt solid var(--ink-blue); border-radius:6px;
    color:#fff; font-weight:700; font-size: var(--fs-btn); line-height:65px; text-align:center; cursor:pointer; display:inline-block;
    transition: transform .08s ease, box-shadow .08s ease;
    position: relative; z-index: 4;
  }
  .btn-submit:hover{ transform: translateY(-1px); box-shadow: 0 8px 20px rgba(0,0,0,.30); }
  .btn-submit:active{ transform: translateY(0);    box-shadow: 0 4px 10px rgba(0,0,0,.20); }

    .site-footer{
    text-align:center; padding: 12px 16px 24px; color:#fff; font-weight:700; font-size:22px; line-height:1.2;
    text-shadow:0 1px 2px rgba(0,0,0,.25); z-index:1; transform: translateY(var(--footer-raise));
  }

  @media (max-height: 640px){ .access-card{ height:auto; } }

/* ----------------------------------------------------------------------
   T27.1 — TABLETS + SMALL LAPTOPS (PORTRAIT & LANDSCAPE)
   Targets:
   - iPad mini: 768×1024 (P), 1024×768 (L)
   - iPad Air:  820×1180 (P), 1180×820 (L)
   - Laptops:   1366×768, 1440×900
---------------------------------------------------------------------- */
@media (min-width: 700px) and (max-width: 1600px) and (min-height: 700px) {

  /* --- V5b: lock the viewport (no scroll/drag) --- */
  html, body{
    height: 100%;
    overflow: hidden;
    overscroll-behavior: none;
}

  :root{
    /* ======================
       CARD SIZE + CENTERING
       ======================*/
    --t27-card-w: 600px;                 /* card width knob */
    --t27-card-h: 580px;                 /* card height knob */
    --t27-card-pad: 18px;                /* internal padding knob */
    --t27-footer-space: 85px;            /* reserve space for footer (affects vertical centering) */
    --t27-card-offset-y: 42px;            /* + moves card DOWN, - moves card UP */

    /* Wire into existing variables (do not touch outside this @media) */
    --card-w: min(var(--t27-card-w), calc(100vw - 96px));
    --card-h: var(--t27-card-h);
    --card-pad: var(--t27-card-pad);

    /* Button stays bottom-anchored (stable), but fully adjustable via T27 knobs */
    --btn-bottom: var(--t27-gap-button-to-bottom);
    --btn-reserved: calc(
        var(--t27-btn-h)
      + var(--t27-gap-button-to-bottom)
      + var(--t27-gap-privacy-to-button)
     );

    /* =================
       TEXT SIZE KNOBS
       =================*/
    --t27-fs-title: 32px;                /* Heading */
    --t27-fs-intro: 15px;                /* Subheading */
    --t27-fs-label: 14px;                /* Text above entry fields */
    --t27-fs-input: 14px;                /* Text inside entry fields */
    --t27-fs-helper: 12px;               /* Information text */
    --t27-fs-error: 13px;                 /* error message text size */
    --t27-fs-notice: 12px;               /* Privacy notice text */
    --t27-fs-btn: 30px;                  /* Submit button text */
    --t27-fs-foot: 18px;                 /* Footnote text */
    --t27-fs-flag: 16px;                 /* Flag text size */

    /* Wire into existing title/intro system */
    --fs-title: var(--t27-fs-title);
    --fs-intro: var(--t27-fs-intro);

    /* ==============
       SPACING KNOBS 
       ==============*/
    --t27-gap-title-to-intro: 10px;       /* heading -> subheading */
    --t27-gap-intro-to-form: 18px;        /* subheading -> first field */
    --t27-gap-label-to-input: 6px;        /* label -> input */
    --t27-gap-input-to-next-label: 14px;  /* input -> next label */
    --t27-gap-fields-to-info: 10px;       /* last field -> info text */
    --t27-inline-slot-h: 20px;            /* error slot reserved height */
    --t27-gap-helper-to-slot: 6px;       /* helper text -> inline slot */
    --t27-gap-slot-to-privacy: 1px;      /* inline slot -> privacy notice */
    --t27-gap-privacy-to-button: 12px;    /* privacy notice -> submit button */
    --t27-gap-button-to-bottom: 18px;      /* submit button -> card bottom */

    /* wire knobs into the vars your base CSS actually uses */
    --error-min-height: var(--t27-inline-slot-h);
    --fs-error: var(--t27-fs-error);

    /* Wire into existing gap system used by .intro/.helper/.inline-slot */
    --gap-title-intro: var(--t27-gap-title-to-intro);
    --gap-intro-form: var(--t27-gap-intro-to-form);
    --gap-helper-error: var(--t27-gap-helper-to-slot);
    --gap-error-notice: var(--t27-gap-slot-to-privacy);

    /* =========================
       BUTTON SIZE KNOBS
       ========================= */
    --t27-btn-w: 145px;
    --t27-btn-h: 55px;

    /* =========================
       FLAGS (WIDTH adjustable, HEIGHT auto/proportional)
       ========================= */
    --t27-flag-w: 56px;                  /* width knob */
    --t27-flag-gap: 8px;                 /* spacing between flags */
    --t27-lang-top: 16px;
    --t27-lang-right: 18px;

    /* ============================
       WATERMARK (POSITION + SIZE)
       ============================ */
    --t27-wm-h: 52px;
    --t27-wm-right: 18px;
    --t27-wm-bottom: 18px;
  }

  /* Make centering behave consistently */
  .page-center{
    min-height: calc(100svh - var(--t27-footer-space));
    min-height: calc(100vh  - var(--t27-footer-space)); /* fallback */
    display: grid;
    place-items: center;
  }

  .access-card{
    transform: translateY(var(--t27-card-offset-y));
  }

  /* Heading/subheading gap is already controlled by variables above.
     Now apply the remaining adjustable text sizes: */
  .col label{ font-size: var(--t27-fs-label); }
  .col input{ font-size: var(--t27-fs-input); }
  .helper{ font-size: var(--t27-fs-helper); }
  .inline-slot{ min-height: var(--t27-inline-slot-h); font-size: var(--t27-fs-helper); }
  .notice{ font-size: var(--t27-fs-notice); }
  .site-footer{ font-size: var(--t27-fs-foot); }

  /* Field spacing control (label/input rhythm) */
  .col{ gap: 0; }
  .col label{ margin: 0 0 var(--t27-gap-label-to-input) 0; }
  .col input{ margin: 0 0 var(--t27-gap-input-to-next-label) 0; }
  .col input:last-of-type{ margin-bottom: 0; }

  /* Gap between last field and info text */
  .meta-block{ margin-top: var(--t27-gap-fields-to-info); }

  /* Flags */
  .lang-switch{
    top: var(--t27-lang-top);
    right: var(--t27-lang-right);
    gap: var(--t27-flag-gap);
  }

  /* flag images: base CSS uses .lang-switch a img (not .lang-switch img) */
  .lang-switch a img{
    width: var(--t27-flag-w) !important;
    height: auto !important;
  }

  .lang-switch .lang-label{
    font-size: var(--t27-fs-flag);
  }

  /* Watermark */
  .card-watermark{
    right: var(--t27-wm-right);
    bottom: var(--t27-wm-bottom);
    height: var(--t27-wm-h);
  }

    /* ✅ Submit button pinned to card bottom (stable), offset adjustable */
  .access-card .actions{
    position: absolute;
    left: 50%;
    bottom: var(--btn-bottom);
    transform: translateX(-50%);
    margin: 0;
  }

  .btn-submit{
    width: var(--t27-btn-w);
    height: var(--t27-btn-h);
    font-size: var(--t27-fs-btn);
    line-height: calc(var(--t27-btn-h) - 4px);
    border: 1.5pt solid var(--ink-blue);
    border-radius:6px;
  }
}

  /* Processing overlay (T13) */
  .processing-overlay{
    position: fixed;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    background: rgba(4, 10, 20, 0.78);
    z-index: 12000; /* above flags, footer & other UI */
  }
  .processing-overlay.is-visible{
    display: flex;
  }
  
/* Block interactions + scrolling while processing overlay is visible */
  html.processing-lock,
  body.processing-lock{
  overflow: hidden !important;
  height: 100% !important;
  touch-action: none;
  }

/* Make flags unclickable while processing */
  body.processing-lock .lang-switch{
  pointer-events: none !important;
  }

/* Ensure overlay itself captures taps */
  .processing-overlay{ pointer-events: none; }
  .processing-overlay.is-visible{ pointer-events: auto; }

  .processing-card{
    max-width: 480px;
    width: 100%;
    background: rgba(8, 18, 35, 0.96);
    border-radius: 24px;
    padding: 32px 28px;
    box-shadow: 0 18px 40px rgba(0,0,0,0.45);
    text-align: center;
    color: #ffffff;
  }
  .processing-card h2{
    margin: 0 0 12px;
    font-size: 24px;
    line-height: 1.3;
    font-weight: 800;
  }
  .processing-card p{
    margin: 0 0 24px;
    font-size: 16px;
    line-height: 1.5;
    font-weight: 400;
  }
  .processing-spinner{
    width: 40px;
    height: 40px;
    border-radius: 999px;
    border: 3px solid rgba(255,255,255,0.25);
    border-top-color: var(--ink-orange); /* T13: brand orange indicator */
    animation: ink-processing-spin 0.9s linear infinite;
    margin: 0 auto;
  }
  @keyframes ink-processing-spin{
    to{ transform: rotate(360deg); }
  }

  /*------------------------------------------------------------------------------
    Access page – Split-screen / Multi-window bridge (481–699px width) – PORTRAIT
  -------------------------------------------------------------------------------*/
  @media (min-width: 481px) and (max-width: 699px) and (orientation: portrait){

    /* Optional: prevent accidental page scrolling in split windows */
    html, body{
      height: 100%;
      overflow: hidden;
      overscroll-behavior: none;
    }

    :root{
      /* ======================
         ACCESS CARD (W/H/SHIFT)
         ====================== */
      --b1-card-w: 460px;        /* knob: card width target */
      --b1-card-h: 505px;        /* knob: card height */
      --b1-card-pad: 16px;       /* knob: internal padding */
      --b1-card-offset-y: 55px;  /* knob: +down / -up */

      --card-w: var(--b1-card-w);
      --card-h: var(--b1-card-h);
      --card-pad: var(--b1-card-pad);
      --card-offset-y: var(--b1-card-offset-y);

      /* Keep notice/helper widths inside the card */
      --col-w: calc(var(--card-w) - (2 * var(--card-pad)));
      --col-w-notice: var(--col-w);

      /* ======================
         TEXT SIZES (PX KNOBS)
         ====================== */
      --fs-title: 28px;
      --fs-intro: 14px;
      --fs-label: 12px;
      --fs-input: 12px;
      --fs-small: 10px;
      --fs-error: 11px;

      /* ======================
         SPACING KNOBS
         ====================== */
      --gap-title-intro: 8px;
      --gap-intro-form: 4px;

      /* Spacing between error slot and privacy text */
      --gap-helper-error: 6px;
      --gap-error-notice: -4px;
      --error-min-height: 22px;

      /* ======================
         SUBMIT BUTTON (ALL KNOBS)
         ====================== */
      --b1-btn-w: 120px;       /* knob */
      --b1-btn-h: 48px;        /* knob */
      --b1-btn-line: 52px;     /* knob */
      --b1-fs-btn: 26px;       /* knob: font-size in px */
      --b1-btn-bottom: 16px;   /* knob: vertical position inside card */
      
      --fs-btn: var(--b1-fs-btn);
      --btn-bottom: var(--b1-btn-bottom);

      /* Reserve enough space at the bottom inside the card for the pinned button area */
      --b1-btn-reserved: 120px; /* knob (keep card content from colliding with button) */
      --btn-reserved: var(--b1-btn-reserved);

      /* ======================
         FOOTER (INDEPENDENT KNOBS)
         ====================== */
      --b1-fs-footer: 14px;     /* knob: footer font size */
      --b1-footer-raise: 65px;  /* knob: footer vertical position */
      --footer-raise: var(--b1-footer-raise);

      /* ======================
         FLAGS (ALL KNOBS)
         ====================== */
      --b1-flag-w: 46px;        /* knob: width (height auto) */
      --b1-flag-gap: 6px;       /* knob */
      --b1-lang-top: 10px;      /* knob */
      --b1-lang-right: 14px;    /* knob */

      /* IMPORTANT (your request): EN/DE label sizes separately */
      --b1-fs-lang-en: 13px;    /* knob */
      --b1-fs-lang-de: 13px;    /* knob */
    }

    /* Column: save height */
    .col{
      width: 100%;
      row-gap: 5px;
    }

    /* Field dimensions */
    input[type="text"],
    input[type="email"]{
      height: 32px;      /* ← adjust this for SE only */
    }

    /* Keep footer space predictable at these heights */
    .page-center{
      min-height: calc(100vh - 110px);
      padding: 12px;
      box-sizing: border-box;
    }

    .card-watermark {
      height: 48px;
      right: 16px;
      bottom: 16px;
    }

    /* Submit button: size + font knobs */
    .btn-submit{
      width: var(--b1-btn-w);
      height: var(--b1-btn-h);
      font-size: var(--b1-fs-btn);
      line-height: var(--b1-btn-line);
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1.5pt solid var(--ink-blue);
      border-radius:6px;
    }

    /* Footer: font-size knob */
    .site-footer{
      font-size: var(--b1-fs-footer);
    }

    /* Flags: position + size knobs */
    .lang-switch{
      top: var(--b1-lang-top);
      right: var(--b1-lang-right);
      gap: var(--b1-flag-gap);
    }
    .lang-switch a img{
      width: var(--b1-flag-w) !important;
      height: auto !important;
    }

    /* EN/DE label font knobs */
    .lang-switch a[data-lang="en"] .lang-label{ font-size: var(--b1-fs-lang-en); }
    .lang-switch a[data-lang="de"] .lang-label{ font-size: var(--b1-fs-lang-de); }
  }

  /*----------------------------------------------------------------------
    Access page – phone portrait layout (common phones up to ~480px width) 
    ----------------------------------------------------------------------*/
  @media (max-width: 480px) {

    :root{
      /* Adjustable knobs for the 390/393 portrait group */
      --card-w: min(calc(100vw - 32px), 740px);
      --card-h: 620px;           /* NEW: card height (adjust) */
      --card-pad: 12px;
      --card-offset-y: 28px;   /* + down / - up */


      --btn-reserved: 80px;      /* space reserved inside card for button/logo area */
      --btn-bottom: 12px;        /* NEW: vertical button position (adjust) */
      --fs-btn: 26px;            /* NEW: submit text size (adjust) */

      --footer-space: 56px;      /* NEW: space reserved so the footer can stay visible */
      --footer-raise: 18px;       /* NOTE: moves footer up/down; does NOT change footer height */

      --col-w: 100%;
      --col-w-notice: 100%;

      --fs-title: 24px;
      --fs-intro: 14px;
      --fs-label: 13px;
      --fs-input: 13px;
      --fs-small: 11px;
      --fs-error: 13px;

      --gap-title-intro: 8px;
      --gap-intro-form: 12px;
      --gap-helper-error: 8px;
      --gap-error-notice: 8px;
    }

    /* Use dedicated mobile background */
    html,
    .page-bg {
      background-image: url('/assets/img/landing-bg-mobile.webp'), var(--bg-lqip-mobile);
      background-position: center, center;
      background-size: cover, cover;
    }

    .page-center{
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: calc(100vh - var(--footer-space));
      padding: 0 8px 8px 8px; /* Handled top padding securely with before pseudo element */
      box-sizing: border-box;
    }

    /* Safe vertical centering algorithm using solid flex spacers */
    .page-center::before,
    .page-center::after {
      content: "";
      display: block;
      flex-grow: 1; /* Automatically absorb surplus empty space exactly equivalent to auto margin */
    }

    .page-center::before {
      min-height: 80px; /* Absolutely guarantees space for the flag row at the top */
      flex-shrink: 0;   /* Top spacer must NEVER be crushed */
    }

/* Better on modern mobile browsers (dynamic viewport) */
  @supports (min-height: 100dvh){
    .page-center{
      min-height: calc(100dvh - var(--footer-space));
      }
    }

    /* Card: height is content-based again, no internal scroll on normal phones */
    .access-card{
      width: var(--card-w);
      border-radius: 22px;
      padding: var(--card-pad);
      padding-bottom: calc(var(--card-pad) + var(--btn-reserved));
      margin: 0 auto; /* Removed potentially faulty vertical auto margin */
      flex-shrink: 0; /* Ensures the card itself doesn't collapse */
      transform: none !important;
      box-sizing: border-box;
      overflow-y: visible; /* card + viewport scroll together if ever needed */
    }

    /* Flags – smaller and less dominant */
    .lang-switch{
      top: 8px;
      right: 15px;
      gap: 6px;
    }

    .lang-switch a img{
      width: 48px;
      height: 32px;
    }

    .lang-switch .lang-label{
      margin-top: 0;
      font-size: 12px;
      letter-spacing: 0.12em;
    }

    /* Less space above the title */
    .title{
      margin-top: 8px;  /* adjust this if you want the title higher/lower */
    }

    /* Helper + inline messages should wrap nicely */
    .inline-msg{
      white-space: normal;
    }

    /* Slightly smaller button, centred label */
    .btn-submit{
      width: 120px;   /* adjust if you want it narrower/wider */
      height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      line-height: 1.1;
      border: 1.5pt solid var(--ink-blue);
      border-radius:6px;
    }

    /* DemoBrand watermark – smaller on phones */
    .card-watermark{
      right: 12px;
      bottom: 12px;
      height: 40px;  /* adjust if you want a smaller/larger logo */
    }

    /* Footer – one line, comfortable size */
    .site-footer{
      font-size: 12px;
      padding: 8px 10px 14px;
    }

    /* Processing overlay remains as in the last good round */
    .processing-card{
      max-width: min(420px, 100%);
      max-height: calc(100vh - 48px);
      padding: 20px 16px;
      box-sizing: border-box;
    }
    .processing-card h2{
      font-size: 20px;
    }
    .processing-card p{
      font-size: 14px;
    }
  }

   /* Drag-scroll allowed to hide URL bar */
  @media (min-width: 360px) and (max-width: 480px) and (orientation: portrait){
    html, body{
      /* let Safari naturally hide toolbars */
    }
  }

  /* ------------------------------------------------------------------------
  T28.1 — iPhone SE (375×667), PORTRAIT (short phones) — Access page
  Only affects portrait phones <= 700px height (does NOT touch 390×844 etc.)
-----------------------------------------------------------------------------*/
@media (orientation: portrait)
  and (min-width: 360px) and (max-width: 430px)
  and (max-height: 700px){

  :root{
    --card-h: 545px;
    --card-pad: 12px;

    --btn-reserved: 76px;
    --btn-bottom: 10px;
    --fs-btn: 24px;

    --fs-title: 22px;
    --fs-intro: 13px;
    --fs-label: 12px;
    --fs-input: 12px;
    --fs-small: 10px;
    --fs-error: 12px;

    --gap-title-intro: 6px;
    --gap-intro-form: 8px;
    --gap-helper-error: 6px;
    --gap-error-notice: 3px;

    /* footer knob (independent) */
    --t281se-pt-footer-raise: 21.5px;     /* footer only */
    --footer-raise: var(--t281se-pt-footer-raise);

    --t28-card-offset-y: 28px;          /* adjust: + down / - up */
  }

  /* Use dedicated mobile background */
    html,
    .page-bg {
    background-image: url('/assets/img/landing-bg-mobile.webp'), var(--bg-lqip-mobile);
    background-position: center, center;
    background-size: cover, cover;
  }

  .access-card{
    transform: translateY(var(--t28-card-offset-y));
    border-radius: 24px;
  }

  input[type="text"], input[type="email"]{
    height: 34px;
  }

  .btn-submit{
    height: 42px;
  }

  .card-watermark{
    height: 36px;
  }
}

  /* -----------------------------------------------------
    T28.2 — iPhone X (375×812), PORTRAIT — Access page
  --------------------------------------------------------*/
  @media (orientation: portrait)
    and (min-width: 375px) and (max-width: 389px)
    and (min-height: 780px) and (max-height: 830px){

    :root{
      --t28x-card-offset-y: 28px; /* + down / - up (does NOT affect footer) */

  /* FOOTER knob (does NOT affect card position) */
      --t28x-footer-raise: 22.5px;    /* smaller = higher, bigger = lower */
      --footer-raise: var(--t28x-footer-raise);
    }

     /* Use dedicated mobile background */
    html,
    .page-bg {
      background-image: url('/assets/img/landing-bg-mobile.webp'), var(--bg-lqip-mobile);
      background-position: center, center;
      background-size: cover, cover;
    }

    .access-card{
      transform: translateY(var(--t28x-card-offset-y));
    }
  }

  /* -----------------------------------------------------------
    T28.3 — iPhone XR (414×896), PORTRAIT — Access page
  --------------------------------------------------------------*/
  @media (orientation: portrait) and (width: 414px) and (height: 896px){

    :root{
      --t28xr-card-offset-y: 28px; /* + down / - up */
    }

    /* Use dedicated mobile background */
    html,
    .page-bg {
      background-image: url('/assets/img/landing-bg-mobile.webp'), var(--bg-lqip-mobile);
      background-position: center, center;
      background-size: cover, cover;
    }

    .access-card{
      transform: translateY(var(--t28xr-card-offset-y));
    }
  }

  /* -----------------------------------------------------------
    T28.4 — iPhone 14 Pro Max (430×932), PORTRAIT — Access page
  --------------------------------------------------------------*/
  @media (orientation: portrait) and (width: 430px) and (height: 932px){

    :root{
      /* CARD knobs (do NOT affect footer position) */
      --t284-card-offset-y: 28px;   /* + down / - up */
      --t284-card-h: 620px;         /* card height for this viewport only */

      /* FOOTER knob (does NOT affect card position) */
      --t284-footer-raise: 18px;    /* smaller = higher, bigger = lower */

      /* Wire into existing variables */
      --card-h: var(--t284-card-h);
      --footer-raise: var(--t284-footer-raise);
    }

    /* Use dedicated mobile background */
    html,
    .page-bg {
      background-image: url('/assets/img/landing-bg-mobile.webp'), var(--bg-lqip-mobile);
      background-position: center, center;
      background-size: cover, cover;
    }

    .access-card{
      transform: translateY(var(--t284-card-offset-y));
    }
  }

  /* -----------------------------------------------------------
    T28.5 — Samsung Galaxy S8+ (360×740), PORTRAIT — Access page
  --------------------------------------------------------------*/
  @media (orientation: portrait) and (width: 360px) and (height: 740px){
    :root{
      --t285s8-card-offset-y: 28px; /* was 28px in generic phone portrait */
      --t285s8-card-h: 600px;         /* card height for this viewport only */
      --t285s8-footer-space: 56px;  /* was 56px in generic phone portrait */

      --fs-btn:   22px;   /* font size button */

      /* wire into existing knobs */
      --card-offset-y: var(--t285s8-card-offset-y);
      --card-h: var(--t285s8-card-h);
      --footer-space: var(--t285s8-footer-space);
    }

    .btn-submit {
      width: 105px;
      height: 40px;
      border-radius: 6px;
      border: 1.5pt solid var(--ink-blue);
      font-size: var(--fs-btn);
    }
  }

  /*------------------------------------ 
    iPhone SE small (320x568), PORTRAIT
    -----------------------------------*/
  @media (max-width: 340px) {

    :root{
      /* SE-only vertical offset for the whole card */
      --card-offset-y-se: 24px;   /* try 12–16px and adjust to taste */
      --flag-height-se: 26px;   /* ← change this to grow/shrink the flags */
      --flag-label-se: 12px;    /* ← change this to grow/shrink EN/DE text */
    }
    /* Move the entire card (and everything inside it) slightly down */
    .access-card{
      transform: translateY(var(--card-offset-y-se));
    }

    /* Bring the title closer to the top edge of the card */
    .title{
      margin-top: 12px;   /* try 8px, then 4px, then 0px until it feels right */
    }

      /* SE-only field dimensions */
    input[type="text"],
    input[type="email"]{
      height: 28px;      /* ← adjust this for SE only */
      padding: 4px 10px; /* ← vertical / horizontal padding on SE */
      font-size: 11px;   /* or var(--fs-input) if you prefer */
    }

    /* Core layout knobs for SE – you can tweak these numbers yourself */
    :root,
    html[lang="en"] :root,
    html[lang="de"] :root {
      /* Card geometry */
      --card-w: 300px;          /* total card width */
      --card-h: 470px;          /* total card height – should fit 320×568 */
      --card-pad: 0px;          /* padding inside the card */
      --col-w: 260px;
      --col-w-notice: 260px;

      /* Typography – smaller than the 390/393 layout */
      --fs-title: 22px;
      --fs-intro: 13px;
      --fs-label: 12px;
      --fs-input: 12px;
      --fs-btn: 22px;
      --fs-small: 10px;
      --fs-error: 11px;

      /* Vertical gaps inside the card */
      --gap-title-intro: 6px;   /* title ↔ intro */
      --gap-intro-form: 4px;    /* intro ↔ first field */
      --gap-meta: 4px;
      --gap-helper-error: 4px;  /* helper ↔ error line */
      --gap-error-notice: 4px;  /* error line ↔ privacy text */
      --error-min-height: 24px; /* reserved space for error line */

      /* Button + footer spacing */
      --btn-bottom: 10px;       /* button ↔ card bottom */
      --btn-reserved: 80px;     /* reserved vertical space for button area */
      --footer-raise: 10px;     /* space above footer */
    }

    /* Use dedicated mobile background on very small phones */
    html,
    .page-bg {
      background-image: url('/assets/img/landing-bg-mobile.webp'), var(--bg-lqip-mobile);
      background-position: center, center;
      background-size: cover, cover;
    }

    /* Lock page height so card + footer sit inside the viewport */
    body {
      overflow-y: hidden;   /* no page scrolling on SE */
    }

    .page-center {
      min-height: calc(100vh - 40px); /* leaves room for footer line */
      padding: 8px;
      box-sizing: border-box;
    }

    .access-card {
      height: var(--card-h);
      max-height: calc(100vh - 80px); /* safety margin for very small heights */
      border-radius: 24px;
    }

    /* Fields a bit tighter for this viewport */
    .col {
      gap: 3px;
    }

    .col input {
      min-height: 25px;
      padding: 6px 10px;
    }

    /* Helper + inline error + privacy block */
    .helper {
      margin-bottom: var(--gap-helper-error);
    }

    .inline-slot {
      min-height: var(--error-min-height);
      margin-bottom: var(--gap-error-notice);
    }

    /* SE: privacy notice as justified text */
    .notice {
      text-align: justify;
      text-align-last: left; /* last line stays clean on the left */
      hyphens: auto;         /* let the browser hyphenate where possible */
    }

    /* Submit button & watermark */
    .actions {
      bottom: var(--btn-bottom);
    }

    .btn-submit {
      width: 105px;
      height: 40px;
      border-radius: 6px;
      border: 1.5pt solid var(--ink-blue);
      font-size: var(--fs-btn);
    }

    .card-watermark {
      height: 36px;
      right: 12px;
      bottom: 12px;
    }

    /* Flags stay the same size, just tuck them a bit closer to the edge */
    .lang-switch {
      top: 10px;
      right: 10px;
    }

    .lang-switch{
    top: 8px;        /* keep or tweak as you like */
    right: 8px;
    gap: 4px;
  }

  /* Flags: height fixed via variable, width keeps aspect ratio */
  .lang-switch a img{
    height: var(--flag-height-se);
    width: auto;
    display: block;
  }

  /* EN / DE label size */
  .lang-switch .lang-label{
    font-size: var(--flag-label-se);
    letter-spacing: 0.12em;   /* keep or tweak if you want it tighter/looser */
  }
}

/*----------------------------------------------------------------------------
    Access page – Split-screen / short-height window (461–699px tall) – LANDSCAPE
    Covers the gap between phone-landscape (<=460h) and T27 (>=700h)
    ------------------------------------------------------------------------------*/
  @media (orientation: landscape)
    and (min-width: 700px) and (max-width: 1600px)
    and (min-height: 461px) and (max-height: 699px){

        /* Lock viewport in this landscape gap (prevents any vertical scroll) */
    html, body{
      height: 100%;
      overflow: hidden;
      overscroll-behavior: none;
    }

    :root{
      /* ACCESS CARD (independent knobs) */
      --b1ls-card-w: 460px;
      --b1ls-card-h: 505px;
      --b1ls-card-pad: 16px;
      --b1ls-card-offset-y: 55px;

      --card-w: var(--b1ls-card-w);
      --card-h: var(--b1ls-card-h);
      --card-pad: var(--b1ls-card-pad);
      --card-offset-y: var(--b1ls-card-offset-y);

      /* Keep notice/helper widths inside the card */
      --col-w: calc(var(--card-w) - (2 * var(--card-pad)));
      --col-w-notice: var(--col-w);

      /* Spacing between error slot and privacy paragraph */
      --gap-helper-error: 6px;
      --gap-error-notice: -4px;
      --error-min-height: 22px;

      /* Spacing between Headline and Sub-headline */
      --gap-title-intro: 8px;
      --gap-intro-form: 4px;

      /* Text sizing (start values; adjust as needed) */
      --fs-title: 28px;
      --fs-intro: 14px;
      --fs-label: 12px;
      --fs-input: 12px;
      --fs-small: 10px;
      --fs-error: 11px;

      /* Submit button knobs */
      --b1ls-btn-w: 120px;
      --b1ls-btn-h: 48px;
      --b1ls-btn-line: 52px;
      --b1ls-fs-btn: 26px;
      --b1ls-btn-bottom: 16px;

      --fs-btn: var(--b1ls-fs-btn);
      --btn-bottom: var(--b1ls-btn-bottom);

      /* Reserve enough space for the pinned button zone */
      --b1ls-actions-h: var(--b1ls-btn-h);
      --btn-reserved: calc(var(--b1ls-actions-h) + var(--btn-bottom) + 22px);

      /* Footer knobs (independent from card) */
      --b1ls-fs-footer: 14px;
      --b1ls-footer-raise: 65px;
      --footer-raise: var(--b1ls-footer-raise);

      /* Flags knobs */
      --b1ls-flag-w: 46px;
      --b1ls-flag-gap: 6px;
      --b1ls-lang-top: 10px;
      --b1ls-lang-right: 14px;

      --b1ls-fs-lang-en: 13px;
      --b1ls-fs-lang-de: 13px;
    }

    /* Override the global (max-height:640px) height:auto rule (fixes 900×600 consistency) */
    .access-card{ height: var(--card-h) !important; }

    /* Column: save height */
    .col{
      width: 100%;
      row-gap: 5px;
    }

    /* Field dimensions */
    input[type="text"],
    input[type="email"]{
      height: 32px;      /* ← adjust this for SE only */
    }

    .card-watermark {
      height: 48px;
      right: 16px;
      bottom: 16px;
    }

    /* Button sizing */
    .btn-submit{
      width: var(--b1ls-btn-w);
      height: var(--b1ls-btn-h);
      font-size: var(--b1ls-fs-btn);
      line-height: var(--b1ls-btn-line);
      display:flex;
      align-items:center;
      justify-content:center;
      border: 1.5pt solid var(--ink-blue);
      border-radius:6px;
    }

    /* Footer sizing */
    .site-footer{ font-size: var(--b1ls-fs-footer); }

    /* Flags: size + position */
    .lang-switch{
      top: var(--b1ls-lang-top);
      right: var(--b1ls-lang-right);
      gap: var(--b1ls-flag-gap);
    }
    .lang-switch a img{
      width: var(--b1ls-flag-w) !important;
      height: auto !important;
    }

    /* EN/DE label font sizes */
    .lang-switch a[data-lang="en"] .lang-label{ font-size: var(--b1ls-fs-lang-en); }
    .lang-switch a[data-lang="de"] .lang-label{ font-size: var(--b1ls-fs-lang-de); }
  }

    /* same section – short-height override (fix 900×600) */
  @media (orientation: landscape)
  and (min-width: 700px) and (max-width: 1600px)
  and (min-height: 461px) and (max-height: 620px){
    /* only affects 900×600 etc., does NOT touch 650px height */
    :root{
      --b1ls-card-offset-y: 32px;
      --b1ls-footer-raise: 20px;
    }
  }

/* -------------------------------------------------------------------
  CH9 — Foldable / split-screen (720×557), LANDSCAPE — Access page
  Card Y + footnote Y independently adjustable (no cross-effects)
----------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 720px) and (height: 557px){

  html, body{ overscroll-behavior: none; overflow: hidden; height: 100%; }

  :root{
    /* ===== CARD knobs (card-only) ===== */
    --ch9f720-card-h: 495px;
    --ch9f720-ls-card-offset-y: 14px;        /* + down / - up (card only) */
    --card-offset-y: var(--ch9f720-ls-card-offset-y);

    /* ===== FOOTER knobs (footer-only) ===== */
    --ch9f720-ls-footer-h: 32px;             /* footer element height */
    --ch9f720-ls-footer-space: 28px;         /* reserve space so card can center */
    --ch9f720-ls-footer-offset-y: 0px;       /* + down / - up (footer only) */
  }

  .access-card{ height: var(--ch9f720-card-h) !important; }

  /* Center card independently from footer */
  .page-center{
    min-height: 0;
    height: calc(100svh - var(--ch9f720-ls-footer-space));
    height: calc(100vh  - var(--ch9f720-ls-footer-space)); /* fallback */
    /* keep existing bridge padding feel */
    padding: var(--ls-top-band) 10px 10px;

    display: grid;
    place-items: center;
  }

  /* Pin footer; move it without affecting card */
  .site-footer{
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;

    height: var(--ch9f720-ls-footer-h);
    padding: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    transform: translateY(var(--ch9f720-ls-footer-offset-y));
    z-index: 5;
  }
}

  /*--------------------------------------------------------------------
    CH9 — Foldable / split-screen (1104×598), LANDSCAPE — Access page
    Footnote Y adjustable only (white card must NOT move)
  ----------------------------------------------------------------------*/
  @media (orientation: landscape) and (width: 1104px) and (height: 598px){

    :root{
      --ch9f1104-ls-footer-shift-y: 16px; /* + down / - up (footer only) */
    }

    /* move footer visually; does not affect card layout */
    .site-footer{
      transform: translateY(var(--ch9f1104-ls-footer-shift-y));
    }
  }

  /*--------------------------------------------------------------------
    CH9 — Foldable / split-screen (900×600), LANDSCAPE — Access page
    Footnote Y adjustable only (white card must NOT move)
  ----------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 900px) and (height: 600px){

  :root{
    --ch9f900-ls-footer-shift-y: 18px; /* + down / - up (footer only) */
  }

  /* move footer visually; does not affect card layout */
  .site-footer{
    transform: translateY(var(--ch9f900-ls-footer-shift-y));
  }
}

  /*------------------------------------------------------------------------------------------
    T26.1 — Phone group iPhone 12/13/14 (844×390) & Pixel 5 (851×393), LANDSCAPE (Access page)
  --------------------------------------------------------------------------------------------*/
  @media (orientation: landscape)
    and (min-width: 640px) and (max-width: 1024px)
    and (min-height: 361px) and (max-height: 460px) {

    html, body{ overscroll-behavior: none; }

    /* T26.1: prevent 1–2px rounding overflow causing tiny scroll */
    html, body { overflow-y: hidden; }

    :root,
    html[lang="en"] :root,
    html[lang="de"] :root{
      --card-w: min(calc(100vw - 20px), 600px);
      --card-pad: 12px;
      --t261-ls-card-max-h: 335px;
      --card-offset-y: 12px;      /* + down / - up */

      /* top band so flags sit on orange background */
      --ls-top-band: 15px;

      /* reserved vertical space for inline error line */
      --error-min-height: 18px;

      /* pinned submit button area */
      --btn-bottom: 12px;
      --actions-h: 42px;              /* must match .btn-submit height */
      --gap-notice-actions: 2px;
      --btn-reserved: calc(var(--actions-h) + var(--btn-bottom) + var(--gap-notice-actions));

      --col-w: 100%;
      --col-w-notice: 100%;

      --fs-title: 22px;
      --fs-intro: 13px;
      --fs-label: 11px;
      --fs-input: 11px;
      --fs-btn:   22px;
      --fs-small: 10px;
      --fs-error: 11px;

      --gap-title-intro: 4px;
      --gap-intro-form: 6px;
      --gap-helper-error: 4px;
      --gap-error-notice: -7px;
    }

    .page-center{
      min-height: calc(100vh - 22px);
      padding: var(--ls-top-band) 10px 10px;
      place-items: start center;
    }

    /* ensure overlay sits above fixed flags */
    .page-center.processing-active{
      z-index: 20000 !important;
    }

    .access-card{
      width: var(--card-w);
      height: var(--t261-ls-card-max-h);
      border-radius: 24px;
      padding: var(--card-pad);
      padding-bottom: calc(var(--card-pad) + var(--btn-reserved));
      transform: translateY(var(--card-offset-y));
    }

    .title{ margin-top: 0px; }
    label{ margin: 3px 0 1px 0; }

    input[type="text"], input[type="email"]{
      height: 26px;
      padding: 0 10px;
      font-size: var(--fs-input);
    }

    /* 2-column: First/Last name side-by-side to save height */
    .col{
      width: 100%;
      display: grid;
      grid-template-columns: 1fr 1fr;
      column-gap: 12px;
      row-gap: 3px;
      align-items: end;
      grid-template-areas:
        "fnLabel lnLabel"
        "fnInput lnInput"
        "emailLabel emailLabel"
        "emailInput emailInput"
        "keyLabel keyLabel"
        "keyInput keyInput"
        "meta meta";
    }

    label[for="first_name"]{ grid-area: fnLabel; }
    #first_name{ grid-area: fnInput; }
    label[for="last_name"]{ grid-area: lnLabel; }
    #last_name{ grid-area: lnInput; }
    label[for="email"]{ grid-area: emailLabel; }
    #email{ grid-area: emailInput; }
    label[for="key_code"]{ grid-area: keyLabel; }
    #key_code{ grid-area: keyInput; }

    .meta-block{
      grid-area: meta;
      left: auto;
      transform: none;
      width: 100%;
      margin-top: 6px;
    }

    .notice{
      font-size: var(--fs-small);
      line-height: 1.2;
      margin: 0;
    }

    .helper, .inline-slot{ width: 100%; }
    .helper{ margin: 0 0 var(--gap-helper-error) 0; }

    .inline-slot{
      min-height: var(--error-min-height);
      margin: 0 0 var(--gap-error-notice) 0;
    }

    .inline-msg{
      white-space: normal;
      line-height: 1.1;
      margin: 0;
    }

    .btn-submit{
      width: 120px;
      height: var(--actions-h);
      font-size: var(--fs-btn);
      border: 1.5pt solid var(--ink-blue);
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
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

    .lang-switch a img{
      width: 44px;
      height: 28px;
    }

    .lang-switch .lang-label{
      font-size: 11px;
      margin-top: 0;
    }

    .site-footer{
      font-size: 12px;
      padding: 1px 8px 10px;
      transform: none;
    }

    .processing-card{
      max-width: min(460px, 100%);
      padding: 18px 16px;
    }
    .processing-card h2{ font-size: 20px; }
    .processing-card p{ font-size: 13px; margin-bottom: 14px; }

    /* T26.1: prevent background flicker during soft language swap (same fix as SE-landscape) */
    html.demo-swap-freeze { contain: none !important; }
  }

/* ------------------------------------------------------
  Z Fold cover (832×384), LANDSCAPE — Access page
  Independent vertical tuning (card vs footer)
---------------------------------------------------------*/
@media (orientation: landscape) and (width: 832px) and (height: 384px){

  :root{
    /* ===== CARD knob (does NOT affect footer) ===== */
    --zf-cover-ls-card-offset-y: 9px;   /* + down / - up */

    /* ===== FOOTER knob (does NOT affect card) ===== */
    --zf-cover-ls-footer-raise: 38px;    /* + down / - up (transform only) */

    /* wire into existing vars */
    --card-offset-y: var(--zf-cover-ls-card-offset-y);
    --footer-raise:  var(--zf-cover-ls-footer-raise);
  }
}

/*-------------------------------------------------------------------
  T26.1b — CH8 — Pixel 8 Pro (e.g. ~998×448), LANDSCAPE — Access page
---------------------------------------------------------------------*/
@media (orientation: landscape)
  and (min-width: 951px) and (max-width: 1024px)
  and (min-height: 431px) and (max-height: 460px){

  :root,
  html[lang="en"] :root,
  html[lang="de"] :root{
    /* Card: vertical offset knob (independent) */
    --card-offset-y: 9px;                 /* + down / - up */

    /* Footer: independent vertical offset knob */
    --t261b-footer-offset-y: 0px;         /* + down / - up */
  }

  /* Make the card truly centered in the usable area */
  .page-center{
    place-items: center;
    height: calc(100vh - 22px);           /* keep footer room stable */
    min-height: calc(100vh - 22px);
  }

  /* Footer: allow independent tuning (does NOT affect card) */
  .site-footer{
    transform: translateY(var(--t261b-footer-offset-y));
  }
}

/* ------------------------------------------------------------------------
  T28.1 — 667×375 LANDSCAPE (medium landscape phones) — Access page
---------------------------------------------------------------------------*/
@media (orientation: landscape)
  and (min-width: 640px) and (max-width: 799px)
  and (min-height: 361px) and (max-height: 430px){

  html, body{ overscroll-behavior: none; overflow: hidden; height: 100%; }

  :root{
    /* ===== knobs ===== */
    --t28-ls-card-w-max: 470px;
    --t28-ls-card-h: 320px;
    --t28-ls-card-offset-y: 10px;      /* + down / - up */

    --t28-ls-footer-h: 32px;          /* fixed footer element height */
    --t28-ls-footer-space: 24px;      /* reserved space for centering (independent!) */

    --t28-ls-pad-x: 10px;
    --t28-ls-pad-y: 8px;

    --t28-ls-gap-helper-to-slot: 6px;
    --t28-ls-inline-slot-h: 16px;
    --t28-ls-gap-slot-to-privacy: -2px; /* can be negative if you really want */
    --t28-ls-gap-privacy-to-button: 2px;

    /* ===== wire into existing vars ===== */
    --card-w: min(calc(100vw - (2 * var(--t28-ls-pad-x))), var(--t28-ls-card-w-max));
    --card-h: var(--t28-ls-card-h);
    --card-pad: 10px;

    --error-min-height: var(--t28-ls-inline-slot-h);
    --gap-helper-error: var(--t28-ls-gap-helper-to-slot);
    --gap-notice-actions: var(--t28-ls-gap-privacy-to-button);

    /* smaller typography for 667×375 */
    --fs-title: 20px;
    --fs-intro: 12px;
    --fs-label: 10px;
    --fs-input: 10px;
    --fs-btn:   22px;
    --fs-small: 9px;
    --fs-error: 10px;

    /* pinned button area */
    --btn-bottom: 10px;
    --actions-h: 38px; /* must match .btn-submit height */
    --btn-reserved: calc(var(--actions-h) + var(--btn-bottom) + var(--gap-notice-actions));

    /* used by the centering box */
    --footer-space: var(--t28-ls-footer-space);
  }

  /* True centering box (more stable on mobile than min-height) */
  .page-center{
    height: calc(100svh - var(--footer-space));
    height: calc(100vh  - var(--footer-space)); /* fallback */
    padding: var(--t28-ls-pad-y) var(--t28-ls-pad-x);
    box-sizing: border-box;
    display: grid;
    place-items: center;
  }

  .access-card{
    width: var(--card-w);
    height: var(--card-h); /* FIXED */
    max-height: calc(100svh - var(--footer-space) - (2 * var(--t28-ls-pad-y)));
    padding: var(--card-pad);
    padding-bottom: calc(var(--card-pad) + var(--btn-reserved));
    overflow: hidden;
    transform: translateY(var(--t28-ls-card-offset-y));
    border-radius: 24px;
  }

  label{ margin: 2px 0 1px 0; }
  input[type="text"], input[type="email"]{
    height: 24px;
    padding: 0 9px;
    font-size: var(--fs-input);
  }

  .helper{ margin: 0 0 var(--gap-helper-error) 0; }

  /* This is the ONLY “slot ↔ privacy” controller you need */
  .inline-slot{
    min-height: var(--error-min-height);
    margin: 0 0 var(--t28-ls-gap-slot-to-privacy) 0 !important;
  }

  .notice{
    font-size: var(--fs-small);
    line-height: 1.2;
    margin: 0;
  }

  .btn-submit{
    height: var(--actions-h);
    width: 108px;
    font-size: var(--fs-btn);
    line-height: 1;
    border: 1.5pt solid var(--ink-blue);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .lang-switch a img{ width: 40px; height: 26px; }
  .lang-switch .lang-label{ font-size: 10px; margin-top: 0; }

  /* Fixed footer (no side padding) */
  .site-footer{
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    height: var(--t28-ls-footer-h);
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    line-height: 1;
    transform: none;
    z-index: 5;
  }
}

  /* ------------------------------------------------------------------------
    T28.2 — iPhone X (812×375), LANDSCAPE — Access page
  ---------------------------------------------------------------------------*/
  @media (orientation: landscape)
    and (min-width: 800px) and (max-width: 830px)
    and (max-height: 380px){

    html, body{ overscroll-behavior: none; overflow: hidden; height: 100%; }

    :root{
      /* ===== knobs ===== */
      --t28x-ls-card-w-max: 580px;
      --t28x-ls-card-h: 330px;
      --t28x-ls-card-offset-y: 6px;     /* + down / - up */

      --t28x-ls-footer-h: 32px;         /* fixed footer element height */
      --t28x-ls-footer-space: 28px;     /* reserved space for centering (independent!) */
      --t28x-ls-footer-offset-y: -0.5px;   /* + down / - up (footer only) */

      --t28x-ls-pad-x: 10px;
      --t28x-ls-pad-y: 8px;

      --t28x-ls-gap-helper-to-slot: 4px;
      --t28x-ls-inline-slot-h: 16px;
      --t28x-ls-gap-slot-to-privacy: -7px;
      --t28x-ls-gap-privacy-to-button: 2px;

      /* ===== wire into existing vars ===== */
      --card-w: min(calc(100vw - (2 * var(--t28x-ls-pad-x))), var(--t28x-ls-card-w-max));
      --card-h: var(--t28x-ls-card-h);
      --card-pad: 12px;

      --error-min-height: var(--t28x-ls-inline-slot-h);
      --gap-helper-error: var(--t28x-ls-gap-helper-to-slot);
      --gap-notice-actions: var(--t28x-ls-gap-privacy-to-button);

      --btn-bottom: 12px;
      --actions-h: 42px; /* must match .btn-submit height */
      --btn-reserved: calc(var(--actions-h) + var(--btn-bottom) + var(--gap-notice-actions));

      --footer-space: var(--t28x-ls-footer-space);
    }

    .page-center{
      height: calc(100svh - var(--footer-space));
      height: calc(100vh  - var(--footer-space)); /* fallback */
      padding: var(--t28x-ls-pad-y) var(--t28x-ls-pad-x);
      box-sizing: border-box;
      display: grid;
      place-items: center;
    }

    .access-card{
      width: var(--card-w);
      height: var(--card-h); /* FIXED */
      max-height: calc(100svh - var(--footer-space) - (2 * var(--t28x-ls-pad-y)));
      padding: var(--card-pad);
      padding-bottom: calc(var(--card-pad) + var(--btn-reserved));
      overflow: hidden;
      transform: translateY(var(--t28x-ls-card-offset-y));
      border-radius: 24px;
    }

    /* keep the existing 2-column grid from T26.1; just tighten vertical rhythm a bit */
    label{ margin: 3px 0 1px 0; }
    input[type="text"], input[type="email"]{ height: 26px; padding: 0 10px; }

    .inline-slot{
      min-height: var(--error-min-height);
      margin: 0 0 var(--t28x-ls-gap-slot-to-privacy) 0 !important;
    }
    .notice{ line-height: 1.2; margin: 0; }

    /* flags: keep T26.1 sizes/placement, but lock it in this scope */
    .lang-switch{ position: fixed; top: 8px; right: 10px; gap: 6px; z-index: 50; }
    .lang-switch a img{ width: 44px; height: 28px; }
    .lang-switch .lang-label{ font-size: 11px; margin-top: 0; }

    /* fixed footer (independent from card) */
    .site-footer{
      position: fixed;
      left: 0; right: 0; bottom: 0;
      height: var(--t28x-ls-footer-h);
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      line-height: 1;
      transform: translateY(var(--t28x-ls-footer-offset-y));
      z-index: 5;
    }
  }

  /* ------------------------------------------------------------------------
    T28.3 — iPhone XR (896×414), LANDSCAPE — Access page
  ---------------------------------------------------------------------------*/
  @media (orientation: landscape) and (width: 896px) and (height: 414px){

    html, body{ overscroll-behavior: none; overflow: hidden; height: 100%; }

    :root{
      /* ===== knobs ===== */
      --t28xr-ls-card-w-max: 600px;
      --t28xr-ls-card-h: 340px;
      --t28xr-ls-card-offset-y: 10px;      /* + down / - up (card only) */

      --t28xr-ls-footer-h: 32px;          /* footer element height */
      --t28xr-ls-footer-space: 28px;      /* reserved space for centering (independent) */
      --t28xr-ls-footer-offset-y: -2px;    /* + down / - up (footer only) */

      --t28xr-ls-pad-x: 10px;
      --t28xr-ls-pad-y: 8px;

      --t28xr-ls-gap-helper-to-slot: 4px;
      --t28xr-ls-inline-slot-h: 18px;
      --t28xr-ls-gap-slot-to-privacy: -6px;
      --t28xr-ls-gap-privacy-to-button: 2px;

      /* ===== wire into existing vars ===== */
      --card-w: min(calc(100vw - (2 * var(--t28xr-ls-pad-x))), var(--t28xr-ls-card-w-max));
      --card-h: var(--t28xr-ls-card-h);
      --card-pad: 12px;

      --error-min-height: var(--t28xr-ls-inline-slot-h);
      --gap-helper-error: var(--t28xr-ls-gap-helper-to-slot);
      --gap-notice-actions: var(--t28xr-ls-gap-privacy-to-button);

      --btn-bottom: 12px;
      --actions-h: 42px; /* must match .btn-submit height */
      --btn-reserved: calc(var(--actions-h) + var(--btn-bottom) + var(--gap-notice-actions));

      --footer-space: var(--t28xr-ls-footer-space);
    }

    /* True centering box */
    .page-center{
      height: calc(100svh - var(--footer-space));
      height: calc(100vh  - var(--footer-space)); /* fallback */
      padding: var(--t28xr-ls-pad-y) var(--t28xr-ls-pad-x);
      box-sizing: border-box;
      display: grid;
      place-items: center;
    }

    .access-card{
      width: var(--card-w);
      height: var(--card-h); /* FIXED */
      max-height: calc(100svh - var(--footer-space) - (2 * var(--t28xr-ls-pad-y)));
      padding: var(--card-pad);
      padding-bottom: calc(var(--card-pad) + var(--btn-reserved));
      overflow: hidden;
      transform: translateY(var(--t28xr-ls-card-offset-y));
      border-radius: 24px;
    }

    /* tighten vertical rhythm slightly */
    label{ margin: 3px 0 1px 0; }
    input[type="text"], input[type="email"]{ height: 26px; padding: 0 10px; }

    .inline-slot{
      min-height: var(--error-min-height);
      margin: 0 0 var(--t28xr-ls-gap-slot-to-privacy) 0 !important;
    }
    .notice{ line-height: 1.2; margin: 0; }

    /* flags: lock placement for this scope */
    .lang-switch{ position: fixed; top: 8px; right: 10px; gap: 6px; z-index: 50; }
    .lang-switch a img{ width: 44px; height: 28px; }
    .lang-switch .lang-label{ font-size: 11px; margin-top: 0; }

    /* fixed footer */
    .site-footer{
      position: fixed;
      left: 0; right: 0; bottom: 0;
      height: var(--t28xr-ls-footer-h);
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      line-height: 1;
      transform: translateY(var(--t28xr-ls-footer-offset-y));
      z-index: 5;
    }
  }

  /* -----------------------------------------------------------
    T28.4 — iPhone 14 Pro Max (932×430), LANDSCAPE — Access page
  --------------------------------------------------------------*/
  @media (orientation: landscape) and (width: 932px) and (height: 430px){

    html, body{ overscroll-behavior: none; overflow: hidden; height: 100%; }

    :root{
      /* ===== CARD knobs (do NOT affect footer position) ===== */
      --t284pm-ls-card-h: 350px;          /* card height */
      --t284pm-ls-card-offset-y: 10px;     /* + down / - up */

      /* ===== FOOTER knobs (do NOT affect card position) ===== */
      --t284pm-ls-footer-h: 32px;         /* footer element height */
      --t284pm-ls-footer-space: 28px;     /* reserved centering space (card-only) */
      --t284pm-ls-footer-offset-y: 0px;   /* + down / - up (footer only) */

      --t284pm-ls-pad-x: 10px;
      --t284pm-ls-pad-y: 8px;

      /* wire into existing vars */
      --card-h: var(--t284pm-ls-card-h);
      --footer-space: var(--t284pm-ls-footer-space);
    }

    /* True centering box */
    .page-center{
      height: calc(100svh - var(--footer-space));
      height: calc(100vh  - var(--footer-space)); /* fallback */
      padding: var(--t284pm-ls-pad-y) var(--t284pm-ls-pad-x);
      box-sizing: border-box;
      display: grid;
      place-items: center;
    }

    .access-card{
      height: var(--card-h); /* FIXED */
      max-height: calc(100svh - var(--footer-space) - (2 * var(--t284pm-ls-pad-y)));
      overflow: hidden;
      transform: translateY(var(--t284pm-ls-card-offset-y));
    }

    /* Fixed footer */
    .site-footer{
      position: fixed;
      left: 0; right: 0; bottom: 0;
      height: var(--t284pm-ls-footer-h);
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      line-height: 1;
      transform: translateY(var(--t284pm-ls-footer-offset-y));
      z-index: 5;
    }
  }

  /* -----------------------------------------------------------
  T28.5 — Samsung Galaxy S8+ (740×360), LANDSCAPE — Access page
  --------------------------------------------------------------*/
@media (orientation: landscape) and (width: 740px) and (height: 360px){

  :root{
    /* ===== CARD knobs (do NOT affect footer position) ===== */
    --t285s8-ls-card-offset-y: 14px;     /* + down / - up */

    /* ===== FOOTER knobs (do NOT affect card position) ===== */
    --t285s8-ls-footer-h: 32px;         /* footer element height */
    --t285s8-ls-footer-space: 28px;     /* reserved centering space (card-only) */
    --t285s8-ls-footer-offset-y: 2.5px;   /* + down / - up (footer only) */

    --t285s8-ls-pad-x: 10px;
    --t285s8-ls-pad-y: 8px;

    /* wire into existing vars */
    --card-offset-y: var(--t285s8-ls-card-offset-y);
  }

  /* Center the card, independent from footer */
  .page-center{
    min-height: 0 !important;
    height: calc(100svh - var(--t285s8-ls-footer-space)) !important;
    height: calc(100vh  - var(--t285s8-ls-footer-space)) !important; /* fallback */
    padding: var(--t285s8-ls-pad-y) var(--t285s8-ls-pad-x) !important;
    display: grid !important;
    place-items: center !important;
  }

  /* Footer: fixed + independently adjustable */
  .site-footer{
    position: fixed !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;

    height: var(--t285s8-ls-footer-h) !important;
    padding: 0 !important;

    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    transform: translateY(var(--t285s8-ls-footer-offset-y)) !important;
    z-index: 5 !important;
  }
}

/* -------------------------------------------------------------------
  CH9 — Samsung Galaxy A51/A71 (914×412), LANDSCAPE — Access page
  Goal: card vertically centered + card Y adjustable, footer unaffected
----------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 914px) and (height: 412px){

  html, body{ overscroll-behavior: none; overflow: hidden; height: 100%; }

  :root{
    /* ===== CARD knobs (do NOT affect footer position) ===== */
    --ch9a51-ls-card-offset-y: 14px;       /* + down / - up (card only) */

    /* ===== FOOTER knobs (do NOT affect card position) ===== */
    --ch9a51-ls-footer-h: 32px;            /* footer element height */
    --ch9a51-ls-footer-space: 28px;        /* reserved centering space (card-only) */
    --ch9a51-ls-footer-offset-y: 2px;      /* + down / - up (footer only) */

    --ch9a51-ls-pad-x: 10px;
    --ch9a51-ls-pad-y: 8px;

    /* wire into existing vars */
    --card-offset-y: var(--ch9a51-ls-card-offset-y);
  }

  /* Center the card, independent from footer */
  .page-center{
    min-height: 0;
    height: calc(100svh - var(--ch9a51-ls-footer-space));
    height: calc(100vh  - var(--ch9a51-ls-footer-space)); /* fallback */
    padding: var(--ch9a51-ls-pad-y) var(--ch9a51-ls-pad-x);
    display: grid;
    place-items: center;
  }

  /* Footer: fixed + independently adjustable */
  .site-footer{
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;

    height: var(--ch9a51-ls-footer-h);
    padding: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    transform: translateY(var(--ch9a51-ls-footer-offset-y));
    z-index: 5;
  }
}

/* -------------------------------------------------------------------
  T28.6 — Samsung Galaxy S20 Ultra (915×412), LANDSCAPE — Access page
----------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 915px) and (height: 412px){

  html, body{ overscroll-behavior: none; overflow: hidden; height: 100%; }

  :root{
    /* ===== CARD knobs (do NOT affect footer position) ===== */
    --t286s20-ls-card-offset-y: 14px;       /* + down / - up (card only) */

    /* ===== FOOTER knobs (do NOT affect card position) ===== */
    --t286s20-ls-footer-h: 32px;           /* footer element height */
    --t286s20-ls-footer-space: 28px;       /* reserved centering space (card-only) */
    --t286s20-ls-footer-offset-y: 2px;     /* + down / - up (footer only) */

    --t286s20-ls-pad-x: 10px;
    --t286s20-ls-pad-y: 8px;

    /* wire into existing vars */
    --card-offset-y: var(--t286s20-ls-card-offset-y);
  }

  /* Center the card, independent from footer */
  .page-center{
    min-height: 0;
    height: calc(100svh - var(--t286s20-ls-footer-space));
    height: calc(100vh  - var(--t286s20-ls-footer-space)); /* fallback */
    padding: var(--t286s20-ls-pad-y) var(--t286s20-ls-pad-x);
    display: grid;
    place-items: center;
  }

  /* Footer: fixed + independently adjustable */
  .site-footer{
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;

    height: var(--t286s20-ls-footer-h);
    padding: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    transform: translateY(var(--t286s20-ls-footer-offset-y));
    z-index: 5;
  }
}

  /* ---------------------------------------
    iPhone SE small (568×320), LANDSCAPE
     ---------------------------------------*/
  @media (max-width: 950px) and (max-height: 360px) and (orientation: landscape) {

      /* T22: stop edge “rubber-band” / overscroll (prevents tiny drag movement at top/bottom) */
    html, body{
      overscroll-behavior: none;
    }
    
    :root,
    html[lang="en"] :root,
    html[lang="de"] :root{
      --card-w: min(calc(100vw - 16px), 520px);
      --card-pad: 8px;

      /* --- knobs --- */
      --ls-top-band: 0px;           /* space above the card so flags sit on orange bg */
      --error-min-height: 14px;      /* reserved height for error slot (tune here) */

      --btn-bottom: 8px;             /* distance from card bottom to button */
      --actions-h: 36px;             /* must match .btn-submit height */
      --gap-notice-actions: 2px;     /* spacing between privacy notice and button area */
      --btn-reserved: calc(var(--actions-h) + var(--btn-bottom) + var(--gap-notice-actions));

      --col-w: 100%;
      --col-w-notice: 100%;

      --fs-title: 22px;
      --fs-intro: 12px;
      --fs-label: 11px;
      --fs-input: 11px;
      --fs-btn: 22px;
      --fs-small: 9px;
      --fs-error: 11px;

      --gap-title-intro: 4px;
      --gap-intro-form: 6px;
      --gap-helper-error: 4px;
      --gap-error-notice: -8px;
    }

    .page-center{
      min-height: calc(100vh - 22px);
      padding: var(--ls-top-band) 6px 6px;
      place-items: start center;
    }

    /* T22: ensure overlay (inside .page-center) sits above fixed flags */
    .page-center.processing-active{
      z-index: 20000 !important;
    }

    .access-card{
      width: var(--card-w);
      height: auto;
      border-radius: 24px;
      padding: var(--card-pad);
      padding-bottom: calc(var(--card-pad) + var(--btn-reserved));
    }

    .title{ margin-top: 2px; }
    label{ margin: 2px 0 1px 0; }

    input[type="text"], input[type="email"]{
      height: 22px;
      padding: 0 8px;
      font-size: var(--fs-input);
    }

    /* 2-column: First/Last name side-by-side to save height */
    .col{
      width: 100%;
      display: grid;
      grid-template-columns: 1fr 1fr;
      column-gap: 10px;
      row-gap: 2px;
      align-items: end;
      grid-template-areas:
        "fnLabel lnLabel"
        "fnInput lnInput"
        "emailLabel emailLabel"
        "emailInput emailInput"
        "keyLabel keyLabel"
        "keyInput keyInput"
        "meta meta";
    }

    label[for="first_name"]{ grid-area: fnLabel; }
    #first_name{ grid-area: fnInput; }
    label[for="last_name"]{ grid-area: lnLabel; }
    #last_name{ grid-area: lnInput; }
    label[for="email"]{ grid-area: emailLabel; }
    #email{ grid-area: emailInput; }
    label[for="key_code"]{ grid-area: keyLabel; }
    #key_code{ grid-area: keyInput; }

    .meta-block{
      grid-area: meta;
      left: auto;
      transform: none;
      width: 100%;
      margin-top: 4px;
    }

    .notice{
      font-size: var(--fs-small);
      line-height: 1.2;
    }

    /* Meta block spacing (SE landscape) */
    .helper, .inline-slot{ width: 100%; }
    .helper{ margin: 0 0 var(--gap-helper-error) 0; }

    .inline-slot{
      min-height: var(--error-min-height);          /* reserves space when empty */
      margin: 0 0 var(--gap-error-notice) 0;        /* THIS is the adjustable gap */
    }

    .inline-msg{
      white-space: normal;
      line-height: 1.05;
      margin: 0;
    }

    /* keep notice tight; gap is controlled by --gap-error-notice above */
    .notice{ margin: 0; }

    .btn-submit{
      width: 105px;
      height: var(--actions-h);
      font-size: var(--fs-btn);
      border: 1.5pt solid var(--ink-blue);
      display: flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
    }

    .card-watermark{
      height: 36px;
      right: 10px;
      bottom: 10px;
    }

    .lang-switch{
      position: fixed;   /* key change: no longer relative to .page-center/card */
      top: 6px;
      right: 8px;
      gap: 4px;
      z-index: 50;
    }

    .lang-switch a img{
      width: 40px;
      height: 26px;
    }
    .lang-switch .lang-label{
      font-size: 10px;
      margin-top: 0;
    }

    .site-footer{
      font-size: 12px;
      padding: 20px 8px 6px;
    }

    /* Keep overlay usable in 320px height */
    .processing-card{
      max-width: min(420px, 100%);
      padding: 16px 14px;
    }
    .processing-card h2{ font-size: 18px; }
    .processing-card p{ font-size: 12px; margin-bottom: 16px; }

    /* T22: prevent background flicker during soft language swap (iPhone SE landscape) */
    html.demo-swap-freeze { contain: none !important; }
  }
  </style>
</head>
<body>
  <div class="page-bg" aria-hidden="true"></div>

    <!-- Language switch -->
  <div class="lang-switch" aria-label="Language selection">
    <a href="/?lang=en" aria-label="English" data-lang="en">
      <img src="/assets/img/flag-en.svg" alt="English">
      <span class="lang-label">EN</span>
    </a>
    <a href="/?lang=de" aria-label="Deutsch" data-lang="de">
      <img src="/assets/img/flag-de.svg" alt="Deutsch">
      <span class="lang-label">DE</span>
    </a>
  </div>

  <div class="page-center">
  <div class="access-card" data-val-req="<?= e(t('val.all_required')) ?>" data-val-email="<?= e(t('val.email')) ?>" data-val-secret="<?= e(t('val.secret')) ?>">
    <div class="stack">
      <h1 class="title"><?= e(defined('CHALLENGE_NAME') ? CHALLENGE_NAME : 'Demo Challenge'); ?></h1>
      <p class="intro"><?= t('access.subheading') ?></p>

      <form id="claimForm" method="post" action="submit.php" novalidate autocomplete="off" class="col">
        <label for="first_name"><?= t('field.first_name') ?></label>
        <input id="first_name" name="first_name" type="text" required autocomplete="given-name" value="<?= e($old['first_name']) ?>">

        <label for="last_name"><?= t('field.last_name') ?></label>
        <input id="last_name" name="last_name" type="text" required autocomplete="family-name" value="<?= e($old['last_name']) ?>">

        <label for="email"><?= t('field.email') ?></label>
        <input id="email" name="email" type="email" required autocomplete="email" autocapitalize="none" spellcheck="false" value="<?= e($old['email']) ?>">

        <label for="key_code"><?= t('field.secret_code') ?></label>
        <input id="key_code" name="key_code" type="text" placeholder="<?= e(t('placeholder.secret_code')) ?>" maxlength="15" required autocomplete="one-time-code" autocapitalize="characters" spellcheck="false" value="<?= e($old['key']) ?>">

        <div class="meta-block">
          <p class="helper"><?= t('helper.required') ?></p>

          <div class="inline-slot">
            <?php if (!empty($inlineMsg ?? '')): ?>
              <div class="inline-msg"><?= e($inlineMsg) ?></div>
            <?php elseif (!empty($cooldownDisplay ?? '')): ?>
              <div class="inline-msg"><?= e($cooldownDisplay) ?></div>
            <?php endif; ?>
          </div>

          <p class="notice">
            <?= t('notice.privacy') ?> <a href="/privacy.php"><?= t('notice.privacy.link') ?></a>.
          </p>
        </div>

        <div class="actions">
          <button type="submit" class="btn-submit"><?= t('btn.submit') ?></button>
        </div>
      </form>
    </div>

    <img class="card-watermark" src="/assets/img/logo.svg" alt="" aria-hidden="true">
  </div>

  <!-- OVERLAY NOW OUTSIDE THE CARD, STILL INSIDE .page-center -->
  <div id="processingOverlay" class="processing-overlay" aria-hidden="true">
    <div class="processing-card" role="status" aria-live="polite">
      <h2><?= t('overlay.heading') ?></h2>
      <p><?= t('overlay.body') ?></p>
      <div class="processing-spinner" aria-hidden="true"></div>
    </div>
  </div>
</div>

  <footer class="site-footer" role="contentinfo">
    Copyright <?= (int)COPYRIGHT_YEAR ?> <?= e(BRAND_NAME) ?>, All rights reserved.
  </footer>

  <script>
    // Basic texts for inline validation (fallback = initial page language)
const L_FALLBACK = {
  req:    <?= json_encode(t('val.all_required')) ?>,
  email:  <?= json_encode(t('val.email')) ?>,
  secret: <?= json_encode(t('val.secret')) ?>
};

// Always read from the currently swapped card (so DE stays DE after flag switch)
function getL() {
  const card = document.querySelector('.access-card');
  if (card && card.dataset) {
    const req    = card.dataset.valReq;
    const email  = card.dataset.valEmail;
    const secret = card.dataset.valSecret;
    if (req && email && secret) return { req, email, secret };
  }
  return L_FALLBACK;
}

    const meta       = document.querySelector('.meta-block');
    const inlineSlot = document.querySelector('.inline-slot');
    const setErrorState = (b) => { if (meta) meta.classList.toggle('has-error', !!b); };

    const form     = document.getElementById('claimForm');
    const keyInput = document.getElementById('key_code');

    const cleanKey = (s) => (s || '').toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,15);
    if (keyInput) {
      keyInput.addEventListener('input', () => {
        const v = cleanKey(keyInput.value);
        if (v !== keyInput.value) keyInput.value = v;
      }, true);
      keyInput.addEventListener('paste', () => {
        setTimeout(() => {
          const v = cleanKey(keyInput.value);
          if (v !== keyInput.value) keyInput.value = v;
        }, 0);
      }, true);
    }

    const ids = ['first_name','last_name','email','key_code'];
    ids.forEach(id => {
      const el = document.getElementById(id); if (!el) return;
      const refresh = () => ((el.value || '').trim() ? el.classList.add('has-value') : el.classList.remove('has-value'));
      refresh(); el.addEventListener('input', refresh, true);
    });

    (function syncErrorClass(){ setErrorState(document.querySelector('.inline-slot .inline-msg') !== null); })();

      (function () {
  // Delegated handlers so validation keeps working after lang-soft-nav swaps the card/form.

  function getFormFromEvent(e) {
    const t = e && e.target;
    if (!t || !t.closest) return null;
    return t.closest('form#claimForm');
  }

  function setErrorStateLive(formEl, hasError) {
    const meta = formEl ? formEl.querySelector('.meta-block') : null;
    if (meta) meta.classList.toggle('has-error', !!hasError);
  }

  function clearInlineLive(formEl) {
    const slot = formEl ? formEl.querySelector('.inline-slot') : null;
    if (slot) slot.innerHTML = '';
    setErrorStateLive(formEl, false);
  }

  function showInlineLive(formEl, msg) {
    const slot = formEl ? formEl.querySelector('.inline-slot') : null;
    if (slot) slot.innerHTML = msg ? ('<div class="inline-msg">' + msg + '</div>') : '';
    setErrorStateLive(formEl, !!msg);
  }

  // Clear only when they actually type or change something (same behavior as before)
  document.addEventListener('input', function (e) {
    const f = getFormFromEvent(e);
    if (!f) return;
    clearInlineLive(f);
  }, true);

  document.addEventListener('change', function (e) {
    const f = getFormFromEvent(e);
    if (!f) return;
    clearInlineLive(f);
  }, true);

  // Validation on submit (survives soft swap)
  document.addEventListener('submit', function (e) {
  const f = getFormFromEvent(e);
  if (!f) return;

    const L = getL();   // <-- ADD THIS LINE HERE

    const data = new FormData(f);
    const first  = String(data.get('first_name')||'').trim();
    const last   = String(data.get('last_name')||'').trim();
    const email  = String(data.get('email')||'').trim();
    const key    = cleanKey(String(data.get('key_code')||'').trim());
    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    let msg = '';
    if (!first || !last || !email)           msg = L.req;
    else if (!emailOk)                       msg = L.email;
    else if (!/^[A-Z0-9]{15}$/.test(key))    msg = L.secret;

    if (msg) {
      e.preventDefault();
      showInlineLive(f, msg);
    } else {
      const keyInputLive = f.querySelector('#key_code');
      if (keyInputLive && key !== keyInputLive.value) keyInputLive.value = key;
    }
  }, true);
})();

        /* Preserve form fields across language switches + Privacy roundtrip */
    (function () {
      const KEY = 'demo_form_draft';

      function get(id) {
        return document.getElementById(id);
      }

      function readDraft() {
        try {
          return JSON.parse(sessionStorage.getItem(KEY) || '{}');
        } catch {
          return {};
        }
      }

      function writeDraft(obj) {
        try {
          sessionStorage.setItem(KEY, JSON.stringify(obj));
        } catch {}
      }

      function captureDraft() {
        const firstEl = get('first_name');
        const lastEl  = get('last_name');
        const emailEl = get('email');
        const keyEl   = get('key_code');

        // Normalize Secret Key Code exactly like the main script
        let keyVal = keyEl && keyEl.value ? String(keyEl.value) : '';
        try {
          if (typeof cleanKey === 'function') {
            keyVal = cleanKey(keyVal);
          } else {
            keyVal = keyVal.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 15);
          }
        } catch {
          keyVal = keyVal.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 15);
        }

        const draft = {
          first_name: (firstEl && firstEl.value ? firstEl.value : '').trim(),
          last_name:  (lastEl  && lastEl.value  ? lastEl.value  : '').trim(),
          email:      (emailEl && emailEl.value ? emailEl.value : '').trim(),
          key_code:   keyVal,
          ts: Date.now()
        };
        writeDraft(draft);
      }

      // 1) Preserve draft when language flags are clicked (delegated, survives soft-nav swaps)
      document.addEventListener('click', function (e) {
        if (!e.target || !e.target.closest) return;
        const langLink = e.target.closest('.lang-switch a[data-lang], .lang-switch a');
        if (langLink) {
          captureDraft();
        }
      }, { capture: true });

      // 2) Preserve draft when leaving to the Privacy page (Access → Privacy)
      document.addEventListener('click', function (e) {
        if (!e.target || !e.target.closest) return;
        const privLink = e.target.closest('.notice a[href*="/privacy.php"]');
        if (privLink) {
          captureDraft();
        }
      }, { capture: true });

      // 3) Restore draft once when coming back to Access (index.php)
      const d = readDraft();
      if (d && d.ts && Date.now() - d.ts < 5 * 60 * 1000) {
        [
          ['first_name', 'first_name'],
          ['last_name',  'last_name'],
          ['email',      'email'],
          ['key_code',   'key_code']
        ].forEach(([id, key]) => {
          const el = get(id);
          if (!el) return;
          if (!el.value && d[key]) {
            el.value = d[key];
            el.classList.add('has-value');
          }
        });

        // one-time restore: prevent stale drafts from leaking into unrelated visits
        sessionStorage.removeItem(KEY);
      }
    })();

    /* Flags set cookie client-side (prevents race/toggle) */
    (function () {
      const DAY = 86400, MAX = 180*DAY;
      function setLangCookie(val){
        const attrs = ['demo_lang=' + encodeURIComponent(val), 'Path=/', 'Max-Age=' + MAX.toString(), 'SameSite=Lax'];
        document.cookie = attrs.join('; ');
      }
      document.querySelectorAll('.lang-switch a[data-lang]').forEach(a => {
        a.addEventListener('click', function(ev){
          ev.preventDefault();
          const lang = a.getAttribute('data-lang') || 'en';
          setLangCookie(lang);
          const url = new URL(location.href);
          url.searchParams.delete('lang');
          location.href = url.pathname + (url.search ? '?'+url.searchParams.toString() : '') + (url.hash || '');
        }, {capture:true});
      });
    })();
          // -------------------------------------------------------------------
      // Extra: lightweight logging hook for client-side validation failures
      // -------------------------------------------------------------------
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.addEventListener('click', function () {
          try {
            const data   = new FormData(form);
            const first  = String(data.get('first_name') || '').trim();
            const last   = String(data.get('last_name')  || '').trim();
            const email  = String(data.get('email')      || '').trim();
            const key    = cleanKey(String(data.get('key_code') || '').trim());
            const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

            if (!email) {
              // Nothing to log if we don't even have an email
              return;
            }

            // Mirror the same validation logic to determine the reason.
            let reason = '';
            if (!first || !last || !email) {
              reason = 'missing_required_fields';
            } else if (!emailOk) {
              reason = 'invalid_email';
            } else if (!/^[A-Z0-9]{15}$/.test(key)) {
              reason = 'invalid_secret_key';
            } else {
              // Form looks valid; the server will log success/cooldown.
              return;
            }

            const langAttr = document.documentElement.getAttribute('lang') || 'en';
            const qs = new URLSearchParams({
              email:  email,
              lang:   (langAttr === 'de' ? 'de' : 'en'),
              reason: reason,
              v:      String(Date.now()) // cache-buster
            });

            const img = new Image();
            img.src = 'email-log-beacon.php?' + qs.toString();
          } catch (err) {
            // Logging is best-effort only; ignore errors completely.
          }
        }, false);
      }
  </script>

<script>
  (function () {
    let overlayTimer = null;

    let safariPrecheckInFlight = false;

    // Chapter_16: prevent Chrome/Non-Safari flicker on fast server-side rejections
    let nonSafariPrecheckInFlight = false;


    function showInlineMessage(form, msg) {
    if (!form) return;
    const slot = form.querySelector('.inline-slot');
    if (slot) {
      slot.innerHTML = '';
      if (msg) {
        const div = document.createElement('div');
        div.className = 'inline-msg';
        div.textContent = String(msg);
        slot.appendChild(div);
      }
    }
    const meta = form.querySelector('.meta-block');
    if (meta) meta.classList.toggle('has-error', !!msg);
  }

    // Chapter_11: detect Safari (macOS/iOS) reliably enough for our use-case
    function isSafari() {
      const ua = navigator.userAgent || '';
      const vendor = navigator.vendor || '';
      const isApple = vendor.indexOf('Apple') > -1;
      const isSafariUA = /Safari/i.test(ua) && !/(Chrome|CriOS|FxiOS|Edg|OPR|OPiOS|EdgiOS|SamsungBrowser)/i.test(ua);
      return isApple && isSafariUA;
    }

    function activateProcessingOverlay() {
      const overlay = document.getElementById('processingOverlay');
      if (!overlay) return null;

      overlay.classList.add('is-visible');
      overlay.setAttribute('aria-hidden', 'false');

      document.documentElement.classList.add('processing-lock');
      document.body.classList.add('processing-lock');

      // Lift the whole card layer above flags & footer while processing
      const pageCenter = document.querySelector('.page-center');
      if (pageCenter) pageCenter.classList.add('processing-active');

      return overlay;
    }

    document.addEventListener('submit', function (e) {
      if (!e.target || !e.target.closest) return;
      const form = e.target.closest('form#claimForm');
      if (!form) return;

      // If client-side validation prevented submission, do nothing.
      if (e.defaultPrevented) {
        if (overlayTimer !== null) {
          clearTimeout(overlayTimer);
          overlayTimer = null;
        }
        return;
      }

      if (overlayTimer !== null) {
        clearTimeout(overlayTimer);
        overlayTimer = null;
      }

      // Chapter_16: Non-Safari precheck gate (prevents POST→302→GET reload flicker on wrong-but-valid key / cooldown)
      // We run the same server-side fast checks as Safari, but for Chrome/others too.
      if (!isSafari()) {
      // If we triggered a follow-up submit via requestSubmit(), allow it through once.
        if (form.dataset && form.dataset.precheckOk === '1') {
          delete form.dataset.precheckOk;
        } else {
          e.preventDefault();

          if (!document.body.contains(form)) return;

          if (nonSafariPrecheckInFlight) return;
          nonSafariPrecheckInFlight = true;

          const fd = new FormData(form);

          fetch('precheck.php', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'fetch' }
          })
          .then(function (res) {
            return res.json().catch(function () { return null; })
              .then(function (data) { return { res: res, data: data }; });
          })
          .then(function (pack) {
            nonSafariPrecheckInFlight = false;
            const data = pack && pack.data ? pack.data : null;

            if (!data || data.ok !== true) {
              const L = (typeof getL === 'function') ? getL() : { secret: 'Invalid or missing Secret Key Code, please try again.' };
              const msg = (data && typeof data.error === 'string' && data.error) ? data.error : L.secret;
              showInlineMessage(form, msg);
              return;
            }

            // Clear any previous inline error
            showInlineMessage(form, '');

            // Mark as prechecked so the next submit can proceed normally (and keep the existing non-Safari overlay delay logic)
            if (form.dataset) form.dataset.precheckOk = '1';

            // Trigger native submit flow again (this time we don't block it)
            if (typeof form.requestSubmit === 'function') {
              form.requestSubmit();
            } else {
              // Fallback: submit directly (older browsers). Overlay delay may be skipped, but no flicker.
              form.submit();
            }
          })
          .catch(function () {
            nonSafariPrecheckInFlight = false;
            // If precheck fails unexpectedly, fall back to existing behavior (submit)
            if (form.dataset) form.dataset.precheckOk = '1';
            if (typeof form.requestSubmit === 'function') {
              form.requestSubmit();
            } else {
              form.submit();
            }
          });

          return;
        }
      }

      // Chapter_11: Safari paint fix — ensure overlay is painted BEFORE the real navigation starts
      if (isSafari()) {
      e.preventDefault();

      if (!document.body.contains(form)) return;

      // Chapter_13: precheck server-side fast-return rejections (secret mismatch, cooldown)
      // so Safari doesn't flash the overlay on an immediate redirect back to index.php.
      if (safariPrecheckInFlight) return;
      safariPrecheckInFlight = true;

      const fd = new FormData(form);

      fetch('precheck.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'fetch' }
      })
      .then(function (res) {
        return res.json().catch(function () { return null; })
          .then(function (data) { return { res: res, data: data }; });
      })
      .then(function (pack) {
        safariPrecheckInFlight = false;
        const data = pack && pack.data ? pack.data : null;

        if (!data || data.ok !== true) {
          const L = (typeof getL === 'function') ? getL() : { secret: 'Invalid or missing Secret Key Code, please try again.' };
          const msg = (data && typeof data.error === 'string' && data.error) ? data.error : L.secret;
          showInlineMessage(form, msg);
          return;
        }

        const overlay = activateProcessingOverlay();
        if (!overlay) return;

        // Force style flush (cheap) and then allow one paint before submitting
        void overlay.offsetHeight;

        requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            form.submit(); // native submit; does NOT re-trigger submit event
          });
        });
      })
      .catch(function () {
        safariPrecheckInFlight = false;
        // Fallback: preserve previous Safari behavior if precheck is unavailable
        const overlay = activateProcessingOverlay();
        if (!overlay) return;
        void overlay.offsetHeight;
        requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            form.submit();
          });
        });
      });

      return;
    }

      // Non-Safari: keep existing behavior (anti-flash delay)
      overlayTimer = window.setTimeout(function () {
        const currentForm = document.querySelector('form#claimForm');
        if (!currentForm || !document.body.contains(currentForm)) return;

        const overlay = document.getElementById('processingOverlay');
        if (!overlay) return;

        overlay.classList.add('is-visible');
        overlay.setAttribute('aria-hidden', 'false');
        document.documentElement.classList.add('processing-lock');
        document.body.classList.add('processing-lock');

        const pageCenter = document.querySelector('.page-center');
        if (pageCenter) {
          pageCenter.classList.add('processing-active');
        }
      }, 650);
    }, false);
  })();
</script>

  <script src="/assets/js/lang-soft-nav.js?v=2025-11-08" defer></script>
   <script>
    (function () {
      // prevent ghost dragging on the Privacy Policy link
      document.addEventListener('dragstart', function (e) {
        if (!e.target || !e.target.closest) return;
        const link = e.target.closest('.notice a[href*="/privacy.php"]');
        if (link) {
          e.preventDefault();
        }
      }, true);
    })();
  </script>

  <script>
(function(){
  function clearClaimForm(){
    const form = document.getElementById('claimForm');
    if (!form) return;

    // reset then hard-clear (beats autofill/form-restore)
    form.reset();

    form.querySelectorAll('input, textarea, select').forEach(el => {
      const type = (el.type || '').toLowerCase();
      if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'file') return;

      if (type === 'checkbox' || type === 'radio') { el.checked = false; return; }

      if (el.tagName === 'SELECT') { el.selectedIndex = 0; return; }

      el.value = '';
    });

    // remove visual "filled" styling if used
    form.querySelectorAll('.has-value').forEach(el => el.classList.remove('has-value'));
  }

  window.addEventListener('pageshow', function(){
    let shouldClear = false;
    try { shouldClear = sessionStorage.getItem('demo_clear_claim_form') === '1'; } catch(e){}
    if (!shouldClear) return;

    try { sessionStorage.removeItem('demo_clear_claim_form'); } catch(e){}

    clearClaimForm();
    // one extra pass to beat late autofill
    setTimeout(clearClaimForm, 50);
  });
})();
</script>

<script>
(function(){
function fitAccessTitleOnce(){
    const el = document.querySelector('.access-card .title');
    if (!el) return;

        const card = el.closest('.access-card');
    if (!card) return;

    // Use the real content wrapper (matches what the title actually sits inside)
    const limitBox = card.querySelector('.stack') || card;

    const boxCS = window.getComputedStyle(limitBox);
    const padL = parseFloat(boxCS.paddingLeft) || 0;
    const padR = parseFloat(boxCS.paddingRight) || 0;

    // Safety margin avoids 1–6px bleed on tiny widths (rounding / font render)
    const safety = 6;

    // Apply extra left/right inset ONLY on tiny portrait viewports (e.g., 320px phones)
    const isPortrait = window.matchMedia && window.matchMedia('(orientation: portrait)').matches;
    const isTiny = isPortrait && Math.min(window.innerWidth, window.innerHeight) <= 340;
    const sideInset = isTiny ? 12 : 0;

    const maxW = Math.max(0, limitBox.clientWidth - padL - padR - safety - (sideInset * 2));

    if (!maxW) return;

    // Force the flex item to respect the width constraint
    el.style.whiteSpace = 'nowrap';
    el.style.display = 'block';
    el.style.boxSizing = 'border-box';
    el.style.width = '100%';
    el.style.maxWidth = maxW + 'px';
    el.style.minWidth = '0';       // IMPORTANT for flex children
    el.style.overflow = 'hidden';  // prevents tiny edge bleed
    el.style.textOverflow = 'clip';
    el.style.marginLeft = 'auto';
    el.style.marginRight = 'auto';

    // Reset to CSS baseline before fitting
    el.style.fontSize = '';

    const cs = window.getComputedStyle(el);
    const basePx = parseFloat(cs.fontSize) || 22;
    const minPx  = 8;

    // If it already fits, stop
    if (el.scrollWidth <= maxW + 0.5) return;

    // Binary-search best font size between minPx and basePx
    let lo = minPx, hi = basePx, best = minPx;
    for (let i = 0; i < 12; i++){
      const mid = (lo + hi) / 2;
      el.style.fontSize = mid.toFixed(2) + 'px';

      if (el.scrollWidth <= maxW + 0.5){
        best = mid;
        lo = mid;
      } else {
        hi = mid;
      }
    }
    el.style.fontSize = best.toFixed(2) + 'px';
  }

  let rafId = 0;
  function scheduleFit(){
    if (rafId) return;
    rafId = requestAnimationFrame(function(){
      rafId = 0;
      fitAccessTitleOnce();
    });
  }

  document.addEventListener('DOMContentLoaded', scheduleFit);
  window.addEventListener('pageshow', scheduleFit);

  let rt = 0;
  window.addEventListener('resize', function(){
    clearTimeout(rt);
    rt = setTimeout(scheduleFit, 80);
  });

  window.addEventListener('load', scheduleFit);

  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(scheduleFit).catch(function(){});
  }

  setTimeout(scheduleFit, 200);
  setTimeout(scheduleFit, 800);

  const obsRoot = document.querySelector('.page-center') || document.body;
  const mo = new MutationObserver(scheduleFit);
  mo.observe(obsRoot, { childList: true, subtree: true });

  // expose for console testing
  window.demoFitAccessTitle = scheduleFit;
})();
</script>
</body>
</html>