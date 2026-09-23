<?php

namespace App\Media;

use ZipArchive;

/**
 * Reads the pages of a CBZ (zipped images) comic or webtoon.
 */
class ComicArchive
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];

    /**
     * Image entries in reading order.
     *
     * @return list<string>
     */
    public function pages(string $file): array
    {
        $zip = new ZipArchive;

        if ($zip->open($file, ZipArchive::RDONLY) !== true) {
            return [];
        }

        $pages = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            $base = basename($name);

            if (str_starts_with($base, '.') || str_contains($name, '__MACOSX/')) {
                continue;
            }

            if (in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true)) {
                $pages[] = $name;
            }
        }

        $zip->close();
        natcasesort($pages);

        return array_values($pages);
    }

    public function page(string $file, int $index): ?string
    {
        $name = $this->pages($file)[$index] ?? null;

        if ($name === null) {
            return null;
        }

        $zip = new ZipArchive;

        if ($zip->open($file, ZipArchive::RDONLY) !== true) {
            return null;
        }

        $bytes = $zip->getFromName($name);
        $zip->close();

        return $bytes === false ? null : $bytes;
    }
}
