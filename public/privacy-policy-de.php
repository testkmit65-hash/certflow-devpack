<?php
declare(strict_types=1);
require_once __DIR__ . '/../code/app/config.php';

$lang = 'de';
$privacyLastUpdated = format_privacy_last_updated($lang);
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Privacy Policy – DemoBrand</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
  <link rel="preload" as="image" href="/assets/img/landing-bg-desktop.webp" type="image/webp" fetchpriority="high">
  <link rel="preload" as="image" href="/assets/img/landing-bg-mobile.webp" type="image/webp" media="(max-width: 480px)" fetchpriority="high">

  <style>
        :root {
      --ink-blue: #0F3558;

        /* Instant background placeholders (prevent blue first paint) */
      --bg-lqip-desktop: url("data:image/webp;base64,UklGRpgAAABXRUJQVlA4IIwAAACwBACdASpAACQAPzGGvleuqCYkqBqqqdAmCUAZjmv4AFS0Zi1u7k7bANk/Ih+AAPbiVue6h/oV3Z8Rdn/bzmz28bx/qvNfDmkW5gG1oP7ln9Uuhh+dvvq9WFzf1DrBuZhBQ8mMXyI4bOaO8nD16rZdk1CbWRB7hGwVG4QfS4zKQBHGiZZ/JICpMgAAAA==");
      --bg-lqip-mobile:  url("data:image/webp;base64,UklGRrYAAABXRUJQVlA4IKoAAABwBQCdASokAE4APzGCt1SuqCWjLNgMcdAmCWMAv7AAwxygPWHFpHLD3xupJ+Yqv5FTMTQAAPcDvToZzbX9CvWX302P+onYjxgTkjGijV050Jl6Xk9aXpIVfis6itElg4CvB4Ywz7OvWqKSaM60ViAKdTXPqmxliAnbrqYM45hQddUC9MSqXB7taYcxczhSuiDSaqB1SzvQ/HirHTZgK50/mw8hZxcSeggAAA==");

      --footer-raise: -87.5px;

      /* Privacy card header tunables (desktop / default) */
      --policy-logo-height: 38px;    /* logo size */
      --policy-logo-shift-x: 458px;    /* horizontal shift relative to text */
      --policy-logo-shift-y: 0px;    /* vertical shift */

      --card-offset-y: 2px;         /* + down / - up (card only) */
 
      --pp-pt-gap-s1-contactprompt-to-contact: 3px; /* “You can contact…” -> “Contact: …” */
      --pp-footer-bottom: 12px;
    }

    * {
      box-sizing: border-box;
    }

    /* Section 1: make the contact gap adjustable */
    .policy-card h2:first-of-type + p + p{
      margin-bottom: 0 !important; /* “You can contact us…” paragraph */
    }
    .policy-card p.policy-contact-line{
      margin-top: var(--t28x-pp-pt-gap-s1-contactprompt-to-contact) !important;
    }

    /* White-striped background matching index.php and success.php */
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

    body {
      font-family: Helvetica, Arial, sans-serif;
      line-height: 1.6;
      margin: 0;
      background: transparent;
      color: #111;
      min-height: 100vh;
      overflow-x: hidden;
      position: relative;
      z-index: 1;
    }
    @supports (min-height:100dvh){ body{ min-height:100dvh; } }

    /* Portrait-only: lock body to exactly 100dvh so no spurious scroll appears */
    @media (orientation: portrait) {
      body{
        height: 100vh;
        overflow-y: hidden;
      }
      @supports (height:100dvh){ body{ height:100dvh; } }
    }

    .page-bg {
      position: fixed;
      inset: 0;
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
      z-index: 0;
      pointer-events: none;
      transform: translateZ(0);
    }

    /* Prevent scrolling on the outer viewport on large screens */
    @media (min-width: 721px) {
      html, body {
        height: 100%;
        overflow: hidden;
      }
    }

    .page-shell {
    /* Leave room for the background footer inside the viewport */
      min-height: calc(100vh - 60px);
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 10mm 16px;
      position: relative;
      z-index: 1;
    }

    /* Ensure the policy card remains scrollable */
    .policy-card {
      max-width: 920px;
      width: min(920px, 100% - 40px);
      background: #ffffff;
      border-radius: 30px;
      padding: 40px 60px 48px;
      box-shadow: 0 18px 40px rgba(0, 0, 0, 0.35);
      max-height: 80vh;
      overflow-y: auto;
      position: relative;
      margin-bottom: 20px;
      transform: translateY(var(--card-offset-y));
  
      clip-path: inset(0 0 0 0 round 30px);
    }

    /* Style the scrollbar */
    .policy-card::-webkit-scrollbar {
      width: 10px; /* Scrollbar width */
      border-radius: 10px; /* Rounded scrollbar corners */
    }

    /* Style the scrollbar thumb (draggable part) */
    .policy-card::-webkit-scrollbar-thumb {
      background-color: rgba(15, 53, 88, 0.15); /* ink-blue with 50% transparency */
      border-radius: 10px; /* Rounded corners for the thumb */
    }

    /* On hover, make the thumb fully opaque (100%) */
    .policy-card::-webkit-scrollbar-thumb:hover {
      background-color: rgba(15, 53, 88, 1); /* ink-blue at 100% opacity */
    }

    /* Style the scrollbar track (background area) */
    .policy-card::-webkit-scrollbar-track {
      background: transparent;
      border-radius: 10px;
    }

        /* Style for the "Close and return" link */
    .close-return-link {
      position: sticky;  /* stick inside the scrolling policy card */
      top: 60px;         /* vertical position for desktop / default */
      font-size: 15px;
      color: rgba(254, 130, 21, 0.6);
      background: rgba(255, 255, 255, 0.6);
      padding: 1px 4px;
      border-radius: 5px;
      text-decoration: none;
      z-index: 1000;
      float: right;
      margin-right: 3px; /* horizontal offset for desktop / default */
      -webkit-user-drag: none;
      user-select: none;
    }

    /* Hover effect for the "Close and return" link */
    .close-return-link:hover {
      background: rgba(254, 130, 21, 1); /* Darker ink-orange on hover */
      color: #fff; /* White text on hover */
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;   /* change to flex-start if you want them a bit higher */
      gap: 24px;
      margin-bottom: 12px;
    }

    .page-header-left {
      display: flex;
      align-items: baseline;
    }

    .page-title {
      display: inline-flex;
      align-items: baseline;
      gap: 0.6rem;
      font-size: 2.0rem;
      margin: 0;
    }

    .page-title-divider {
      font-weight: 300;
      opacity: 0.7;
    }

    .logo-inline {
      height: var(--policy-logo-height);
      width: auto;
      display: inline-block;
      transform: translate(
      var(--policy-logo-shift-x),
      var(--policy-logo-shift-y)
      );
      /* Prevent ghost dragging for the logo */
      -webkit-user-drag: none;
      user-select: none;
    }

    .lang-switch-main {
      margin: 0;                        /* no extra top/bottom gap, header handles spacing */
      display: flex;
      align-items: center;
      gap: 16px;
      font-size: 0.9rem;
    }

    .lang-switch-main a {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-decoration: none;
      color: #0f6ad8;
      font-weight: 500;
    }

    .lang-switch-main a img {
      width: 48px;
      height: auto;
      margin-bottom: 2px;
      display: block;
      transition: transform .08s ease;
      transform: translateZ(0);
    }

    .lang-switch-main a:hover img {
      transform: translateY(-1px);
    }

    .lang-switch-main a:active img {
      transform: translateY(0);
    }

    .lang-switch-main .lang-label {
      font-size: 0.8rem;
      letter-spacing: 0.06em;
    }

    .subtitle {
      color: #4b5563;
      font-size: 0.95rem;
      margin: 0.75rem 0 0.25rem;
    }

    .meta {
      font-size: 0.9rem;
      color: #6b7280;
      margin-bottom: 1.5rem;
    }

    .site-footer{
      text-align:center;
      padding: 12px 16px 24px;
      color:#fff;
      font-weight:700;
      font-size:22px;
      line-height:1.2;
      text-shadow:0 1px 2px rgba(0,0,0,.25);
      transform: translateY(var(--footer-raise));
    }

    h1, h2, h3 {
      font-weight: 600;
      color: #111827;
    }

    h1 { font-size: 1.9rem; margin-bottom: 1rem; }
    h2 { font-size: 1.4rem; margin-top: 2rem; }
    h3 { font-size: 1.15rem; margin-top: 1.4rem; }

    a {
      color: #0f6ad8;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    ul {
      margin: 0.3rem 0 0.9rem 1.25rem;
      padding-left: 0.75rem;
    }

    li {
      margin-bottom: 0.25rem;
    }

   /* Prevent ghost dragging for the email link */
    a[href="mailto:support@example.test"] {
  -webkit-user-drag: none; 
  user-select: none;
    } 

