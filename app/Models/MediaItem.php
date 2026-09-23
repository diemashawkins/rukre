<?php

namespace App\Models;

use App\Enums\MediaType;
use Database\Factories\MediaItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

#[Fillable([
    'type', 'disk', 'path', 'path_hash', 'title', 'collection', 'creator', 'album', 'year',
    'track_number', 'description', 'extension', 'mime_type', 'size', 'duration', 'cover_path',
    'file_modified_at', 'scanned_at',
])]
class MediaItem extends Model
{
    /** @use HasFactory<MediaItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'size' => 'integer',
            'duration' => 'integer',
            'year' => 'integer',
            'track_number' => 'integer',
            'file_modified_at' => 'datetime',
            'scanned_at' => 'datetime',
        ];
    }

    public static function hashPath(string $disk, string $path): string
    {
        return sha1($disk.'|'.$path);
    }

    /**
     * @return HasMany<MediaProgress, $this>
     */
    public function progress(): HasMany
    {
        return $this->hasMany(MediaProgress::class);
    }

    /**
     * The signed-in user's progress for this item.
     *
     * @return HasOne<MediaProgress, $this>
     */
    public function myProgress(): HasOne
    {
        return $this->hasOne(MediaProgress::class)->where('user_id', Auth::id() ?? 0);
    }

    /**
     * @param  Builder<MediaItem>  $query
     */
    public function scopeOfType(Builder $query, MediaType $type): void
    {
        $query->where('type', $type);
    }

    /**
     * @param  Builder<MediaItem>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        $query->where(function (Builder $query) use ($like): void {
            $query->where('title', 'like', $like)
                ->orWhere('creator', 'like', $like)
                ->orWhere('album', 'like', $like)
                ->orWhere('collection', 'like', $like);
        });
    }

    /**
     * Items that live in the same folder, in play order.
     *
     * @return Builder<MediaItem>
     */
    public function siblings(): Builder
    {
        return static::query()
            ->ofType($this->type)
            ->where('collection', $this->collection)
            ->orderByRaw('track_number is null')
            ->orderBy('track_number')
            ->orderBy('title');
    }

    public function fileName(): string
    {
        return basename($this->path);
    }

    public function formattedDuration(): ?string
    {
        if (! $this->duration) {
            return null;
        }

        $hours = intdiv($this->duration, 3600);
        $minutes = intdiv($this->duration % 3600, 60);
        $seconds = $this->duration % 60;

        return $hours > 0
            ? sprintf('%d:%02d:%02d', $hours, $minutes, $seconds)
            : sprintf('%d:%02d', $minutes, $seconds);
    }

    public function formattedSize(): string
    {
        return Number::fileSize($this->size, precision: 1);
    }

    /**
     * How the book can be opened in the browser, or null when it can only be downloaded.
     */
    public function readerKind(): ?string
    {
        return match ($this->extension) {
            'pdf' => 'pdf',
            'epub' => 'epub',
            'cbz' => 'comic',
            'txt' => 'text',
            default => null,
        };
    }

    public function url(): string
    {
        return match ($this->type) {
            MediaType::Video => route('videos.show', $this),
            MediaType::Audio => route('audio.show', $this),
            MediaType::Book => route('books.show', $this),
        };
    }

    /**
     * Data handed to the persistent audio player.
     *
     * @return array{id: int, title: string, creator: ?string, cover: string, src: string, url: string, progress: string}
     */
    public function toPlayerTrack(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'creator' => $this->creator ?? $this->album ?? $this->collection,
            'cover' => route('media.cover', $this),
            'src' => route('media.stream', $this),
            'url' => $this->url(),
            'progress' => route('media.progress', $this),
        ];
    }
}
