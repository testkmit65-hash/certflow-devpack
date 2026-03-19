<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../code/app/config.php';
require_once __DIR__ . '/../code/app/lib/UnsubscribeToken.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$token  = (string)($_GET['token'] ?? '');

// Source hint (minimal + safe)
$source = ($method === 'POST') ? 'list-ui' : 'footer';

// Always respond without leaking whether an email exists/was subscribed.
function render_page(string $title, string $message): void {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!doctype html><html lang=\"en\"><head><meta charset=\"utf-8\">";
    echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">";
    echo "<title>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</title>";
    echo "</head><body style=\"font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 40px;\">";
    echo "<h1 style=\"margin:0 0 12px 0; font-size:22px;\">" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</h1>";
    echo "<p style=\"margin:0; font-size:16px; line-height:1.5;\">" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "</p>";
    echo "</body></html>";
}

// Record suppression entry (JSONL, locked, idempotent by email_hash)
function record_marketing_suppression(string $emailHash, string $source): bool {
    if (!defined('SUPPRESSION_FILE')) { return false; }

    $dir = dirname(SUPPRESSION_FILE);
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }

    $fp = @fopen(SUPPRESSION_FILE, 'c+'); // create if missing, read/write
    if (!$fp) { return false; }

    // Exclusive lock to prevent race conditions
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }

    // Check if already present (idempotent)
    $already = false;
    rewind($fp);
    while (($line = fgets($fp)) !== false) {
        $line = trim($line);
        if ($line === '') { continue; }
        $obj = json_decode($line, true);
        if (!is_array($obj)) { continue; }
        if (($obj['email_hash'] ?? '') === $emailHash) {
            $already = true;
            break;
        }
    }

    if (!$already) {
        $entry = [
            'v'               => 1,
            'email_hash'      => $emailHash,
            'unsubscribed_at' => gmdate('c'),
            'source'          => $source,
        ];
        $json = json_encode($entry, JSON_UNESCAPED_SLASHES);
        if ($json !== false) {
            fseek($fp, 0, SEEK_END);
            fwrite($fp, $json . "\n");
        }
    }

    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

// Verify token (safe failure)
$payload = ($token !== '') ? unsub_verify_token($token) : null;

if ($payload && isset($payload['eh']) && is_string($payload['eh'])) {
    // Write suppression entry (global marketing)
    record_marketing_suppression($payload['eh'], $source);

    if ($method === 'POST') {
        // One-click clients expect 200 OK with minimal body
        header('Content-Type: text/plain; charset=utf-8');
        echo "OK";
        exit;
    }

    // GET confirmation page (generic, no leakage)
    render_page(
        'Unsubscribe confirmed',
        'If this email address was eligible for marketing emails, it has now been unsubscribed.'
    );
    exit;
}

// Invalid / missing token: do NOT leak anything.
// For POST: still return 200 OK, minimal body.
if ($method === 'POST') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "OK";
    exit;
}

render_page(
    'Unsubscribe link invalid',
    'This unsubscribe link is invalid or expired. Please use the latest unsubscribe link from an email.'
);