/* ------------------------------------------------------------------------
  T27.4 — Tablet & laptop viewport audit — Datenschutzerklärung (DE)
---------------------------------------------------------------------------*/
@media (min-width: 700px) and (max-width: 1600px) and (min-height: 700px){

  :root{
    /* White card: size + geometry */
    --t27pp-card-max-w: 920px;
    --t27pp-card-max-h: 75vh;          /* keep vh on purpose */
    --t27pp-card-radius: 24px;

    /* Card padding (space all around text block) */
    --t27pp-card-pad-t: 32px;
    --t27pp-card-pad-r: 16px;
    --t27pp-card-pad-b: 40px;
    --t27pp-card-pad-l: 24px;

    --t27pp-card-offset-y: 2px;         /* + down / - up (card only) */

    /* Space around card (page-shell) */
    --t27pp-shell-pad-tb: 38px;        /* ~10mm */
    --t27pp-shell-pad-lr: 38px;

    /* Close & return: position + size */
    --t27pp-link-top: 42px;
    --t27pp-link-mr: 0px;
    --t27pp-link-fs: 15px;

    /* Logo: size + position */
    --t27pp-logo-h: 38px;
    --t27pp-logo-top: 0px;
    --t27pp-logo-right: -152px;
    --t27pp-title-right-reserve: 140px;

    /* Header spacing */
    --t27pp-gap-cardtop-to-header: -10px;
    --t27pp-gap-header-to-subtitle: 30px;

    /* Title / subtitle / meta */
    --t27pp-fs-title: 32px;
    --t27pp-fs-subtitle: 15px;
    --t27pp-fs-meta: 14px;

    --t27pp-gap-subtitle-to-meta: 4px;
    --t27pp-gap-meta-to-body: 24px;

    /* Headings + body font sizes */
    --t27pp-fs-h2: 21px;
    --t27pp-fs-h3: 18px;
    --t27pp-fs-body: 14px;

    /* Line spacing (unitless is best) */
    --t27pp-lh-body: 1.6;

    /* Heading spacing (above + below)
       Keep defaults as keywords to preserve current baseline unless you change them */
    --t27pp-h2-mt: 32px;
    --t27pp-h2-mb: revert;
    --t27pp-h3-mt: 22px;
    --t27pp-h3-mb: revert;

    /* Paragraph spacing — preserve current baseline by default */
    --t27pp-p-mt: revert;
    --t27pp-p-mb: revert;

    /* Gap before "Contact: support@example.test" (Section 1) */
    --t27pp-contact-mt: revert;

    /* NEW: Section-specific paragraph gaps you asked for (px) */
    --t27pp-gap-s1-services-to-contactprompt: 14px; /* “…our”).” -> “You can contact us…” */
    --t27pp-gap-s1-contactline-to-next: 14px;       /* “Contact: …” -> “If you are based…” */

    --t27pp-gap-s14-prompt-to-contact: 14px;        /* “…contact us at:” -> “Contact: …” */
    --t27pp-gap-s14-contact-to-response: 14px;      /* “Contact: …” -> “We will do our best…” */

    /* Footer (footnote): size + distance to bottom */
    --t27pp-footer-fs: 18px;           /* ~16pt */
    --t27pp-footer-pad-t: 12px;
    --t27pp-footer-pad-b: 24px;
    --t27pp-footer-raise: 10.5px;

    /* Wire existing footer var */
    --footer-raise: var(--t27pp-footer-raise);
  }

  .page-shell{
    padding: var(--t27pp-shell-pad-tb) var(--t27pp-shell-pad-lr) !important;
  }

  .policy-card{
    max-width: var(--t27pp-card-max-w) !important;   /* makes --t27pp-card-max-w respond */
    max-height: var(--t27pp-card-max-h) !important;
    border-radius: var(--t27pp-card-radius) !important;
    padding: var(--t27pp-card-pad-t) var(--t27pp-card-pad-r) var(--t27pp-card-pad-b) var(--t27pp-card-pad-l) !important;
    transform: translateY(var(--t27pp-card-offset-y));
    clip-path: inset(0 0 0 0 round var(--t27pp-card-radius)) !important;
  }

  .page-header{
    position: relative;
    margin-top: var(--t27pp-gap-cardtop-to-header);
    margin-bottom: var(--t27pp-gap-header-to-subtitle);
  }

  .page-title{
    font-size: var(--t27pp-fs-title);
    padding-right: var(--t27pp-title-right-reserve);
  }

  /* Logo: stop desktop translate, make it reliably visible on tablet portrait */
  .logo-inline{
    position: absolute;
    top: var(--t27pp-logo-top);
    right: var(--t27pp-logo-right);
    height: var(--t27pp-logo-h);
    width: auto;
    transform: none;
    pointer-events: none;
  }

  .close-return-link{
    top: var(--t27pp-link-top);
    margin-right: var(--t27pp-link-mr);
    font-size: var(--t27pp-link-fs);
  }

  /* FIX: ensure subtitle + meta knobs are not overridden by generic paragraph rules */
  .policy-card p.subtitle{
    font-size: var(--t27pp-fs-subtitle) !important;
    margin: 12px 0 var(--t27pp-gap-subtitle-to-meta) 0 !important;
  }

  .policy-card p.meta{
    font-size: var(--t27pp-fs-meta) !important;
    margin: 0 !important; /* meta->body gap controlled by the next rule */
  }

  /* FIX: make --t27pp-gap-meta-to-body actually control spacing before the first h2 */
  .policy-card p.meta + h2{
    margin-top: var(--t27pp-gap-meta-to-body) !important;
  }

  .policy-card h2{
    font-size: var(--t27pp-fs-h2);
    margin-top: var(--t27pp-h2-mt);
    margin-bottom: var(--t27pp-h2-mb);
  }

  .policy-card h3{
    font-size: var(--t27pp-fs-h3);
    margin-top: var(--t27pp-h3-mt);
    margin-bottom: var(--t27pp-h3-mb);
  }

  /* Body text (exclude subtitle/meta so they keep their own sizes) */
  .policy-card p:not(.subtitle):not(.meta),
  .policy-card li{
    font-size: var(--t27pp-fs-body);
    line-height: var(--t27pp-lh-body);
  }

  .policy-card p:not(.subtitle):not(.meta){
    margin-top: var(--t27pp-p-mt);
    margin-bottom: var(--t27pp-p-mb);
  }

  /* Section 1: specific gaps you requested */
  .policy-card h2:first-of-type + p{
    margin-bottom: var(--t27pp-gap-s1-services-to-contactprompt) !important;
  }
  .policy-card h2:first-of-type + p + p{
    margin-top: 0 !important;
    margin-bottom: 0 !important;
  }
  .policy-card p.policy-contact-line{
    margin-top: var(--t27pp-contact-mt);
    margin-bottom: var(--t27pp-gap-s1-contactline-to-next) !important;
  }
  .policy-card p.policy-contact-line + p{
    margin-top: 0 !important;
  }

  /* Section 14: gaps around the contact paragraph (NOTE: no policy-contact-line class there) */
  .policy-card h2:last-of-type + p{
    margin-bottom: 0 !important;
  }
  .policy-card h2:last-of-type + p + p{
    margin-top: var(--t27pp-gap-s14-prompt-to-contact) !important;
    margin-bottom: 0 !important;
  }
  .policy-card h2:last-of-type + p + p + p{
    margin-top: var(--t27pp-gap-s14-contact-to-response) !important;
  }

  .site-footer{
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;

  font-size: var(--t27pp-footer-fs);
  padding: var(--t27pp-footer-pad-t) 16px var(--t27pp-footer-pad-b);
  transform: translateY(var(--footer-raise));
 }
}

/*--------------------------------------------------------------------------------
Privacy Policy (DE) – Split-screen / Foldable bridge (481–699px width) – PORTRAIT
----------------------------------------------------------------------------------*/
@media (orientation: portrait)
  and (min-width: 481px) and (max-width: 699px){

  html, body{
    height: 100%;
    overflow: hidden;
    overscroll-behavior: none;
  }

  :root{
    /* White card: size + geometry (all adjustable) */
    --pp-card-w: min(calc(100vw - 34px), 400px);
    --pp-card-max-h: calc(100vh - var(--pp-footer-reserve));
    --pp-card-radius: 24px;

    --pp-card-pad-t: 22px;
    --pp-card-pad-r: 22px;
    --pp-card-pad-b: 22px;
    --pp-card-pad-l: 22px;

    --pp-card-offset-y: 0px; /* + down / - up (card only) */

    /* Close & return: position + size */
    --pp-link-top: 44px;      /* Y */
    --pp-link-mr: -12px;        /* X (distance from right edge of card) */
    --pp-fs-link: 15px;

    /* Logo: size + position */
    --pp-logo-h: 34px;        /* height, width auto */
    --pp-logo-top: -2px;      /* Y inside card */
    --pp-logo-right: -152px;    /* X inside card */
    --pp-title-right-reserve: 50px;

    /* Header spacing (all adjustable) */
    --pp-gap-top-title: 0px;        /* card top -> header */
    --pp-gap-title-subtitle: 30px;   /* header -> subtitle */

    /* Subtitle + meta spacing (all adjustable) */
    --pp-gap-subtitle-meta: 6px;     /* subtitle -> meta (“updated…”) */
    --pp-gap-meta-body: 18px;        /* meta -> first h2 */

    /* Headings + paragraph spacing */
    --pp-gap-h2-top: 20px;
    --pp-gap-h2-bottom: 8px;
    --pp-gap-h3-top: 16px;
    --pp-gap-h3-bottom: 6px;

    --pp-gap-p: 14px; /* paragraph spacing */

    /* Space around card (page-shell) */
    --pp-shell-pad-tb: 38px;        /* ~10mm */
    --pp-shell-pad-lr: 38px;

    /* Line spacing */
    --pp-lh-body: 1.55;

    /* Font sizes (all individually adjustable) */
    --pp-fs-title: 28px;
    --pp-fs-subtitle: 14px;
    --pp-fs-meta: 12px;
    --pp-fs-h2: 20px;
    --pp-fs-h3: 18px;
    --pp-fs-body: 13px;

    /* Footer: size + distance to bottom (independent from card) */
    --pp-fs-footer: 14px;
    --pp-footer-bottom: 17px;    /* distance to viewport bottom */
    --pp-footer-reserve: 130px;  /* reserve space so card never clashes */
    --pp-shell-footer-offset: 50px;

    /* Section 1 “Contact:” gap knob (keep both names safe) */
    --pp-pt-gap-s1-contactprompt-to-contact: 3px;
    --t28x-pp-pt-gap-s1-contactprompt-to-contact: var(--pp-pt-gap-s1-contactprompt-to-contact);
  }

  @supports (height: 100dvh){
    :root{
      --pp-card-max-h: calc(100dvh - var(--pp-footer-reserve));
    }
  }

  .page-shell{
    padding: var(--pp-shell-pad-tb) var(--pp-shell-pad-lr) !important;
  }

  .policy-card{
    width: var(--pp-card-w) !important;
    max-height: var(--pp-card-max-h) !important;
    border-radius: var(--pp-card-radius)!important;
    padding: var(--pp-card-pad-t) var(--pp-card-pad-r) var(--pp-card-pad-b) var(--pp-card-pad-l) !important;
    transform: translateY(var(--pp-card-offset-y));
    clip-path: inset(0 0 0 0 round var(--pp-card-radius)) !important;
  }

  .close-return-link{
    top: var(--pp-link-top);
    margin-right: var(--pp-link-mr);
    font-size: var(--pp-fs-link);
  }

  .page-header{
    margin-top: var(--pp-gap-top-title);
    margin-bottom: var(--pp-gap-title-subtitle);
    position: relative;
  }

  .page-title{
    font-size: var(--pp-fs-title);
    padding-right: var(--pp-title-right-reserve);
    white-space: normal;
  }

  .logo-inline{
    position: absolute;
    top: var(--pp-logo-top);
    right: var(--pp-logo-right);
    height: var(--pp-logo-h);
    width: auto;
    transform: none;
    pointer-events: none;
  }

  .subtitle{
    font-size: var(--pp-fs-subtitle);
    margin: 0 0 var(--pp-gap-subtitle-meta) 0;
  }

  .meta{
    font-size: var(--pp-fs-meta);
    margin: 0 0 var(--pp-gap-meta-body) 0;
  }

  h2{
    font-size: var(--pp-fs-h2);
    margin: var(--pp-gap-h2-top) 0 var(--pp-gap-h2-bottom) 0;
  }

  h3{
    font-size: var(--pp-fs-h3);
    margin: var(--pp-gap-h3-top) 0 var(--pp-gap-h3-bottom) 0;
  }

  p, li{
    font-size: var(--pp-fs-body);
    line-height: var(--pp-lh-body);
  }

  p{ margin: 0 0 var(--pp-gap-p) 0; }

  ul, ol{
    margin: 0 0 var(--pp-gap-p) 22px;
    padding: 0;
  }

  .site-footer{
    position: fixed;
    left: 0; right: 0;
    bottom: var(--pp-footer-bottom);

    font-size: var(--pp-fs-footer);
    padding: 0 16px;
    margin: 0;

    transform: none;
    line-height: 1.1;
    z-index: 5;

    pointer-events: none; /* prevents “drag feel” on footer */
  }
}

/*-------------------------------------------------------------
   Common phones 12/13/14 & Pixel 5 (≈390/393px), PORTRAIT (DE)
  -------------------------------------------------------------*/
