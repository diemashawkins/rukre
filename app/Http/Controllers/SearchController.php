<?php

namespace App\Http\Controllers;

use App\Enums\MediaType;
use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $term = trim($request->string('q')->toString());

        $results = collect(MediaType::cases())->mapWithKeys(fn (MediaType $type): array => [
            $type->value => $term === '' ? collect() : MediaItem::query()
                ->ofType($type)
                ->search($term)
                ->orderBy('title')
                ->limit(24)
                ->get(),
        ]);

        return view('search', ['term' => $term, 'results' => $results]);
    }
}
