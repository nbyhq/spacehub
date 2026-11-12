<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_profile(): void
    {
        $user = User::factory()->create(['name' => 'Nawa Tes']);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Nawa Tes');
    }

    public function test_user_can_update_name_and_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), ['name' => 'Nama Baru', 'email' => 'baru@contoh.test'])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nama Baru', 'email' => 'baru@contoh.test']);
    }

    public function test_user_can_keep_own_email(): void
    {
        $user = User::factory()->create(['email' => 'saya@contoh.test']);

        $this->actingAs($user)
            ->patch(route('profile.update'), ['name' => 'Nama Baru', 'email' => 'saya@contoh.test'])
            ->assertSessionHasNoErrors();
    }

    public function test_user_cannot_take_email_of_another_user(): void
    {
        User::factory()->create(['email' => 'dipakai@contoh.test']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), ['name' => 'Nama', 'email' => 'dipakai@contoh.test'])
            ->assertSessionHasErrors('email');
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => 'PasswordLama1']);

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'PasswordLama1',
                'password' => 'PasswordBaru22',
                'password_confirmation' => 'PasswordBaru22',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('PasswordBaru22', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'PasswordLama1']);

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'salah-total',
                'password' => 'PasswordBaru22',
                'password_confirmation' => 'PasswordBaru22',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('PasswordLama1', $user->fresh()->password));
    }

    public function test_new_password_must_be_confirmed_and_different(): void
    {
        $user = User::factory()->create(['password' => 'PasswordLama1']);

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'PasswordLama1',
                'password' => 'PasswordLama1',
                'password_confirmation' => 'PasswordLama1',
            ])
            ->assertSessionHasErrors('password');

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'PasswordLama1',
                'password' => 'PasswordBaru22',
                'password_confirmation' => 'beda-lagi',
            ])
            ->assertSessionHasErrors('password');
    }
}
