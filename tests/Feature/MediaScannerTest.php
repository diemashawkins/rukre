<?php

namespace Tests\Feature;

use App\Enums\MediaType;
use App\Models\MediaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class MediaScannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');
        Storage::fake('local');
        config(['rukre.ffmpeg' => '', 'rukre.pdftoppm' => '']);
    }

    public function test_it_indexes_each_library_by_file_type(): void
    {
        $disk = Storage::disk('media');
        $disk->put('Videos/Show Name/01 - Pilot_Episode.mp4', 'video');
        $disk->put('Videos/Movie.mkv', 'video');
        $disk->put('Videos/notes.txt', 'not a video');
        $disk->put('Music/Album/02 Second Song.mp3', 'audio');
        $disk->put('Books/Guide.pdf', '%PDF-1.4');
        $disk->put('Books/.hidden.pdf', '%PDF-1.4');
        $disk->put('Books/@eaDir/Guide.pdf', '%PDF-1.4');

        $this->artisan('rukre:scan')->assertSuccessful();

        $this->assertSame(2, MediaItem::query()->ofType(MediaType::Video)->count());
        $this->assertSame(1, MediaItem::query()->ofType(MediaType::Audio)->count());
        $this->assertSame(1, MediaItem::query()->ofType(MediaType::Book)->count());

        $episode = MediaItem::query()->where('path', 'Videos/Show Name/01 - Pilot_Episode.mp4')->firstOrFail();
        $this->assertSame('Pilot Episode', $episode->title);
        $this->assertSame(1, $episode->track_number);
        $this->assertSame('Show Name', $episode->collection);
        $this->assertSame('video/mp4', $episode->mime_type);

        $movie = MediaItem::query()->where('path', 'Videos/Movie.mkv')->firstOrFail();
        $this->assertNull($movie->collection);
        $this->assertSame('video/x-matroska', $movie->mime_type);
    }

    public function test_it_removes_items_whose_files_were_deleted(): void
    {
        $disk = Storage::disk('media');
        $disk->put('Music/a.mp3', 'a');
        $disk->put('Music/b.mp3', 'b');

        $this->artisan('rukre:scan')->assertSuccessful();
        $this->assertSame(2, MediaItem::query()->count());

        $disk->delete('Music/b.mp3');
        $this->artisan('rukre:scan')->assertSuccessful();

        $this->assertSame(['Music/a.mp3'], MediaItem::query()->pluck('path')->all());
    }

    public function test_a_missing_library_folder_does_not_wipe_its_items(): void
    {
        Storage::disk('media')->put('Books/Guide.pdf', '%PDF-1.4');
        $this->artisan('rukre:scan')->assertSuccessful();

        Storage::disk('media')->deleteDirectory('Books');
        $this->artisan('rukre:scan')->assertSuccessful();

        $this->assertSame(1, MediaItem::query()->ofType(MediaType::Book)->count());
    }

    public function test_unchanged_files_are_not_re_read(): void
    {
        Storage::disk('media')->put('Books/Guide.pdf', '%PDF-1.4');
        $this->artisan('rukre:scan')->assertSuccessful();

        MediaItem::query()->update(['title' => 'Renamed by hand']);
        $this->artisan('rukre:scan')->assertSuccessful();
        $this->assertSame('Renamed by hand', MediaItem::query()->value('title'));

        $this->artisan('rukre:scan --force')->assertSuccessful();
        $this->assertSame('Guide', MediaItem::query()->value('title'));
    }

    public function test_it_reads_epub_metadata_and_cover(): void
    {
        $epub = $this->makeEpub();
        Storage::disk('media')->put('Books/Fiction/book.epub', (string) file_get_contents($epub));
        unlink($epub);

        $this->artisan('rukre:scan')->assertSuccessful();

        $book = MediaItem::query()->firstOrFail();
        $this->assertSame('The Obsidian Sky', $book->title);
        $this->assertSame('Ada Writer', $book->creator);
        $this->assertSame(2021, $book->year);
        $this->assertSame('A story about stars.', $book->description);
        $this->assertSame('Fiction', $book->collection);
        $this->assertNotNull($book->cover_path);
        Storage::disk('local')->assertExists($book->cover_path);
    }

    public function test_folder_artwork_is_used_as_cover(): void
    {
        Storage::disk('media')->put('Music/Album/01 Song.mp3', 'audio');
        Storage::disk('media')->put('Music/Album/Folder.JPG', $this->pngBytes());

        $this->artisan('rukre:scan')->assertSuccessful();

        $track = MediaItem::query()->firstOrFail();
        $this->assertNotNull($track->cover_path);
        Storage::disk('local')->assertExists($track->cover_path);
    }

    private function makeEpub(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'epub');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('mimetype', 'application/epub+zip');
        $zip->addFromString('META-INF/container.xml', '<?xml version="1.0"?><container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container"><rootfiles><rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/></rootfiles></container>');
        $zip->addFromString('OEBPS/content.opf', <<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <package xmlns="http://www.idpf.org/2007/opf" version="3.0">
              <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
                <dc:title>The Obsidian Sky</dc:title>
                <dc:creator>Ada Writer</dc:creator>
                <dc:date>2021-05-01</dc:date>
                <dc:description>&lt;p&gt;A story about stars.&lt;/p&gt;</dc:description>
              </metadata>
              <manifest>
                <item id="cover" href="images/cover.png" media-type="image/png" properties="cover-image"/>
              </manifest>
            </package>
            XML);
        $zip->addFromString('OEBPS/images/cover.png', $this->pngBytes());
        $zip->close();

        return $path;
    }

    private function pngBytes(): string
    {
        $image = imagecreatetruecolor(40, 60);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 211, 0));
        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }
}
