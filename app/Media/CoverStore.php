<?php

namespace App\Media;

use Illuminate\Support\Facades\Storage;

/**
 * Saves cover art and poster frames, scaled down, on the app's own disk.
 */
class CoverStore
{
    private const MAX_WIDTH = 720;

    /**
     * Store image bytes and return the stored path, or null when they are not an image.
     */
    public function store(string $bytes, string $key): ?string
    {
        if ($bytes === '' || @getimagesizefromstring($bytes) === false) {
            return null;
        }

        $path = 'covers/'.$key.'.jpg';
        $jpeg = $this->toJpeg($bytes);

        if ($jpeg === null) {
            return null;
        }

        Storage::disk('local')->put($path, $jpeg);

        return $path;
    }

    public function storeFile(string $file, string $key): ?string
    {
        if (! is_file($file)) {
            return null;
        }

        return $this->store((string) file_get_contents($file), $key);
    }

    public function delete(?string $path): void
    {
        if ($path !== null) {
            Storage::disk('local')->delete($path);
        }
    }

    private function toJpeg(string $bytes): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $image = @imagecreatefromstring($bytes);

        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > self::MAX_WIDTH) {
            $scaled = imagescale($image, self::MAX_WIDTH, (int) round($height * self::MAX_WIDTH / $width));

            if ($scaled !== false) {
                $image = $scaled;
            }
        }

        ob_start();
        imagejpeg($image, null, 84);

        return (string) ob_get_clean();
    }
}
