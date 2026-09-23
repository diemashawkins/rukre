<?php

namespace App\Enums;

enum MediaType: string
{
    case Video = 'video';
    case Audio = 'audio';
    case Book = 'book';

    /**
     * File extensions (lower case) recognised for each media type.
     *
     * @return list<string>
     */
    public function extensions(): array
    {
        return match ($this) {
            self::Video => ['mp4', 'm4v', 'mkv', 'webm', 'mov', 'avi', 'wmv', 'mpg', 'mpeg', 'ts', 'ogv', '3gp'],
            self::Audio => ['mp3', 'm4a', 'm4b', 'aac', 'flac', 'ogg', 'oga', 'opus', 'wav', 'wma', 'aiff', 'alac'],
            self::Book => ['pdf', 'epub', 'cbz', 'mobi', 'azw', 'azw3', 'fb2', 'djvu', 'txt', 'cbr'],
        };
    }

    public static function fromExtension(string $extension): ?self
    {
        $extension = strtolower($extension);

        foreach (self::cases() as $type) {
            if (in_array($extension, $type->extensions(), true)) {
                return $type;
            }
        }

        return null;
    }

    public function label(): string
    {
        return match ($this) {
            self::Video => 'Videos',
            self::Audio => 'Music & Audio',
            self::Book => 'Books',
        };
    }

    public function routeName(): string
    {
        return match ($this) {
            self::Video => 'videos.index',
            self::Audio => 'audio.index',
            self::Book => 'books.index',
        };
    }
}