@media (min-width: 360px) and (max-width: 480px) and (orientation: portrait){

  :root{
    /* Card size + centering */
    --pp-card-w: calc(100vw - 40px);   /* matches privacy.php narrowed view */
    --pp-card-max-h: 87vh;             /* adjustable */
    --pp-card-radius: 24px;
    --pp-card-pad-t: 10px;
    --pp-card-pad-r: 14px;
    --pp-card-pad-b: 14px;
    --pp-card-pad-l: 14px;
    --pp-pt-card-offset-y: 25px;         /* + down / - up (card only) */


    /* Font sizes (all individually adjustable) */
    --pp-fs-title: 23px;     /* H1 */
    --pp-fs-subtitle: 13px;  /* Subheading 1 (.subtitle) */
    --pp-fs-meta: 12px;      /* Subheading 2 (.meta) */
    --pp-fs-h2: 16px;        /* Section headings */
    --pp-fs-h3: 14px;        /* Sub-section headings */
    --pp-fs-body: 12px;      /* Body text + lists */
    --pp-fs-link: 14px;      /* Close and return */
    --pp-fs-footer: 12px;    /* Footnote/footer */

    --pp-lh-body: 1.45;

    /* Margins / gaps (all individually adjustable) */
    --pp-gap-top-title: -8px;           /* NEW: card top edge -> heading block */
    --pp-gap-title-subtitle: 30px;     /* Heading -> Subheading */
    --pp-gap-subtitle-meta: 6px;       /* Subheading -> Subheading 2 */
    --pp-gap-meta-body: 14px;          /* Subheading 2 -> body (first h2) */

    --pp-pt-gap-s1-contactprompt-to-contact: 2px; /* “You can contact…” -> “Contact: …” */

    --pp-gap-p: 12px;                 /* paragraph spacing */
    --pp-gap-h2-top: 18px;
    --pp-gap-h2-bottom: 6px;
    --pp-gap-h3-top: 14px;
    --pp-gap-h3-bottom: 6px;

    /* Close-link position (top + left of white card) */
    --pp-link-top: 55px;
    --pp-link-left: 189px;            /* relative to card content area (inside padding) */
    --pp-gap-link-header: 0px;        /* link -> header spacing */

    /* Logo position + size (top/right of white card) */
    --pp-logo-h: 32px;                /* adjustable */
    --pp-logo-top: 12px;              /* to top edge of card */
    --pp-logo-right: 12px;            /* to right edge of card */
    --pp-title-right-reserve: 120px;  /* keep title clear of logo */

    /* Footer spacing */
    --pp-gap-card-footer: 22px;       /* card -> footer */
    --pp-footer-pad-b: 14px;          /* footer -> bottom of viewport */
    --footer-raise: 0px; 
    --pp-footer-bottom: 12px;
    --pp-shell-footer-offset: 140px;   /* was 100, increase for more footer room */
    --pp-card-max-h: calc(100dvh - var(--pp-shell-footer-offset));
  }
  @supports (min-height: 100dvh){
    :root{
      --pp-card-max-h: calc(100dvh - var(--pp-shell-footer-offset));
    }
  }

  /* White-striped background matching index.php / success.php */
  .page-bg{
    background-color: #f7f9fc;
    background-image: repeating-linear-gradient(
      -45deg,
      #f7f9fc,
      #f7f9fc 18px,
      #e2e8f0 18px,
      #e2e8f0 20px
    );
    background-position: unset;
    background-size: unset;
    background-repeat: unset;
  }

  /* Keep outer viewport locked (as you want) */
  html, body{
    height: 100%;
    overflow: hidden;
    overscroll-behavior: none;
  }

  /* Perfect centering in portrait viewport */
  .page-shell{
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 16px;
    height: 100%;
    min-height: 100dvh;
    box-sizing: border-box;
  }
  @supports (min-height: 100dvh){
    .page-shell{ min-height: 100dvh; height: 100dvh; }
  }

  /* White policy card: adjustable size + internal scrolling area */
  .policy-card{
    width: var(--pp-card-w);
    max-width: none;
    max-height: var(--pp-card-max-h) !important;
    border-radius: var(--pp-card-radius);
    padding: 20px var(--pp-card-pad-r) var(--pp-card-pad-b) var(--pp-card-pad-l);
    transform: none; /* remove legacy offset, rely on flex centering */
    overflow-y: auto !important;
    overflow-x: hidden;
    margin: 0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  }

   /* Style the scrollbar */
  .policy-card::-webkit-scrollbar {
    width: 6px; /* Scrollbar width */
    border-radius: 6px; /* Rounded scrollbar corners */
    }

  /* Close link: sticky + always clickable from any scroll position */
  .close-return-link{
    float: none;
    display: inline-block;
    position: sticky;
    top: var(--pp-link-top);
    margin-left: var(--pp-link-left);
    margin-right: 0;
    margin-bottom: var(--pp-gap-link-header);
    font-size: var(--pp-fs-link);
  }

  /* Title + logo */
  .page-header{
    margin-top: 5px;
    margin-bottom: var(--pp-gap-title-subtitle);
  }

  .page-title{
    font-size: var(--pp-fs-title);
    padding-right: var(--pp-title-right-reserve);
    white-space: normal; /* allow wrapping on phones */
  }

  .logo-inline{
    position: absolute;
    top: var(--pp-logo-top);
    right: var(--pp-logo-right);
    height: var(--pp-logo-h);
    width: auto;
    transform: none; /* disable desktop shift logic on phones */
    -webkit-user-drag: none;
    user-select: none;
    pointer-events: none;
  }

  .subtitle{
    font-size: var(--pp-fs-subtitle);
    margin: 0 0 var(--pp-gap-subtitle-meta) 0;
  }

  .meta{
    font-size: var(--pp-fs-meta);
    margin: 0 0 var(--pp-gap-meta-body) 0;
  }

  /* Content typography */
  h2{
    font-size: var(--pp-fs-h2);
    margin: var(--pp-gap-h2-top) 0 var(--pp-gap-h2-bottom) 0;
  }

  h3{
    font-size: var(--pp-fs-h3);
    margin: var(--pp-gap-h3-top) 0 var(--pp-gap-h3-bottom) 0;
  }

  p, li{
    font-size: var(--pp-fs-body);
    line-height: var(--pp-lh-body);
  }

  p{
    margin: 0 0 var(--pp-gap-p) 0;
  }

  ul, ol{
    margin: 0 0 var(--pp-gap-p) 22px;
    padding: 0;
  }

  /* Section 1: make the contact gap adjustable */
  .policy-card h2:first-of-type + p + p{
    margin-bottom: 0 !important; /* “You can contact us…” paragraph */
  }
  .policy-card p.policy-contact-line{
    margin-top: var(--t28x-pp-pt-gap-s1-contactprompt-to-contact) !important;
  }

  /* Footer */
  .site-footer{
    font-size: var(--pp-fs-footer);
    padding: 4px 8px var(--pp-footer-pad-b);
  }
}

/* ------------------------------------------------------------------
  Pixel 5 (393×851), PORTRAIT — Privacy Policy (DE)
--------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 393px) and (height: 851px){

  :root{
    /* Close-link position (top + left of white card) */
    --p5-pp-link-top: 55px;
    --p5-pp-link-left: 192px;            /* relative to card content area (inside padding) */
  }

  .close-return-link{
    top: var(--p5-pp-link-top);
    margin-left: var(--p5-pp-link-left);
  }
}

/* ----------------------------------------------------------
  T28.2 — iPhone X (375×812), PORTRAIT — Privacy Policy (DE)
-------------------------------------------------------------*/
@media (orientation: portrait)
  and (min-width: 375px) and (max-width: 389px)
  and (min-height: 780px) and (max-height: 830px){

  :root{
    /* ===== knobs ===== */
    --t28x-pp-pt-card-offset-y: -12px;     /* + down / - up (card only) */

    --t28x-pp-pt-footer-bottom: 7.5px;   /* footer only */
    --t28x-pp-pt-footer-h: 22px;        /* footer only */
    --t28x-pp-pt-footer-fs: 12px;       /* footer only */
    --t28x-pp-pt-footer-reserve: 95px;  /* prevents card/footer clash (independent) */

    --t28x-pp-pt-link-top: 54px;        /* close link Y */
    --t28x-pp-pt-link-left: 174px;      /* close link X (keeps the existing margin-left logic) */

    --t28x-pp-pt-logo-top: 11px;        /* logo Y */
    --t28x-pp-pt-logo-right: 12px;      /* logo X */
    --t28x-pp-pt-logo-h: 32px;          /* logo size */

    --t28x-pp-pt-fs-title: 22px;        /* “Privacy Policy” heading */

    --t28x-pp-pt-gap-s1-contactprompt-to-contact: 2px; /* “You can contact…” -> “Contact: …” */

    /* ===== wire into existing phone vars ===== */
    --pp-fs-title: var(--t28x-pp-pt-fs-title);

    --pp-link-top: var(--t28x-pp-pt-link-top);
    --pp-link-left: var(--t28x-pp-pt-link-left);

    --pp-logo-top: var(--t28x-pp-pt-logo-top);
    --pp-logo-right: var(--t28x-pp-pt-logo-right);
    --pp-logo-h: var(--t28x-pp-pt-logo-h);

    --pp-fs-footer: var(--t28x-pp-pt-footer-fs);

    /* make the card’s max height iPhone-X-tunable */
    --pp-card-max-h: calc(100vh - var(--t28x-pp-pt-footer-reserve));
    --pp-gap-card-footer: 0px;
  }
  @supports (height: 100dvh){
    :root{
      --pp-card-max-h: calc(100dvh - var(--t28x-pp-pt-footer-reserve));
    }
  }

  /* true viewport centering (card only) */
  .page-shell{
    min-height: 100vh;
    height: 100vh;
    padding: 12px 12px 0;
    align-items: center;
    justify-content: center;
  }
  @supports (height: 100dvh){
    .page-shell{ min-height: 100dvh; height: 100dvh; }
  }

  .policy-card{
    margin: 0;
    transform: translateY(var(--t28x-pp-pt-card-offset-y));
  }

  /* footer fixed + independent */
  .site-footer{
    position: fixed;
    left: 0; right: 0;
    bottom: var(--t28x-pp-pt-footer-bottom);
    height: var(--t28x-pp-pt-footer-h);
    padding: 0;
    margin: 0;
    transform: none;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    z-index: 5;
  }

  /* Section 1: make the contact gap adjustable */
  .policy-card h2:first-of-type + p + p{
    margin-bottom: 0 !important; /* “You can contact us…” paragraph */
  }
  .policy-card p.policy-contact-line{
    margin-top: var(--t28x-pp-pt-gap-s1-contactprompt-to-contact) !important;
  }
}

/* -----------------------------------------------------------
  T28.3 — iPhone XR (414×896), PORTRAIT — Privacy Policy (DE)
--------------------------------------------------------------*/
@media (orientation: portrait) and (width: 414px) and (height: 896px){

  :root{
    /* ===== knobs (XR only) ===== */
    --t28xr-pp-pt-card-offset-y: -12px;         /* + down / - up (card only) */

    /* Close & return link position (inside the white card) */
    --t28xr-pp-pt-link-top: 54px;             /* Y */
    --t28xr-pp-pt-link-left: 213px;           /* X (keeps existing margin-left logic) */

    /* Section 1: “You can contact…” -> “Contact: …” gap */
    --t28xr-pp-pt-gap-s1-contactprompt-to-contact: 2px;

    /* Footer: move independently from card */
    --t28xr-pp-pt-footer-bottom: 12px;         /* footer-only vertical position */
    --t28xr-pp-pt-footer-h: 22px;             /* footer-only */
    --t28xr-pp-pt-footer-fs: 12px;            /* footer-only */
    --t28xr-pp-pt-footer-reserve: 98px;       /* card max-height reserve (independent) */

    /* ===== wire into existing phone vars ===== */
    --pp-link-top: var(--t28xr-pp-pt-link-top);
    --pp-link-left: var(--t28xr-pp-pt-link-left);
    --pp-fs-footer: var(--t28xr-pp-pt-footer-fs);

    /* make card max-height XR-tunable (prevents footer clash) */
    --pp-card-max-h: calc(100vh - var(--t28xr-pp-pt-footer-reserve));
  }

  @supports (height: 100dvh){
    :root{
      --pp-card-max-h: calc(100dvh - var(--t28xr-pp-pt-footer-reserve));
    }
  }

  /* true viewport centering (card only) */
  .page-shell{
    min-height: 100vh;
    height: 100vh;
    padding: 12px 12px 0;
    align-items: center;
    justify-content: center;
  }
  @supports (height: 100dvh){
    .page-shell{ min-height: 100dvh; height: 100dvh; }
  }

  .policy-card{
    margin: 0;
    transform: translateY(var(--t28xr-pp-pt-card-offset-y));
  }

  /* footer fixed + independent */
  .site-footer{
    position: fixed;
    left: 0; right: 0;
    bottom: var(--t28xr-pp-pt-footer-bottom);
    height: var(--t28xr-pp-pt-footer-h);
    padding: 0;
    margin: 0;
    transform: none;

    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    z-index: 5;
  }

  /* Section 1: make the “You can contact…” -> “Contact:” gap adjustable */
  .policy-card h2:first-of-type + p + p{
    margin-bottom: 0 !important; /* “You can contact us…” paragraph */
  }
  .policy-card p.policy-contact-line{
    margin-top: var(--t28xr-pp-pt-gap-s1-contactprompt-to-contact) !important;
  }
}

/* --------------------------------------------------------------------------
  T28.6 — Samsung Galaxy S20 Ultra (412×915), PORTRAIT — Privacy Policy (DE)
-----------------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 412px) and (height: 915px){

  :root{
    /* Link knobs (S20 Ultra portrait only) */
    --t286s20-pp-pt-link-top: 54px;     /* + down / - up */
    --t286s20-pp-pt-link-left: 211px;   /* + right / - left (keeps existing margin-left logic) */

    /* wire into existing vars used by .close-return-link */
    --pp-link-top: var(--t286s20-pp-pt-link-top);
    --pp-link-left: var(--t286s20-pp-pt-link-left);
  }
}

/* --------------------------------------------------------------------------
  CH9 — Samsung Galaxy A51/A71 (412×914), PORTRAIT — Privacy Policy (DE)
-----------------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 412px) and (height: 914px){

  :root{
    /* Close link knobs (A51/A71 portrait only) */
    --ch9a51-pp-pt-link-top: 55px;     /* Y: + down / - up */
    --ch9a51-pp-pt-link-left: 211px;   /* X: + right / - left */

    /* wire into existing vars used by .close-return-link */
    --pp-link-top: var(--ch9a51-pp-pt-link-top);
    --pp-link-left: var(--ch9a51-pp-pt-link-left);
  }
}

/* --------------------------------------------------------------------------
  CH8 — Pixel 8 Pro (448×998), PORTRAIT — Privacy Policy (DE)
-----------------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 448px) and (height: 998px){
  :root{
    /* Link knobs (Pixel 8 Pro portrait only) */
    --ch8p8pro-pp-pt-link-top: 55px;     /* + down / - up */
    --ch8p8pro-pp-pt-link-left: 247px;   /* + right / - left */

    /* wire into existing vars used by .close-return-link */
    --pp-link-top: var(--ch8p8pro-pp-pt-link-top);
    --pp-link-left: var(--ch8p8pro-pp-pt-link-left);
    }
  }

