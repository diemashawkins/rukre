<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Welcome back');
    }

    public function test_users_can_sign_in_and_out(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'secret-password'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'nope'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_accounts_are_created_from_the_command_line(): void
    {
        $this->artisan('rukre:user', ['email' => 'me@example.com', '--name' => 'Me', '--password' => 'long-enough'])
            ->assertSuccessful();

        $user = User::query()->where('email', 'me@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('long-enough', $user->password));

        $this->artisan('rukre:user', ['email' => 'me@example.com', '--password' => 'another-password'])
            ->assertSuccessful();
        $this->assertTrue(Hash::check('another-password', $user->fresh()->password));
    }
}
