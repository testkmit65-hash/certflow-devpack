<?php
declare(strict_types=1);

// --- BYPASS FOR UI WORK ---
session_start();
$_SESSION['flash'] = [];
$_SESSION['form_data'] = [
    'first_name' => $_POST['first_name'] ?? 'Jane',
    'last_name' => $_POST['last_name'] ?? 'Doe',
    'email' => $_POST['email'] ?? 'test@example.com'
];
$_SESSION['cert_url'] = '/download.php?f=cert';
$_SESSION['bonus_url'] = '/download.php?f=bonus';

// Show loading for 5 seconds
sleep(5);

header('Location: success.php?rid=dummy');
exit;
// --------------------------

// --- ORIGINAL CODE BELOW ---
// ini_set('display_errors', '1');
// error_reporting(E_ALL);
// 
// require_once __DIR__ . '/../code/app/config.php';
// require_once __DIR__ . '/../code/app/lib/Utils.php';
// require_once __DIR__ . '/../code/app/Cooldown.php';
// 
// use App\Cooldown;
// 
// // Only allow POST (prevent direct access)
// if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
//   header('Location: ./');
//   exit;
// }
// 
// // Helper to set flash + redirect back to form
// function flash_and_back(array $data): void {
//   if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
//   $_SESSION['flash'] = $data;
//   header('Location: index.php');
//   exit;
// }
// 
// /* ---------- 1) Collect and normalize inputs ---------- */
// $first  = trim((string)($_POST['first_name'] ?? ''));
// $last   = trim((string)($_POST['last_name']  ?? ''));
// $email  = trim((string)($_POST['email']      ?? ''));
// $email  = strtolower(trim(preg_replace('/^mailto:\s*/i', '', $email)));
// $keyRaw = '';
// foreach (['key_code','key','secret_key','code','access_code','challenge_code'] as $k) {
//   if (isset($_POST[$k])) { $keyRaw = (string)$_POST[$k]; break; }
// }
// $key = strtoupper(preg_replace('/\s+/', '', trim($keyRaw))); // remove spaces; UI hints A–Z, 0–9
// 
// $old = ['first_name'=>$first, 'last_name'=>$last, 'email'=>$email, 'key'=>$keyRaw];
// 
// /* ---------- 1a) Language-aware messages (EN / DE) ---------- */
// $lang = 'en';
// if (!empty($_COOKIE['demo_lang'])) {
//   $c = strtolower(trim((string)$_COOKIE['demo_lang']));
//   if (in_array($c, ['en','de'], true)) {
//     $lang = $c;
//   }
// }
// 
// $msgTable = [
//   'en' => [
//     'all_required' => 'Please fill all required fields.',
//     'secret'       => 'Invalid or missing Secret Key Code, please try again.',
//     'cooldown'     => 'You can resubmit your form in %d minutes.',
//   ],
//   'de' => [
//     'all_required' => 'Bitte fülle alle Pflichtfelder aus.',
//     'secret'       => 'Ungültiger oder fehlender geheimer Schlüsselcode. Bitte versuche es erneut.',
//     'cooldown'     => 'Du kannst das Formular in %d Minuten erneut senden.',
//   ],
// ];
// $msg = $msgTable[$lang] ?? $msgTable['en'];
// 
// /* ---------- 2) Missing core fields (ONLY first/last/email) ---------- */
// /* Rule:
//    - If any of these are empty → show the generic "all required" message.
//    - Secret Key Code is handled separately further below.
// */
// if ($first === '' || $last === '' || $email === '') {
//   // Log a validation error for this email (if present)
//   log_email_attempt($email, 'validation_error', 'missing_required_fields', $lang);
// 
//   flash_and_back([
//     'error' => $msg['all_required'],
//     'old'   => $old,
//   ]);
// }
// 
// /* ---------- 3) Secret Key Code validation (missing OR invalid) ---------- */
// /* Rule:
//    - At this point, first/last/email are all present.
//    - If key is empty OR has the wrong format → show ONLY the secret-key message.
//    - Cooldown is NOT checked while there is a key error.
// */
// if ($key === '' || !preg_match('/^[A-Z0-9]{15}$/', $key)) {
//   // Log a validation error specifically for the Secret Key Code
//   log_email_attempt($email, 'validation_error', 'invalid_secret_key', $lang);
// 
//   flash_and_back([
//     'error' => $msg['secret'],
//     'old'   => $old,
//   ]);
// }
// 
// /* ---------- 3b) Secret Key Code must match configured code ---------- */
// $expected = defined('SECRET_KEY_CODE') ? SECRET_KEY_CODE : '';
// if ($expected !== '' && !hash_equals($expected, $key)) {
//   log_email_attempt($email, 'validation_error', 'secret_key_mismatch', $lang);
// 
//   flash_and_back([
//     'error' => $msg['secret'],
//     'old'   => $old,
//   ]);
// }
// 
// /* ---------- 4) Cooldown check (per email) ---------- */
// /* Rule:
//    - Reached only if all fields (including key) are valid.
//    - If cooldown is active, show ONLY the cooldown message.
// */
// $remaining = Cooldown::remainingMinutes($email, COOLDOWN_MINUTES);
// if ($remaining > 0) {
//   $cooldownText = sprintf($msg['cooldown'], $remaining);
// 
//   // Log a cooldown hit for this email
//   log_email_attempt($email, 'cooldown', 'cooldown_active', $lang);
// 
//   flash_and_back([
//     'cooldown' => $cooldownText,
//     'old'      => $old,
//   ]);
// }
// 
// /* ---------- 5) Success: hand off to your original flow ---------- */
// // Log a successful attempt for this email
// log_email_attempt($email, 'success', 'ok', $lang);
// 
// Cooldown::mark($email); // start 30-min window on accepted submit
// 
// // Clear any stray flash and inline vars on success (belt-and-suspenders)
// if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
// unset($_SESSION['flash']);
// unset($inlineMsg, $cooldownMessage);
// 
// // Continue with your previous processing (email sending, certificate, etc.)
// require __DIR__ . '/submit.php.bak';
// exit;