<?php

namespace App\Media;

use App\Enums\MediaType;
use getID3;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Works out titles, artists, durations and cover art for files on the home server.
 */
class MetadataExtractor
{
    private const SIDECAR_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    private const FOLDER_IMAGES = ['cover', 'folder', 'poster', 'front', 'album'];

    public function __construct(
        private MediaLibrary $library,
        private CoverStore $covers,
        private EpubReader $epub,
        private ComicArchive $comics,
    ) {}

    /**
     * Build the database attributes for a media file.
     *
     * @return array<string, mixed>
     */
    public function extract(MediaType $type, string $diskName, string $path, string $libraryDirectory, int $size): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        [$title, $trackNumber] = $this->parseFileName(pathinfo($path, PATHINFO_FILENAME));
        $folder = trim(dirname($path), '/.');

        $attributes = [
            'type' => $type,
            'title' => $title,
            'track_number' => $trackNumber,
            'collection' => $folder === trim($libraryDirectory, '/') ? null : basename($folder),
            'creator' => null,
            'album' => null,
            'year' => null,
            'description' => null,
            'duration' => null,
            'extension' => $extension,
            'mime_type' => $this->mimeType($extension, $type),
            'cover_path' => null,
        ];

        $key = sha1($diskName.'|'.$path);
        $cover = null;

        try {
            $this->library->withLocalCopy($diskName, $path, $size, function (?string $local) use ($type, $extension, $diskName, $path, &$attributes, &$cover): void {
                if ($local === null) {
                    return;
                }

                match (true) {
                    $type === MediaType::Book && $extension === 'epub' => $cover = $this->readEpub($local, $attributes),
                    $type === MediaType::Book && $extension === 'cbz' => $cover = $this->readComic($local),
                    $type === MediaType::Book && $extension === 'pdf' => $cover = $this->renderPdfCover($local),
                    $type === MediaType::Book => null,
                    default => $cover = $this->readAudioVisual($local, $attributes),
                };

                if ($cover === null && $type === MediaType::Video) {
                    $cover = $this->sidecarImage($diskName, $path) ?? $this->grabVideoFrame($local, $attributes['duration']);
                }
            });
        } catch (Throwable $e) {
            report($e);
        }

        $cover ??= $this->sidecarImage($diskName, $path);

        if ($cover !== null) {
            $attributes['cover_path'] = $this->covers->store($cover, $key);
        }

