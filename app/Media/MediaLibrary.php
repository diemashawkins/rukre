<?php

namespace App\Media;

use App\Enums\MediaType;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Access to the media folders on the home server, wherever they live.
 */
class MediaLibrary
{
    public function diskName(): string
    {
        return (string) config('rukre.disk');
    }

    public function disk(?string $name = null): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($name ?? $this->diskName());

        return $disk;
    }

    public function isLocal(?string $name = null): bool
    {
        return $this->disk($name)->getAdapter() instanceof LocalFilesystemAdapter;
    }

    /**
     * The configured folders for every enabled library.
     *
     * @return array<string, array{type: MediaType, directories: list<string>}>
     */
    public function libraries(): array
    {
        $libraries = [];

        foreach ((array) config('rukre.libraries') as $key => $directories) {
            $type = MediaType::tryFrom((string) $key);

            if ($type === null) {
                continue;
            }

            $directories = collect(is_array($directories) ? $directories : explode(',', (string) $directories))
                ->map(fn ($directory): string => trim((string) $directory, " \t\n\r\0\x0B/"))
                ->filter(fn (string $directory): bool => $directory !== '')
                ->values()
                ->all();

            if ($directories !== []) {
                $libraries[$type->value] = ['type' => $type, 'directories' => $directories];
            }
        }

        return $libraries;
    }

    /**
     * Run a callback against a path on the local filesystem for a media file.
     *
     * Files on the local disk are used in place. Files on a remote disk are
     * copied to a temporary file first, unless they are larger than the
     * configured download limit, in which case the callback gets null.
     *
     * @template TReturn
     *
     * @param  callable(?string): TReturn  $callback
     * @return TReturn
     */
    public function withLocalCopy(string $diskName, string $path, int $size, callable $callback): mixed
    {
        if ($this->isLocal($diskName)) {
            return $callback($this->disk($diskName)->path($path));
        }

        if ($size > $this->remoteDownloadLimit()) {
            return $callback(null);
        }

        $temporary = tempnam(sys_get_temp_dir(), 'rukre');

        try {
            $copied = $this->copyToLocal($this->disk($diskName), $path, $temporary);

            return $callback($copied ? $temporary : null);
        } finally {
            @unlink($temporary);
        }
    }

    /**
     * A long-lived local path for a media file, used by readers that need random
     * access (comic archives). Remote files are cached under storage/app.
     */
    public function cachedLocalPath(string $diskName, string $path, int $size): ?string
    {
        if ($this->isLocal($diskName)) {
            $local = $this->disk($diskName)->path($path);

            return is_file($local) ? $local : null;
        }

        if ($size > $this->remoteDownloadLimit() * 4) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $cached = storage_path('app/media-cache/'.sha1($diskName.'|'.$path).'.'.$extension);

        if (is_file($cached) && filesize($cached) === $size) {
            return $cached;
        }

        if (! is_dir(dirname($cached))) {
            mkdir(dirname($cached), 0755, true);
        }

        return $this->copyToLocal($this->disk($diskName), $path, $cached) ? $cached : null;
    }

    private function copyToLocal(Filesystem $disk, string $path, string $destination): bool
    {
        $source = $disk->readStream($path);

        if (! is_resource($source)) {
            return false;
        }

        $target = fopen($destination, 'wb');

        try {
            return $target !== false && stream_copy_to_stream($source, $target) !== false;
        } finally {
            fclose($source);

            if (is_resource($target)) {
                fclose($target);
            }
        }
    }

    private function remoteDownloadLimit(): int
    {
        return max(0, (int) config('rukre.remote_download_limit')) * 1024 * 1024;
    }
}
