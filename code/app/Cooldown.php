<?php
declare(strict_types=1);

namespace App;

final class Cooldown
{
    private static function dir(): string
    {
        // Use configured requests directory if available
        if (defined('REQUESTS_DIR')) {
            return rtrim((string)REQUESTS_DIR, '/');
        }
        // Fallback to storage/requests under project root
        $base = defined('STORAGE_DIR')
            ? rtrim((string)STORAGE_DIR, '/')
            : rtrim(dirname(__DIR__, 2) . '/storage', '/'); // __DIR__ = /code/app
        return $base . '/requests';
    }

    private static function key(string $email): string
    {
        return sha1(strtolower(trim($email)));
    }

    private static function path(string $email): string
    {
        return self::dir() . '/' . self::key($email) . '.json';
    }

    /** Returns remaining full minutes (ceil), or 0 if no cooldown. */
    public static function remainingMinutes(string $email, int $minutes): int
    {
        $file = self::path($email);
        if (!is_file($file)) return 0;

        $json = @file_get_contents($file);
        $data = $json ? json_decode($json, true) : null;
        $last = is_array($data) && isset($data['ts']) ? (int)$data['ts'] : 0;
        if ($last <= 0) return 0;

        $remain = ($minutes * 60) - (time() - $last);
        return ($remain > 0) ? (int)ceil($remain / 60) : 0;
    }

    /** Records a submission time for this email (now). */
    public static function mark(string $email): void
    {
        $dir = self::dir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $payload = json_encode(['ts' => time()], JSON_UNESCAPED_SLASHES);
        @file_put_contents(self::path($email), $payload, LOCK_EX);
    }
}
