<?php
declare(strict_types=1);
require_once __DIR__ . '/../code/app/config.php';

header('Cache-Control: private, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: text/html; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

/* ------------ i18n ------------ */
(function () {
  $projI18n = dirname(__DIR__) . '/code/app/i18n.php';
  if (is_file($projI18n)) {
    @require_once $projI18n; }

  if (!function_exists('demo_get_lang')) {
    function demo_get_lang(): string
    {
      $c = strtolower((string) ($_COOKIE['demo_lang'] ?? 'en'));
      return in_array($c, ['en', 'de'], true) ? $c : 'en';
    }
  }
  if (!function_exists('t')) {
    function t(string $key): string
    {
      static $T = null;
      if ($T === null) {
        $en = [
          'title' => 'Privacy Information',
          'updated' => 'updated:',
          'p1' => 'We collect the details you enter on this page (first name, last name, email address and secret key code) solely to confirm your successful completion of the activity, generate your certificate and deliver any bonus content you requested. If you choose to receive updates from us, we also use your email address to keep you informed about our products, promotions and news; you can unsubscribe at any time.',
          'optout' => 'We do not sell your personal data. We only share it with service providers that help us run this website (for example hosting and email providers), and they must protect your data and may not use it for their own purposes.',
          'legal' => 'For visitors in the EU/EEA and the UK, our legal basis under the GDPR/UK GDPR is Article 6(1)(b) (performance of a contract) and, where applicable, Article 6(1)(f) (legitimate interests such as preventing abuse of the certificate system).',
          'rights' => 'Depending on where you live, you may have rights to request access to your data, ask for correction or deletion, object to certain uses, or lodge a complaint with a data protection authority. We explain these rights in more detail on our <a href="/privacy-policy.php">Privacy Policy Page</a>.',
          'close' => 'Close and return',
        ];
        $de = [
          'title' => 'Datenschutzhinweis',
          'updated' => 'aktualisiert:',
          'p1' => 'Wir erfassen die Daten, die Sie auf dieser Seite eingeben (Vorname, Nachname, E-Mail-Adresse und Geheimcode), ausschließlich, um Ihre erfolgreiche Teilnahme zu bestätigen, Ihr Zertifikat zu erstellen und ggf. angeforderte Bonus-Inhalte bereitzustellen. Wenn Sie Neuigkeiten von uns erhalten möchten, verwenden wir Ihre E-Mail-Adresse außerdem, um Sie über unsere Produkte, Aktionen und Neuigkeiten zu informieren; Sie können sich jederzeit abmelden.',
          'optout' => 'Wir verkaufen Ihre personenbezogenen Daten nicht. Wir geben sie nur an Dienstleister weiter, die uns beim Betrieb dieser Website unterstützen (z.B. Hosting- und E-Mail-Anbieter). Diese sind vertraglich verpflichtet, Ihre Daten zu schützen und nicht für eigene Zwecke zu nutzen.',
          'legal' => 'Für Besucher aus der EU/dem EWR und dem Vereinigten Königreich stützt sich die Verarbeitung auf Art. 6 Abs. 1 lit. b DSGVO/UK GDPR (Vertragserfüllung) und, soweit erforderlich, auf Art. 6 Abs. 1 lit. f DSGVO (berechtigte Interessen, z.B. Verhinderung von Missbrauch des Zertifikat-Systems).',
          'rights' => 'Je nach Wohnsitz haben Sie möglicherweise Rechte auf Auskunft, Berichtigung oder Löschung Ihrer Daten, auf Widerspruch gegen bestimmte Verarbeitungen sowie das Recht, sich bei einer Datenschutzaufsichtsbehörde zu beschweren. Weitere Details finden Sie auf unserer <a href="/privacy-policy-de.php">Datenschutzerklärung</a>.',
          'close' => 'Schließen und zurück',
        ];
        $lang = demo_get_lang();
        $T = ($lang === 'de') ? array_replace($en, $de) : $en;
      }
      return $T[$key] ?? $key;
    }
  }
})();

$lang = demo_get_lang();
$privacyLastUpdated = format_privacy_last_updated($lang);
?>
<!doctype html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
  <title><?= htmlspecialchars(t('title'), ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preload" as="image" href="/assets/img/landing-bg-desktop.webp" type="image/webp" fetchpriority="high">
  <link rel="preload" as="image" href="/assets/img/landing-bg-mobile.webp" type="image/webp" media="(max-width: 480px)"
    fetchpriority="high">
  <style>
    :root {
      --ink-blue: #0F3558;
      --ink-orange: #FE8215;
      --white: #FFFFFF;
      --footer-raise: -9.5px;

      --bg-lqip-desktop: url("data:image/webp;base64,UklGRpgAAABXRUJQVlA4IIwAAACwBACdASpAACQAPzGGvleuqCYkqBqqqdAmCUAZjmv4AFS0Zi1u7k7bANk/Ih+AAPbiVue6h/oV3Z8Rdn/bzmz28bx/qvNfDmkW5gG1oP7ln9Uuhh+dvvq9WFzf1DrBuZhBQ8mMXyI4bOaO8nD16rZdk1CbWRB7hGwVG4QfS4zKQBHGiZZ/JICpMgAAAA==");
      --bg-lqip-mobile: url("data:image/webp;base64,UklGRrYAAABXRUJQVlA4IKoAAABwBQCdASokAE4APzGCt1SuqCWjLNgMcdAmCWMAv7AAwxygPWHFpHLD3xupJ+Yqv5FTMTQAAPcDvToZzbX9CvWX302P+onYjxgTkjGijV050Jl6Xk9aXpIVfis6itElg4CvB4Ywz7OvWqKSaM60ViAKdTXPqmxliAnbrqYM45hQddUC9MSqXB7taYcxczhSuiDSaqB1SzvQ/HirHTZgK50/mw8hZxcSeggAAA==");

      --card-offset-y: 27px;
      /* + down / - up (card only) */
    }

    /* White-striped background matching index.php and success.php */
    html {
      background-color: #f7f9fc;
      overflow-x: hidden;
      -webkit-text-size-adjust: 100%;
      background-image: repeating-linear-gradient(-45deg,
          #f7f9fc,
          #f7f9fc 18px,
          #e2e8f0 18px,
          #e2e8f0 20px);
    }

    body {
      font-family: Helvetica, Arial, sans-serif;
      margin: 0;
      color: #111;
      background: transparent;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
      position: relative;
      z-index: 1;
    }

    @supports (min-height:100dvh) {
      body {
        min-height: 100dvh;
      }
    }

    /* Portrait-only: lock body to exactly 100dvh so no spurious scroll appears */
    @media (orientation: portrait) {
      body {
        height: 100vh;
        overflow-y: hidden;
      }

      @supports (height:100dvh) {
        body {
          height: 100dvh;
        }
      }
    }

    .page-bg {
      position: fixed;
      inset: 0;
      width: 100%;
      height: 100vh;
      height: 100dvh;
      background-color: #f7f9fc;
      background-image: repeating-linear-gradient(-45deg,
          #f7f9fc,
          #f7f9fc 18px,
          #e2e8f0 18px,
          #e2e8f0 20px);
      z-index: 0;
      pointer-events: none;
      transform: translateZ(0);
    }

    /* Wrapper that centers the card between top and footer */
    .layout {
      flex: 1 0 auto;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 16px 24px;
      box-sizing: border-box;
      position: relative;
      z-index: 1;
    }

    /* Card */
    main {
      max-width: 920px;
      width: min(920px, 100% - 40px);
      background: #fff;
      padding: 24px 32px 28px;
      border-radius: 30px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, .2);
      position: relative;
      /* anchor for the logo */
      transform: translateY(var(--card-offset-y));
    }

    /* Heading + logo row */
    .header-row {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 24px;
      margin-bottom: 8px;
    }

    .title-block {
      flex: 1 1 auto;
    }

    h1 {
      margin: 0 0 8px 0;
      font-size: 28px;
    }

    .meta {
      color: #6b7280;
      margin: 0 0 16px 0;
      font-size: 14px;
    }

    .logo-block {
      position: absolute;
      /* take it out of the flex flow */
      top: 12px;
      /* vertical offset inside the card */
      right: 26px;
      /* horizontal offset inside the card */
    }

    .demo-logo {
      display: block;
      height: 40px;
      width: auto;
      -webkit-user-drag: none;
      user-select: none;
    }

    p {
      margin: 10px 0;
      line-height: 1.5;
    }

    a.btn {
      display: inline-block;
      margin-top: 16px;
      color: #FE8215;
      text-decoration: underline;
    }

    .site-footer {
      text-align: center;
      padding: 12px 16px 24px;
      color: #fff;
      font-weight: 700;
      font-size: 22px;
      line-height: 1.2;
      text-shadow: 0 1px 2px rgba(0, 0, 0, .25);
      transform: translateY(var(--footer-raise));
    }

    a[href="/index.php"],
    a[href="/index.html"],
    a[href="/index-de.html"] {
      -webkit-user-drag: none;
      user-select: none;
    }

    @media (max-width: 720px) {
      .layout {
        padding: 16px 12px 16px;
        align-items: center;
        justify-content: center;
      }

      main {
        width: calc(100% - 24px);
        max-width: 540px;
        margin: 0 auto;
        padding: 18px 16px 22px;
        box-sizing: border-box;
      }

      .header-row {
        flex-direction: row;
        align-items: flex-start;
        gap: 0;
      }

      .demo-logo {
        max-width: 100px;
      }
    }

    /* -----------------------------------------------------------------
   T27.3 — Tablet & laptop viewport audit (Privacy Information)
--------------------------------------------------------------------*/
    @media (min-width: 700px) and (max-width: 1600px) and (min-height: 700px) {

      :root {

        --t27-card-offset-y: 25px;
        /* + down / - up (card only) */

        --t27-footer-fs: 18px;
        --t27-footer-raise: 10.5px;

        --footer-raise: var(--t27-footer-raise);
      }

      .site-footer {
        font-size: var(--t27-footer-fs);
      }

      html,
      body {
        height: 100%;
        overflow: hidden;
        overscroll-behavior: none;
      }

      main {
        transform: translateY(var(--t27-card-offset-y));
        box-sizing: border-box;
      }
    }

    /*----------------------------------------------------------------------------------
  Privacy Information – Split-screen / Foldable bridge (481–699px width) – PORTRAIT
------------------------------------------------------------------------------------*/
    @media (min-width: 481px) and (max-width: 699px) and (orientation: portrait) {

      :root {
        --b1pi-ls-card-w: 640px;
        --b1pi-ls-card-min-h: 460px;
        --b1pi-ls-card-pad: 22px;
        --b1pi-ls-card-radius: 24px;
        --b1pi-ls-card-offset-y: -8px;

        --fs-h1: 28px;
        --fs-meta: 12px;
        --fs-body: 13px;
        --fs-link: 15px;

        --lh-body: 1.45;

        --gap-h1-meta: 10px;
        --gap-meta-body: 16px;
        --gap-paragraph: 10px;
        --gap-body-link: 12px;

        --logo-h: 35px;
        --logo-top: 10px;
        --logo-right: 15px;

        --b1pi-ls-footer-fs: 14px;
        --b1pi-ls-footer-bottom: 17px;

        --b1pi-ls-footer-reserve: 92px;
      }

      html,
      body {
        height: 100%;
        overflow: hidden;
        overscroll-behavior: none;
      }

      @supports (height: 100dvh) {
        body {
          height: 100dvh;
        }
      }

      .layout {
        height: 100vh;
        padding: 18px 16px 0;
        box-sizing: border-box;
      }

      @supports (height: 100dvh) {
        .layout {
          height: 100dvh;
        }
      }

      main {
        max-width: none !important;
        width: min(var(--b1pi-ls-card-w), calc(100vw - 32px)) !important;
        padding: var(--b1pi-ls-card-pad) !important;
        border-radius: var(--b1pi-ls-card-radius) !important;
        transform: translateY(var(--b1pi-ls-card-offset-y)) !important;
        min-height: var(--b1pi-ls-card-min-h) !important;
        max-height: calc(100vh - var(--b1pi-ls-footer-reserve)) !important;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
        box-sizing: border-box;
      }

      @supports (height: 100dvh) {
        main {
          max-height: calc(100dvh - var(--b1pi-ls-footer-reserve)) !important;
        }
      }

      h1 {
        font-size: var(--fs-h1);
        margin: 0 0 var(--gap-h1-meta) 0;
        line-height: 1.05;
      }

      .meta {
        font-size: var(--fs-meta);
        margin: 0 0 var(--gap-meta-body) 0;
      }

      p {
        font-size: var(--fs-body);
        line-height: var(--lh-body);
        margin: 0 0 var(--gap-paragraph) 0;
      }

      a.btn {
        font-size: var(--fs-link);
      }

      main p:last-of-type {
        margin-bottom: var(--gap-body-link);
      }

      .logo-block {
        position: absolute;
        top: var(--logo-top);
        right: var(--logo-right);
      }

      .demo-logo {
        height: var(--logo-h);
        width: auto;
      }

      .site-footer {
        position: fixed !important;
        left: 0;
        right: 0;
        bottom: var(--b1pi-ls-footer-bottom) !important;
        font-size: var(--b1pi-ls-footer-fs) !important;
        transform: none !important;
        padding: 0 16px !important;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1.15;
      }
    }

    /* ----------------------------------------------------------------
   Common phones (≈390/393px), PORTRAIT – Privacy Information page
   ----------------------------------------------------------------*/
    @media (min-width: 360px) and (max-width: 480px) and (orientation: portrait) {

      :root {
        --card-w: calc(100vw - 40px);
        --card-min-h: 520px;
        --card-max-h: calc(100dvh - 120px);
        --card-pad: 18px;
        --card-radius: 24px;
        --pt-card-offset-y: 23px;

        --fs-h1: 21px;
        --fs-meta: 12px;
        --fs-body: 12px;
        --fs-link: 14px;
        --fs-footer: 12px;

        --lh-body: 1.45;

        --gap-h1-meta: 10px;
        --gap-meta-body: 20px;
        --gap-paragraph: 10px;
        --gap-body-link: 20px;

        --link-left: 0px;
        --link-bottom: 0px;

        --logo-h: 30px;
        --logo-top: 7px;
        --logo-right: 15px;
        --title-right-reserve: 120px;

        --layout-pad-top: 12px;
        --layout-pad-x: 16px;
        --gap-card-footer: 26px;

        --footer-bottom-pad: 15.5px;
      }

      html,
      .page-bg {
        background-color: #f7f9fc;
        background-image: repeating-linear-gradient(-45deg,
            #f7f9fc,
            #f7f9fc 18px,
            #e2e8f0 18px,
            #e2e8f0 20px);
        background-position: unset;
        background-size: unset;
        background-repeat: unset;
      }

      html,
      body {
        height: 100%;
        overflow: hidden;
        overscroll-behavior: none;
      }

      @supports (height: 100dvh) {
        body {
          height: 100dvh;
        }
      }

      .layout {
        flex: 1 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: var(--layout-pad-top) var(--layout-pad-x) var(--gap-card-footer);
        box-sizing: border-box;
      }

      main {
        width: var(--card-w);
        max-width: none;
        height: auto;
        min-height: var(--card-min-h);
        max-height: var(--card-max-h);
        transform: translateY(var(--pt-card-offset-y));
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
        padding: var(--card-pad);
        border-radius: var(--card-radius);
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
      }

      .title-block {
        padding-right: var(--title-right-reserve);
      }

      .logo-block {
        position: absolute;
        top: var(--logo-top);
        right: var(--logo-right);
      }

      .demo-logo {
        height: var(--logo-h);
        width: auto;
      }

      .header-row {
        margin: 0;
      }

      h1 {
        font-size: var(--fs-h1);
        margin: 0 0 var(--gap-h1-meta) 0;
        line-height: 1.05;
      }

      .meta {
        font-size: var(--fs-meta);
        margin: 0 0 var(--gap-meta-body) 0;
      }

      p {
        font-size: var(--fs-body);
        line-height: var(--lh-body);
        margin: 0 0 var(--gap-paragraph) 0;
      }

      main p:last-of-type {
        margin-bottom: var(--gap-body-link);
      }

      a.btn {
        font-size: var(--fs-link);
        margin-top: auto;
        align-self: flex-start;
        margin-left: var(--link-left);
        margin-bottom: var(--link-bottom);
      }

      .site-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--footer-bottom-pad);
        font-size: var(--fs-footer);
        padding: 0 16px;
        margin: 0;
        transform: none;
        line-height: 1.1;
        z-index: 5;
        pointer-events: none;
      }
    }

    /* ---------------------------------------------------------------------
  Z Fold cover (384×832), PORTRAIT — Privacy Information
-----------------------------------------------------------------------*/
    @media (orientation: portrait) and (width: 384px) and (height: 832px) {

      :root {
        --zf-pt-en-h1-fs: 20px;
        --zf-pt-en-title-reserve: 96px;
        --zf-pt-logo-right: 12px;
        --zf-pt-link-left: 0px;
        --zf-pt-link-bottom: 0px;
        --logo-right: var(--zf-pt-logo-right);
        --link-left: var(--zf-pt-link-left);
        --link-bottom: var(--zf-pt-link-bottom);
      }

      html[lang="en"] {
        --fs-h1: var(--zf-pt-en-h1-fs);
        --title-right-reserve: var(--zf-pt-en-title-reserve);
      }

      a.btn {
        white-space: nowrap;
      }
    }

    /* ---------------------------------------------------------------
  T28.2 — iPhone X (375×812), PORTRAIT — Privacy Information
------------------------------------------------------------------*/
    @media (orientation: portrait) and (min-width: 375px) and (max-width: 389px) and (min-height: 780px) and (max-height: 830px) {

      :root {
        --t28x-pt-card-offset-y: -5px;
        --t28x-pt-footer-bottom: 12px;
        --t28x-pt-footer-fs: 12px;
        --t28x-pt-en-h1-fs: 20px;
        --t28x-pt-en-title-reserve: 100px;
        --gap-card-footer: 0px;
      }

      html[lang="en"] {
        --fs-h1: var(--t28x-pt-en-h1-fs);
        --title-right-reserve: var(--t28x-pt-en-title-reserve);
      }

      .layout {
        height: 100vh;
      }

      @supports (height: 100dvh) {
        .layout {
          height: 100dvh;
        }
      }

      main {
        transform: translateY(var(--t28x-pt-card-offset-y));
      }

      .site-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--t28x-pt-footer-bottom);
        padding: 0 12px;
        font-size: var(--t28x-pt-footer-fs);
        transform: none;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1.1;
      }
    }

    /* ------------------------------------------------------------------------
  T28.3 — iPhone XR (414×896), PORTRAIT — Privacy info page
------------------------------------------------------------------------*/
    @media (orientation: portrait) and (width: 414px) and (height: 896px) {

      :root {
        --t28xr-pt-card-offset-y: 9px;
        --t28xr-pt-footer-bottom: 16.5px;
      }

      main {
        transform: translateY(var(--t28xr-pt-card-offset-y));
      }

      .site-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--t28xr-pt-footer-bottom);
        padding: 0 12px;
        transform: none;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1.1;
      }
    }

    /* ------------------------------------------------------------------------
  T28.4 — iPhone 14 Pro Max (430×932), PORTRAIT — Privacy Information
---------------------------------------------------------------------------*/
    @media (orientation: portrait) and (width: 430px) and (height: 932px) {

      :root {
        --t284pm-pt-card-offset-y: -5px;
        --t284pm-pt-card-minh: 540px;
        --t284pm-pt-footer-reserve: 86px;
        --t284pm-pt-footer-bottom: 16.5px;
        --t284pm-pt-footer-fs: 12px;
        --gap-card-footer: 0px;
        --card-min-h: var(--t284pm-pt-card-minh);
        --card-max-h: calc(100vh - var(--t284pm-pt-footer-reserve));
      }

      @supports (height: 100dvh) {
        :root {
          --card-max-h: calc(100dvh - var(--t284pm-pt-footer-reserve));
        }
      }

      .layout {
        height: 100vh;
      }

      @supports (height: 100dvh) {
        .layout {
          height: 100dvh;
        }
      }

      main {
        max-height: var(--card-max-h);
        transform: translateY(var(--t284pm-pt-card-offset-y));
      }

      .site-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--t284pm-pt-footer-bottom);
        padding: 0 12px;
        font-size: var(--t284pm-pt-footer-fs);
        transform: none;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1.1;
      }
    }

    /* -------------------------------------------------------------------
  T28.5 — Samsung Galaxy S8+ (360×740), PORTRAIT — Privacy Information
----------------------------------------------------------------------*/
    @media (orientation: portrait) and (width: 360px) and (height: 740px) {

      :root {
        --t285s8-pt-fs-h1: 21px;
        --t285s8-pt-en-title-reserve: 80px;
        --t285s8-pt-card-offset-y: -6px;
        --t285s8-pt-footer-reserve: 70px;
        --t285s8-pt-footer-bottom: 16.5px;
        --t285s8-pt-footer-fs: 12px;
        --gap-card-footer: 0px;
        --fs-h1: var(--t285s8-pt-fs-h1);
        --pt-card-offset-y: var(--t285s8-pt-card-offset-y);
        --card-max-h: calc(100vh - var(--t285s8-pt-footer-reserve));
      }

      @supports (height: 100dvh) {
        :root {
          --card-max-h: calc(100dvh - var(--t285s8-pt-footer-reserve));
        }
      }

      html[lang="en"] {
        --title-right-reserve: var(--t285s8-pt-en-title-reserve);
      }

      .layout {
        height: 100vh;
      }

      @supports (height: 100dvh) {
        .layout {
          height: 100dvh;
        }
      }

      main {
        overflow-x: hidden;
        max-height: var(--card-max-h);
      }

      p,
      a {
        overflow-wrap: anywhere;
        word-break: break-word;
      }

      .site-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--t285s8-pt-footer-bottom);
        padding: 0 12px;
        font-size: var(--t285s8-pt-footer-fs);
        transform: none;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1.1;
      }
    }

    /* ------------------------------------------------------------------
  T28.1 — 375×667 PORTRAIT (short phones) — Privacy Information page
---------------------------------------------------------------------*/
    @media (orientation: portrait) and (min-width: 360px) and (max-width: 430px) and (max-height: 700px) {

      :root {
        --card-w: calc(100vw - 40px);
        --card-min-h: 460px;
        --card-max-h: calc(100dvh - 140px);
        --card-pad: 16px;
        --fs-h1: 20px;
        --fs-meta: 11px;
        --fs-body: 11px;
        --fs-link: 13px;
        --fs-footer: 12px;
        --gap-h1-meta: 8px;
        --gap-meta-body: 16px;
        --gap-paragraph: 9px;
        --gap-body-link: 16px;
        --logo-h: 28px;
        --title-right-reserve: 110px;
        --layout-pad-top: 10px;
        --layout-pad-x: 10px;
        --gap-card-footer: 18px;
        --footer-bottom-pad: 12px;
        --t28p-pt-card-offset-y: 0px;
      }

      main {
        transform: translateY(var(--t28p-pt-card-offset-y));
      }
    }

    /*-----------------------------------------------------------
Extra-small phones (e.g. iPhone SE 320×568 – portrait only) 
-------------------------------------------------------------*/
    /*-----------------------------------------------------------
Extra-small phones (e.g. iPhone SE 320×568 – portrait only) 
FIX: equal top/bottom spacing + no footer overlap EVER
-------------------------------------------------------------*/
    @media (max-width: 340px) {

      :root {
        --pm-footer-bottom: 10px;
        --pm-footer-fs: 12px;

        /* ✅ reserve space for footer + breathing gap */
        --pm-footer-reserve: 72px;

        /* optional fine tuning */
        --pm-card-offset-y: 0px;
      }

      html,
      body {
        height: 100%;
        overflow: hidden;
      }

      /* ✅ equal spacing top & bottom */
      .layout {
        height: 100vh;
        padding: 18px 10px var(--pm-footer-reserve);
        box-sizing: border-box;

        display: flex;
        align-items: center;
        justify-content: center;
      }

      @supports (height: 100dvh) {
        .layout {
          height: 100dvh;
        }
      }

      /* ✅ card never touches footer */
      main {
        max-width: 420px;
        width: calc(100% - 40px);

        padding: 15px 14px 18px;
        border-radius: 24px;

        /* 🔥 key fix */
        max-height: calc(100dvh - var(--pm-footer-reserve));
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;

        transform: translateY(var(--pm-card-offset-y));
        box-sizing: border-box;
      }

      h1 {
        font-size: 18px;
        margin-bottom: 10px;
      }

      .meta {
        font-size: 12px;
        margin-bottom: 10px;
      }

      p {
        font-size: 10.5px;
        line-height: 1.3;
        margin: 6px 0;
      }

      a.btn {
        font-size: 13px;
        margin-top: 12px;
      }

      .logo-block {
        top: 5px;
        right: 12px;
      }

      .demo-logo {
        height: 28px;
      }

      /* ✅ footer ALWAYS separate, never overlaps */
      .site-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--pm-footer-bottom);

        padding: 0 12px;
        font-size: var(--pm-footer-fs);

        display: flex;
        align-items: center;
        justify-content: center;

        line-height: 1.1;
        z-index: 5;

        pointer-events: none;
        text-align: center;
      }
    }

    /* =====================================================================
   MOBILE LANDSCAPE — all phone landscape modes
   FIX: card must have top+bottom breathing room; footer never overlaps
   ===================================================================== */

    /*--------------------------------------------------------------------------------------
      Privacy Information – split-screen / short-height window (461–699px tall) – LANDSCAPE
    ----------------------------------------------------------------------------------------*/
    @media (orientation: landscape) and (min-width: 700px) and (max-width: 1600px) and (min-height: 461px) and (max-height: 699px) {

      :root {
        --b1pi-ls-card-w: 640px;
        --b1pi-ls-card-min-h: 460px;
        --b1pi-ls-card-pad: 22px;
        --b1pi-ls-card-radius: 24px;
        --b1pi-ls-card-offset-y: -9px;

        --fs-h1: 28px;
        --fs-meta: 12px;
        --fs-body: 13px;
        --fs-link: 15px;

        --lh-body: 1.45;

        --gap-h1-meta: 10px;
        --gap-meta-body: 16px;
        --gap-paragraph: 10px;
        --gap-body-link: 12px;

        --logo-h: 35px;
        --logo-top: 10px;
        --logo-right: 15px;

        --b1pi-ls-footer-fs: 14px;
        --b1pi-ls-footer-bottom: 17px;
        --b1pi-ls-footer-reserve: 92px;
      }

      html,
      body {
        height: auto;
        min-height: 100%;
        overflow-y: auto;
        overscroll-behavior: auto;
      }

      /* FIX: 20px top + bottom breathing room around card */
      .layout {
        min-height: 100vh;
        padding: 20px 16px 20px;
        box-sizing: border-box;
      }

      @supports (height: 100dvh) {
        .layout {
          min-height: 100dvh;
        }
      }

      main {
        max-width: none !important;
        width: min(var(--b1pi-ls-card-w), calc(100vw - 32px)) !important;
        padding: var(--b1pi-ls-card-pad) !important;
        border-radius: var(--b1pi-ls-card-radius) !important;
        transform: translateY(var(--b1pi-ls-card-offset-y)) !important;
        min-height: var(--b1pi-ls-card-min-h) !important;
        max-height: none !important;
        overflow-y: visible !important;
        box-sizing: border-box;
      }

      h1 {
        font-size: var(--fs-h1);
        margin: 0 0 var(--gap-h1-meta) 0;
        line-height: 1.05;
      }

      .meta {
        font-size: var(--fs-meta);
        margin: 0 0 var(--gap-meta-body) 0;
      }

      p {
        font-size: var(--fs-body);
        line-height: var(--lh-body);
        margin: 0 0 var(--gap-paragraph) 0;
      }

      a.btn {
        font-size: var(--fs-link);
      }

      main p:last-of-type {
        margin-bottom: var(--gap-body-link);
      }

      .logo-block {
        position: absolute;
        top: var(--logo-top);
        right: var(--logo-right);
      }

      .demo-logo {
        height: var(--logo-h);
        width: auto;
      }

      .site-footer {
        position: fixed !important;
        left: 0;
        right: 0;
        bottom: var(--b1pi-ls-footer-bottom) !important;
        font-size: var(--b1pi-ls-footer-fs) !important;
        transform: none !important;
        padding: 0 16px !important;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1.15;
      }
    }

    /* ----------------------------------------------------------------------
    CH9 — Foldable / split-screen (720×557), LANDSCAPE — Privacy Info page
  -------------------------------------------------------------------------*/
    @media (orientation: landscape) and (width: 720px) and (height: 557px) {
      :root {
        --ch9f720-ls-pi-footer-bottom: 8.5px;
        --b1pi-ls-footer-bottom: var(--ch9f720-ls-pi-footer-bottom);
      }
    }

    /* ------------------------------------------------------------------------------------------------
    T26.3 — Phone group iPhone 12/13/14 (844×390) & Pixel 5 (851×393), LANDSCAPE (Privacy Information)
     --------------------------------------------------------------------------------------------------*/
    @media (orientation: landscape) and (min-width: 640px) and (max-width: 1024px) and (min-height: 361px) and (max-height: 460px) {

      html,
      body {
        height: auto !important;
        min-height: 100%;
        overflow-y: auto !important;
        overscroll-behavior: auto;
      }

      html {
        background-color: #f7f9fc;
        background-image: repeating-linear-gradient(-45deg,
            #f7f9fc,
            #f7f9fc 18px,
            #e2e8f0 18px,
            #e2e8f0 20px);
      }

      html.demo-swap-freeze {
        contain: none !important;
      }

      :root {
        --pi-card-w: min(calc(100vw - 20px), 750px);
        --pi-card-pad: 18px 14px 14px;
        --pi-card-radius: 24px;
        --pt-card-offset-y: 10px;

        --pi-fs-h1: 21px;
        --pi-fs-meta: 11px;
        --pi-fs-body: 11px;
        --pi-fs-link: 14px;
        --pi-fs-footer: 12px;
        --pi-lh-body: 1.3;

        --pi-gap-h1-meta: 8px;
        --pi-gap-meta-body: 10px;
        --pi-gap-paragraph: 6px;
        --pi-gap-body-link: 12px;

        --pi-logo-h: 30px;
        --pi-logo-top: 6px;
        --pi-logo-right: 14px;

        /* FIX: layout padding with 20px top+bottom breathing room + footer reserve */
        --pi-layout-pad-top: 20px;
        --pi-layout-pad-x: 10px;
        /* footer is ~28px tall (12px font + 16px padding) + 6.5px bottom offset = ~34px reserve */
        --pi-layout-pad-bottom: 54px;

        --pi-footer-bottom-pad: 6.5px;
      }

      :root {
        --footer-raise: 0px;
      }

      /* FIX: 20px top + 20px bottom space around card */
      .layout {
        padding: var(--pi-layout-pad-top) var(--pi-layout-pad-x) var(--pi-layout-pad-bottom);
        align-items: center;
        justify-content: center;
      }

      main {
        width: var(--pi-card-w);
        max-width: none;
        padding: var(--pi-card-pad);
        border-radius: var(--pi-card-radius);
        transform: translateY(var(--pt-card-offset-y));
        max-height: none;
        overflow-y: visible;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
      }

      h1 {
        font-size: var(--pi-fs-h1);
        margin: 0 0 var(--pi-gap-h1-meta) 0;
        line-height: 1.05;
      }

      .meta {
        font-size: var(--pi-fs-meta);
        margin: 0 0 var(--pi-gap-meta-body) 0;
      }

      p {
        font-size: var(--pi-fs-body);
        line-height: var(--pi-lh-body);
        margin: 0 0 var(--pi-gap-paragraph) 0;
      }

      main p:last-of-type {
        margin-bottom: var(--pi-gap-body-link);
      }

      a.btn {
        font-size: var(--pi-fs-link);
        margin-top: auto;
        align-self: flex-start;
      }

      .logo-block {
        top: var(--pi-logo-top);
        right: var(--pi-logo-right);
      }

      .demo-logo {
        height: var(--pi-logo-h);
        width: auto;
      }

      .site-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--pi-footer-bottom-pad);
        font-size: var(--pi-fs-footer);
        padding: 0 12px;
        margin: 0;
        transform: none;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1.1;
      }
    }

    ---------------------------------------------------------------------------*/ @media (orientation: landscape) and (min-width: 800px) and (max-width: 830px) and (max-height: 380px) {

      :root {
        --t28x-ls-card-offset-y: 0px;
        --t28x-ls-footer-bottom: 10px;
        --t28x-ls-footer-fs: 12px;
        --t28x-ls-footer-reserve: 60px;
        --t28x-ls-card-maxh: calc(100vh - var(--t28x-ls-footer-reserve));
      }

      html,
      body {
        height: auto !important;
        min-height: 100%;
        overflow-y: auto !important;
        overscroll-behavior: auto;
      }

      html {
        background-color: #f7f9fc;
        background-image: repeating-linear-gradient(-45deg,
            #f7f9fc,
            #f7f9fc 18px,
            #e2e8f0 18px,
            #e2e8f0 20px);
      }

      /* FIX: top 20px breathing + bottom 58px (20px gap + ~38px footer clearance) */
      .layout {
        min-height: 100vh;
        padding: 20px 10px 58px;
        align-items: center;
        justify-content: center;
      }

      @supports (height: 100dvh) {
        .layout {
          min-height: 100dvh;
        }
      }

      main {
        max-height: none !important;
        overflow-y: visible !important;
        transform: translateY(var(--t28x-ls-card-offset-y));
      }

      .site-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--t28x-ls-footer-bottom);
        padding: 0 12px;
        font-size: var(--t28x-ls-footer-fs);
        transform: none;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1.1;
      }
    }

    /* ------------------------------------------------------------------------
  T28.3 — iPhone XR (896×414), LANDSCAPE — Privacy Information
---------------------------------------------------------------------------*/
    @media (orientation: landscape) and (width: 896px) and (height: 414px) {

      :root {
        --t28xr-ls-card-offset-y: 0px;
        --t28xr-ls-footer-bottom: 11.5px;
        --t28xr-ls-footer-fs: 12px;
        --t28xr-ls-footer-reserve: 60px;
        --t28xr-ls-card-maxh: calc(100vh - var(--t28xr-ls-footer-reserve));
      }

      @supports (height: 100dvh) {
        :root {
          --t28xr-ls-card-maxh: calc(100dvh - var(--t28xr-ls-footer-reserve));
        }
      }

      /* FIX: top 20px breathing + bottom 60px (20px gap + ~40px footer clearance) */
      .layout {
        min-height: 100vh;
        padding: 20px 10px 60px;
        align-items: center;
        justify-content: center;
      }

      @supports (height: 100dvh) {
        .layout {
          min-height: 100dvh;
        }
      }

      main {
        max-height: none;
        overflow-y: visible;
        transform: translateY(var(--t28xr-ls-card-offset-y));
      }

      .site-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--t28xr-ls-footer-bottom);
        padding: 0 12px;
        font-size: var(--t28xr-ls-footer-fs);
        transform: none;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1.1;
      }
    }

    /* ------------------------------------------------------------------------
  T28.4 — iPhone 14 Pro Max (932×430), LANDSCAPE — Privacy Information
---------------------------------------------------------------------------*/
    @media (orientation: landscape) and (width: 932px) and (height: 430px) {

      :root {
        --t284pm-ls-card-offset-y: 0px;
        --t284pm-ls-footer-reserve: 60px;
        --t284pm-ls-card-maxh: calc(100vh - var(--t284pm-ls-footer-reserve));
        --t284pm-ls-footer-bottom: 9.5px;
        --t284pm-ls-footer-offset-y: 0px;
        --t284pm-ls-footer-fs: 12px;
        /* FIX: layout padding with 20px breathing room */
        --t284pm-ls-layout-pad: 20px 10px;
      }

      @supports (height: 100dvh) {
        :root {
          --t284pm-ls-card-maxh: calc(100dvh - var(--t284pm-ls-footer-reserve));
        }
      }

      html,
      body {
        overscroll-behavior: auto;
        overflow-y: auto;
        height: auto;
        min-height: 100%;
      }

      /* FIX: 20px top + 20px bottom breathing room */
      .layout {
        padding: var(--t284pm-ls-layout-pad);
        align-items: center;
        justify-content: center;
      }

      main {
        max-height: none;
        overflow-y: visible;
        transform: translateY(var(--t284pm-ls-card-offset-y));
      }

      .site-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--t284pm-ls-footer-bottom);
        padding: 0 12px;
        font-size: var(--t284pm-ls-footer-fs);
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1.1;
        transform: translateY(var(--t284pm-ls-footer-offset-y));
      }
    }

    /* --------------------------------------------------------------------------
  T28.1 — 667×375 LANDSCAPE (medium landscape phones) — Privacy Information
  -----------------------------------------------------------------------------*/
    @media (orientation: landscape) and (min-width: 640px) and (max-width: 799px) and (min-height: 361px) and (max-height: 430px) {

      html,
      body {
        overscroll-behavior: auto;
        overflow-y: auto;
        height: auto;
        min-height: 100%;
      }

      :root {
        --t28pi-card-w: min(calc(100vw - 20px), 650px);
        --t28pi-card-pad: 16px 14px 14px;
        --t28pi-card-radius: 24px;

        --t28pi-fs-h1: 22px;
        --t28pi-fs-meta: 11px;
        --t28pi-fs-body: 11px;
        --t28pi-fs-link: 13px;
        --t28pi-fs-footer: 12px;
        --t28pi-lh-body: 1.40;

        --t28pi-gap-h1-meta: 8px;
        --t28pi-gap-meta-body: 10px;
        --t28pi-gap-paragraph: 6px;
        --t28pi-gap-body-link: 12px;

        --t28pi-logo-h: 28px;
        --t28pi-logo-top: 8px;
        --t28pi-logo-right: 12px;
        --t28pi-title-right-reserve: 110px;

        /* FIX: 20px top+bottom breathing room */
        --t28pi-layout-pad-top: 20px;
        --t28pi-layout-pad-x: 10px;
        --t28pi-layout-pad-bottom: 20px;

        --t28pi-footer-bottom-pad: 8.5px;
        --t28pi-card-offset-y: 6px;
        --t28pi-footer-reserve: 62px;
      }

      :root {
        --footer-raise: 0px;
      }

      html,
      body {
        height: auto !important;
        min-height: 100%;
        overflow-y: auto !important;
        overscroll-behavior: auto;
      }

      html {
        background-color: #f7f9fc;
        background-image: repeating-linear-gradient(-45deg,
            #f7f9fc,
            #f7f9fc 18px,
            #e2e8f0 18px,
            #e2e8f0 20px);
      }

      /* FIX: 20px top + 20px bottom space around card */
      .layout {
        padding: var(--t28pi-layout-pad-top) var(--t28pi-layout-pad-x) var(--t28pi-layout-pad-bottom);
        align-items: center;
        justify-content: center;
      }

      main {
        width: var(--t28pi-card-w);
        max-width: none;
        padding: 18px 14px 14px;
        border-radius: 24px;
        transform: translateY(10px);
        max-height: none !important;
        overflow-y: visible !important;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
      }

      .header-row {
        margin: 0;
        justify-content: flex-start;
      }

      .title-block {
        padding-right: var(--t28pi-title-right-reserve);
      }

      h1 {
        font-size: var(--t28pi-fs-h1);
        margin: 0 0 var(--t28pi-gap-h1-meta) 0;
        line-height: 1.05;
        text-align: left;
      }

      .meta {
        font-size: var(--t28pi-fs-meta);
        margin: 0 0 var(--t28pi-gap-meta-body) 0;
        text-align: left;
      }

      p {
        font-size: var(--t28pi-fs-body);
        line-height: var(--t28pi-lh-body);
        margin: 0 0 var(--t28pi-gap-paragraph) 0;
      }

      main p:last-of-type {
        margin-bottom: var(--t28pi-gap-body-link);
      }

      a.btn {
        font-size: var(--t28pi-fs-link);
        margin-top: auto;
        align-self: flex-start;
        margin-left: 0;
      }

      .logo-block {
        top: var(--t28pi-logo-top);
        right: var(--t28pi-logo-right);
      }

      .demo-logo {
        height: var(--t28pi-logo-h);
        width: auto;
      }

      .site-footer {
        font-size: var(--t28pi-fs-footer);
        padding: 0 0 var(--t28pi-footer-bottom-pad);
        transform: translateY(0);
      }
    }

    /*------------------------------------------------------
   iPhone SE (568×320), LANDSCAPE – Privacy Information
   FIX: 18px top+bottom breathing room added
   -----------------------------------------------------*/
    @media (max-width: 950px) and (max-height: 360px) and (orientation: landscape) {

      :root {
        --card-offset-y: 13px;
        --footer-safe-gap: 70px;
      }

      html,
      .page-bg {
        background-position: center;
        background-size: cover;
      }

      html,
      body {
        height: auto !important;
        min-height: 100%;
        overflow-y: auto !important;
        overscroll-behavior: auto;
      }

      html {
        background-color: #f7f9fc;
        background-image: repeating-linear-gradient(-45deg,
            #f7f9fc,
            #f7f9fc 18px,
            #e2e8f0 18px,
            #e2e8f0 20px);
      }

      /* FIX: 18px top + 18px bottom breathing room */
      .layout {
        padding: 18px 8px var(--footer-safe-gap);
        align-items: center;
        justify-content: center;
      }

      main {
        max-width: 550px;
        width: min(calc(100vw - 20px), 545px);
        border-radius: 24px;
        padding: 12px 14px 14px;
        max-height: none;
        overflow-y: visible;
        overflow-x: hidden;
        transform: translateY(var(--card-offset-y));
      }

      h1 {
        font-size: 18px;
        margin: 0 0 8px 0;
      }

      .meta {
        font-size: 11px;
        margin: 0 0 10px 0;
      }

      p {
        font-size: 11px;
        line-height: 1.3;
        margin: 6px 0;
      }

      a.btn {
        font-size: 13px;
        margin-top: 10px;
      }

      .logo-block {
        top: 6px;
        right: 12px;
      }

      .demo-logo {
        height: 28px;
        width: auto;
      }

      .site-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 12px;
        font-size: 11px;
        padding: 0 16px;
        margin: 0;
        transform: none;
        line-height: 1.1;
        z-index: 5;
        pointer-events: none;
        text-align: center;
        color: #fff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
      }

      /* .site-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 12px;
        font-size: 11px;
        padding: 0 16px;
        z-index: 5;
        text-align: center;
        pointer-events: none;
      } */
    }
  </style>
</head>

<body>
  <div class="page-bg" aria-hidden="true"></div>

  <div class="layout">
    <main>
      <div class="header-row">
        <div class="title-block">
          <h1><?= htmlspecialchars(t('title'), ENT_QUOTES, 'UTF-8') ?></h1>
          <p class="meta">
            <?= htmlspecialchars(t('updated'), ENT_QUOTES, 'UTF-8') ?>
            <?= htmlspecialchars($privacyLastUpdated, ENT_QUOTES, 'UTF-8') ?>
          </p>
        </div>
        <div class="logo-block">
          <img src="/assets/img/logo.svg" alt="DemoBrand logo" class="demo-logo">
        </div>
      </div>

      <p><?= htmlspecialchars(t('p1'), ENT_QUOTES, 'UTF-8') ?></p>
      <p><?= htmlspecialchars(t('optout'), ENT_QUOTES, 'UTF-8') ?></p>
      <p><?= htmlspecialchars(t('legal'), ENT_QUOTES, 'UTF-8') ?></p>
      <p><?= t('rights') ?></p>

      <a href="/index.php" class="btn"><?= htmlspecialchars(t('close'), ENT_QUOTES, 'UTF-8') ?></a>
    </main>
  </div>

  <footer class="site-footer" role="contentinfo">
    Copyright <?= (int) COPYRIGHT_YEAR ?> <?= htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') ?>, All rights reserved.
  </footer>
  <!-- keep your dragstart script here exactly as it is now -->

  <script>
    (function () {
      // prevent ghost dragging on the Close-and-return link
      document.addEventListener('dragstart', function (e) {
        if (!e.target || !e.target.closest) return;
        const link = e.target.closest('a[href*="/index.php"]');
        if (link) {
          e.preventDefault();
        }
      }, true);
    })();

    /* ---------------------------------------------------------------
       Hide the mobile browser address bar on tap / touch start.
       Only fires in landscape on small screens (phones).
    --------------------------------------------------------------- */
    (function () {
      var triggered = false;

      function hideBrowserBar() {
        if (triggered) return;
        if (window.innerHeight > 600) return;
        triggered = true;
        document.body.style.minHeight = (window.innerHeight + 2) + 'px';
        setTimeout(function () {
          window.scrollTo(0, 1);
          setTimeout(function () {
            window.scrollTo(0, 0);
            document.body.style.minHeight = '';
            triggered = false;
          }, 40);
        }, 50);
      }

      document.addEventListener('touchstart', hideBrowserBar, { passive: true, once: false });

      window.addEventListener('orientationchange', function () {
        triggered = false;
        setTimeout(hideBrowserBar, 300);
      });

      window.addEventListener('load', function () {
        setTimeout(hideBrowserBar, 400);
      });
    })();
  </script>
</body>

</html>