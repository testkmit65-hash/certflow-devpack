# Sanitized Contractor Dev Pack (Responsive CSS Fixes)

This package is a **sanitized clone** of the demo-certflow project for external contractors.

## What was sanitized
- Removed all real secrets/credentials (real `.env` replaced with dummy values).
- Disabled outbound email (**EMAIL_DISABLE=1**). Any generated emails are written as HTML files to:
  - `storage/emails/<BOOK_ID>/`
- Replaced all brand references (text + titles) with **DemoBrand**.
- Replaced logo/background/certificate images with safe placeholder assets (same dimensions).
- Replaced bonus PDF with a placeholder.
- Cleared `storage/logs`, `storage/tmp`, `storage/suppression` contents.

## What still works (for testing)
- Pages render and behave like production for **layout / viewport / scroll** testing.
- Full flow works locally (Access → Submit → Success), but **no real emails** are sent.
- Certificate generation still runs using placeholder assets.

## Quick setup (local)
1) Requirements:
   - PHP 8.1+ (CLI)
   - Composer (only if `vendor/` is missing)

2) In the project root:
   - Ensure `.env` exists (already included in this dev pack).

3) Run servers (use 0.0.0.0 so phones/tablets on the same Wi‑Fi can reach it):

   Terminal A (app):
   - `php -S 0.0.0.0:8000 -t public`

   Terminal B (generator):
   - `php -S 0.0.0.0:8001 -t public`

4) Open in desktop browser:
   - `http://localhost:8000/`

5) Open on a real phone/tablet (same network):
   - Find your computer IP (e.g. `192.168.x.x`)
   - Open: `http://<YOUR-IP>:8000/`

## Remote realistic device testing (no hardware)
If you don’t have devices, use a **cloud real-device** provider.
They need a URL that is reachable from the internet.

Safer options:
- Deploy this sanitized pack to a temporary staging URL.
- Put it behind **HTTP Basic Auth** (username/password).
- Do NOT reuse production domains, SMTP, or API keys.

## Scope for contractor
- Focus: CSS / viewport stability (portrait + landscape) on phones + tablets.
- Do not change the submit flow logic.
- Avoid device-exact breakpoints; prefer range-based breakpoints + safe-area handling.
