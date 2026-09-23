<?php

namespace App\Media;

use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams media from the home server with HTTP Range support, so browsers
 * can seek in videos and audio without downloading the whole file first.
 */
class MediaStreamer
{
    private const CHUNK_SIZE = 1024 * 1024;

    public function __construct(private MediaLibrary $library) {}

    public function stream(MediaItem $item, Request $request, bool $download = false): Response
    {
        $disposition = $this->disposition($item, $download);
        $mime = $item->mime_type ?: 'application/octet-stream';

        if ($this->library->isLocal($item->disk)) {
            $path = $this->library->disk($item->disk)->path($item->path);
            abort_unless(is_file($path), 404);

            $response = new BinaryFileResponse($path, 200, ['Content-Type' => $mime], false, null, false, true);
            $response->headers->set('Content-Disposition', $disposition);
            $response->headers->set('Cache-Control', 'private, max-age=3600');

            return $response;
        }

        return $this->streamRemote($item, $request, $mime, $disposition);
    }

    private function streamRemote(MediaItem $item, Request $request, string $mime, string $disposition): Response
    {
        $disk = $this->library->disk($item->disk);
        abort_unless($disk->fileExists($item->path), 404);

        $size = (int) $disk->size($item->path);
        $range = $this->parseRange($request->headers->get('Range'), $size);

        $headers = [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, max-age=3600',
        ];

        if ($range === false) {
            return new Response('', 416, ['Content-Range' => "bytes */{$size}"]);
        }

        [$start, $end] = $range ?? [0, max(0, $size - 1)];
        $length = $size === 0 ? 0 : $end - $start + 1;
        $headers['Content-Length'] = (string) $length;

        if ($range !== null) {
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        if ($request->isMethod('HEAD')) {
            return new Response('', $range === null ? 200 : 206, $headers);
        }

        return new StreamedResponse(function () use ($disk, $item, $start, $length): void {
            $stream = $disk->readStream($item->path);

            if (! is_resource($stream)) {
                return;
            }

            try {
                $this->skip($stream, $start);
                $remaining = $length;

                while ($remaining > 0 && ! feof($stream) && connection_aborted() === 0) {
                    $chunk = fread($stream, min(self::CHUNK_SIZE, $remaining));

                    if ($chunk === false || $chunk === '') {
                        break;
                    }

                    echo $chunk;
                    $remaining -= strlen($chunk);
                    flush();
                }
            } finally {
                fclose($stream);
            }
        }, $range === null ? 200 : 206, $headers);
    }

    /**
     * Parse a single "bytes=start-end" range.
     *
     * @return array{0: int, 1: int}|null|false Null for no range, false when it cannot be satisfied.
     */
    public function parseRange(?string $header, int $size): array|null|false
    {
        if ($header === null || ! preg_match('/^bytes=(\d*)-(\d*)$/', trim($header), $match)) {
            return null;
        }

        [, $start, $end] = $match;

        if ($start === '' && $end === '') {
            return null;
        }

        if ($start === '') {
            $start = max(0, $size - (int) $end);
            $end = $size - 1;
        } else {
            $start = (int) $start;
            $end = $end === '' ? $size - 1 : min((int) $end, $size - 1);
        }

        if ($size === 0 || $start > $end || $start >= $size) {
            return false;
        }

        return [$start, $end];
    }

    /**
     * @param  resource  $stream
     */
    private function skip($stream, int $bytes): void
    {
        if ($bytes === 0) {
            return;
        }

        if ((stream_get_meta_data($stream)['seekable'] ?? false) && fseek($stream, $bytes) === 0) {
            return;
        }

        while ($bytes > 0 && ! feof($stream)) {
            $read = fread($stream, min(self::CHUNK_SIZE, $bytes));

            if ($read === false || $read === '') {
                return;
            }

            $bytes -= strlen($read);
        }
    }

    private function disposition(MediaItem $item, bool $download): string
    {
        $name = str_replace(['/', '\\'], '-', $item->fileName());
        $fallback = str_replace('%', '', Str::ascii($name)) ?: 'media.'.$item->extension;

        return HeaderUtils::makeDisposition(
            $download ? HeaderUtils::DISPOSITION_ATTACHMENT : HeaderUtils::DISPOSITION_INLINE,
            $name,
            $fallback,
        );
    }
}
