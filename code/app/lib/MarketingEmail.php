<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/MarketingSuppression.php';
require_once __DIR__ . '/UnsubscribeLink.php';

/**
 * Send a MARKETING email with suppression enforcement.
 *
 * Returns:
 *  - ['sent' => bool, 'skipped' => bool, 'reason' => string]
 *
 * Options:
 *  - dry_run (bool) default false  -> if true, never sends (useful for tests)
 *  - content_type (string) default 'text/html'
 *  - reply_to (string|null) default 'no-reply@example.test'
 *  - from_addr (string) default EMAIL_FROM
 *  - from_name (string) default SMTP_FROM_NAME
 */
function send_marketing_email(string $to, string $subject, string $body, array $opts = []): array
{
    $to = strtolower(trim($to));
    if ($to === '') {
        return ['sent' => false, 'skipped' => true, 'reason' => 'empty_to'];
    }

    // 1) Enforce global marketing suppression
    if (is_suppressed_for_marketing($to)) {
        return ['sent' => false, 'skipped' => true, 'reason' => 'suppressed'];
    }

    $dryRun      = (bool)($opts['dry_run'] ?? false);
    $contentType = (string)($opts['content_type'] ?? 'text/html');
    $replyTo     = $opts['reply_to'] ?? 'no-reply@example.test';

    $fromAddr = (string)($opts['from_addr'] ?? (defined('EMAIL_FROM') ? EMAIL_FROM : 'challenge@example.test'));
    $fromName = (string)($opts['from_name'] ?? (defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : (defined('BRAND_NAME') ? BRAND_NAME : 'DemoBrand')));

    if ($dryRun) {
        return ['sent' => false, 'skipped' => false, 'reason' => 'dry_run'];
    }

    $luHeaders = unsub_build_list_unsubscribe_headers($to);
    $unsubUrl  = unsub_build_unsubscribe_url_for_email($to);
    $body      = marketing_append_unsub_footer($body, $unsubUrl, $contentType);


    // 2) Send via SMTP if available, else PHP mail() fallback
    $useSmtp  = defined('SMTP_USE') ? (bool)SMTP_USE : true;
    $hasCreds = defined('SMTP_HOST') && SMTP_HOST !== ''
             && defined('SMTP_USERNAME') && SMTP_USERNAME !== ''
             && defined('SMTP_PASSWORD') && SMTP_PASSWORD !== '';

    if ($useSmtp && $hasCreds) {
        if (!function_exists('smtp_send')) {
            require_once PROJ_ROOT . '/code/app/lib/Smtp.php';
        }
        $ok = @smtp_send($to, $subject, $body, $fromAddr, $fromName, $replyTo, $contentType, $luHeaders);
        return ['sent' => (bool)$ok, 'skipped' => false, 'reason' => $ok ? 'sent_smtp' : 'smtp_failed'];
    }

    // mail() fallback
    $headers = [
        'From: ' . ($fromName ? "{$fromName} <{$fromAddr}>" : $fromAddr),
        'Reply-To: ' . ($replyTo ?: 'no-reply@example.test'),
        'MIME-Version: 1.0',
        "Content-Type: {$contentType}; charset=UTF-8",
        'Content-Transfer-Encoding: 8bit',
    ];
    
    $headers = array_merge($headers, $luHeaders);

    $ok = @mail($to, $subject, $body, implode("\r\n", $headers));
    return ['sent' => (bool)$ok, 'skipped' => false, 'reason' => $ok ? 'sent_mail' : 'mail_failed'];
}

function marketing_append_unsub_footer(string $body, string $unsubUrl, string $contentType): string
{
    // Only append to HTML messages
    if (stripos($contentType, 'text/html') === false) {
        return $body;
    }

    $footer = ''
    . '<hr style="border:0;border-top:1px solid #ddd;margin:24px 0;">'
    . '<p style="font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.4;color:#555;margin:0;">'
    . '<a href="' . htmlspecialchars($unsubUrl, ENT_QUOTES, 'UTF-8') . '" rel="nofollow">Unsubscribe / Abmelden</a>'
    . '</p>';

    // If a full HTML doc exists, inject before </body> or </html>
    if (stripos($body, '</body>') !== false) {
        return preg_replace('~</body>~i', $footer . '</body>', $body, 1) ?? ($body . $footer);
    }
    if (stripos($body, '</html>') !== false) {
        return preg_replace('~</html>~i', $footer . '</html>', $body, 1) ?? ($body . $footer);
    }

    return $body . $footer;
}

