<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Str;

class OgImageService
{
    /**
     * Generates a static 1200x630 PNG OG image ONCE on publish using PHP GD.
     * Never called per public HTTP request (Section 12).
     */
    public function generateForTitle(string $title, string $subtitle, string $slugKey): ?string
    {
        if (app()->runningUnitTests() || ! extension_loaded('gd')) {
            return null;
        }

        $directory = public_path('og');
        if (! is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        $filename = Str::slug(substr($slugKey, 0, 80)).'-'.substr(md5($slugKey), 0, 8).'.png';
        $fullPath = $directory.DIRECTORY_SEPARATOR.$filename;

        $width = 1200;
        $height = 630;
        $img = imagecreatetruecolor($width, $height);
        if (! $img) {
            return null;
        }

        $bgDark = imagecolorallocate($img, 10, 58, 37);      // #0a3a25
        $accent = imagecolorallocate($img, 22, 121, 76);     // #16794c
        $white = imagecolorallocate($img, 255, 255, 255);
        $muted = imagecolorallocate($img, 213, 236, 224);

        imagefilledrectangle($img, 0, 0, $width, $height, $bgDark);
        imagefilledrectangle($img, 0, 0, 24, $height, $accent);
        imagefilledrectangle($img, 0, $height - 14, $width, $height, $accent);

        $siteName = strtoupper((string) SiteSetting::getValue('site_name', 'DARASAHUB'));
        $canonicalHost = (string) SiteSetting::getValue('canonical_host', 'darasahub.com');

        // Built-in GD fonts (safe on every shared host without needing custom TTF files)
        imagestring($img, 5, 72, 68, $siteName.'  |  KENYA CBC EXAMS DIRECTORY', $muted);

        $wrappedTitle = wordwrap(trim($title), 46, "\n", true);
        $lines = array_slice(explode("\n", $wrappedTitle), 0, 4);
        $y = 180;
        foreach ($lines as $line) {
            imagestring($img, 5, 72, $y, $line, $white);
            $y += 42;
        }

        if (trim($subtitle) !== '') {
            $subLines = array_slice(explode("\n", wordwrap(trim($subtitle), 66, "\n", true)), 0, 2);
            $sy = $y + 30;
            foreach ($subLines as $sline) {
                imagestring($img, 4, 72, $sy, $sline, $muted);
                $sy += 28;
            }
        }

        imagestring($img, 4, 72, $height - 64, 'https://'.$canonicalHost, $muted);

        imagepng($img, $fullPath, 6);
        imagedestroy($img);

        return '/og/'.$filename;
    }
}
