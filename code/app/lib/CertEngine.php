<?php
declare(strict_types=1);

namespace App\Lib;

/**
 * CertEngine
 * - Renders Name + Date onto /assets/img/certificate-bg@300.png
 * - Auto-sizes the Name to fit a max width and centers it horizontally
 * - Writes a PNG to /storage/runtime/cert_preview_<hash>.png
 * - Returns detailed metrics for debugging/tuning
 *
 * Usage:
 *   $res = \App\Lib\CertEngine::render_to_png_file("Jane Doe", null, true);
 *   if ($res['ok']) { // $res['png'] contains the file path }
 */
final class CertEngine
{
    // ---- Tunables: adjust if you want small nudges without touching the math ----
    private const NAME_MAX_WIDTH_PCT = 0.72;  // Max width of name text vs. canvas width
    private const NAME_Y_PCT         = 0.625; // Baseline Y position (0..1) of name
    private const DATE_Y_PCT         = 0.705; // Baseline Y position (0..1) of date
    private const DATE_FONT_REL_H    = 0.033; // Date font size as % of image height
    private const NAME_FONT_MIN      = 48.0;  // clamp lower bound
    private const NAME_FONT_MAX      = 240.0; // clamp upper bound
    private const STROKE_PCT_H       = 0.0015;// outline thickness vs. height
    private const NAME_COLOR_RGB     = [34,34,34];
    private const STROKE_COLOR_RGB   = [255,255,255];
    private const DATE_COLOR_RGB     = [34,34,34];

    /**
     * Renders the certificate to a PNG file in /storage/runtime and returns info.
     *
     * @param string      $recipientName Name to print (required, non-empty)
     * @param string|null $dateISO       Date (YYYY-MM-DD) or null for "today"
     * @param bool        $debug         If true, draws debug boxes & returns metrics
     * @return array{
     *   ok:bool, png?:string, w?:int, h?:int, font_size?:float, date_text?:string,
     *   name_bbox?:array, max_width?:int, errors?:array
     * }
     */
    public static function render_to_png_file(string $recipientName, ?string $dateISO = null, bool $debug = false): array
    {
        $errors = [];

        $recipientName = trim($recipientName);
        if ($recipientName === '') {
            return ['ok' => false, 'errors' => ['Empty recipient name']];
        }

        // Resolve project root from /code/app/lib
        $root = \dirname(__DIR__, 3);

        // Background image
        $bgPath = $root . '/assets/img/certificate-bg@300.png';
        if (!is_file($bgPath)) {
            return ['ok' => false, 'errors' => ["Background not found: {$bgPath}"]];
        }

        // Font resolution: try Hamilton Script for name, Nunito/Avenir for date
        $fontName = self::findFont($root, ['*Hamilton*Script*.ttf', '*Hamilton*Script*.otf']);
        if ($fontName === null) {
            // graceful fallback: try any fancy script or default to Nunito
            $fontName = self::findFont($root, ['*Script*.ttf', '*Nunito*Regular*.ttf', '*Avenir*Book*.ttf']);
        }
        $fontDate = self::findFont($root, ['*Avenir*Book*.ttf', '*Nunito*Regular*.ttf', '*Nunito Sans*Regular*.ttf', '*Avenir*.ttf', '*Nunito*.ttf']);
        if ($fontDate === null) {
            $fontDate = $fontName; // last resort: same as name
        }
        if ($fontName === null || $fontDate === null) {
            return ['ok' => false, 'errors' => ['No suitable fonts found in /assets/fonts (Hamilton/Nunito/Avenir).']];
        }

        // Load background
        $im = @imagecreatefrompng($bgPath);
        if (!$im) {
            return ['ok' => false, 'errors' => ["Failed to load PNG: {$bgPath}"]];
        }
        $w = imagesx($im);
        $h = imagesy($im);

        // Colors
        $nameColor   = imagecolorallocate($im, ...self::NAME_COLOR_RGB);
        $strokeColor = imagecolorallocate($im, ...self::STROKE_COLOR_RGB);
        $dateColor   = imagecolorallocate($im, ...self::DATE_COLOR_RGB);

        // Calculate date text
        $date = null;
        if ($dateISO) {
            try {
                $date = new \DateTime($dateISO);
            } catch (\Throwable $e) {
                $errors[] = "Invalid date '{$dateISO}', falling back to today.";
            }
        }
        if (!$date) {
            $date = new \DateTime('now');
        }
        // Format: October 13, 2025
        $dateText = $date->format('F j, Y');

        // Target metrics
        $maxWidth = (int)\floor($w * self::NAME_MAX_WIDTH_PCT);
        $nameY    = (int)\round($h * self::NAME_Y_PCT);
        $dateY    = (int)\round($h * self::DATE_Y_PCT);

        // Auto-size name using binary search within bounds
        [$fontSize, $bbox] = self::findFittingFontSize($recipientName, $fontName, $maxWidth, self::NAME_FONT_MIN, self::NAME_FONT_MAX);

        // Compute X positions centered using bbox
        $nameX = self::centerXFromBBox($bbox, $w);
        $dateFontSize = max(10.0, $h * self::DATE_FONT_REL_H);
        $dateBBox = self::ttfBBox($dateFontSize, 0.0, $fontDate, $dateText);
        $dateX = self::centerXFromBBox($dateBBox, $w);

        // Optional debug guides (draw max width box)
        if ($debug) {
            $guideColor = imagecolorallocate($im, 230, 90, 90);
            imagerectangle($im,
                (int)\round(($w - $maxWidth) / 2), $nameY - 100,
                (int)\round(($w + $maxWidth) / 2), $nameY + 20,
                $guideColor
            );
        }

        // Draw Name (stroke + fill)
        $strokePx = max(1, (int)\round($h * self::STROKE_PCT_H));
        self::imagettftext_stroke($im, $fontSize, 0.0, $nameX, $nameY, $nameColor, $strokeColor, $fontName, $recipientName, $strokePx);

        // Draw Date (no stroke, just crisp)
        imagettftext($im, $dateFontSize, 0.0, $dateX, $dateY, $dateColor, $fontDate, $dateText);

        // Ensure runtime dir
        $outDir = $root . '/storage/runtime';
        if (!is_dir($outDir)) {
            @mkdir($outDir, 0775, true);
        }

        // Deterministic file name to avoid clutter
        $hash = substr(sha1($recipientName . '|' . $dateText . '|' . (string)filesize($bgPath)), 0, 10);
        $pngPath = "{$outDir}/cert_preview_{$hash}.png";

        // Write PNG
        imagesavealpha($im, true);
        $ok = imagepng($im, $pngPath, 9);
        imagedestroy($im);

        if (!$ok) {
            return ['ok' => false, 'errors' => ['Failed to save PNG to ' . $pngPath]];
        }

        return [
            'ok'         => true,
            'png'        => $pngPath,
            'w'          => $w,
            'h'          => $h,
            'font_size'  => $fontSize,
            'date_text'  => $dateText,
            'name_bbox'  => $bbox,
            'max_width'  => $maxWidth,
            'errors'     => $errors,
        ];
    }