        return $attributes;
    }

    /**
     * Turn "01 - Some_Song.Name" into ["Some Song Name", 1].
     *
     * @return array{0: string, 1: ?int}
     */
    public function parseFileName(string $name): array
    {
        $trackNumber = null;

        if (preg_match('/^(\d{1,3})(?:\s*[-._)]\s*|\s+)(.+)$/u', $name, $match) && ! preg_match('/^\d{4}$/', $match[1])) {
            $trackNumber = (int) $match[1];
            $name = $match[2];
        }

        $title = trim(preg_replace('/\s+/u', ' ', str_replace(['_', '.'], ' ', $name)) ?? $name);

        return [$title === '' ? $name : $title, $trackNumber];
    }

    private function mimeType(string $extension, MediaType $type): string
    {
        $known = match ($extension) {
            'mkv' => 'video/x-matroska',
            'm4b' => 'audio/mp4',
            'cbz' => 'application/vnd.comicbook+zip',
            'cbr' => 'application/vnd.comicbook-rar',
            default => null,
        };

        if ($known !== null) {
            return $known;
        }

        $candidates = MimeTypes::getDefault()->getMimeTypes($extension);
        $prefix = match ($type) {
            MediaType::Video => 'video/',
            MediaType::Audio => 'audio/',
            MediaType::Book => 'application/',
        };

        foreach ($candidates as $candidate) {
            if (str_starts_with($candidate, $prefix)) {
                return $candidate;
            }
        }

        return $candidates[0] ?? 'application/octet-stream';
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function readAudioVisual(string $file, array &$attributes): ?string
    {
        $getId3 = new getID3;
        $getId3->option_md5_data = false;
        $info = $getId3->analyze($file);
        $getId3->CopyTagsToComments($info);
        $comments = $info['comments'] ?? [];

        $first = function (string $key) use ($comments): ?string {
            $value = $comments[$key][0] ?? null;
            $value = is_scalar($value) ? trim((string) $value) : '';

            return $value === '' ? null : Str::limit($value, 250, '');
        };

        $attributes['title'] = $first('title') ?? $attributes['title'];
        $attributes['creator'] = $first('artist') ?? $first('album_artist') ?? $first('band');
        $attributes['album'] = $first('album');
        $attributes['description'] = $first('comment') ?? $first('description');

        if (($year = $first('year') ?? $first('date')) && preg_match('/\b(1[89]\d\d|20\d\d)\b/', $year, $match)) {
            $attributes['year'] = (int) $match[1];
        }

        if (($track = $first('track_number') ?? $first('tracknumber')) && (int) $track > 0) {
            $attributes['track_number'] = (int) $track;
        }

        if (isset($info['playtime_seconds'])) {
            $attributes['duration'] = (int) round((float) $info['playtime_seconds']);
        }

        $picture = $comments['picture'][0]['data'] ?? null;

        return is_string($picture) && $picture !== '' ? $picture : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function readEpub(string $file, array &$attributes): ?string
    {
        $book = $this->epub->read($file);

        $attributes['title'] = $book['title'] ? Str::limit($book['title'], 250, '') : $attributes['title'];
        $attributes['creator'] = $book['creator'] ? Str::limit($book['creator'], 250, '') : null;
        $attributes['description'] = $book['description'];
        $attributes['year'] = $book['year'];

        return $book['cover'];
    }

    private function readComic(string $file): ?string
    {
        return $this->comics->page($file, 0);
    }

    private function renderPdfCover(string $file): ?string
    {
        $binary = $this->binary('pdftoppm');

        if ($binary === null) {
            return null;
        }

        $prefix = tempnam(sys_get_temp_dir(), 'rukre-pdf');

        try {
            (new Process([$binary, '-jpeg', '-f', '1', '-l', '1', '-singlefile', '-scale-to', '720', $file, $prefix]))
                ->setTimeout(60)
                ->run();

            return is_file($prefix.'.jpg') ? (string) file_get_contents($prefix.'.jpg') : null;
        } finally {
            @unlink($prefix);
            @unlink($prefix.'.jpg');
        }
    }

    private function grabVideoFrame(string $file, ?int $duration): ?string
    {
        $binary = $this->binary('ffmpeg');

        if ($binary === null) {
            return null;
        }

        $seek = $duration ? max(1, (int) floor($duration * 0.1)) : 5;
        $output = tempnam(sys_get_temp_dir(), 'rukre-frame').'.jpg';

        try {
            (new Process([$binary, '-v', 'error', '-y', '-ss', (string) $seek, '-i', $file, '-frames:v', '1', '-vf', 'scale=720:-2', $output]))
                ->setTimeout(120)
                ->run();

            return is_file($output) && filesize($output) > 0 ? (string) file_get_contents($output) : null;
        } finally {
            @unlink($output);
            @unlink(substr($output, 0, -4));
        }
    }

    /**
     * Look for artwork next to the file ("Movie.jpg") or in its folder ("Cover.jpg").
     */
    private function sidecarImage(string $diskName, string $path): ?string
    {
        $disk = $this->library->disk($diskName);
        $directory = dirname($path);
        $base = strtolower(pathinfo($path, PATHINFO_FILENAME));

        $candidates = [];

        foreach (self::SIDECAR_EXTENSIONS as $extension) {
            $candidates[] = $base.'.'.$extension;
            $candidates[] = $base.'-poster.'.$extension;
        }

        foreach (self::FOLDER_IMAGES as $name) {
            foreach (self::SIDECAR_EXTENSIONS as $extension) {
                $candidates[] = $name.'.'.$extension;
            }
        }

        try {
            $files = collect($disk->files($directory === '.' ? '' : $directory))
                ->keyBy(fn (string $file): string => strtolower(basename($file)));

            foreach ($candidates as $candidate) {
                if ($files->has($candidate)) {
                    return $disk->get($files->get($candidate));
                }
            }
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    private function binary(string $tool): ?string
    {
        $configured = (string) config("rukre.{$tool}");

        if ($configured === '') {
            return null;
        }

        if (str_contains($configured, DIRECTORY_SEPARATOR)) {
            return is_executable($configured) ? $configured : null;
        }

        return (new ExecutableFinder)->find($configured);
    }
}
