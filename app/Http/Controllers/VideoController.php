<?php

namespace App\Http\Controllers;

use App\Enums\MediaType;
use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(Request $request): View
    {
        $collection = $request->string('collection')->toString() ?: null;
        $sort = $request->string('sort')->toString() === 'title' ? 'title' : 'recent';

        $videos = MediaItem::query()
            ->ofType(MediaType::Video)
            ->when($collection, fn ($query) => $query->where('collection', $collection))
            ->when($sort === 'title', fn ($query) => $query->orderBy('title'), fn ($query) => $query->latest()->latest('id'))
            ->paginate(36)
            ->withQueryString();

        return view('videos.index', [
            'videos' => $videos,
            'collections' => $this->collections(MediaType::Video),
            'collection' => $collection,
            'sort' => $sort,
        ]);
    }

    public function show(MediaItem $item): View
    {
        abort_unless($item->type === MediaType::Video, 404);

        $item->load('myProgress');

        return view('videos.show', [
            'item' => $item,
            'upNext' => $item->collection === null
                ? MediaItem::query()->ofType(MediaType::Video)->whereKeyNot($item->id)->latest()->limit(12)->get()
                : $item->siblings()->limit(100)->get(),
        ]);
    }

    /**
     * Folder names with how many items each holds, biggest first.
     *
     * @return array<string, int>
     */
    public static function collections(MediaType $type, int $limit = 24): array
    {
        return MediaItem::query()
            ->ofType($type)
            ->whereNotNull('collection')
            ->selectRaw('collection, count(*) as total')
            ->groupBy('collection')
            ->orderByDesc('total')
            ->orderBy('collection')
            ->limit($limit)
            ->pluck('total', 'collection')
            ->map(fn ($total): int => (int) $total)
            ->all();
    }
}