/* ------------------------------------------------------------------
  T28.4 — iPhone 14 Pro Max (430×932), PORTRAIT — Privacy Policy (DE)
---------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 430px) and (height: 932px){

  :root{
    /* ===== CARD knobs (independent) ===== */
    --t284pm-pp-pt-card-offset-y: -12px;          /* + down / - up (card only) */
    --t284pm-pp-pt-footer-reserve: 105px;        /* card-only: controls max height */
    --t284pm-pp-pt-card-max-h: calc(100vh - var(--t284pm-pp-pt-footer-reserve));

    /* Close link knobs (optional, but safe to have) */
    --t284pm-pp-pt-link-top: 55px;
    --t284pm-pp-pt-link-left: 229px;

    /* Logo knobs (optional) */
    --t284pm-pp-pt-logo-top: 12px;
    --t284pm-pp-pt-logo-right: 12px;
    --t284pm-pp-pt-logo-h: 32px;

    /* Section 1: “You can contact…” -> “Contact: …” gap */
    --t28xr-pp-pt-gap-s1-contactprompt-to-contact: 2px;

    /* ===== FOOTER knobs (independent) ===== */
    --t284pm-pp-pt-footer-bottom: 12px;          /* footer-only */
    --t284pm-pp-pt-footer-h: 22px;              /* footer-only */
    --t284pm-pp-pt-footer-fs: 12px;             /* footer-only */
    --t284pm-pp-pt-footer-offset-y: 0px;        /* + down / - up (footer only) */

    /* ===== wire into existing vars ===== */
    --pp-card-max-h: var(--t284pm-pp-pt-card-max-h);

    --pp-link-top: var(--t284pm-pp-pt-link-top);
    --pp-link-left: var(--t284pm-pp-pt-link-left);

    --pp-logo-top: var(--t284pm-pp-pt-logo-top);
    --pp-logo-right: var(--t284pm-pp-pt-logo-right);
    --pp-logo-h: var(--t284pm-pp-pt-logo-h);

    --pp-fs-footer: var(--t284pm-pp-pt-footer-fs);
    --pp-gap-card-footer: 0px;
  }

  @supports (height: 100dvh){
    :root{
      --t284pm-pp-pt-card-max-h: calc(100dvh - var(--t284pm-pp-pt-footer-reserve));
      --pp-card-max-h: var(--t284pm-pp-pt-card-max-h);
    }
  }

  /* true viewport centering (card only) */
  .page-shell{
    min-height: 100vh;
    height: 100vh;
    padding: 12px 12px 0;
    align-items: center;
    justify-content: center;
  }
  @supports (height: 100dvh){
    .page-shell{ min-height: 100dvh; height: 100dvh; }
  }

  .policy-card{
    margin: 0;
    transform: translateY(var(--t284pm-pp-pt-card-offset-y));
  }

  /* Section 1: make the “You can contact…” -> “Contact:” gap adjustable */
  .policy-card h2:first-of-type + p + p{
    margin-bottom: 0 !important; /* “You can contact us…” paragraph */
  }
  .policy-card p.policy-contact-line{
    margin-top: var(--t28xr-pp-pt-gap-s1-contactprompt-to-contact) !important;
  }

  /* footer fixed + independent */
  .site-footer{
    position: fixed;
    left: 0; right: 0;
    bottom: var(--t284pm-pp-pt-footer-bottom);
    height: var(--t284pm-pp-pt-footer-h);
    padding: 0;
    margin: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    line-height: 1;
    z-index: 5;
    transform: translateY(var(--t284pm-pp-pt-footer-offset-y));
  }
}

/* -------------------------------------------------------------------
  T28.5 — Samsung Galaxy S8+ (360×740), PORTRAIT — Privacy Policy (DE)
----------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 360px) and (height: 740px){

  :root{
    /* Close link knobs (optional, but safe to have) */
    --t285s8-pp-pt-link-top: 53px;
    --t285s8-pp-pt-link-left: 159px;

    /* Logo knobs */
    --t285s8-pp-pt-logo-top: 11px;        /* logo Y */
    --t285s8-pp-pt-logo-right: 12px;      /* logo X */
    --t285s8-pp-pt-logo-h: 31px;          /* logo size */

    /* Font sizes */
    --t285s8-pp-pt-fs-title: 21px;

    /* wire */
    --pp-link-top: var(--t285s8-pp-pt-link-top);
    --pp-link-left: var(--t285s8-pp-pt-link-left);
    --pp-fs-title: var(--t285s8-pp-pt-fs-title);

    --pp-logo-top: var(--t285s8-pp-pt-logo-top);
    --pp-logo-right: var(--t285s8-pp-pt-logo-right);
    --pp-logo-h: var(--t285s8-pp-pt-logo-h);
  }
}

/* ------------------------------------------------------------
  T28.1 — 375×667 PORTRAIT (short phones) — Privacy Policy (DE)
---------------------------------------------------------------*/
@media (orientation: portrait)
  and (min-width: 360px) and (max-width: 430px)
  and (max-height: 700px){

  :root{
    /* Card size */
    --pp-card-w: calc(100vw - 40px);
    --pp-card-max-h: calc(100dvh - 140px);

    --pp-card-pad-r: 12px;
    --pp-card-pad-l: 12px;
    --pp-card-pad-b: 0px;

    /* card + centering */
    --pp-pt-card-offset-y: 0px; 
    --t28pp-ls-card-offset-y: 0px;

    /* Font sizes */
    --pp-fs-title: 22px;
    --pp-fs-subtitle: 12px;
    --pp-fs-meta: 11px;
    --pp-fs-h2: 15px;
    --pp-fs-h3: 13px;
    --pp-fs-body: 11px;
    --pp-fs-link: 13px;
    --pp-fs-footer: 12px;

    --pp-lh-body: 1.40;

    /* Gaps */
    --pp-gap-top-title: -10px;
    --pp-gap-title-subtitle: 30px;
    --pp-gap-subtitle-meta: 5px;
    --pp-gap-meta-body: 12px;

    --pp-gap-s1-contactprompt-to-contact: 2px; 

    --pp-gap-p: 10px;
    --pp-gap-h2-top: 16px;
    --pp-gap-h2-bottom: 6px;
    --pp-gap-h3-top: 12px;
    --pp-gap-h3-bottom: 6px;

    /* Link + logo */
    --pp-link-top: 52px;
    --pp-link-left: 187px;

    --pp-logo-h: 30px;
    --pp-title-right-reserve: 110px;

    --pp-ls-footer-bottom: 13.5px; /* + moves footer up (use 6–14px typically) */
  }

  .policy-card{
    transform: translateY(var(--t28pp-ls-card-offset-y));
  }

    /* Section 1: "You can contact us..." -> "Contact: support@..." gap */
  .policy-card h2:first-of-type + p + p{
    margin-bottom: 0 !important; /* prompt paragraph */
  }

  .policy-card p.policy-contact-line{
    margin-top: var(--pp-gap-s1-contactprompt-to-contact) !important;
  }

  /* fixed footer — height adjustable without moving the card */
  .site-footer{
    position: fixed;
    left: 0;
    right: 0;
    bottom: var(--pp-ls-footer-bottom);
    padding: 0;                  /* no side padding */
    margin: 0;
    transform: none;

    display: flex;
    align-items: center;
    justify-content: center;

    line-height: 1;
    z-index: 5;
  }
}

