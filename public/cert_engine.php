<?php
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../code/app/config.php';

/**
 * Certificate generator (GD -> PNG; Imagick -> PDF when available).
 *
 * Inputs (GET/POST):
 *   name=<string>        (required)
 *   date=YYYY-MM-DD      (optional; defaults to today)
 *   preview=1            (optional; force PNG preview output)
 *   debug=1              (optional; draw span boxes / baselines)
 */

// -------------------- inputs --------------------
$name = trim($_GET['name'] ?? $_POST['name'] ?? '');
if ($name === '') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Missing ?name=...";
    exit;
}
if (mb_strlen($name) > 64) {
    $name = mb_substr($name, 0, 64);
}

$dateISO = trim($_GET['date'] ?? $_POST['date'] ?? '');
if ($dateISO === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateISO)) {
    $dateISO = date('Y-m-d');
}
$ts = strtotime($dateISO) ?: time();
$humanDate = date('F j, Y', $ts); // e.g., October 11, 2025

$forcePreviewPng = isset($_GET['preview']) && $_GET['preview'] == '1';
$debugOverlay     = isset($_GET['debug']) && $_GET['debug'] == '1';

// -------------------- language detection (EN / DE) --------------------
$lang = 'en';

// Prefer explicit ?lang= from the generator URL
if (isset($_GET['lang'])) {
    $c = strtolower(trim((string)$_GET['lang']));
    if (in_array($c, ['en', 'de'], true)) {
        $lang = $c;
    }
}
// Fallback: cookie from the main app, if present
elseif (!empty($_COOKIE['demo_lang'])) {
    $c = strtolower(trim((string)$_COOKIE['demo_lang']));
    if (in_array($c, ['en', 'de'], true)) {
        $lang = $c;
    }
}

// -------------------- background --------------------
$bgCandidates = [];

// DE: prefer German artwork if available
if ($lang === 'de') {
    $bgCandidates[] = __DIR__ . '/../assets/img/certificate-bg-de@300.png';
    $bgCandidates[] = dirname(__DIR__) . '/assets/img/certificate-bg-de@300.png';
    $bgCandidates[] = __DIR__ . '/../assets/img/certificate-bg-de@300';
}

// Always append EN fallbacks (safe default)
$bgCandidates[] = __DIR__ . '/../assets/img/certificate-bg@300.png';
$bgCandidates[] = dirname(__DIR__) . '/assets/img/certificate-bg@300.png';
$bgCandidates[] = __DIR__ . '/../assets/img/certificate-bg@300';

$bgPathFs = null;
foreach ($bgCandidates as $p) {
    if (is_file($p)) { $bgPathFs = $p; break; }
}
if (!$bgPathFs) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Background not found: assets/img/certificate-bg@300.png";
    exit;
}

$im = @imagecreatefrompng($bgPathFs);
if (!$im) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Could not open background image.";
    exit;
}
imagesavealpha($im, true);

$W = imagesx($im); // expected 2480
$H = imagesy($im); // expected 3508

// -------------------- units & helpers --------------------
$mm2px = static function (float $mm): int {
    // 300 dpi → 300 px/in; 1 in = 25.4 mm
    return (int) round($mm * 300 / 25.4);
};
$pt2px_300dpi = static function (float $pt): int {
    return (int) round($pt * 300 / 72);
};

$bbox = static function (int $pt, string $fontFile, string $text): array {
    return imagettfbbox($pt, 0, $fontFile, $text);
};
$measure_width = static function (int $pt, string $fontFile, string $text) use ($bbox): int {
    $b = $bbox($pt, $fontFile, $text);
    return (int) (max($b[0], $b[2], $b[4], $b[6]) - min($b[0], $b[2], $b[4], $b[6]));
};
$measure_height = static function (int $pt, string $fontFile, string $text) use ($bbox): int {
    $b = $bbox($pt, $fontFile, $text);
    $ys = [$b[1], $b[3], $b[5], $b[7]];
    return (int) (max($ys) - min($ys));
};

/**
 * Find a point size so the TEXT HEIGHT ≈ targetPx (±1) at 300dpi,
 * clamped to [minPt, maxPt]. Binary-search for stability.
 */
