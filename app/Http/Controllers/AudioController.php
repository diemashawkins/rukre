<?php

namespace App\Http\Controllers;

use App\Enums\MediaType;
use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AudioController extends Controller
{
    private const ALBUM_KEY = "coalesce(album, collection, '')";

    public function index(Request $request): View
    {
        $album = $request->has('album') ? $request->string('album')->toString() : null;

        $albums = MediaItem::query()
            ->ofType(MediaType::Audio)
            ->selectRaw(self::ALBUM_KEY.' as album_key, min(id) as cover_id, count(*) as tracks, max(creator) as creator, max(created_at) as added_at')
            ->groupByRaw(self::ALBUM_KEY)
            ->orderByDesc('added_at')
            ->limit(30)
            ->get();

        $tracks = MediaItem::query()
            ->ofType(MediaType::Audio)
            ->when($album !== null, fn (Builder $query) => $this->inAlbum($query, $album)->orderByRaw('track_number is null')->orderBy('track_number')->orderBy('title'))
            ->when($album === null, fn (Builder $query) => $query->latest()->latest('id'))
            ->paginate(60)
            ->withQueryString();

        return view('audio.index', [
            'albums' => $albums,
            'albumCovers' => MediaItem::query()->whereIn('id', $albums->pluck('cover_id'))->get()->keyBy('id'),
            'tracks' => $tracks,
            'album' => $album,
        ]);
    }

    public function show(MediaItem $item): View
    {
        abort_unless($item->type === MediaType::Audio, 404);

        $albumKey = $item->album ?? $item->collection ?? '';

        return view('audio.show', [
            'item' => $item,
            'albumKey' => $albumKey,
            'tracks' => $this->inAlbum(MediaItem::query()->ofType(MediaType::Audio), $albumKey)
                ->orderByRaw('track_number is null')
                ->orderBy('track_number')
                ->orderBy('title')
                ->limit(300)
                ->get(),
        ]);
    }

    /**
     * @param  Builder<MediaItem>  $query
     * @return Builder<MediaItem>
     */
    private function inAlbum(Builder $query, string $album): Builder
    {
        return $query->whereRaw(self::ALBUM_KEY.' = ?', [$album]);
    }
}
