<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

class FileCompressionHelper
{
    public static function compressPdf(string $path): string
    {
        if (! File::exists($path) || ! self::ghostscriptPath()) {
            return $path;
        }

        $compressedPath = $path.'.compressed.pdf';
        $command = sprintf(
            '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s',
            escapeshellarg(self::ghostscriptPath()),
            escapeshellarg($compressedPath),
            escapeshellarg($path),
        );

        exec($command, $output, $status);

        if ($status === 0 && File::exists($compressedPath) && File::size($compressedPath) > 0) {
            if (File::size($compressedPath) < File::size($path)) {
                File::move($compressedPath, $path);
            } else {
                File::delete($compressedPath);
            }
        }

        return $path;
    }

    public static function compressImage(string $path): string
    {
        if (! File::exists($path)) {
            return $path;
        }

        try {
            $manager = new ImageManager(new Driver);
            $image = $manager->decodePath($path)->scaleDown(width: 1920, height: 1920);
            $image->save($path, quality: 80);
        } catch (Throwable) {
            return $path;
        }

        return $path;
    }

    public static function compress(string $path): string
    {
        $mime = File::mimeType($path);

        return match ($mime) {
            'application/pdf' => self::compressPdf($path),
            'image/jpeg', 'image/png' => self::compressImage($path),
            default => $path,
        };
    }

    private static function ghostscriptPath(): ?string
    {
        $paths = [];
        exec('command -v gs', $paths, $status);

        return $status === 0 && filled($paths[0] ?? null) ? $paths[0] : null;
    }
}
