<?php
/**
 * Unsubscribe token helper (stateless, HMAC signed).
 *
 * Token format: <base64url(payload_json)>.<base64url(hmac_sha256(payload_b64))>
 *
 * Payload fields:
 * - v   : version (int)
 * - eh  : email hash (hex sha256 of canonical email)
 * - iat : issued-at unix timestamp (int)
 */

function unsub_canonical_email(string $email): string {
    return strtolower(trim($email));
}

function unsub_email_hash(string $email): string {
    $canon = unsub_canonical_email($email);
    // hex sha256 (64 chars)
    return hash('sha256', $canon);
}

function unsub_b64url_encode(string $bin): string {
    return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
}

function unsub_b64url_decode(string $b64url): string|false {
    $b64 = strtr($b64url, '-_', '+/');
    $pad = strlen($b64) % 4;
    if ($pad) { $b64 .= str_repeat('=', 4 - $pad); }
    return base64_decode($b64, true);
}

function unsub_sign(string $payload_b64): string {
    if (!defined('UNSUBSCRIBE_SECRET')) {
        // config.php should define it; fall back hard if not present
        throw new RuntimeException('UNSUBSCRIBE_SECRET is not defined');
    }
    // Return raw binary signature
    return hash_hmac('sha256', $payload_b64, UNSUBSCRIBE_SECRET, true);
}

/**
 * Generate a signed token for an email.
 * Does NOT include the plain email, only email_hash.
 */
function unsub_make_token(string $email): string {
    $payload = [
        'v'   => 1,
        'eh'  => unsub_email_hash($email),
        'iat' => time(),
    ];

    $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Failed to encode unsubscribe payload');
    }

    $payload_b64 = unsub_b64url_encode($json);
    $sig_bin     = unsub_sign($payload_b64);
    $sig_b64     = unsub_b64url_encode($sig_bin);

    return $payload_b64 . '.' . $sig_b64;
}

/**
 * Verify token signature and basic structure.
 * Returns array with payload fields on success, or null on failure.
 * Safe failure: does not leak which part failed.
 */
function unsub_verify_token(string $token): ?array {
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) { return null; }

    [$payload_b64, $sig_b64] = $parts;

    $sig_bin = unsub_b64url_decode($sig_b64);
    if ($sig_bin === false) { return null; }

    $expected_sig = unsub_sign($payload_b64);
    if (!hash_equals($expected_sig, $sig_bin)) { return null; }

    $json = unsub_b64url_decode($payload_b64);
    if ($json === false) { return null; }

    $payload = json_decode($json, true);
    if (!is_array($payload)) { return null; }

    // Validate fields
    if (!isset($payload['v'], $payload['eh'], $payload['iat'])) { return null; }
    if ((int)$payload['v'] !== 1) { return null; }
    if (!is_string($payload['eh']) || !preg_match('/^[a-f0-9]{64}$/', $payload['eh'])) { return null; }
    if (!is_int($payload['iat']) && !ctype_digit((string)$payload['iat'])) { return null; }

    // Normalize iat to int
    $payload['iat'] = (int)$payload['iat'];

    return $payload;
}
