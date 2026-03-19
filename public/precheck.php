<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../code/app/config.php';
require_once __DIR__ . '/../code/app/lib/Utils.php';
require_once __DIR__ . '/../code/app/Cooldown.php';

use App\Cooldown;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Only allow POST (prevent direct access)
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
  exit;
}

/* ---------- 1) Collect and normalize inputs ---------- */
$first  = trim((string)($_POST['first_name'] ?? ''));
$last   = trim((string)($_POST['last_name']  ?? ''));
$email  = trim((string)($_POST['email']      ?? ''));
$email  = strtolower(trim(preg_replace('/^mailto:\s*/i', '', $email)));

$keyRaw = '';
foreach (['key_code','key','secret_key','code','access_code','challenge_code'] as $k) {
  if (isset($_POST[$k])) { $keyRaw = (string)$_POST[$k]; break; }
}
$key = strtoupper(preg_replace('/\s+/', '', trim($keyRaw))); // remove spaces; UI hints A–Z, 0–9

/* ---------- 1a) Language-aware messages (EN / DE) ---------- */
$lang = 'en';
if (!empty($_COOKIE['demo_lang'])) {
  $c = strtolower(trim((string)$_COOKIE['demo_lang']));
  if (in_array($c, ['en','de'], true)) {
    $lang = $c;
  }
}

$msgTable = [
  'en' => [
    'all_required' => 'Please fill all required fields.',
    'secret'       => 'Invalid or missing Secret Key Code, please try again.',
    'cooldown'     => 'You can resubmit your form in %d minutes.',
  ],
  'de' => [
    'all_required' => 'Bitte fülle alle Pflichtfelder aus.',
    'secret'       => 'Ungültiger oder fehlender geheimer Schlüsselcode. Bitte versuche es erneut.',
    'cooldown'     => 'Du kannst das Formular in %d Minuten erneut senden.',
  ],
];
$msg = $msgTable[$lang] ?? $msgTable['en'];

/* ---------- 2) Missing core fields (ONLY first/last/email) ---------- */
if ($first === '' || $last === '' || $email === '') {
  log_email_attempt($email, 'validation_error', 'missing_required_fields', $lang);
  echo json_encode(['ok' => false, 'error' => $msg['all_required']]);
  exit;
}

/* ---------- 3) Secret Key Code validation (missing OR invalid) ---------- */
if ($key === '' || !preg_match('/^[A-Z0-9]{15}$/', $key)) {
  log_email_attempt($email, 'validation_error', 'invalid_secret_key', $lang);
  echo json_encode(['ok' => false, 'error' => $msg['secret']]);
  exit;
}

/* ---------- 3b) Secret Key Code must match configured code ---------- */
$expected = defined('SECRET_KEY_CODE') ? SECRET_KEY_CODE : '';
if ($expected !== '' && !hash_equals($expected, $key)) {
  log_email_attempt($email, 'validation_error', 'secret_key_mismatch', $lang);
  echo json_encode(['ok' => false, 'error' => $msg['secret']]);
  exit;
}

/* ---------- 4) Cooldown check (per email) ---------- */
$remaining = Cooldown::remainingMinutes($email, COOLDOWN_MINUTES);
if ($remaining > 0) {
  $cooldownText = sprintf($msg['cooldown'], $remaining);
  log_email_attempt($email, 'cooldown', 'cooldown_active', $lang);
  echo json_encode(['ok' => false, 'error' => $cooldownText, 'cooldown' => true, 'remaining' => $remaining]);
  exit;
}

/* ---------- 5) OK: allow real submit/processing ---------- */
echo json_encode(['ok' => true]);
exit;
