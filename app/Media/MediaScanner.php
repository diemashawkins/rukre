<?php

namespace App\Media;

use App\Enums\MediaType;
use App\Models\MediaItem;
use Closure;
use League\Flysystem\FileAttributes;
use Throwable;

/**
 * Indexes the video, audio and book folders on the home server.
 */
class MediaScanner
{
    /**
     * Folder names created by NAS software that never hold real media.
     */
    private const IGNORED_SEGMENTS = ['@eaDir', '#recycle', '$RECYCLE.BIN', 'lost+found'];

    public function __construct(
        private MediaLibrary $library,
        private MetadataExtractor $extractor,
        private CoverStore $covers,
    ) {}

    /**
     * Scan every configured library.
     *
     * @param  (Closure(string): void)|null  $report  Receives progress messages.
     * @return array{added: int, updated: int, unchanged: int, removed: int, failed: int}
     */
    public function scan(bool $force = false, ?Closure $report = null): array
    {
        $report ??= fn (string $message): null => null;
        $diskName = $this->library->diskName();
        $disk = $this->library->disk($diskName);
        $stats = ['added' => 0, 'updated' => 0, 'unchanged' => 0, 'removed' => 0, 'failed' => 0];

        foreach ($this->library->libraries() as ['type' => $type, 'directories' => $directories]) {
            $seen = [];
            $complete = true;

            foreach ($directories as $directory) {
                try {
                    if (! $disk->directoryExists($directory)) {
                        $report("Skipping missing {$type->value} folder \"{$directory}\".");
                        $complete = false;

                        continue;
                    }

                    $report("Scanning {$type->value} folder \"{$directory}\"...");

                    foreach ($disk->getDriver()->listContents($directory, true) as $entry) {
                        if (! $entry instanceof FileAttributes || ! $this->isMediaFile($entry->path(), $type)) {
                            continue;
                        }

                        $hash = MediaItem::hashPath($diskName, $entry->path());
                        $seen[$hash] = true;
                        $outcome = $this->index($type, $diskName, $directory, $entry, $hash, $force);
                        $stats[$outcome]++;

                        if ($outcome === 'failed') {
                            $report("Could not read \"{$entry->path()}\".");
                        }
                    }
                } catch (Throwable $e) {
                    report($e);
                    $report("Could not scan \"{$directory}\": {$e->getMessage()}");
                    $complete = false;
                }
            }

            if ($complete) {
                $stats['removed'] += $this->prune($type, $diskName, $seen);
            }
        }

        return $stats;
    }

    public function isMediaFile(string $path, MediaType $type): bool
    {
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || str_starts_with($segment, '.') || in_array($segment, self::IGNORED_SEGMENTS, true)) {
                return false;
            }
        }

        return MediaType::fromExtension(pathinfo($path, PATHINFO_EXTENSION)) === $type;
    }

    /**
     * @return 'added'|'updated'|'unchanged'|'failed'
     */
    private function index(MediaType $type, string $diskName, string $directory, FileAttributes $entry, string $hash, bool $force): string
    {
        $item = MediaItem::query()->where('path_hash', $hash)->first();
        $size = (int) ($entry->fileSize() ?? 0);
        $modified = $entry->lastModified();

        if (! $force && $item !== null && $item->size === $size && $item->file_modified_at?->getTimestamp() === $modified) {
            return 'unchanged';
        }

        try {
            $attributes = $this->extractor->extract($type, $diskName, $entry->path(), $directory, $size);
        } catch (Throwable $e) {
            report($e);

            return 'failed';
        }

        if ($item !== null && $item->cover_path !== null && $item->cover_path !== $attributes['cover_path']) {
            $this->covers->delete($item->cover_path);
        }

        MediaItem::query()->updateOrCreate(['path_hash' => $hash], $attributes + [
            'disk' => $diskName,
            'path' => $entry->path(),
            'size' => $size,
            'file_modified_at' => $modified === null ? null : now()->setTimestamp($modified),
            'scanned_at' => now(),
        ]);

        return $item === null ? 'added' : 'updated';
    }

    /**
     * Remove items whose files have gone from the home server.
     *
     * @param  array<string, true>  $seen
     */
    private function prune(MediaType $type, string $diskName, array $seen): int
    {
        $removed = 0;

        MediaItem::query()
            ->ofType($type)
            ->where('disk', $diskName)
            ->select(['id', 'path_hash', 'cover_path'])
            ->lazyById()
            ->reject(fn (MediaItem $item): bool => isset($seen[$item->path_hash]))
            ->each(function (MediaItem $item) use (&$removed): void {
                $this->covers->delete($item->cover_path);
                $item->delete();
                $removed++;
            });

        return $removed;
    }
}
