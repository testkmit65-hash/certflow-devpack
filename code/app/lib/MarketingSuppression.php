<?php
declare(strict_types=1);

require_once __DIR__ . '/UnsubscribeToken.php';

/**
 * Check if the suppression file contains the given email_hash.
 * Append-only JSONL; safe to scan line-by-line.
 */
function marketing_suppression_has_hash(string $emailHash): bool {
    if (!defined('SUPPRESSION_FILE')) { return false; }
    if (!is_file(SUPPRESSION_FILE)) { return false; }

    $fp = @fopen(SUPPRESSION_FILE, 'r');
    if (!$fp) { return false; }

    // Shared lock (best effort)
    @flock($fp, LOCK_SH);

    while (($line = fgets($fp)) !== false) {
        $line = trim($line);
        if ($line === '') { continue; }
        $obj = json_decode($line, true);
        if (!is_array($obj)) { continue; }
        if (($obj['email_hash'] ?? '') === $emailHash) {
            @flock($fp, LOCK_UN);
            fclose($fp);
            return true;
        }
    }

    @flock($fp, LOCK_UN);
    fclose($fp);
    return false;
}

/**
 * Global marketing suppression check for a plain email (email not stored in token/URL).
 */
function is_suppressed_for_marketing(string $email): bool {
    $emailHash = unsub_email_hash($email);
    return marketing_suppression_has_hash($emailHash);
}
