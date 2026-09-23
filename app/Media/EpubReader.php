<?php

namespace App\Media;

use DOMDocument;
use DOMXPath;
use ZipArchive;

/**
 * Reads the title, author, description and cover image out of an EPUB file.
 */
class EpubReader
{
    /**
     * @return array{title: ?string, creator: ?string, description: ?string, year: ?int, cover: ?string}
     */
    public function read(string $file): array
    {
        $result = ['title' => null, 'creator' => null, 'description' => null, 'year' => null, 'cover' => null];
        $zip = new ZipArchive;

        if ($zip->open($file, ZipArchive::RDONLY) !== true) {
            return $result;
        }

        try {
            $opfPath = $this->packagePath($zip);
            $opf = $opfPath === null ? false : $zip->getFromName($opfPath);

            if ($opf === false) {
                return $result;
            }

            $xpath = $this->xpath($opf);

            if ($xpath === null) {
                return $result;
            }

            $result['title'] = $this->text($xpath, '//dc:title');
            $result['creator'] = $this->text($xpath, '//dc:creator');
            $description = $this->text($xpath, '//dc:description');
            $result['description'] = $description === null ? null : trim(html_entity_decode(strip_tags($description)));
            $date = $this->text($xpath, '//dc:date');

            if ($date !== null && preg_match('/\b(1[5-9]\d\d|20\d\d)\b/', $date, $match)) {
                $result['year'] = (int) $match[1];
            }

            $coverHref = $this->coverHref($xpath);

            if ($coverHref !== null) {
                $cover = $zip->getFromName($this->resolve(dirname($opfPath), $coverHref));
                $result['cover'] = $cover === false ? null : $cover;
            }
        } finally {
            $zip->close();
        }

        return $result;
    }

    private function packagePath(ZipArchive $zip): ?string
    {
        $container = $zip->getFromName('META-INF/container.xml');

        if ($container !== false && preg_match('/full-path\s*=\s*"([^"]+)"/', $container, $match)) {
            return $match[1];
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);

            if (str_ends_with(strtolower($name), '.opf')) {
                return $name;
            }
        }

        return null;
    }

    private function xpath(string $xml): ?DOMXPath
    {
        $document = new DOMDocument;

        if (! @$document->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            return null;
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('opf', 'http://www.idpf.org/2007/opf');
        $xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');

        return $xpath;
    }

    private function text(DOMXPath $xpath, string $query): ?string
    {
        $node = $xpath->query($query)?->item(0);
        $value = $node === null ? '' : trim($node->textContent);

        return $value === '' ? null : $value;
    }

    private function coverHref(DOMXPath $xpath): ?string
    {
        $href = $this->text($xpath, '//opf:manifest/opf:item[contains(concat(" ", @properties, " "), " cover-image ")]/@href');

        if ($href !== null) {
            return $href;
        }

        $coverId = $this->text($xpath, '//opf:metadata/opf:meta[@name="cover"]/@content');

        if ($coverId !== null) {
            $href = $this->text($xpath, sprintf('//opf:manifest/opf:item[@id="%s"]/@href', str_replace('"', '', $coverId)));

            if ($href !== null) {
                return $href;
            }
        }

        return $this->text($xpath, '//opf:manifest/opf:item[starts-with(@media-type, "image/") and contains(translate(@id, "COVER", "cover"), "cover")]/@href');
    }

    private function resolve(string $base, string $href): string
    {
        $path = ($base === '.' || $base === '' ? '' : $base.'/').rawurldecode($href);
        $parts = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '..') {
                array_pop($parts);
            } elseif ($segment !== '.' && $segment !== '') {
                $parts[] = $segment;
            }
        }

        return implode('/', $parts);
    }
}