/*----------------------------------------------------------------
  Extra-small phones (e.g. iPhone SE 320×568 – portrait only) (DE) 
  ----------------------------------------------------------------*/
     @media (max-width: 340px) {

  /* SE-specific header tuning */
  :root {
  --policy-logo-shift-x: 20px;
  --policy-logo-shift-y: 0px;

  --footer-raise: -28px;

  --policy-title-subtitle-gap: 28px;      /* gap between H1 and subtitle */
  --pp-fs-subtitle: 14px;              /* subtitle font size (adjustable) */
  --policy-body-line-height: 1.40;       /* line spacing in body text */
  --policy-paragraph-gap: 6px;           /* default vertical gap between paragraphs */
  --policy-h2-top-gap: 20px;             /* space above section headings (e.g. “2. Scope…”) */
  --policy-h2-bottom-gap: 3px;           /* space below h2 before the first paragraph */
  --policy-contact-gap-top: -7px;         /* gap between “You can contact us…” and “Contact: …” */
  --policy-shell-footer-offset: 140px;    /* matches common phone to ensure card stops before footer */

  --pp-pt-card-offset-y: -3px;         /* + down / - up (card only) */
  }

  /* White-striped background for small phones in portrait */
  .page-bg{
    background-color: #f7f9fc;
    background-image: repeating-linear-gradient(
      -45deg,
      #f7f9fc,
      #f7f9fc 18px,
      #e2e8f0 18px,
      #e2e8f0 20px
    );
    background-position: unset;
    background-size: unset;
    background-repeat: unset;
  }

  /* Slightly tighter padding around the card */
  .page-shell {
    padding: 20px 16px;
    min-height: calc(100vh - var(--policy-shell-footer-offset));
  }

  .policy-card {
    width: var(--pp-card-w);
    border-radius: 24px;
  /* Lock body for SE too */
  html, body {
    height: 100%;
    overflow: hidden;
  }

  /* Card: internal scrolling area */
  .policy-card {
    border-radius: 24px;
    padding: 20px 14px 18px;
    max-height: calc(100dvh - 120px) !important;
    transform: translateY(var(--pp-pt-card-offset-y));
    overflow-y: auto;
    overflow-x: hidden;    /* guard against sideways panning */
    margin: 0 auto;        /* center the white box vertically in flex area */
  }

   /* Style the scrollbar */
  .policy-card::-webkit-scrollbar {
    width: 6px; /* Scrollbar width */
    border-radius: 6px; /* Rounded scrollbar corners */
    }

    /* Base body text inside the card */
  .policy-card p,
  .policy-card li {
    font-size: 12px;
    line-height: var(--policy-body-line-height);  /* line spacing in body text */
    margin: 4px 0 8px;                            /* spacing between sentences / list items */
  }

  /* Sub-headings (like 5.1, 5.2, etc.) */
  .policy-card h3 {
    font-size: 13px;
    margin-top: 12px;       /* gap above subheading */
    margin-bottom: 4px;     /* gap below subheading */
  }

  /* Header/title area */
  .page-title {
    gap: 6px;
    font-size: 18px;
    margin: 0;
    white-space: nowrap;   /* keep "Privacy Policy" on a single line */
  }

  .subtitle {
    font-size: var(--pp-fs-subtitle);
    margin-top: 0px;
    margin-bottom: 4px;
  }

  .meta {
    font-size: 12px;
    margin-bottom: 10px;    /* controls space before the first h2 */
  }

  /* Base body text */
  body {
    font-size: 12px;       /* ~0.9rem */
  }

  /* Control gap between title and subtitle */
  .page-header {
        margin-bottom: var(--policy-title-subtitle-gap);
    }

  /* SE-only: control the gap before "Contact: support@example.test" */
  .policy-card p.policy-contact-line {
    margin-top: var(--policy-contact-gap-top);
    }

  .policy-card h2 {
    font-size: 15px;
    margin-top: var(--policy-h2-top-gap);
    margin-bottom: var(--policy-h2-bottom-gap);
    }

  /* "Close and return" – SE portrait override */
  .close-return-link{
    top: 36px;            /* vertical SE position – adjust here */
    font-size: 13px;
    padding: 0.5px 3px;
    margin-right: -6px;   /* horizontal SE offset – adjust here */
  }

 /* Footer size on iPhone SE portrait */
  .site-footer{
    font-size:12px;
    padding:0px 8px 10px;
  }

  /* SE-only logo size */
  .logo-inline {
    height: 26px;     /* adjust this px value to taste */
  }
}

/* ----------------------------------------------------
  Samsung Galaxy S9+ — Privacy Policy (EN) – LANDSCAPE
-------------------------------------------------------*/
@media (orientation: portrait) and (width: 320px) and (height: 658px){

/* card border rounding */
  .policy-card{
    border-radius: 24px !important;
    clip-path: inset(0 0 0 0 round 24px) !important;
  }
}

/*-------------------------------------------------------------------------------------
Privacy Policy (DE) – Split-screen / Short-height window (461–699px tall) – LANDSCAPE
---------------------------------------------------------------------------------------*/
@media (orientation: landscape)
  and (min-width: 700px) and (max-width: 1600px)
  and (min-height: 461px) and (max-height: 699px){

  html, body{
    height: 100%;
    overflow: hidden;
    overscroll-behavior: none;
  }

  :root{
    /* White card: size + geometry (all adjustable) */
    --pp-card-w: min(calc(100vw - 34px), 400px);
    --pp-card-max-h: calc(100vh - var(--pp-footer-reserve));
    --pp-card-radius: 24px;

    --pp-card-pad-t: 22px;
    --pp-card-pad-r: 22px;
    --pp-card-pad-b: 22px;
    --pp-card-pad-l: 22px;

    --pp-card-offset-y: 0px; /* + down / - up (card only) */

    /* Close & return: position + size */
    --pp-link-top: 44px;      /* Y */
    --pp-link-mr: -12px;        /* X (distance from right edge of card) */
    --pp-fs-link: 15px;

    /* Logo: size + position */
    --pp-logo-h: 34px;        /* height, width auto */
    --pp-logo-top: -2px;      /* Y inside card */
    --pp-logo-right: -152px;    /* X inside card */
    --pp-title-right-reserve: 50px;

    /* Header spacing (all adjustable) */
    --pp-gap-top-title: 0px;        /* card top -> header */
    --pp-gap-title-subtitle: 30px;   /* header -> subtitle */

    /* Subtitle + meta spacing (all adjustable) */
    --pp-gap-subtitle-meta: 6px;     /* subtitle -> meta (“updated…”) */
    --pp-gap-meta-body: 18px;        /* meta -> first h2 */

    /* Headings + paragraph spacing */
    --pp-gap-h2-top: 20px;
    --pp-gap-h2-bottom: 8px;
    --pp-gap-h3-top: 16px;
    --pp-gap-h3-bottom: 6px;

    --pp-gap-p: 14px; /* paragraph spacing */

    /* Space around card (page-shell) */
    --pp-shell-pad-tb: 38px;        /* ~10mm */
    --pp-shell-pad-lr: 38px;

    /* Line spacing */
    --pp-lh-body: 1.55;

    /* Font sizes (all individually adjustable) */
    --pp-fs-title: 28px;
    --pp-fs-subtitle: 14px;
    --pp-fs-meta: 12px;
    --pp-fs-h2: 20px;
    --pp-fs-h3: 18px;
    --pp-fs-body: 13px;

    /* Footer: size + distance to bottom (independent from card) */
    --pp-fs-footer: 14px;
    --pp-footer-bottom: 17px;    /* distance to viewport bottom */
    --pp-footer-reserve: 130px;  /* reserve space so card never clashes */
    --pp-shell-footer-offset: 50px;

    /* Section 1 “Contact:” gap knob (keep both names safe) */
    --pp-pt-gap-s1-contactprompt-to-contact: 3px;
    --t28x-pp-pt-gap-s1-contactprompt-to-contact: var(--pp-pt-gap-s1-contactprompt-to-contact);
  }

  @supports (height: 100dvh){
    :root{
      --pp-card-max-h: calc(100dvh - var(--pp-footer-reserve));
    }
  }

  .page-shell{
    padding: var(--pp-shell-pad-tb) var(--pp-shell-pad-lr) !important;
  }

  .policy-card{
    width: var(--pp-card-w) !important;
    max-height: none !important;
    border-radius: var(--pp-card-radius)!important;
    padding: var(--pp-card-pad-t) var(--pp-card-pad-r) var(--pp-card-pad-b) var(--pp-card-pad-l) !important;
    transform: translateY(var(--pp-card-offset-y));
    overflow: visible !important;
  }

  .close-return-link{
    top: var(--pp-link-top);
    margin-right: var(--pp-link-mr);
    font-size: var(--pp-fs-link);
  }

  .page-header{
    margin-top: var(--pp-gap-top-title);
    margin-bottom: var(--pp-gap-title-subtitle);
    position: relative;
  }

  .page-title{
    font-size: var(--pp-fs-title);
    padding-right: var(--pp-title-right-reserve);
    white-space: normal;
  }

  .logo-inline{
    position: absolute;
    top: var(--pp-logo-top);
    right: var(--pp-logo-right);
    height: var(--pp-logo-h);
    width: auto;
    transform: none;
    pointer-events: none;
  }

  .subtitle{
    font-size: var(--pp-fs-subtitle);
    margin: 0 0 var(--pp-gap-subtitle-meta) 0;
  }

  .meta{
    font-size: var(--pp-fs-meta);
    margin: 0 0 var(--pp-gap-meta-body) 0;
  }

  h2{
    font-size: var(--pp-fs-h2);
    margin: var(--pp-gap-h2-top) 0 var(--pp-gap-h2-bottom) 0;
  }

  h3{
    font-size: var(--pp-fs-h3);
    margin: var(--pp-gap-h3-top) 0 var(--pp-gap-h3-bottom) 0;
  }

  p, li{
    font-size: var(--pp-fs-body);
    line-height: var(--pp-lh-body);
  }

  p{ margin: 0 0 var(--pp-gap-p) 0; }

  ul, ol{
    margin: 0 0 var(--pp-gap-p) 22px;
    padding: 0;
  }

  .site-footer{
    position: fixed;
    left: 0; right: 0;
    bottom: var(--pp-footer-bottom);

    font-size: var(--pp-fs-footer);
    padding: 0 16px;
    margin: 0;

    transform: none;
    line-height: 1.1;
    z-index: 5;

    pointer-events: none; /* prevents “drag feel” on footer */
  }
}

/* -------------------------------------------------------------------------
    CH9 — Foldable / split-screen (720×557), LANDSCAPE — Privacy Policy (DE)
    Footnote Y adjustable only (white card must NOT move)
  --------------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 720px) and (height: 557px){
  :root{
    --ch9f720-ls-pp-footer-bottom: 8.5px; /* bigger value pushes footer UP */
    --pp-footer-bottom: var(--ch9f720-ls-pp-footer-bottom);
  }
}

/* --------------------------------------------------------------------------------
  T26.4 — Phone group iPhone 12/13/14 (844×390) & Pixel 5 (851×393), LANDSCAPE (DE)
  ---------------------------------------------------------------------------------*/
@media (orientation: landscape)
  and (min-width: 640px) and (max-width: 1024px)
  and (min-height: 361px) and (max-height: 460px) {

  html, body{
    height: 100%;
    overflow: hidden;
    overscroll-behavior: none;
  }

  /* prevent background flicker on EN/DE soft swap */
  html.demo-swap-freeze{ contain: none !important; }

  :root{
    /* Card size + padding (adjustable) */
    --pp-card-w: min(calc(100vw - 20px), 750px);
    --pp-card-max-h: 75vh;
    --pp-card-radius: 24px;
    --pp-card-pad-t: 14px;
    --pp-card-pad-r: 14px;
    --pp-card-pad-b: 14px;
    --pp-card-pad-l: 14px;
    --pp-pt-card-offset-y: 20px;         /* + down / - up (card only) */


    /* Fonts (adjustable) */
    --pp-fs-title: 23px;
    --pp-fs-subtitle: 13px;
    --pp-fs-meta: 12px;
    --pp-fs-h2: 16px;
    --pp-fs-h3: 14px;
    --pp-fs-body: 12px;
    --pp-fs-link: 14px;
    --pp-fs-footer: 12px;

    --pp-lh-body: 1.35;

    /* Gaps (adjustable) */
    --pp-gap-title-subtitle: 12px;
    --pp-gap-subtitle-meta: 6px;
    --pp-gap-meta-body: 10px;

    --pp-pt-gap-s1-contactprompt-to-contact: 2px; /* “You can contact…” -> “Contact: …” */

    --pp-gap-p: 12px;
    --pp-gap-h2-top: 18px;
    --pp-gap-h2-bottom: 6px;
    --pp-gap-h3-top: 14px;
    --pp-gap-h3-bottom: 6px;

    /* Close link (adjustable) */
    --pp-link-top: 39px;
    --pp-link-mr: -2px;

    /* Logo (adjustable) */
    --pp-logo-h: 32px;
    --pp-logo-top: -6px;
    --pp-logo-right: -140px;
    --pp-title-right-reserve: 92px;

    /* Footer */
    --footer-raise: 19px;
    --pp-footer-pad-b: 8px;
  }

  .page-shell{
    padding: 12px 10px 0;
    min-height: calc(100vh - 56px);
  }

  .policy-card{
    width: var(--pp-card-w);
    max-width: none;

    max-height: var(--pp-card-max-h);
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;

    border-radius: var(--pp-card-radius);
    padding: var(--pp-card-pad-t) var(--pp-card-pad-r) var(--pp-card-pad-b) var(--pp-card-pad-l);
    transform: translateY(var(--pp-pt-card-offset-y));
    clip-path: inset(0 0 0 0 round var(--pp-card-radius));
    margin-bottom: 8px;
  }

   /* Style the scrollbar */
  .policy-card::-webkit-scrollbar {
    width: 6px; /* Scrollbar width */
    border-radius: 6px; /* Rounded scrollbar corners */
    }

  .close-return-link{
    top: var(--pp-link-top);
    font-size: var(--pp-fs-link);
    margin-right: var(--pp-link-mr);
  }

  .page-header{
    margin: 0 0 var(--pp-gap-title-subtitle) 0;
    position: relative;
  }

  /* IMPORTANT: target the real title class (wins over generic h1) */
  .page-title{
    font-size: var(--pp-fs-title);
    padding-right: var(--pp-title-right-reserve);
    white-space: normal;
  }

  /* Disable desktop translate and pin logo cleanly */
  .logo-inline{
    position: absolute;
    top: var(--pp-logo-top);
    right: var(--pp-logo-right);
    height: var(--pp-logo-h);
    width: auto;
    transform: none;
    pointer-events: none;
  }

  .policy-card p.meta{
    font-size: var(--pp-fs-meta);
    margin: 0 0 var(--pp-gap-meta-body) 0;
  }

  .policy-card p.subtitle{
    font-size: var(--pp-fs-subtitle);
    margin: 0 0 var(--pp-gap-subtitle-meta) 0;
  }

  .policy-card h2{
    font-size: var(--pp-fs-h2);
    margin: var(--pp-gap-h2-top) 0 var(--pp-gap-h2-bottom) 0;
  }

  .policy-card h3{
    font-size: var(--pp-fs-h3);
    margin: var(--pp-gap-h3-top) 0 var(--pp-gap-h3-bottom) 0;
  }

  .policy-card p,
  .policy-card li{
    font-size: var(--pp-fs-body);
    line-height: var(--pp-lh-body);
  }

  .policy-card p{
    margin: 0 0 var(--pp-gap-p) 0;
  }

  .policy-card ul{
    margin: 0 0 var(--pp-gap-p) 22px;
    padding: 0;
  }

  /* Section 1: make the contact gap adjustable */
  .policy-card h2:first-of-type + p + p{
    margin-bottom: 0 !important; /* “You can contact us…” paragraph */
  }
  .policy-card p.policy-contact-line{
    margin-top: var(--t28x-pp-pt-gap-s1-contactprompt-to-contact) !important;
  }

  .site-footer{
    font-size: var(--pp-fs-footer);
    padding: 4px 8px var(--pp-footer-pad-b);
    transform: translateY(var(--footer-raise));
  }
}

