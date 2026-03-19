<?php
require_once __DIR__ . '/../config.php';
function make_token(string $file_path, int $expires_ts): string {
    $payload = json_encode(['p'=>$file_path, 'e'=>$expires_ts], JSON_UNESCAPED_SLASHES);
    $sig = hash_hmac('sha256', $payload, APP_SECRET, true);
    return rtrim(strtr(base64_encode($payload . '::' . $sig), '+/', '-_'), '=');
}
function parse_token(string $token): ?array {
    $raw = base64_decode(strtr($token, '-_', '+/'), true);
    if ($raw === false) return null;
    $parts = explode('::', $raw);
    if (count($parts) !== 2) return null;
    [$payload_json, $sig] = $parts;
    $expected = hash_hmac('sha256', $payload_json, APP_SECRET, true);
    if (!hash_equals($expected, $sig)) return null;
    $payload = json_decode($payload_json, true);
    if (!$payload || time() > intval($payload['e'] ?? 0)) return null;
    return $payload;
}
?>