    // ---------- helpers ----------

    private static function findFont(string $root, array $patterns): ?string
    {
        $fontDir = $root . '/assets/fonts';
        foreach ($patterns as $pat) {
            foreach (glob($fontDir . '/' . $pat) ?: [] as $f) {
                if (is_file($f)) {
                    return $f;
                }
            }
        }
        return null;
    }

    private static function ttfBBox(float $fontSize, float $angle, string $fontFile, string $text): array
    {
        $box = imagettfbbox($fontSize, $angle, $fontFile, $text);
        // Normalize into assoc for clarity
        $xs = [$box[0], $box[2], $box[4], $box[6]];
        $ys = [$box[1], $box[3], $box[5], $box[7]];
        return [
            'left'   => min($xs),
            'right'  => max($xs),
            'top'    => min($ys),
            'bottom' => max($ys),
            'width'  => max($xs) - min($xs),
            'height' => max($ys) - min($ys),
            'raw'    => $box,
        ];
    }

    private static function findFittingFontSize(string $text, string $font, int $maxWidth, float $min, float $max): array
    {
        $lo = $min;
        $hi = $max;
        $bestSize = $min;
        $bestBox  = self::ttfBBox($bestSize, 0.0, $font, $text);

        // Binary search ~12 iterations is plenty
        for ($i = 0; $i < 16; $i++) {
            $mid = ($lo + $hi) / 2.0;
            $box = self::ttfBBox($mid, 0.0, $font, $text);

            if ($box['width'] <= $maxWidth) {
                $bestSize = $mid;
                $bestBox  = $box;
                $lo = $mid + 0.5; // nudge up
            } else {
                $hi = $mid - 0.5; // nudge down
            }

            if (abs($hi - $lo) < 0.6) {
                break;
            }
        }

        // Final clamp and recompute
        $bestSize = max($min, min($bestSize, $max));
        $bestBox  = self::ttfBBox($bestSize, 0.0, $font, $text);
        return [$bestSize, $bestBox];
    }

    private static function centerXFromBBox(array $bbox, int $imgW): int
    {
        // Place so that the text visual box is centered
        $textW = $bbox['width'];
        $leftCorrection = -$bbox['left']; // because bbox left can be negative
        return (int)round(($imgW - $textW) / 2 + $leftCorrection);
    }

    private static function imagettftext_stroke(
        \GdImage $im,
        float $size,
        float $angle,
        int $x,
        int $y,
        int $textColor,
        int $strokeColor,
        string $fontfile,
        string $text,
        int $px
    ): void {
        if ($px > 0) {
            for ($ox = -$px; $ox <= $px; $ox++) {
                for ($oy = -$px; $oy <= $px; $oy++) {
                    imagettftext($im, $size, $angle, $x + $ox, $y + $oy, $strokeColor, $fontfile, $text);
                }
            }
        }
        imagettftext($im, $size, $angle, $x, $y, $textColor, $fontfile, $text);
    }
}