/* -----------------------------------------------------------
  T28.2 — iPhone X (812×375), LANDSCAPE — Privacy Policy (DE)
--------------------------------------------------------------*/
@media (orientation: landscape)
  and (min-width: 800px) and (max-width: 830px)
  and (max-height: 380px){

  :root{
    /* ===== knobs ===== */
    --t28x-pp-ls-card-offset-y: -6px;     /* + down / - up (card only) */

    --t28x-pp-ls-footer-bottom: 5.5px;   /* footer only */
    --t28x-pp-ls-footer-h: 22px;        /* footer only */
    --t28x-pp-ls-footer-fs: 12px;       /* footer only */
    --t28x-pp-ls-footer-reserve: 95px;  /* reserved space so card never clashes */

    --t28x-pp-ls-gap-s1-contactprompt-to-contact: 2px;

    /* wire */
    --pp-fs-footer: var(--t28x-pp-ls-footer-fs);
    --pp-card-max-h: calc(100vh - var(--t28x-pp-ls-footer-reserve));
    --pp-gap-card-footer: 0px;
  }
  @supports (height: 100dvh){
    :root{
      --pp-card-max-h: calc(100dvh - var(--t28x-pp-ls-footer-reserve));
    }
  }

  /* true viewport centering */
  .page-shell{
    height: 100vh;
    min-height: 100vh;
    padding: 10px 12px 0;
    align-items: center;
    justify-content: center;
  }
  @supports (height: 100dvh){
    .page-shell{ height: 100dvh; min-height: 100dvh; }
  }

  .policy-card{
    transform: translateY(var(--t28x-pp-ls-card-offset-y));
  }

  /* footer fixed + independent */
  .site-footer{
    position: fixed;
    left: 0; right: 0;
    bottom: var(--t28x-pp-ls-footer-bottom);
    height: var(--t28x-pp-ls-footer-h);
    padding: 0;
    margin: 0;
    transform: none;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    z-index: 5;
  }

  /* Section 1: contact gap knob */
  .policy-card h2:first-of-type + p + p{
    margin-bottom: 0 !important;
  }
  .policy-card p.policy-contact-line{
    margin-top: var(--t28x-pp-ls-gap-s1-contactprompt-to-contact) !important;
  }
}

/* ------------------------------------------------------------
  T28.3 — iPhone XR (896×414), LANDSCAPE — Privacy Policy (DE)
---------------------------------------------------------------*/
@media (orientation: landscape) and (width: 896px) and (height: 414px){

  :root{
    /* ===== knobs ===== */
    --t28xr-pp-ls-card-offset-y: -6px;        /* + down / - up (card only) */

    --t28xr-pp-ls-footer-bottom: 7px;       /* footer-only */
    --t28xr-pp-ls-footer-h: 22px;           /* footer-only */
    --t28xr-pp-ls-footer-fs: 12px;          /* footer-only */
    --t28xr-pp-ls-footer-reserve: 92px;     /* reserve space for card max-height */

    /* Section 1: “You can contact…” -> “Contact: …” gap */
    --t28xr-pp-pt-gap-s1-contactprompt-to-contact: 2px;

    /* Wire into existing policy vars */
    --pp-fs-footer: var(--t28xr-pp-ls-footer-fs);
    --pp-card-max-h: calc(100vh - var(--t28xr-pp-ls-footer-reserve));
  }

  @supports (height: 100dvh){
    :root{
      --pp-card-max-h: calc(100dvh - var(--t28xr-pp-ls-footer-reserve));
    }
  }

  /* Make centering deterministic */
  .page-shell{
    height: 100vh;
    padding: 10px 10px 0;
    display: grid;
    place-items: center;
  }
  @supports (height: 100dvh){
    .page-shell{ height: 100dvh; }
  }

  .policy-card{
    transform: translateY(var(--t28xr-pp-ls-card-offset-y));
  }

  /* Section 1: make the “You can contact…” -> “Contact:” gap adjustable */
  .policy-card h2:first-of-type + p + p{
    margin-bottom: 0 !important; /* “You can contact us…” paragraph */
  }
  .policy-card p.policy-contact-line{
    margin-top: var(--t28xr-pp-pt-gap-s1-contactprompt-to-contact) !important;
  }

  /* fixed footer (independent) */
  .site-footer{
    position: fixed;
    left: 0; right: 0;
    bottom: var(--t28xr-pp-ls-footer-bottom);
    height: var(--t28xr-pp-ls-footer-h);
    padding: 0;
    margin: 0;
    transform: none;

    display: flex;
    align-items: center;
    justify-content: center;

    line-height: 1;
    z-index: 5;
  }
}

/* -------------------------------------------------------------------
  T28.4 — iPhone 14 Pro Max (932×430), LANDSCAPE — Privacy Policy (DE)
----------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 932px) and (height: 430px){

  :root{
    /* ===== CARD knobs (independent) ===== */
    --t284pm-pp-ls-card-offset-y: -10px;        /* + down / - up (card only) */
    --t284pm-pp-ls-footer-reserve: 92px;      /* card-only: controls max height */
    --t284pm-pp-ls-card-max-h: calc(100vh - var(--t284pm-pp-ls-footer-reserve));

    /* optional: close-link knobs (safe) */
    --t284pm-pp-ls-link-top: 45px;
    --t284pm-pp-ls-link-left: 750px;

    /* optional: logo knobs (safe) */
    --t284pm-pp-ls-logo-top: -4px;
    --t284pm-pp-ls-logo-right: -140px;
    --t284pm-pp-ls-logo-h: 30px;

    --pp-gap-s1-contactprompt-to-contact: 2px; 

    /* ===== FOOTER knobs (independent) ===== */
    --t284pm-pp-ls-footer-bottom: 7.5px;        /* footer-only */
    --t284pm-pp-ls-footer-h: 22px;            /* footer-only */
    --t284pm-pp-ls-footer-fs: 12px;           /* footer-only */
    --t284pm-pp-ls-footer-offset-y: 2.5px;      /* + down / - up (footer only) */

    /* wire into existing vars */
    --pp-card-max-h: var(--t284pm-pp-ls-card-max-h);

    --pp-link-top: var(--t284pm-pp-ls-link-top);
    --pp-link-left: var(--t284pm-pp-ls-link-left);

    --pp-logo-top: var(--t284pm-pp-ls-logo-top);
    --pp-logo-right: var(--t284pm-pp-ls-logo-right);
    --pp-logo-h: var(--t284pm-pp-ls-logo-h);

    --pp-fs-footer: var(--t284pm-pp-ls-footer-fs);
    --pp-gap-card-footer: 0px;
  }

  @supports (height: 100dvh){
    :root{
      --t284pm-pp-ls-card-max-h: calc(100dvh - var(--t284pm-pp-ls-footer-reserve));
      --pp-card-max-h: var(--t284pm-pp-ls-card-max-h);
    }
  }

  /* True viewport centering (card only) */
  .page-shell{
    min-height: 100vh;
    height: 100vh;
    padding: 10px 12px 0;
    align-items: center;
    justify-content: center;
  }
  @supports (height: 100dvh){
    .page-shell{ min-height: 100dvh; height: 100dvh; }
  }

  .policy-card{
    margin: 0;
    transform: translateY(var(--t284pm-pp-ls-card-offset-y));
  }

  /* Section 1: "You can contact us..." -> "Contact: support@..." gap */
  .policy-card h2:first-of-type + p + p{
    margin-bottom: 0 !important; /* prompt paragraph */
  }

  .policy-card p.policy-contact-line{
    margin-top: var(--pp-gap-s1-contactprompt-to-contact) !important;
  }

  /* Fixed footer (independent) */
  .site-footer{
    position: fixed;
    left: 0; right: 0;
    bottom: var(--t284pm-pp-ls-footer-bottom);
    height: var(--t284pm-pp-ls-footer-h);
    padding: 0;
    margin: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    line-height: 1;
    z-index: 5;
    transform: translateY(var(--t284pm-pp-ls-footer-offset-y));
  }
}

/* --------------------------------------------------------------------------
  T28.1 — 667×375 LANDSCAPE (medium landscape phones) — Privacy Policy (DE)
-----------------------------------------------------------------------------*/
@media (orientation: landscape)
  and (min-width: 640px) and (max-width: 799px)
  and (min-height: 361px) and (max-height: 430px){

  html, body{
    height: 100%;
    overflow: hidden;
    overscroll-behavior: none;
  }

  :root{
    /* footer (decoupled) */
    --t28pp-ls-footer-h: 26px;        /* adjust freely */
    --t28pp-ls-footer-space: 22px;    /* reserved for centering */
    --t28pp-ls-footer-fs: 12px;

    /* card + centering */
    --t28pp-ls-card-offset-y: -1px;    /* + down / - up */

    /* card sizing */
    --pp-card-w: min(calc(100vw - 20px), 650px);
    --pp-card-max-h: calc(100vh - var(--t28pp-ls-footer-space) - 75px);
    --pp-card-radius: 24px;
    --pp-card-pad-t: 12px;
    --pp-card-pad-r: 14px;
    --pp-card-pad-b: 14px;
    --pp-card-pad-l: 14px;

    /* fonts */
    --pp-fs-title: 22px;
    --pp-fs-subtitle: 12px;
    --pp-fs-meta: 11px;
    --pp-fs-h2: 15px;
    --pp-fs-h3: 13px;
    --pp-fs-body: 11px;
    --pp-fs-link: 13px;

    --pp-lh-body: 1.40;

    /* gaps */
    --pp-gap-title-subtitle: 10px;
    --pp-gap-subtitle-meta: 5px;
    --pp-gap-meta-body: 12px;

    --pp-gap-p: 10px;
    --pp-gap-h2-top: 16px;
    --pp-gap-h2-bottom: 6px;
    --pp-gap-h3-top: 12px;
    --pp-gap-h3-bottom: 6px;

    --pp-gap-s1-contactprompt-to-contact: 2px; 

    /* close link + logo */
    --pp-link-top: 41px;
    --pp-link-mr: -9px;

    --pp-logo-h: 28px;
    --pp-logo-top: -2px;
    --pp-logo-right: -130px;
    --pp-title-right-reserve: 96px;

    --t28pp-ls-footer-bottom: 3px; /* + moves footer up (use 6–14px typically) */
  }

  .page-shell{
    padding: 12px 10px 0;
    min-height: 0;
    height: calc(100vh - var(--t28pp-ls-footer-space));
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .policy-card{
    width: var(--pp-card-w);
    max-width: none;

    max-height: var(--pp-card-max-h);
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;

    border-radius: var(--pp-card-radius);
    padding: var(--pp-card-pad-t) var(--pp-card-pad-r) var(--pp-card-pad-b) var(--pp-card-pad-l);
    clip-path: inset(0 0 0 0 round var(--pp-card-radius));
    margin: 0;
    transform: translateY(var(--t28pp-ls-card-offset-y));
  }

  .close-return-link{
    top: var(--pp-link-top);
    font-size: var(--pp-fs-link);
    margin-right: var(--pp-link-mr);
  }

  .page-header{
    margin: 0 0 var(--pp-gap-title-subtitle) 0;
    position: relative;
  }

  .page-title{
    font-size: var(--pp-fs-title);
    padding-right: var(--pp-title-right-reserve);
    white-space: normal;
  }

  .logo-inline{
    position: absolute;
    top: var(--pp-logo-top);
    right: var(--pp-logo-right);
    height: var(--pp-logo-h);
    width: auto;
    transform: none;
    pointer-events: none;
  }

  .policy-card p.subtitle{
    font-size: var(--pp-fs-subtitle);
    margin: 0 0 var(--pp-gap-subtitle-meta) 0;
  }

  .policy-card p.meta{
    font-size: var(--pp-fs-meta);
    margin: 0 0 var(--pp-gap-meta-body) 0;
  }

  .policy-card h2{
    font-size: var(--pp-fs-h2);
    margin: var(--pp-gap-h2-top) 0 var(--pp-gap-h2-bottom) 0;
  }

  .policy-card h3{
    font-size: var(--pp-fs-h3);
    margin: var(--pp-gap-h3-top) 0 var(--pp-gap-h3-bottom) 0;
  }

  .policy-card p,
  .policy-card li{
    font-size: var(--pp-fs-body);
    line-height: var(--pp-lh-body);
  }

  .policy-card p{ margin: 0 0 var(--pp-gap-p) 0; }
  .policy-card ul{ margin: 0 0 var(--pp-gap-p) 22px; padding: 0; }

   /* Section 1: "You can contact us..." -> "Contact: support@..." gap */
  .policy-card h2:first-of-type + p + p{
    margin-bottom: 0 !important; /* prompt paragraph */
  }

  .policy-card p.policy-contact-line{
    margin-top: var(--pp-gap-s1-contactprompt-to-contact) !important;
  }

  /* fixed footer — height adjustable without moving the card */
  .site-footer{
    position: fixed;
    left: 0;
    right: 0;
    bottom: var(--t28pp-ls-footer-bottom);
    height: var(--t28pp-ls-footer-h);
    padding: 0;                  /* no side padding */
    margin: 0;
    transform: none;

    display: flex;
    align-items: center;
    justify-content: center;

    line-height: 1;
    z-index: 5;
  }
}

/* --------------------------------------------------------------------
  T28.5 — Samsung Galaxy S8+ (740×360), LANDSCAPE — Privacy Policy (DE)
-----------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 740px) and (height: 360px){

  :root{
    --t285s8-pp-ls-logo-shift-x: 279px; /* + right / - left */
  }

  .logo-inline{
    transform: translate(var(--t285s8-pp-ls-logo-shift-x), var(--policy-logo-shift-y)) !important;
  }
}

