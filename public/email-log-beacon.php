<?php
declare(strict_types=1);

require_once __DIR__ . '/../code/app/config.php';
require_once __DIR__ . '/../code/app/lib/Utils.php';

// Called from JS when client-side validation fails:
// /email-log-beacon.php?email=...&lang=en|de&reason=...

$email  = isset($_GET['email'])  ? (string) $_GET['email']  : '';
$lang   = isset($_GET['lang'])   ? (string) $_GET['lang']   : 'en';
$reason = isset($_GET['reason']) ? (string) $_GET['reason'] : 'frontend_validation';

$email = strtolower(trim($email));
$lang  = ($lang === 'de') ? 'de' : 'en';

if ($email !== '' && function_exists('log_email_attempt')) {
    // These are *frontend-blocked* attempts: we treat them as validation_error.
    log_email_attempt($email, 'validation_error', $reason, $lang);
}

// No body needed – just a tiny response suitable for an image beacon
http_response_code(204);
header('Content-Type: image/gif');
exit;