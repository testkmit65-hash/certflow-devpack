<?php
declare(strict_types=1);

/**
 * Simple, reliable i18n loader for DemoBrand.
 * - GET ?lang=en|de overrides and sets a cookie
 * - Cookie persists selection
 * - EN is the fallback; DE overrides keys present in de.php
 */

const DEMO_LANG_DEFAULT = 'en';
const DEMO_LANGS = ['en','de'];

function demo_get_lang(): string {
    // 1) URL param wins and sets cookie
    if (isset($_GET['lang'])) {
        $lang = strtolower(trim((string)$_GET['lang']));
        if (in_array($lang, DEMO_LANGS, true)) {
            setcookie('demo_lang', $lang, [
                'expires'  => time() + 60*60*24*180, // 180 days
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
            return $lang;
        }
    }
    // 2) cookie next
    if (!empty($_COOKIE['demo_lang'])) {
        $cookie = strtolower(trim((string)$_COOKIE['demo_lang']));
        if (in_array($cookie, DEMO_LANGS, true)) return $cookie;
    }
    // 3) fallback
    return DEMO_LANG_DEFAULT;
}

function demo_load_translations(string $lang): array {
    $base = __DIR__ . '/lang';
    $en = is_file($base . '/en.php') ? include $base . '/en.php' : [];
    if ($lang === 'en') return $en;

    $loc = is_file($base . '/' . $lang . '.php') ? include $base . '/' . $lang . '.php' : [];
    // Local overrides English; any missing keys fall back to EN
    return array_replace($en, $loc);
}

function t(string $key): string {
    static $cache = null;
    if ($cache === null) {
        $cache = demo_load_translations(demo_get_lang());
    }
    return $cache[$key] ?? $key;
}