/*---------------------------------------------------------
  iPhone SE (568×320), LANDSCAPE – Full Privacy Policy (DE)
  ---------------------------------------------------------*/
@media (max-width: 950px) and (max-height: 360px) and (orientation: landscape){

  :root{
    --footer-raise: 0px;
    --policy-title-subtitle-gap: 28px;      /* gap between H1 and subtitle */
    --policy-contact-gap-top: -7px;         /* gap between “You can contact us…” and “Contact: …” */

    --pp-card-max-h: 75vh;
    --pp-pt-card-offset-y: 8px;         /* + down / - up (card only) */

    /* kill desktop logo push that can create sideways panning */
    --policy-logo-shift-x: 245px;
    --policy-logo-shift-y: 0px;
    --policy-logo-height: 26px;
  }

  /* no outer scrolling */
  html, body{
    height: 100%;
    overflow: hidden;
    overscroll-behavior: none;
  }

  /* Make footer visible (no outer scroll, but footer stays on-screen) */
  body{
    display: flex;
    flex-direction: column;
  }

  /* centered content area with equal edge spacing */
  .page-shell{
    flex: 1 1 auto;
    min-height: 0; /* critical in locked viewport */
    padding: 16px 12px 0;
    align-items: center;
    justify-content: center;
  }

  /* card: only scroll area + no sideways panning */
  .policy-card{
    max-width: none;
    width: min(calc(100vw - 20px), 545px);
    border-radius: 24px;
    clip-path: inset(0 0 0 0 round 24px);

    transform: translateY(var(--pp-pt-card-offset-y));

    padding: 12px 14px 14px;
    padding-right: 12px;

    max-height: var(--pp-card-max-h);
    overflow-y: auto;
    overflow-x: hidden;

    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 8px;
  }

  /* Style the scrollbar */
  .policy-card::-webkit-scrollbar{
    width: 6px;
    border-radius: 6px;
  }

  /* header + text sizing for 320px height */
  .page-title{
    font-size: 20px;
    gap: 6px;
    white-space: nowrap;
  }

  /* Control gap between title and subtitle */
  .page-header {
    margin-bottom: var(--policy-title-subtitle-gap);
  }

  /* SE-only: control the gap before "Contact: support@example.test" */
  .policy-card p.policy-contact-line {
    margin-top: var(--policy-contact-gap-top);
    }

  .policy-card p,
  .policy-card li{
    font-size: 11px;
    line-height: 1.30;
    margin: 4px 0 8px;
  }

  .policy-card h2{
    font-size: 15px;
    margin-top: 14px;
    margin-bottom: 4px;
  }

  .policy-card h3{
    font-size: 13px;
    margin-top: 10px;
    margin-bottom: 4px;
  }

  /* Fix: subtitle/meta */
  .policy-card p.subtitle{
    font-size: 13px;
    margin: 4px 0 4px;
  }

  .policy-card p.meta{
    font-size: 12px;
    margin: 0 0 10px 0;
  }

  /* "Close and return" – usable in landscape */
  .close-return-link{
    top: 36px;
    font-size: 13px;
    padding: 0.5px 3px;
    margin-right: -6px;
  }

  /* footer readable and stable */
  .site-footer{
    flex: 0 0 auto;
    margin: 0;
    position: relative;
    z-index: 1;

    transform: none;
    font-size: 12px;
    padding: 4px 8px 6px;
  }
}

/* --------------------------------------------------------------------
  Samsung Galaxy S9+ (658×320), LANDSCAPE — Privacy Policy (DE)
  Fix: override SE-landscape logo shift for this device only
-----------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 658px) and (height: 320px){
  :root{
    --policy-logo-shift-x: 279px;  /* puts logo back next to title (no far-right jump) */
  }

  /* card border rounding */
  .policy-card{
    border-radius: 24px;
  }
}

  </style>
</head>
<body>
  <div class="page-bg"></div>

  <div class="page-shell">
    <main class="policy-card">
     <header class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">
      Datenschutzerklärung
      <img src="../assets/img/logo.svg" alt="DemoBrand Logo" class="logo-inline">
    </h1>
  </div>
