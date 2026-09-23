<?php

namespace Tests\Feature;

use App\Media\MediaLibrary;
use App\Models\MediaItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaStreamingTest extends TestCase
{
    use RefreshDatabase;

    private MediaItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');
        Storage::disk('media')->put('Music/song.mp3', '0123456789');
        $this->item = MediaItem::factory()->audio()->create([
            'path' => 'Music/song.mp3',
            'path_hash' => MediaItem::hashPath('media', 'Music/song.mp3'),
            'size' => 10,
        ]);
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('media.stream', $this->item))->assertRedirect(route('login'));
        $this->get(route('home'))->assertRedirect(route('login'));
    }

    public function test_guests_can_browse_when_login_is_disabled(): void
    {
        config(['rukre.require_login' => false]);

        $this->get(route('home'))->assertOk();
        $this->get(route('media.stream', $this->item))->assertOk();
    }

    public function test_it_streams_the_whole_file(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('media.stream', $this->item));

        $response->assertOk()->assertHeader('Accept-Ranges', 'bytes');
        $this->assertSame('0123456789', $response->streamedContent());
        $this->assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_it_serves_byte_ranges_for_seeking(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('media.stream', $this->item), ['Range' => 'bytes=2-5']);

        $response->assertStatus(206)->assertHeader('Content-Range', 'bytes 2-5/10');
        $this->assertSame('2345', $response->streamedContent());
    }

    public function test_remote_disks_stream_byte_ranges(): void
    {
        $this->partialMock(MediaLibrary::class, fn ($mock) => $mock->shouldReceive('isLocal')->andReturnFalse());
        $user = User::factory()->create();

        $partial = $this->actingAs($user)->get(route('media.stream', $this->item), ['Range' => 'bytes=7-']);
        $partial->assertStatus(206)->assertHeader('Content-Range', 'bytes 7-9/10')->assertHeader('Content-Length', '3');
        $this->assertSame('789', $partial->streamedContent());

        $full = $this->actingAs($user)->get(route('media.stream', $this->item));
        $full->assertOk()->assertHeader('Content-Length', '10');
        $this->assertSame('0123456789', $full->streamedContent());

        $this->actingAs($user)->get(route('media.stream', $this->item), ['Range' => 'bytes=50-'])
            ->assertStatus(416)
            ->assertHeader('Content-Range', 'bytes */10');
    }

    public function test_download_is_sent_as_attachment(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('media.download', $this->item));

        $response->assertOk();
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_missing_files_return_not_found(): void
    {
        Storage::disk('media')->delete('Music/song.mp3');

        $this->actingAs(User::factory()->create())->get(route('media.stream', $this->item))->assertNotFound();
    }

    public function test_a_placeholder_cover_is_drawn_when_there_is_no_artwork(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('media.cover', $this->item));

        $response->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertNotFalse(simplexml_load_string($response->getContent()));
    }

    public function test_progress_is_saved_per_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('media.progress', $this->item), ['position' => 42.5, 'progress' => 0.5])
            ->assertOk()
            ->assertJson(['saved' => true]);

        $this->actingAs($user)
            ->postJson(route('media.progress', $this->item), ['position' => 99, 'progress' => 0.97])
            ->assertOk();

        $this->assertDatabaseCount('media_progress', 1);
        $this->assertDatabaseHas('media_progress', ['user_id' => $user->id, 'media_item_id' => $this->item->id, 'position' => 99, 'completed' => true]);
    }
}
