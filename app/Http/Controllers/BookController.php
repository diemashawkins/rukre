<?php

namespace App\Http\Controllers;

use App\Enums\MediaType;
use App\Media\ComicArchive;
use App\Media\MediaLibrary;
use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * Format filters shown as chips, keyed by query value.
     *
     * @var array<string, array{label: string, extensions: list<string>}>
     */
    private const FORMATS = [
        'epub' => ['label' => 'EPUB', 'extensions' => ['epub']],
        'pdf' => ['label' => 'PDF', 'extensions' => ['pdf']],
        'comics' => ['label' => 'Comics', 'extensions' => ['cbz', 'cbr']],
        'other' => ['label' => 'Other', 'extensions' => ['mobi', 'azw', 'azw3', 'fb2', 'djvu', 'txt']],
    ];

    public function __construct(private MediaLibrary $library, private ComicArchive $comics) {}

    public function index(Request $request): View
    {
        $format = array_key_exists($request->string('format')->toString(), self::FORMATS) ? $request->string('format')->toString() : null;
        $collection = $request->string('collection')->toString() ?: null;
        $sort = $request->string('sort')->toString() === 'title' ? 'title' : 'recent';

        $books = MediaItem::query()
            ->ofType(MediaType::Book)
            ->with('myProgress')
            ->when($format, fn ($query) => $query->whereIn('extension', self::FORMATS[$format]['extensions']))
            ->when($collection, fn ($query) => $query->where('collection', $collection))
            ->when($sort === 'title', fn ($query) => $query->orderBy('title'), fn ($query) => $query->latest()->latest('id'))
            ->paginate(48)
            ->withQueryString();

        return view('books.index', [
            'books' => $books,
            'formats' => collect(self::FORMATS)->map(fn (array $format): string => $format['label'])->all(),
            'format' => $format,
            'collections' => VideoController::collections(MediaType::Book),
            'collection' => $collection,
            'sort' => $sort,
        ]);
    }

    public function show(MediaItem $item): View
    {
        abort_unless($item->type === MediaType::Book, 404);

        $item->load('myProgress');
        $pageCount = 0;

        if ($item->readerKind() === 'comic') {
            $file = $this->library->cachedLocalPath($item->disk, $item->path, $item->size);
            $pageCount = $file === null ? 0 : count($this->comics->pages($file));
        }

        return view('books.show', [
            'item' => $item,
            'reader' => $item->readerKind() === 'comic' && $pageCount === 0 ? null : $item->readerKind(),
            'pageCount' => $pageCount,
            'related' => $item->collection === null ? collect() : $item->siblings()->whereKeyNot($item->id)->limit(12)->get(),
        ]);
    }

    public function page(MediaItem $item, int $page): Response
    {
        abort_unless($item->type === MediaType::Book && $item->readerKind() === 'comic', 404);

        $file = $this->library->cachedLocalPath($item->disk, $item->path, $item->size);
        $bytes = $file === null ? null : $this->comics->page($file, $page);
        abort_if($bytes === null, 404);

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes) ?: 'image/jpeg';

        return response($bytes, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=604800',
        ]);
    }
}