$pt_for_target_height = static function (
    string $text, string $fontFile, int $targetPx, int $minPt, int $maxPt
) use ($measure_height): int {
    $lo = $minPt;
    $hi = max($maxPt, $minPt + 1);
    // expand hi until height >= target or cap at 256pt
    while ($measure_height($hi, $fontFile, $text) < $targetPx && $hi < 256) {
        $hi += max(1, (int) round(($hi - $lo) / 2));
    }
    // binary search
    for ($i = 0; $i < 16; $i++) {
        $mid = (int) floor(($lo + $hi) / 2);
        $h = $measure_height($mid, $fontFile, $text);
        if ($h >= $targetPx) $hi = max($minPt, $mid);
        else                 $lo = min($hi, $mid + 1);
        if (abs($h - $targetPx) <= 1) return max($minPt, min($mid, $maxPt));
    }
    return max($minPt, min($hi, $maxPt));
};

/**
 * Shrink point size until width <= maxWidthPx (never below minPt).
 */
$shrink_to_fit_width = static function (
    string $text, string $fontFile, int $pt, int $minPt, int $maxWidthPx
) use ($measure_width): int {
    $s = $pt;
    while ($s > $minPt && $measure_width($s, $fontFile, $text) > $maxWidthPx) {
        $s--;
    }
    return $s;
};

$draw_centered_in_span = static function (
    $im, string $text, string $fontFile, int $pt, int $baselineYpx,
    int $spanLeftPx, int $spanRightPx, int $color
): array {
    $b = imagettfbbox($pt, 0, $fontFile, $text);
    $textW = (int) (max($b[0], $b[2], $b[4], $b[6]) - min($b[0], $b[2], $b[4], $b[6]));
    $centerX = (int) round(($spanLeftPx + $spanRightPx) / 2);
    $x = (int) round($centerX - ($textW / 2));
    imagettftext($im, $pt, 0, $x, $baselineYpx, $color, $fontFile, $text);
    return [$x, $baselineYpx, $textW];
};

// -------------------- fonts --------------------
$FONT_NAME = PROJ_ROOT . '/assets/fonts/HamiltonScript-Regular.ttf'; // Name
$FONT_DATE = PROJ_ROOT . '/assets/fonts/Nunito-Regular.ttf';         // Date

$hasHamilton = is_file($FONT_NAME);
$hasNunito   = is_file($FONT_DATE);
if (!$hasHamilton && $hasNunito) {
    $FONT_NAME = $FONT_DATE; // fallback
}

$ink = imagecolorallocate($im, 20, 20, 20); // deep gray

// -------------------- SPEC LAYOUT (in mm; converted to px) --------------------
// PAGE: A4 portrait 210×297 mm (2480×3508 px @300 dpi)

// --- final baselines you confirmed ---
$NAME_BASELINE_MM = 179.8;   // was 180.0
$DATE_BASELINE_MM = 253.8;   // was 254.0

// NAME (Hamilton Script Regular)
$NAME_LEFT_PX     = $mm2px(46.0);   // your tweak (was 47.0)
$NAME_RIGHT_PX    = $mm2px(164.0);
$NAME_BASELINE_PX = $mm2px($NAME_BASELINE_MM);
$NAME_MAX_W       = $NAME_RIGHT_PX - $NAME_LEFT_PX;
$NAME_TARGET_H_PX = $pt2px_300dpi(48);   // target visual height for 48pt at 300dpi = 200px
$NAME_MIN_H_PX    = $pt2px_300dpi(28);   // min visual height for 28pt ≈117px
$NAME_MIN_PT      = 6;                   // guard (won't be hit in normal cases)

// DATE (Nunito Regular)
$DATE_LEFT_PX     = $mm2px(129.0);  // your tweak (was 129.5)
$DATE_RIGHT_PX    = $mm2px(168.5);
$DATE_BASELINE_PX = $mm2px($DATE_BASELINE_MM);
$DATE_MAX_W       = $DATE_RIGHT_PX - $DATE_LEFT_PX;
$DATE_TARGET_H_PX = $pt2px_300dpi(12);   // 12pt -> 50px
$DATE_MIN_PT      = 6;                   // guard

// (Optional) debug overlays to visualize spans & baselines
if ($debugOverlay) {
    imagesavealpha($im, true);
    $blue  = imagecolorallocatealpha($im, 0, 120, 255, 80);
    $green = imagecolorallocatealpha($im, 0, 200,   0, 80);
    // Name span box & baseline
    imagefilledrectangle($im, $NAME_LEFT_PX,  $NAME_BASELINE_PX-36, $NAME_RIGHT_PX, $NAME_BASELINE_PX+10, $blue);
    imageline($im, $NAME_LEFT_PX, $NAME_BASELINE_PX, $NAME_RIGHT_PX, $NAME_BASELINE_PX, $blue);
    // Date span box & baseline
    imagefilledrectangle($im, $DATE_LEFT_PX,  $DATE_BASELINE_PX-24, $DATE_RIGHT_PX, $DATE_BASELINE_PX+10, $green);
    imageline($im, $DATE_LEFT_PX, $DATE_BASELINE_PX, $DATE_RIGHT_PX, $DATE_BASELINE_PX, $green);
}