</header>

      <p class="subtitle">Wie wir Ihre personenbezogenen Daten erheben, verwenden und schützen.</p>
      <p class="meta">
      aktualisiert:
      <?= htmlspecialchars($privacyLastUpdated, ENT_QUOTES, 'UTF-8') ?>
    </p>

      <h2>1. Wer wir sind</h2>
      <p>
        Diese Webseite und die zugehörigen Online-Dienste (zusammen die „Dienste“) werden unter der Marke DemoBrand von
        <strong>DemoBrand</strong> betrieben („DemoBrand“, „wir“, „uns“, „unser“).
      </p>
      <p>
        Sie können uns zu Datenschutzthemen wie folgt kontaktieren:
      </p>
      <p class="policy-contact-line">
        <strong>Kontakt:</strong> <a href="mailto:support@example.test">support@example.test</a>
      </p>
      <p>
        Wenn Sie im EWR oder im Vereinigten Königreich ansässig sind, sind wir für die in dieser Erklärung beschriebene Verarbeitung
        Ihrer personenbezogenen Daten verantwortlich (Verantwortlicher im Sinne der DSGVO bzw. UK GDPR).
      </p>

      <h2>2. Geltungsbereich dieser Datenschutzerklärung</h2>
      <p>
        Diese Datenschutzerklärung erläutert, wie wir personenbezogene Daten erheben, verwenden, weitergeben und schützen, wenn Sie:
      </p>
      <ul>
        <li>unsere Websites, einschließlich <strong>example.test</strong> und zugehörige Subdomains, besuchen,</li>
        <li>unsere Landingpages für Aktivitäten wie Rätsel-Challenges und Zertifikate nutzen,</li>
        <li>unsere E-Mails oder andere Marketingkommunikation abonnieren,</li>
        <li>uns kontaktieren oder anderweitig online mit uns interagieren.</li>
      </ul>
      <p>
        Diese Erklärung gilt nicht für Dienste Dritter, die eigene Datenschutzerklärungen haben (z.&nbsp;B. Amazon oder andere Plattformen,
        über die Sie unsere Bücher erwerben).
      </p>

      <h2>3. Welche personenbezogenen Daten wir erheben</h2>
      <p>Wir können insbesondere folgende Kategorien personenbezogener Daten erheben:</p>
      <ul>
        <li>
          <strong>Identitätsdaten:</strong> Vorname, Nachname, Anzeigename oder Benutzername (falls verwendet).
        </li>
        <li>
          <strong>Kontaktdaten:</strong> E-Mail-Adresse und sonstige Kontaktdaten, die Sie bereitstellen.
        </li>
        <li>
          <strong>Aktivitäts- und Zertifikatsdaten:</strong> Geheimcodes oder ähnliche Kennungen, mit denen bestätigt wird,
          dass Sie eine bestimmte Aktivität, ein Rätsel oder eine Challenge erfolgreich abgeschlossen haben; Informationen
          dazu, zu welcher Challenge oder welchem Buch die Aktivität gehört; Zertifikats-IDs und Zeitstempel.
        </li>
        <li>
          <strong>Kommunikationsdaten:</strong> Inhalte von Nachrichten, die Sie uns senden (z.&nbsp;B. Support-Anfragen, Feedback).
        </li>
        <li>
          <strong>Technische Daten und Nutzungsdaten:</strong> IP-Adresse, Browsertyp, Geräteinformationen, aufgerufene Seiten,
          Datum und Uhrzeit von Besuchen sowie verweisende URLs, die über Server-Logs und ähnliche Technologien erfasst werden.
        </li>
        <li>
          <strong>Marketing-Präferenzen:</strong> Ihre Entscheidungen in Bezug auf den Erhalt von Marketing-E-Mails oder Newslettern
          (Opt-in / Opt-out).
        </li>
      </ul>
      <p>
        Wir erfassen nicht bewusst besondere Kategorien personenbezogener Daten (z.&nbsp;B. Gesundheitsdaten oder Informationen
        über Ihre Religion, politischen Meinungen oder Ähnliches).
      </p>

      <h2>4. Wie wir personenbezogene Daten erheben</h2>
      <p>Wir erheben personenbezogene Daten auf drei Hauptwegen:</p>
      <p><strong>(a) Direkt von Ihnen, wenn Sie:</strong></p>
      <ul>
        <li>Formulare auf unseren Landingpages ausfüllen (z.&nbsp;B. zur Anforderung eines Zertifikats oder von Bonus-Inhalten),</li>
        <li>unseren Newsletter oder Updates abonnieren,</li>
        <li>uns per E-Mail oder über andere Kanäle kontaktieren.</li>
      </ul>
      <p><strong>(b) Automatisch, wenn Sie:</strong></p>
      <ul>
        <li>
          unsere Websites oder Landingpages besuchen; bestimmte technische Daten und Nutzungsdaten werden dabei über Cookies,
          Server-Logs und ähnliche Technologien erfasst.
        </li>
      </ul>
      <p><strong>(c) Von Dritten, in begrenzten Fällen, zum Beispiel:</strong></p>
      <ul>
        <li>
          von Analyse- oder Anti-Spam-Tools, die uns helfen zu verstehen, wie unsere Website genutzt wird, und sie vor Missbrauch zu schützen.
        </li>
      </ul>

      <h2>5. Wie wir personenbezogene Daten verwenden und Rechtsgrundlagen</h2>
      <p>Wir verwenden personenbezogene Daten zu folgenden Zwecken:</p>

      <h3>5.1 Bereitstellung der Dienste und Erfüllung Ihrer Anfragen</h3>
      <ul>
        <li>Überprüfung Ihres erfolgreichen Abschlusses von Rätsel-Aktivitäten oder Challenges,</li>
        <li>Erstellung und Bereitstellung digitaler Zertifikate,</li>
        <li>Bereitstellung von Bonus-Inhalten oder Downloads, die Sie angefordert haben,</li>
        <li>Beantwortung Ihrer Anfragen und Support-Tickets.</li>
      </ul>
      <p>
        Für Personen im EWR und im Vereinigten Königreich erfolgt diese Verarbeitung in der Regel auf Grundlage von
        <strong>Art. 6 Abs. 1 lit. b DSGVO / UK GDPR (Vertragserfüllung)</strong> oder von
        <strong>Art. 6 Abs. 1 lit. f DSGVO / UK GDPR</strong>, wenn wir uns auf unsere berechtigten Interessen stützen (z.&nbsp;B. Verhinderung von
        Missbrauch des Zertifikat-Systems).
      </p>

      <h3>5.2 Versand von Marketing-Kommunikation (mit Ihrer Einwilligung oder wie gesetzlich zulässig)</h3>
      <ul>
        <li>
          Versand von E-Mails zu neuen Büchern, Rätseln, Produkten, Aktionen, Gewinnspielen oder Neuigkeiten rund um DemoBrand,
        </li>
        <li>Ausrichtung der Kommunikation auf Ihre Interessen, soweit möglich.</li>
      </ul>
      <p>
        Wir versenden Marketing-E-Mails nur, wenn dies nach geltendem Recht zulässig ist. Sie können den Erhalt jederzeit über den
        Abmeldelink in unseren E-Mails oder per E-Mail an
        <a href="mailto:support@example.test">support@example.test</a> beenden.
      </p>

      <h3>5.3 Betrieb, Wartung und Verbesserung unserer Dienste</h3>
      <ul>
        <li>Überwachung der Leistung und Nutzung unserer Websites,</li>
        <li>Erkennung und Verhinderung von Betrug oder Missbrauch,</li>
        <li>Verbesserung von Inhalten, Funktionen und Nutzererlebnis.</li>
      </ul>
      <p>
        Für Besucher aus dem EWR/UK erfolgt dies in der Regel auf Grundlage unserer berechtigten Interessen am Betrieb eines sicheren
        und nützlichen Dienstes.
      </p>

      <h3>5.4 Erfüllung rechtlicher Verpflichtungen</h3>
      <ul>
        <li>Führung von Aufzeichnungen, zu deren Aufbewahrung wir gesetzlich verpflichtet sind,</li>
        <li>Beantwortung rechtmäßiger Anfragen von Aufsichtsbehörden oder Strafverfolgungsbehörden, soweit anwendbar.</li>
      </ul>

      <h2>6. Cookies und ähnliche Technologien</h2>
      <p>
        Wir können Cookies und ähnliche Technologien (z.&nbsp;B. Local Storage oder Pixel) einsetzen, um:
      </p>
      <ul>
        <li>Ihre Spracheinstellungen zu speichern,</li>
        <li>Sitzungen zu sichern,</li>
        <li>zu verstehen, wie Besucher unsere Websites nutzen (z.&nbsp;B. in Form aggregierter Statistiken).</li>
      </ul>
      <p>
        Soweit nach geltendem Recht erforderlich (z.&nbsp;B. im EWR und im Vereinigten Königreich für nicht essenzielle Cookies),
        holen wir Ihre Einwilligung ein, bevor wir nicht essenzielle Cookies setzen. Sie können Ihre Cookie-Einstellungen in Ihrem
        Browser und – soweit von uns bereitgestellt – über unser Cookie-Banner oder eine Einstellungsoberfläche anpassen.
      </p>

      <h2>7. Wann wir personenbezogene Daten weitergeben</h2>
      <p>Wir verkaufen Ihre personenbezogenen Daten nicht.</p>
      <p>Wir können personenbezogene Daten weitergeben an:</p>
      <ul>
        <li>
          <strong>Dienstleister / Auftragsverarbeiter</strong>, die uns bei der Bereitstellung der Dienste unterstützen, z.&nbsp;B.:
          <ul>
            <li>Website- und Hosting-Anbieter,</li>
            <li>E-Mail-Dienstleister,</li>
            <li>Analyse- und Anti-Missbrauchs-Tools,</li>
            <li>Support- und Kommunikations-Tools.</li>
          </ul>
        </li>
      </ul>
      <p>
        Diese Dienstleister dürfen personenbezogene Daten nur nach unseren Weisungen verarbeiten und müssen sie angemessen schützen.
      </p>
      <p>Außerdem können wir personenbezogene Daten weitergeben, wenn:</p>
      <ul>
        <li>dies gesetzlich vorgeschrieben ist oder im Rahmen eines rechtlichen Verfahrens erforderlich ist,</li>
        <li>es notwendig ist, unsere Rechte oder die Rechte und die Sicherheit anderer zu schützen,</li>
        <li>
          dies im Zusammenhang mit einer Unternehmens­transaktion wie einer Umstrukturierung oder einem Verkauf geschieht,
          soweit rechtlich zulässig.
        </li>
      </ul>

      <h2>8. Internationale Datenübermittlungen</h2>
      <p>
        Da wir online tätig sind und weltweit tätige Dienstleister einsetzen, können Ihre personenbezogenen Daten in Ländern
        verarbeitet werden, die sich <strong>außerhalb</strong> des Landes befinden, in dem Sie leben. Dazu können Länder gehören,
        in denen das Datenschutzniveau nicht als dem des EWR oder des Vereinigten Königreichs gleichwertig angesehen wird.
      </p>
      <p>
        Wenn wir personenbezogene Daten aus dem EWR oder dem Vereinigten Königreich in Länder außerhalb dieser Regionen übermitteln,
        verwenden wir geeignete Garantien, zum Beispiel:
      </p>
      <ul>
        <li>von der EU-Kommission oder den britischen Behörden genehmigte Standardvertragsklauseln, oder</li>
        <li>andere rechtliche Mechanismen, die nach dem Datenschutzrecht zulässig sind.</li>
      </ul>
      <p>
        Weitere Informationen zu den von uns eingesetzten Schutzmaßnahmen für internationale Datenübermittlungen erhalten Sie auf Anfrage.
      </p>

      <h2>9. Wie lange wir personenbezogene Daten aufbewahren</h2>
      <p>
        Wir bewahren personenbezogene Daten nur so lange auf, wie es für die in dieser Erklärung beschriebenen Zwecke erforderlich ist, unter anderem:
      </p>
      <ul>
        <li>während wir die Dienste bereitstellen und Ihr Zertifikat oder zugehörige Inhalte verwalten,</li>
        <li>für einen angemessenen Zeitraum danach, um Fragen oder Anliegen zu bearbeiten,</li>
        <li>solange wir gesetzlich zur Aufbewahrung bestimmter Daten verpflichtet sind (z.&nbsp;B. für steuerliche oder buchhalterische Zwecke).</li>
      </ul>
      <p>
        Wenn wir personenbezogene Daten nicht mehr benötigen, löschen wir sie oder anonymisieren sie so, dass sie nicht mehr Ihnen zugeordnet werden können.
      </p>

      <h2>10. Wie wir personenbezogene Daten schützen</h2>
      <p>
        Wir setzen geeignete technische und organisatorische Maßnahmen ein, um personenbezogene Daten vor unbeabsichtigter oder
        unrechtmäßiger Zerstörung, Verlust, Veränderung, unbefugter Offenlegung oder unbefugtem Zugriff zu schützen. Dazu gehören unter anderem:
      </p>
      <ul>
        <li>sichere Hosting-Umgebungen,</li>
        <li>Zugriffskontrollen und Authentifizierungsmechanismen für unsere Systeme,</li>
        <li>Verschlüsselung und sichere Kommunikationswege, soweit angemessen.</li>
      </ul>
      <p>
        Kein System kann vollständig sicher sein, aber wir arbeiten kontinuierlich daran, Ihre Daten zu schützen und unsere
        Sicherheitsmaßnahmen regelmäßig zu überprüfen.
      </p>

      <h2>11. Ihre Datenschutzrechte</h2>
      <p>
        Ihre Datenschutzrechte hängen davon ab, in welchem Land Sie leben. Sie können uns jederzeit unter
        <a href="mailto:support@example.test">support@example.test</a> kontaktieren, um Ihre Rechte zu erfragen oder auszuüben.
      </p>

      <h3>(a) EWR und Vereinigtes Königreich</h3>
      <p>
        Wenn Sie im EWR oder im Vereinigten Königreich ansässig sind, haben Sie nach der DSGVO bzw. UK GDPR in der Regel folgende Rechte:
      </p>
      <ul>
        <li>Recht auf Information darüber, wie Ihre Daten verwendet werden (diese Erklärung und weitere Hinweise),</li>
        <li>Recht auf Auskunft über die personenbezogenen Daten, die wir über Sie speichern,</li>
        <li>Recht auf Berichtigung unrichtiger oder unvollständiger Daten,</li>
        <li>Recht auf Löschung („Recht auf Vergessenwerden“) in bestimmten Fällen,</li>
        <li>Recht auf Einschränkung der Verarbeitung in bestimmten Fällen,</li>
        <li>Recht auf Datenübertragbarkeit für bestimmte Daten, die Sie uns bereitgestellt haben,</li>
        <li>Recht, bestimmten Verarbeitungen zu widersprechen, einschließlich Direktmarketing,</li>
        <li>Recht, eine einmal erteilte Einwilligung jederzeit zu widerrufen,</li>
        <li>Recht, eine Beschwerde bei einer Datenschutzaufsichtsbehörde einzureichen.</li>
      </ul>
      <p>
        Die Kontaktdaten der Aufsichtsbehörden im EWR finden Sie auf der Website des Europäischen Datenschutzausschusses.
        Für das Vereinigte Königreich ist die zuständige Behörde das Information Commissioner’s Office (ICO).
      </p>

      <h3>(b) Kanada</h3>
      <p>
        Wenn Sie in Kanada ansässig sind und der kanadische Datenschutzrechtsrahmen (z.&nbsp;B. PIPEDA) Anwendung findet, haben Sie in der Regel das Recht:
      </p>
      <ul>
        <li>Auskunft über die personenbezogenen Informationen zu verlangen, die wir über Sie speichern,</li>
        <li>die Berichtigung unrichtiger oder unvollständiger Informationen zu verlangen,</li>
        <li>Informationen über unsere Datenschutzpraktiken zu erhalten,</li>
        <li>
          bei Unzufriedenheit mit unserer Antwort beim Office of the Privacy Commissioner of Canada Beschwerde einzulegen.
        </li>
      </ul>

      <h3>(c) Australien</h3>
      <p>
        Wenn Sie in Australien ansässig sind, können Ihnen nach dem Privacy Act 1988 (Cth) und den Australian Privacy Principles (APPs)
        insbesondere folgende Rechte zustehen:
      </p>
      <ul>
        <li>Zugriff auf die personenbezogenen Informationen zu verlangen, die wir über Sie halten,</li>
        <li>
          die Berichtigung von Informationen zu verlangen, wenn diese unrichtig, unvollständig, veraltet, irrelevant oder irreführend sind,
        </li>
        <li>Beschwerde einzureichen, wenn Sie der Ansicht sind, dass wir gegen die APPs verstoßen haben.</li>
      </ul>
      <p>
        Weitere Informationen und Kontaktdaten des Office of the Australian Information Commissioner (OAIC) finden Sie auf dessen Website.
      </p>

      <h3>(d) Vereinigte Staaten von Amerika</h3>
      <p>
        Wenn Sie in den USA ansässig sind, können sich Ihre Datenschutzrechte je nach Bundesstaat unterscheiden. Einige
        bundesstaatliche Datenschutzgesetze gewähren Ihnen gegebenenfalls Rechte wie:
      </p>
      <ul>
        <li>das Recht, Zugang zu den personenbezogenen Informationen zu verlangen, die wir über Sie speichern,</li>
        <li>das Recht, die Löschung bestimmter personenbezogener Informationen zu verlangen,</li>
        <li>das Recht, bestimmten Formen des „Verkaufs“ oder „Weitergebens“ von Daten zu widersprechen.</li>
      </ul>
      <p>
        Wir verkaufen Ihre personenbezogenen Daten nicht im üblichen Sinn dieses Begriffs. Wenn Sie der Ansicht sind, dass ein bestimmtes
        US-Bundesstaatsgesetz auf Sie anwendbar ist und Sie Ihre Rechte nach diesem Gesetz ausüben möchten, können Sie uns unter
        <a href="mailto:support@example.test">support@example.test</a> kontaktieren; wir antworten im Rahmen der jeweils geltenden gesetzlichen Vorgaben.
      </p>

      <h2>12. Datenschutz bei Kindern</h2>
      <p>
        Unsere Dienste richten sich nicht an Kinder unter 16 Jahren, und wir erheben nicht wissentlich personenbezogene Daten von Kindern
        unter 16 Jahren ohne die erforderliche Einwilligung, soweit gesetzlich vorgeschrieben. Wenn Sie der Meinung sind, dass ein Kind uns
        personenbezogene Daten ohne entsprechende Einwilligung übermittelt hat, kontaktieren Sie uns bitte; wir werden die Daten soweit
        erforderlich löschen.
      </p>

      <h2>13. Änderungen dieser Datenschutzerklärung</h2>
      <p>
        Wir können diese Datenschutzerklärung von Zeit zu Zeit aktualisieren, zum Beispiel um Änderungen unserer Dienste oder gesetzliche
        Anforderungen zu berücksichtigen. Bei wesentlichen Änderungen aktualisieren wir das Datum „Zuletzt aktualisiert“ am Anfang dieser
        Seite und können zusätzliche Hinweise bereitstellen, soweit angemessen.
      </p>
      <p>
        Wir empfehlen Ihnen, diese Datenschutzerklärung regelmäßig zu überprüfen, um darüber informiert zu bleiben, wie wir Ihre
        Informationen schützen.
      </p>

      <h2>14. Kontakt</h2>
      <p>
        Wenn Sie Fragen oder Anliegen zu dieser Datenschutzerklärung oder zu unserem Umgang mit Ihren personenbezogenen Daten haben oder
        Ihre Datenschutzrechte ausüben möchten, können Sie uns wie folgt kontaktieren:
      </p>
      <p>
        <strong>Kontakt:</strong> <a href="mailto:support@example.test">support@example.test</a>
      </p>
      <p>
        Wir werden uns bemühen, zeitnah zu antworten und etwaige Anliegen bestmöglich zu klären.
      </p>
      <a href="javascript:void(0);" class="close-return-link" onclick="goBack()">Schließen und zurück</a>
    </main>
  </div>

  <footer class="site-footer" role="contentinfo">
    Copyright <?= (int)COPYRIGHT_YEAR ?> <?= htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') ?>, All rights reserved.
  </footer>

  <script>
  function goBack(event) {
    if (event) event.preventDefault();

    // Force language to DE for the next page (/privacy.php reads cookie only)
    document.cookie = "demo_lang=de; path=/; max-age=31536000; samesite=lax";

    window.location.href = "/privacy.php";
  }
</script>
</body>
</html>