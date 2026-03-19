<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/UnsubscribeToken.php';

/**
 * Build the unsubscribe endpoint URL from UNSUBSCRIBE_URL.
 * Works whether UNSUBSCRIBE_URL is:
 *  - a base URL (https://example.test)
 *  - an endpoint URL (https://example.test/unsubscribe.php)
 *  - a legacy endpoint (https://example.test/unsubscribe)
 */
function unsub_build_unsubscribe_url_for_token(string $token): string {
    $base = defined('UNSUBSCRIBE_URL') ? (string)UNSUBSCRIBE_URL : '';
    $base = trim($base);
    if ($base === '') { $base = 'https://example.test/unsubscribe'; }

    $url  = $base;
    $parts = @parse_url($url);
    $path  = is_array($parts) ? (string)($parts['path'] ?? '') : '';

    // If path does NOT mention "unsubscribe", treat as base and append endpoint.
    if (stripos($path, 'unsubscribe') === false) {
        $url = rtrim($url, '/') . '/unsubscribe.php';
    }

    $sep = (strpos($url, '?') !== false) ? '&' : '?';
    return $url . $sep . 'token=' . rawurlencode($token);
}

function unsub_build_unsubscribe_url_for_email(string $email): string {
    $token = unsub_make_token($email);
    return unsub_build_unsubscribe_url_for_token($token);
}

function unsub_build_list_unsubscribe_headers(string $email): array {
    $u = unsub_build_unsubscribe_url_for_email($email);
    return [
        'List-Unsubscribe: <' . $u . '>',
        'List-Unsubscribe-Post: List-Unsubscribe=One-Click',
    ];
}