// -------------------- render text --------------------
if (function_exists('imagettfbbox') && function_exists('imagettftext') && is_file($FONT_NAME)) {

    // --- NAME: choose a point size that yields 200 px height, then shrink to fit width ---
    $namePtHeight = $pt_for_target_height($name, $FONT_NAME, $NAME_TARGET_H_PX, $NAME_MIN_PT, 256);
    // Also clamp height to >= 28pt visual (~117 px) and <= 48pt visual (200 px)
    $namePt = $namePtHeight;
    // Now ensure width ≤ span width
    $namePt = $shrink_to_fit_width($name, $FONT_NAME, $namePt, $NAME_MIN_PT, $NAME_MAX_W);
    // Also never let the resulting visual height go *below* the 28pt target unless name is extremely long
    $nameHeightNow = $measure_height($namePt, $FONT_NAME, $name);
    if ($nameHeightNow < $NAME_MIN_H_PX && $namePt < 256) {
        // (best effort) try to bump a little closer to min visual height without overflowing width
        $try = $namePt + 1;
        while ($try <= 256 && $measure_height($try, $FONT_NAME, $name) <= $NAME_MIN_H_PX && $measure_width($try, $FONT_NAME, $name) <= $NAME_MAX_W) {
            $namePt = $try;
            $try++;
        }
    }
    $draw_centered_in_span($im, $name, $FONT_NAME, $namePt, $NAME_BASELINE_PX, $NAME_LEFT_PX, $NAME_RIGHT_PX, $ink);

    // --- DATE: choose a point size that yields 50 px height, then shrink if width > span ---
    $dateFont = $hasNunito ? $FONT_DATE : $FONT_NAME;
    $datePt   = $pt_for_target_height($humanDate, $dateFont, $DATE_TARGET_H_PX, $DATE_MIN_PT, 128);
    $datePt   = $shrink_to_fit_width($humanDate, $dateFont, $datePt, $DATE_MIN_PT, $DATE_MAX_W);

    $draw_centered_in_span($im, $humanDate, $dateFont, $datePt, $DATE_BASELINE_PX, $DATE_LEFT_PX, $DATE_RIGHT_PX, $ink);

} else {
    // Bitmap fallback
    $font = 5;
    // Name
    $tw = imagefontwidth($font) * strlen($name);
    $th = imagefontheight($font);
    $cx = (int) round(($NAME_LEFT_PX + $NAME_RIGHT_PX) / 2);
    $x  = (int) round($cx - $tw / 2);
    imagestring($im, $font, $x, $NAME_BASELINE_PX - (int)round($th/2), $name, $ink);
    // Date
    $ds = $humanDate;
    $dw = imagefontwidth($font) * strlen($ds);
    $dh = imagefontheight($font);
    $dcx= (int) round(($DATE_LEFT_PX + $DATE_RIGHT_PX) / 2);
    $dx = (int) round($dcx - $dw / 2);
    imagestring($im, $font, $dx, $DATE_BASELINE_PX - (int)round($dh/2), $ds, $ink);
}

// -------------------- temp PNG --------------------
$tmpDir = sys_get_temp_dir();
$tmpPng = $tmpDir . '/cert_' . uniqid('', true) . '.png';
imagepng($im, $tmpPng);
imagedestroy($im);

// -------------------- output --------------------
// PDF if Imagick exists and preview is NOT forced; otherwise PNG.
if (!$forcePreviewPng && class_exists('Imagick')) {
    try {
        $pdf = new Imagick();
        $pdf->readImage($tmpPng);
        $pdf->setImageFormat('pdf');

        header('Content-Type: application/pdf');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Content-Disposition: inline; filename="certificate.pdf"');

        echo $pdf->getImagesBlob();
        @unlink($tmpPng);
        exit;
    } catch (Throwable $e) {
        // fall through to PNG if PDF conversion fails
    }
}

// default: PNG (no Imagick, or preview explicitly requested)
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
readfile($tmpPng);
@unlink($tmpPng);
exit;