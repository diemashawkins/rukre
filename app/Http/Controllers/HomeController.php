<?php

namespace App\Http\Controllers;

use App\Enums\MediaType;
use App\Models\MediaItem;
use App\Models\MediaProgress;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $recent = fn (MediaType $type, int $limit) => MediaItem::query()->ofType($type)->with('myProgress')->latest()->latest('id')->limit($limit)->get();

        $continue = $request->user() === null ? collect() : MediaProgress::query()
            ->whereBelongsTo($request->user())
            ->where('completed', false)
            ->where('progress', '>', 0.01)
            ->with('mediaItem')
            ->latest('updated_at')
            ->limit(12)
            ->get()
            ->map(fn (MediaProgress $progress): ?MediaItem => $progress->mediaItem?->setRelation('myProgress', $progress))
            ->filter()
            ->values();

        $featured = MediaItem::query()->ofType(MediaType::Video)->whereNotNull('cover_path')->latest()->first()
            ?? MediaItem::query()->ofType(MediaType::Video)->latest()->first();

        return view('home', [
            'featured' => $featured,
            'continue' => $continue,
            'videos' => $recent(MediaType::Video, 12),
            'audio' => $recent(MediaType::Audio, 12),
            'books' => $recent(MediaType::Book, 12),
            'counts' => MediaItem::query()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
        ]);
    }
}
