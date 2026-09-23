<?php

namespace App\Http\Controllers;

use App\Enums\MediaType;
use App\Media\MediaStreamer;
use App\Models\MediaItem;
use App\Models\MediaProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MediaController extends Controller
{
    public function __construct(private MediaStreamer $streamer) {}

    public function stream(Request $request, MediaItem $item): SymfonyResponse
    {
        return $this->streamer->stream($item, $request);
    }

    public function download(Request $request, MediaItem $item): SymfonyResponse
    {
        return $this->streamer->stream($item, $request, download: true);
    }

    public function cover(MediaItem $item): SymfonyResponse
    {
        $disk = Storage::disk('local');

        if ($item->cover_path !== null && $disk->exists($item->cover_path)) {
            return response()->file($disk->path($item->cover_path), [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'private, max-age=86400',
            ]);
        }

        return new Response(view('covers.placeholder', ['item' => $item])->render(), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function progress(Request $request, MediaItem $item): JsonResponse
    {
        $data = $request->validate([
            'position' => ['required', 'numeric', 'min:0'],
            'progress' => ['required', 'numeric', 'between:0,1'],
            'location' => ['nullable', 'string', 'max:512'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        if ($request->user() === null) {
            return response()->json(['saved' => false]);
        }

        $completed = $request->boolean('completed')
            || ($item->type !== MediaType::Book && (float) $data['progress'] >= 0.95);

        MediaProgress::query()->updateOrCreate(
            ['user_id' => $request->user()->id, 'media_item_id' => $item->id],
            [
                'position' => (float) $data['position'],
                'progress' => (float) $data['progress'],
                'location' => $data['location'] ?? null,
                'completed' => $completed,
            ],
        );

        return response()->json(['saved' => true]);
    }
}
