<?php

namespace Tests\Feature;

use App\Models\MediaItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_home_shows_every_shelf(): void
    {
        $video = MediaItem::factory()->video()->create();
        $track = MediaItem::factory()->audio()->create();
        $book = MediaItem::factory()->book()->create();

        $this->actingAs($this->user)->get(route('home'))
            ->assertOk()
            ->assertSee($video->title)
            ->assertSee($track->title)
            ->assertSee($book->title);
    }

    public function test_empty_library_explains_how_to_scan(): void
    {
        $this->actingAs($this->user)->get(route('home'))->assertOk()->assertSee('rukre:scan');
    }

    public function test_library_pages_render(): void
    {
        $video = MediaItem::factory()->video()->create(['collection' => 'Anime']);
        $track = MediaItem::factory()->audio()->create(['album' => 'Night Drive']);
        MediaItem::factory()->book('pdf')->create();

        $this->actingAs($this->user);

        $this->get(route('videos.index'))->assertOk()->assertSee($video->title)->assertSee('Anime');
        $this->get(route('videos.index', ['collection' => 'Other']))->assertOk()->assertDontSee($video->title);
        $this->get(route('videos.show', $video))->assertOk()->assertSee(route('media.stream', $video), false);

        $this->get(route('audio.index'))->assertOk()->assertSee('Night Drive');
        $this->get(route('audio.index', ['album' => 'Night Drive']))->assertOk()->assertSee($track->title);
        $this->get(route('audio.show', $track))->assertOk()->assertSee('queue-album');

        $this->get(route('books.index', ['format' => 'pdf']))->assertOk();
    }

    public function test_items_only_open_on_their_own_page_type(): void
    {
        $track = MediaItem::factory()->audio()->create();

        $this->actingAs($this->user)->get(route('videos.show', $track))->assertNotFound();
    }

    public function test_book_readers_match_the_format(): void
    {
        $this->actingAs($this->user);

        $epub = MediaItem::factory()->book('epub')->create();
        $this->get(route('books.show', $epub))->assertOk()->assertSee('data-epub-reader', false);

        $pdf = MediaItem::factory()->book('pdf')->create();
        $this->get(route('books.show', $pdf))->assertOk()->assertSee('data-pdf-reader', false);

        $mobi = MediaItem::factory()->book('mobi')->create();
        $this->get(route('books.show', $mobi))->assertOk()->assertSee('Download');
    }

    public function test_search_finds_titles_and_creators(): void
    {
        MediaItem::factory()->audio()->create(['title' => 'Neon Rain', 'creator' => 'Kinetic']);
        MediaItem::factory()->book()->create(['title' => 'Quiet Pages', 'creator' => 'Someone Else']);

        $this->actingAs($this->user)->get(route('search', ['q' => 'kinetic']))
            ->assertOk()
            ->assertSee('Neon Rain')
            ->assertDontSee('Quiet Pages');
    }

    public function test_continue_shelf_lists_items_in_progress(): void
    {
        $video = MediaItem::factory()->video()->create(['title' => 'Halfway Film']);
        $video->progress()->create(['user_id' => $this->user->id, 'position' => 300, 'progress' => 0.4]);

        $this->actingAs($this->user)->get(route('home'))->assertOk()->assertSeeInOrder(['Continue', 'Halfway Film']);
    }
}
