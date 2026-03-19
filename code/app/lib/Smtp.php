<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

/**
 * Minimal SMTP sender (TLS/SSL) with strict reply checking.
 * - AUTHs with the real mailbox (SMTP_USERNAME / SMTP_PASSWORD)
 * - Envelope sender = authenticated mailbox (best deliverability)
 * - Header From = EMAIL_FROM (your alias) + SMTP_FROM_NAME (brand)
 */
function smtp_send(
    string $to,
    string $subject,
    string $body,
    string $headerFrom,
    string $fromName = '',
    ?string $replyTo = null,
    string $contentType = 'text/html',
    array $extra_headers = []
): bool {
    $host   = SMTP_HOST;
    $port   = (int) SMTP_PORT;
    $secure = strtolower(SMTP_SECURE);
    $user   = SMTP_USERNAME;
    $pass   = SMTP_PASSWORD;

    // ---- connect (implicit SSL only for 465) ----
    $remote = ($secure === 'ssl' ? 'ssl://' : '') . $host;
    $fp = @fsockopen($remote, $port, $errno, $errstr, 20);
    if (!$fp) { return false; }

    $read = function() use ($fp): string {
        $resp = '';
        while (!feof($fp)) {
            $line = fgets($fp, 1000);
            if ($line === false) break;
            $resp .= $line;
            // end of multiline: "250 " (note space)
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $resp;
    };
    $code = function(string $resp): int {
        return (preg_match('/^(\d{3})/m', $resp, $m)) ? (int)$m[1] : 0;
    };
    $write = function(string $cmd) use ($fp): void {
        fwrite($fp, $cmd . "\r\n");
    };

    // ---- 220 banner ----
    $resp = $read();
    if ($code($resp) !== 220) { fclose($fp); return false; }

    // EHLO hostname (avoid localhost)
    $hostName = parse_url(getenv('BASE_URL') ?: '', PHP_URL_HOST) ?: 'example.test';
    if ($hostName === '' || $hostName === 'localhost') { $hostName = 'example.test'; }

    // ---- EHLO ----
    $write("EHLO {$hostName}");
    $resp = $read();
    if ($code($resp) !== 250) { fclose($fp); return false; }

    // ---- STARTTLS for TLS on 587 ----
    if ($secure === 'tls') {
        $write("STARTTLS");
        $resp = $read();
        if ($code($resp) !== 220) { fclose($fp); return false; }

        if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($fp); return false;
        }

        // EHLO again after TLS
        $write("EHLO {$hostName}");
        $resp = $read();
        if ($code($resp) !== 250) { fclose($fp); return false; }
    }

    // ---- AUTH LOGIN ----
    $write("AUTH LOGIN");
    $resp = $read();
    if ($code($resp) !== 334) { fclose($fp); return false; }

    $write(base64_encode($user));
    $resp = $read();
    if ($code($resp) !== 334) { fclose($fp); return false; }

    $write(base64_encode($pass));
    $resp = $read();
    if ($code($resp) !== 235) { fclose($fp); return false; }

    // ---- Envelope sender & recipient ----
    $envelopeFrom = $user; // keep as the authenticated mailbox
    $write("MAIL FROM:<{$envelopeFrom}>");
    $resp = $read();
    $c = $code($resp);
    if (!in_array($c, [250, 251], true)) { fclose($fp); return false; }

    $write("RCPT TO:<{$to}>");
    $resp = $read();
    $c = $code($resp);
    if (!in_array($c, [250, 251], true)) { fclose($fp); return false; }

    // ---- DATA ----
    $write("DATA");
    $resp = $read();
    if ($code($resp) !== 354) { fclose($fp); return false; }

    // Build headers (visible From = alias)
    $fromHeader = $fromName ? "{$fromName} <{$headerFrom}>" : $headerFrom;
    $headers = [
        "From: {$fromHeader}",
        "To: <{$to}>",
        "Subject: ".$subject,
        "MIME-Version: 1.0",
        "Content-Type: {$contentType}; charset=UTF-8",
        "Content-Transfer-Encoding: 8bit",
        "Date: " . date('r'),
        "Message-ID: <" . uniqid('', true) . "@example.test>"
    ];
    if ($replyTo) $headers[] = "Reply-To: {$replyTo}";

    // Optional extra headers (e.g., List-Unsubscribe) — safe, blocks header injection
if (!empty($extra_headers)) {
    foreach ($extra_headers as $k => $v) {
        $line = is_int($k) ? (string)$v : (string)$k . ': ' . (string)$v;
        $line = trim($line);
        if ($line === '') { continue; }
        if (preg_match("/[\r\n]/", $line)) { continue; } // block injection
        $headers[] = $line;
    }
}

    // Normalize newlines + dot-stuffing
    $body = str_replace(["\r\n", "\r"], "\n", $body);
    $body = str_replace("\n.", "\n..", $body);

    $payload = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";

    $write($payload);
    $resp = $read();
    $ok = ($code($resp) === 250);

    $write("QUIT");
    fclose($fp);
    return $ok;
}