<?php
declare(strict_types=1);
require_once __DIR__ . '/../code/app/config.php';

$lang = 'en';
$privacyLastUpdated = format_privacy_last_updated($lang);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Privacy Policy – DemoBrand</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
      --policy-logo-shift-x: 578px;    /* horizontal shift relative to text */
      --policy-logo-shift-y: 0px;    /* vertical shift */

      --card-offset-y: 2px;         /* + down / - up (card only) */

      --pp-pt-gap-s1-contactprompt-to-contact: 3px; /* “You can contact…” -> “Contact: …” */
    }

      {
      box-sizing: border-box;
    }

    /* Section 1: make the contact gap adjustable */
    .policy-card h2:first-of-type + p + p{
      margin-bottom: 0 !important; /* “You can contact us…” paragraph */
    }
    .policy-card p.policy-contact-line{
      margin-top: var(--t28x-pp-pt-gap-s1-contactprompt-to-contact) !important;
    }

    html{
      background-color: var(--ink-blue);
      background-image: url("../assets/img/landing-bg-desktop.webp"), var(--bg-lqip-desktop);
      background-position: center center, center center;
      background-size: cover, cover;
      background-repeat: no-repeat, no-repeat;
    }

    body {
      font-family: Helvetica, Arial, sans-serif;
      line-height: 1.6;
      margin: 0;
      background: transparent; /* let .page-bg show through */
      color: #111;
    }

    .page-bg {
      position: fixed;
      inset: 0;
      background-color: var(--ink-blue);
      background-image: url("../assets/img/landing-bg-desktop.webp"), var(--bg-lqip-desktop);
      background-position: center center, center center;
      background-size: cover, cover;
      background-repeat: no-repeat, no-repeat;
      z-index: 0;
      pointer-events: none;
      transform: translateZ(0);
    }

    /* Prevent scrolling on the outer viewport */
    html, body {
      height: 100%;
      overflow: hidden;  /* Remove the outer scrollbar */
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
      width: 100%;
      max-width: 900px;
      background: #ffffff;
      border-radius: 30px;
      padding: 32px 24px 40px;
      box-shadow: 0 18px 40px rgba(0, 0, 0, 0.35);
      max-height: 80vh;
      overflow-y: auto;
      position: relative;
      margin-bottom: 20px;
      padding-right: 16px;
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

/* ------------------------------------------------------------
  T27.4 — Tablet & laptop viewport audit — Privacy Policy (EN)
---------------------------------------------------------------*/
@media (min-width: 700px) and (max-width: 1600px) and (min-height: 700px){

  :root{
    /* White card: size + geometry */
    --t27pp-card-max-w: 900px;
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
    --t27pp-link-top: 53px;
    --t27pp-link-mr: 3px;
    --t27pp-link-fs: 15px;

    /* Logo: size + position */
    --t27pp-logo-h: 38px;
    --t27pp-logo-top: 0px;
    --t27pp-logo-right: -120px;
    --t27pp-title-right-reserve: 140px;

    /* Header spacing */
    --t27pp-gap-cardtop-to-header: -10px;
    --t27pp-gap-header-to-subtitle: 12px;

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
Privacy Policy (EN) – Split-screen / Foldable bridge (481–699px width) – PORTRAIT
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
    --pp-logo-right: -118px;    /* X inside card */
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

  /*-----------------------------------------------------------
   Common phones 12/13/14 & Pixel 5 (≈390/393px), PORTRAIT (EN)
   ------------------------------------------------------------*/
@media (min-width: 360px) and (max-width: 480px) and (orientation: portrait){

  :root{
    /* Card size + centering */
    --pp-card-w: calc(100vw - 30px);   /* adjustable */
    --pp-card-max-h: 87vh;             /* adjustable */
    --pp-card-radius: 24px;
    --pp-card-pad-t: 0px;
    --pp-card-pad-r: 14px;
    --pp-card-pad-b: 20px;
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
    --pp-link-left: 220px;            /* relative to card content area (inside padding) */
    --pp-gap-link-header: 0px;        /* link -> header spacing */

    /* Logo position + size (top/right of white card) */
    --pp-logo-h: 32px;                /* adjustable */
    --pp-logo-top: 12px;              /* to top edge of card */
    --pp-logo-right: 12px;            /* to right edge of card */
    --pp-title-right-reserve: 120px;  /* keep title clear of logo */

    /* Footer spacing */
    --pp-gap-card-footer: 18px;       /* card -> footer */
    --pp-footer-pad-b: 10px;          /* footer -> bottom of viewport */
    --footer-raise: -2px;              /* keep stable on phones */
    --pp-shell-footer-offset: 56px;   /* helps vertical centering vs footer */
  }

  /* Mobile background like the rest */
  .page-bg{
    background-color: var(--ink-blue);
    background-image: url("../assets/img/landing-bg-mobile.webp"), var(--bg-lqip-mobile);
    background-position: center center, center center;
    background-size: cover, cover;
    background-repeat: no-repeat, no-repeat;
  }

  /* Keep outer viewport locked (as you want) */
  html, body{
    height: 100%;
    overflow: hidden;
    overscroll-behavior: none;
  }

  /* Center the card in the viewport (like SE, but adjustable) */
  .page-shell{
    padding: 12px 12px;
    min-height: calc(100vh - var(--pp-shell-footer-offset));
  }
  @supports (min-height: 100dvh){
    .page-shell{ min-height: calc(100dvh - var(--pp-shell-footer-offset)); }
  }

  /* White policy card: adjustable size + internal scrolling */
  .policy-card{
    width: var(--pp-card-w);
    max-width: none;
    max-height: var(--pp-card-max-h);
    border-radius: var(--pp-card-radius);
    padding: var(--pp-card-pad-t) var(--pp-card-pad-r) var(--pp-card-pad-b) var(--pp-card-pad-l);
    transform: translateY(var(--pp-pt-card-offset-y));
    overflow-y: auto;
    overflow-x: hidden;
    margin: 0 auto var(--pp-gap-card-footer);
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
    margin-top: var(--pp-gap-top-title);
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
  Pixel 5 (393×851), PORTRAIT — Privacy Policy (EN)
--------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 393px) and (height: 851px){

  :root{
    /* Close-link position (top + left of white card) */
    --p5-pp-link-top: 55px;
    --p5-pp-link-left: 223px;            /* relative to card content area (inside padding) */
  }

  .close-return-link{
    top: var(--p5-pp-link-top);
    margin-left: var(--p5-pp-link-left);
  }
}

/* ---------------------------------------------------------
  T28.2 — iPhone X (375×812), PORTRAIT — Privacy Policy (EN)
------------------------------------------------------------*/
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
    --t28x-pp-pt-link-left: 205px;      /* close link X (keeps the existing margin-left logic) */

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
  T28.3 — iPhone XR (414×896), PORTRAIT — Privacy Policy (EN)
--------------------------------------------------------------*/
@media (orientation: portrait) and (width: 414px) and (height: 896px){

  :root{
    /* ===== knobs (XR only) ===== */
    --t28xr-pp-pt-card-offset-y: -12px;         /* + down / - up (card only) */

    /* Close & return link position (inside the white card) */
    --t28xr-pp-pt-link-top: 54px;             /* Y */
    --t28xr-pp-pt-link-left: 244px;           /* X (keeps existing margin-left logic) */

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
  T28.6 — Samsung Galaxy S20 Ultra (412×915), PORTRAIT — Privacy Policy (EN)
-----------------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 412px) and (height: 915px){

  :root{
    /* Link knobs (S20 Ultra portrait only) */
    --t286s20-pp-pt-link-top: 54px;     /* + down / - up */
    --t286s20-pp-pt-link-left: 242px;   /* + right / - left (keeps existing margin-left logic) */

    /* wire into existing vars used by .close-return-link */
    --pp-link-top: var(--t286s20-pp-pt-link-top);
    --pp-link-left: var(--t286s20-pp-pt-link-left);
  }
}

/* --------------------------------------------------------------------------
  CH9 — Samsung Galaxy A51/A71 (412×914), PORTRAIT — Privacy Policy (EN)
-----------------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 412px) and (height: 914px){

  :root{
    /* Close link knobs (A51/A71 portrait only) */
    --ch9a51-pp-pt-link-top: 54px;     /* Y: + down / - up */
    --ch9a51-pp-pt-link-left: 242px;   /* X: + right / - left */

    /* wire into existing vars used by .close-return-link */
    --pp-link-top: var(--ch9a51-pp-pt-link-top);
    --pp-link-left: var(--ch9a51-pp-pt-link-left);
  }
}

/* ---------------------------------------------------------------------
  CH8 — Pixel 8 Pro (448×998), PORTRAIT — Privacy Policy (EN)
------------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 448px) and (height: 998px){
  :root{
    /* Link knobs (Pixel 8 Pro portrait only) */
    --ch8p8pro-pp-pt-link-top: 55px;     /* + down / - up */
    --ch8p8pro-pp-pt-link-left: 278px;   /* + right / - left */

    /* wire into existing vars used by .close-return-link */
    --pp-link-top: var(--ch8p8pro-pp-pt-link-top);
    --pp-link-left: var(--ch8p8pro-pp-pt-link-left);
    }
  }

/* ------------------------------------------------------------------
  T28.4 — iPhone 14 Pro Max (430×932), PORTRAIT — Privacy Policy (EN)
---------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 430px) and (height: 932px){

  :root{
    /* ===== CARD knobs (independent) ===== */
    --t284pm-pp-pt-card-offset-y: -12px;          /* + down / - up (card only) */
    --t284pm-pp-pt-footer-reserve: 105px;        /* card-only: controls max height */
    --t284pm-pp-pt-card-max-h: calc(100vh - var(--t284pm-pp-pt-footer-reserve));

    /* Close link knobs (optional, but safe to have) */
    --t284pm-pp-pt-link-top: 55px;
    --t284pm-pp-pt-link-left: 260px;

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
  T28.5 — Samsung Galaxy S8+ (360×740), PORTRAIT — Privacy Policy (EN)
----------------------------------------------------------------------*/
@media (orientation: portrait) and (width: 360px) and (height: 740px){

  :root{
    /* Close link knobs (optional, but safe to have) */
    --t285s8-pp-pt-link-top: 55px;
    --t285s8-pp-pt-link-left: 190px;

    /* wire */
    --pp-link-top: var(--t285s8-pp-pt-link-top);
    --pp-link-left: var(--t285s8-pp-pt-link-left);
  }
}


/* ------------------------------------------------------------
  T28.1 — 375×667 PORTRAIT (short phones) — Privacy Policy (EN)
---------------------------------------------------------------*/
@media (orientation: portrait)
  and (min-width: 360px) and (max-width: 430px)
  and (max-height: 700px){

  :root{
    /* Card size */
    --pp-card-w: calc(100vw - 28px);
    --pp-card-max-h: 89vh;

    --pp-card-pad-r: 12px;
    --pp-card-pad-l: 12px;
    --pp-card-pad-b: 0px;

    /* card + centering */
    --t28pp-ls-card-offset-y: 24px;    /* + down / - up */

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
    --pp-link-left: 216px;

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
    Extra-small phones (e.g. iPhone SE 320×568 – portrait only) (EN) 
    ----------------------------------------------------------------*/
    @media (max-width: 340px) {

  /* SE-specific header tuning */
  :root {
  --policy-logo-shift-x: 89px;
  --policy-logo-shift-y: 0px;

  --footer-raise: -28px;

  --policy-title-subtitle-gap: 25px;      /* gap between H1 and subtitle */
  --pp-fs-subtitle: 14px;              /* subtitle font size (adjustable) */
  --policy-body-line-height: 1.40;       /* line spacing in body text */
  --policy-paragraph-gap: 6px;           /* default vertical gap between paragraphs */
  --policy-h2-top-gap: 20px;             /* space above section headings (e.g. “2. Scope…”) */
  --policy-h2-bottom-gap: 3px;           /* space below h2 before the first paragraph */
  --policy-contact-gap-top: -7px;         /* gap between “You can contact us…” and “Contact: …” */
  --policy-shell-footer-offset: 14px;    /* used to visually centre the card vs. footer */

  --pp-pt-card-offset-y: -3px;         /* + down / - up (card only) */
  }

  /* Use the dedicated mobile background to match Access/Success */
  .page-bg{
    background-color: var(--ink-blue);
    background-image: url("../assets/img/landing-bg-mobile.webp"), var(--bg-lqip-mobile);
    background-position: center center, center center;
    background-size: cover, cover;
    background-repeat: no-repeat, no-repeat;
  }

  /* Slightly tighter padding around the card */
  .page-shell {
    padding: 10px 10px;
    min-height: calc(100vh - var(--policy-shell-footer-offset));
  }

  /* Card: centered + compact padding, no horizontal scroll */
  .policy-card {
    border-radius: 24px;
    padding: 6px 14px 18px;
    max-height: 84vh;      /* keep card as the only scroll area */
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
    top: 35px;            /* vertical SE position – adjust here */
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

/* ---------------------------------------------------
  Samsung Galaxy S9+ — Privacy Policy (EN) – PORTRAIT
------------------------------------------------------*/
@media (orientation: portrait) and (width: 320px) and (height: 658px){

/* card border rounding */
  .policy-card{
    border-radius: 24px !important;
    clip-path: inset(0 0 0 0 round 24px) !important;
  }
}

/*------------------------------------------------------------------------------------
Privacy Policy (EN) – Split-screen / short-height window (461–699px tall) – LANDSCAPE
--------------------------------------------------------------------------------------*/
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
    --pp-logo-right: -118px;    /* X inside card */
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

  /* -----------------------------------------------------------------------
    CH9 — Foldable / split-screen (720×557), LANDSCAPE — Privacy Policy (EN)
    Footnote Y adjustable only (white card must NOT move)
  --------------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 720px) and (height: 557px){
  :root{
    --ch9f720-ls-pp-footer-bottom: 8.5px; /* bigger value pushes footer UP */
    --pp-footer-bottom: var(--ch9f720-ls-pp-footer-bottom);
  }
}


/* --------------------------------------------------------------------------------
  T26.4 — Phone group iPhone 12/13/14 (844×390) & Pixel 5 (851×393), LANDSCAPE (EN)
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
    --pp-logo-right: -112px;
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
  T28.2 — iPhone X (812×375), LANDSCAPE — Privacy Policy (EN)
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
  T28.3 — iPhone XR (896×414), LANDSCAPE — Privacy Policy (EN)
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
  T28.4 — iPhone 14 Pro Max (932×430), LANDSCAPE — Privacy Policy (EN)
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
    --t284pm-pp-ls-logo-right: -110px;
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
  T28.1 — 667×375 LANDSCAPE (medium landscape phones) — Privacy Policy (EN)
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
    --pp-logo-right: -102px;
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
  T28.5 — Samsung Galaxy S8+ (740×360), LANDSCAPE — Privacy Policy (EN)
-----------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 740px) and (height: 360px){

  :root{
    --t285s8-pp-ls-logo-shift-x: 353px; /* + right / - left */
  }

  .logo-inline{
    transform: translate(var(--t285s8-pp-ls-logo-shift-x), var(--policy-logo-shift-y)) !important;
  }
}

/*---------------------------------------------------------
  iPhone SE (568×320), LANDSCAPE – Full Privacy Policy (EN)
  ---------------------------------------------------------*/
@media (max-width: 950px) and (max-height: 360px) and (orientation: landscape){

  :root{
    --footer-raise: 0px;
    --policy-contact-gap-top: -7px;         /* gap between “You can contact us…” and “Contact: …” */

    --pp-card-max-h: 75vh;
    --pp-pt-card-offset-y: 8px;         /* + down / - up (card only) */

    /* kill desktop logo push that can create sideways panning */
    --policy-logo-shift-x: 320px;
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
    width: min(calc(100vw - 16px), 552px);
    border-radius: 24px;
    clip-path: inset(0 0 0 0 round 24px);

    transform: translateY(var(--pp-pt-card-offset-y));

    padding: 10px 14px 14px;
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
    top: 43px;
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
  Samsung Galaxy S9+ (658×320), LANDSCAPE — Privacy Policy (EN)
  Fix: override SE-landscape logo shift for this device only
-----------------------------------------------------------------------*/
@media (orientation: landscape) and (width: 658px) and (height: 320px){
  :root{
    --policy-logo-shift-x: 352px;  /* puts logo back next to title (no far-right jump) */
  }

  /* card: only scroll area + no sideways panning */
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
  <!-- Close and return link, fixed in top-right corner -->
  <a href="javascript:void(0);" class="close-return-link" onclick="goBack()">Close and return</a>
<header class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">
      Privacy Policy
      <img src="../assets/img/logo.svg" alt="DemoBrand logo" class="logo-inline">
    </h1>
  </div>
</header>

<p class="subtitle">How we collect, use and protect your personal data.</p>
<p class="meta">
  updated:
  <?= htmlspecialchars($privacyLastUpdated, ENT_QUOTES, 'UTF-8') ?>
</p>

      <h2>1. Who we are</h2>
      <p>
        This website and related online services (together, the “Services”) are operated under the DemoBrand brand
        by <strong>DemoBrand</strong> (“DemoBrand”, “we”, “us”, “our”).
      </p>
      <p>
        You can contact us about privacy at:
      </p>
      <p class="policy-contact-line">
      <strong>Contact:</strong> <a href="mailto:support@example.test">support@example.test</a>
      </p>
      <p>
        If you are based in the EU/EEA or UK, we act as the data controller for the processing of your personal
        data described in this policy.
      </p>

      <h2>2. Scope of this Privacy Policy</h2>
      <p>
        This Privacy Policy explains how we collect, use, disclose and protect personal data when you:
      </p>
      <ul>
        <li>visit our websites, including <strong>example.test</strong> and related subdomains,</li>
        <li>use our landing pages for activities such as puzzle challenges and certificates,</li>
        <li>subscribe to our emails or other marketing communications,</li>
        <li>contact us or otherwise interact with us online.</li>
      </ul>
      <p>
        It does not cover third-party services that have their own privacy policies (for example, Amazon or other
        platforms where you buy our books).
      </p>

      <h2>3. What personal data we collect</h2>
      <p>The categories of personal data we may collect include:</p>
      <ul>
        <li>
          <strong>Identity data:</strong> first name, last name, display name or username (if used).
        </li>
        <li>
          <strong>Contact data:</strong> email address and any other contact details you choose to provide.
        </li>
        <li>
          <strong>Activity and certificate data:</strong> secret key codes or similar identifiers used to confirm
          that you have successfully completed a specific activity, puzzle or challenge; information about which
          challenge or book the activity belongs to; certificate IDs and timestamps.
        </li>
        <li>
          <strong>Communication data:</strong> content of messages you send us (for example, support requests,
          feedback).
        </li>
        <li>
          <strong>Technical and usage data:</strong> IP address, browser type, device information, pages viewed,
          time and date of visits, and referring URLs, collected through server logs and similar technologies.
        </li>
        <li>
          <strong>Marketing preference data:</strong> your choices about receiving marketing emails or newsletters
          from us (opt-in / opt-out).
        </li>
      </ul>
      <p>
        We do not intentionally collect sensitive categories of personal data (such as health information or
        information about your religion, political opinions or similar).
      </p>

      <h2>4. How we collect personal data</h2>
      <p>We collect personal data in three main ways:</p>
      <p><strong>(a) Directly from you, when you:</strong></p>
      <ul>
        <li>fill in forms on our landing pages (for example, to request a certificate or bonus content),</li>
        <li>subscribe to our newsletter or updates,</li>
        <li>contact us by email or through other channels.</li>
      </ul>
      <p><strong>(b) Automatically, when you:</strong></p>
      <ul>
        <li>
          browse our websites or landing pages. Some technical and usage data are collected through cookies,
          server logs and similar technologies.
        </li>
      </ul>
      <p><strong>(c) From third parties, in limited cases, such as:</strong></p>
      <ul>
        <li>
          analytics or anti-spam tools that help us understand how our site is used and protect it from abuse.
        </li>
      </ul>

      <h2>5. How we use personal data and legal bases</h2>
      <p>We use personal data for the following purposes:</p>

      <h3>5.1 To provide the Services and fulfil your requests</h3>
      <ul>
        <li>verifying your successful completion of puzzle activities or challenges,</li>
        <li>generating and delivering digital certificates,</li>
        <li>providing access to bonus content or downloads you requested,</li>
        <li>responding to your enquiries and support requests.</li>
      </ul>
      <p>
        For individuals in the EU/EEA and UK, this processing is normally based on
        <strong>Article 6(1)(b) GDPR/UK GDPR (performance of a contract)</strong>, or
        <strong>Article 6(1)(f)</strong> where we rely on our legitimate interests (for example, preventing abuse of
        the certificate system).
      </p>

      <h3>5.2 To send you marketing communications (with your consent or as permitted by law)</h3>
      <ul>
        <li>
          sending you emails about new books, puzzles, products, promotions, competitions or news about DemoBrand,
        </li>
        <li>tailoring communications to your interests where possible.</li>
      </ul>
      <p>
        We only send marketing emails where allowed by applicable law, and you can unsubscribe at any time using
        the link in our emails or by contacting us at
        <a href="mailto:support@example.test">support@example.test</a>.
      </p>

      <h3>5.3 To maintain and improve our Services</h3>
      <ul>
        <li>monitoring performance and usage of our websites,</li>
        <li>detecting and preventing fraud or abuse,</li>
        <li>improving content, features and user experience.</li>
      </ul>
      <p>
        For EU/EEA/UK visitors, this typically relies on our legitimate interests in operating a secure and useful
        service.
      </p>

      <h3>5.4 To comply with legal obligations</h3>
      <ul>
        <li>keeping records required by law,</li>
        <li>responding to lawful requests from regulators or law enforcement authorities where applicable.</li>
      </ul>

      <h2>6. Cookies and similar technologies</h2>
      <p>
        We may use cookies and similar technologies (such as local storage or pixels) to:
      </p>
      <ul>
        <li>remember your language preferences,</li>
        <li>keep sessions secure,</li>
        <li>understand how visitors use our websites (for example, aggregated statistics).</li>
      </ul>
      <p>
        Where required by law (for example, in the EU/EEA and UK for non-essential cookies), we will ask for your
        consent before setting non-essential cookies. You can manage cookie preferences in your browser settings
        and, where implemented, through our cookie banner or settings interface.
      </p>

      <h2>7. When we share personal data</h2>
      <p>We do not sell your personal data.</p>
      <p>We may share personal data with:</p>
      <ul>
        <li>
          <strong>Service providers / processors</strong> who help us operate the Services, such as:
          <ul>
            <li>website and hosting providers,</li>
            <li>email service providers,</li>
            <li>analytics and anti-abuse tools,</li>
            <li>customer support tools.</li>
          </ul>
        </li>
      </ul>
      <p>
        These providers are only allowed to process personal data on our instructions and must protect it
        appropriately.
      </p>
      <p>We may also share personal data if:</p>
      <ul>
        <li>required by law or a legal process,</li>
        <li>necessary to protect our rights, safety, or the rights and safety of others,</li>
        <li>
          in connection with a business transaction such as a restructuring or sale, where permitted by law.
        </li>
      </ul>

      <h2>8. International transfers</h2>
      <p>
        Because we operate online and use global service providers, your personal data may be processed in
        countries <strong>outside</strong> the country where you live. This may include countries where the level of
        data protection is not regarded as equivalent to that in the EU/EEA or UK.
      </p>
      <p>
        Where we transfer personal data from the EU/EEA or UK to countries outside those regions, we will use
        appropriate safeguards, such as:
      </p>
      <ul>
        <li>Standard Contractual Clauses approved by the European Commission or UK authorities, or</li>
        <li>other legal mechanisms recognised under data protection law.</li>
      </ul>
      <p>
        You can contact us for more information about the safeguards we use for international transfers.
      </p>

      <h2>9. How long we keep personal data</h2>
      <p>
        We keep personal data only for as long as necessary for the purposes described in this policy, including:
      </p>
      <ul>
        <li>while we provide the Services and manage your certificate or related content,</li>
        <li>for a reasonable time afterwards to handle any questions or issues,</li>
        <li>for as long as required by law (for example, certain records for tax or accounting).</li>
      </ul>
      <p>
        When we no longer need personal data, we will delete it or anonymise it so it can no longer be linked to
        you.
      </p>

      <h2>10. How we protect personal data</h2>
      <p>
        We use appropriate technical and organisational measures designed to protect personal data against
        accidental or unlawful destruction, loss, alteration, unauthorised disclosure or access. These measures
        include:
      </p>
      <ul>
        <li>secure hosting environments,</li>
        <li>access controls and authentication for our systems,</li>
        <li>encryption and secure communication channels, where appropriate.</li>
      </ul>
      <p>
        No system can be completely secure, but we work to keep your data protected and regularly review our
        security practices.
      </p>

      <h2>11. Your privacy rights</h2>
      <p>
        Your privacy rights depend on where you live. You can always contact us at
        <a href="mailto:support@example.test">support@example.test</a> to ask about your rights or exercise them.
      </p>

      <h3>(a) EU/EEA and UK</h3>
      <p>
        If you are in the EU/EEA or UK, you generally have the following rights under the GDPR/UK GDPR:
      </p>
      <ul>
        <li>Right to be informed about how your data is used (this policy and related notices).</li>
        <li>Right of access to the personal data we hold about you.</li>
        <li>Right to rectification of inaccurate or incomplete data.</li>
        <li>Right to erasure (“right to be forgotten”) in certain circumstances.</li>
        <li>Right to restrict processing in certain cases.</li>
        <li>Right to data portability for some data you have provided.</li>
        <li>Right to object to certain processing, including direct marketing.</li>
        <li>Right to withdraw consent where processing is based on consent.</li>
        <li>Right to lodge a complaint with a data protection authority.</li>
      </ul>
      <p>
        You can find the contact details of EU supervisory authorities on the European Data Protection Board
        website, and for the UK, the Information Commissioner’s Office (ICO).
      </p>

      <h3>(b) Canada</h3>
      <p>
        If you are in Canada and PIPEDA applies, you generally have the right to:
      </p>
      <ul>
        <li>access the personal information we hold about you,</li>
        <li>request correction of inaccurate or incomplete information,</li>
        <li>obtain information about our personal information practices, and</li>
        <li>
          complain to the Office of the Privacy Commissioner of Canada if you are not satisfied with our
          response.
        </li>
      </ul>

      <h3>(c) Australia</h3>
      <p>
        If you are in Australia, the Australian Privacy Act 1988 and the Australian Privacy Principles (APPs)
        may give you rights to:
      </p>
      <ul>
        <li>request access to the personal information we hold about you,</li>
        <li>
          request correction if it is inaccurate, out of date, incomplete, irrelevant or misleading,
        </li>
        <li>complain if you believe we have breached the APPs.</li>
      </ul>
      <p>
        You can find more information and contact details for the Office of the Australian Information
        Commissioner (OAIC) on its website.
      </p>

      <h3>(d) United States</h3>
      <p>
        If you are in the United States, your privacy rights may vary by state. Some state privacy laws may give
        you rights such as:
      </p>
      <ul>
        <li>requesting access to personal information we hold about you,</li>
        <li>requesting deletion of certain personal information,</li>
        <li>opting out of certain types of data “sale” or “sharing”.</li>
      </ul>
      <p>
        We do not sell your personal data in the ordinary sense of that term. If you believe a specific US state
        law applies to you and wish to exercise rights under that law, you can contact us at
        <a href="mailto:support@example.test">support@example.test</a>, and we will respond as required by applicable
        law.
      </p>

      <h2>12. Children’s privacy</h2>
      <p>
        Our Services are not intended for children under 16, and we do not knowingly collect personal data from
        children under 16 without appropriate consent where required by law. If you believe a child has provided
        us with personal data without proper consent, please contact us and we will delete it where required.
      </p>

      <h2>13. Changes to this Privacy Policy</h2>
      <p>
        We may update this Privacy Policy from time to time, for example to reflect changes in our Services or
        legal requirements. When we make material changes, we will update the “Last updated” date at the top of
        this page and may provide additional notice where appropriate.
      </p>
      <p>
        We encourage you to review this Privacy Policy periodically to stay informed about how we protect your
        information.
      </p>

      <h2>14. How to contact us</h2>
      <p>
        If you have questions or concerns about this Privacy Policy or our handling of your personal data, or if
        you wish to exercise your privacy rights, you can contact us at:
      </p>
      <p>
        <strong>Contact:</strong> <a href="mailto:support@example.test">support@example.test</a>
      </p>
      <p>
        We will do our best to respond promptly and to resolve any concerns you may have.
      </p>
    </main>
  </div>

  <footer class="site-footer" role="contentinfo">
    Copyright <?= (int)COPYRIGHT_YEAR ?> <?= htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') ?>, All rights reserved.
  </footer>

  <script>
  function goBack(event) {
    if (event) event.preventDefault();

    // Force language to EN for the next page (/privacy.php reads cookie only)
    document.cookie = "demo_lang=en; path=/; max-age=31536000; samesite=lax";

    window.location.href = "/privacy.php";
  }
</script>
</script>
</body>
</html>