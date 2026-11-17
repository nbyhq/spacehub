<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'password' => 'password']);
    }

    public function test_admin_lands_on_dashboard_after_login(): void
    {
        $admin = $this->user('admin');

        $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_regular_user_lands_on_home_after_login(): void
    {
        $user = $this->user('user');

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('home'));
    }

    public function test_intended_page_is_kept_for_admin(): void
    {
        $admin = $this->user('admin');

        $this->get(route('admin.spaces.index'))->assertRedirect(route('login'));

        $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.spaces.index'));
    }

    public function test_wrong_password_does_not_redirect_to_dashboard(): void
    {
        $admin = $this->user('admin');

        $this->from(route('login'))
            ->post(route('login'), ['email' => $admin->email, 'password' => 'salah'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }
}
