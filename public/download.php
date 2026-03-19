<?php
declare(strict_types=1);
require_once __DIR__ . '/../code/app/config.php';
require_once __DIR__ . '/../code/app/lib/Token.php';

$t = $_GET['t'] ?? '';
$payload = $t ? parse_token($t) : null;
if (!$payload) { http_response_code(403); echo "Invalid or expired link."; exit; }
$path = $payload['p'];
$real = realpath($path);
$stor = realpath(STORAGE_DIR);
if (!$real || strpos($real, $stor) !== 0 || !is_file($real)) { http_response_code(404); echo "File not found."; exit; }
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($real) . '"');
header('Content-Length: ' . filesize($real));
readfile($real);
?>