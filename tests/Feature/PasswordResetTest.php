<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_loads_and_login_links_to_it(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('Lupa password');
        $this->get(route('login'))->assertOk()->assertSee(route('password.request'), false);
    }

    public function test_reset_link_is_sent_to_registered_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_gets_the_same_answer_and_no_mail(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'tidak-ada@example.com'])
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_email_is_required_and_must_be_valid(): void
    {
        $this->post(route('password.email'), ['email' => 'bukan-email'])
            ->assertSessionHasErrors('email');
    }

    public function test_mail_is_in_indonesian_and_links_to_reset_form(): void
    {
        Notification::fake();
        $user = User::factory()->create(['name' => 'Sari Dewi']);

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $mail = $notification->toMail($user);

            $this->assertSame('Atur ulang password SpaceHub', $mail->subject);
            $this->assertStringContainsString('Sari Dewi', $mail->greeting);
            $this->assertStringContainsString('/reset-password/'.$notification->token, $mail->actionUrl);
            $this->assertStringContainsString(urlencode($user->email), $mail->actionUrl);

            return true;
        });
    }

    public function test_reset_form_shows_token_and_email(): void
    {
        $this->get(route('password.reset', ['token' => 'abc123', 'email' => 'a@b.com']))
            ->assertOk()
            ->assertSee('abc123')
            ->assertSee('a@b.com');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ])->assertRedirect(route('login'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('passwordbaru123', $user->fresh()->password));

        $this->post(route('login'), ['email' => $user->email, 'password' => 'passwordbaru123'])
            ->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_token_can_only_be_used_once(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $payload = [
            'token' => $token, 'email' => $user->email,
            'password' => 'passwordbaru123', 'password_confirmation' => 'passwordbaru123',
        ];

        $this->post(route('password.store'), $payload)->assertRedirect(route('login'));
        $this->post(route('password.store'), $payload)->assertSessionHasErrors('email');
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $oldHash = $user->password;

        $this->post(route('password.store'), [
            'token' => 'token-palsu', 'email' => $user->email,
            'password' => 'passwordbaru123', 'password_confirmation' => 'passwordbaru123',
        ])->assertSessionHasErrors('email');

        $this->assertSame($oldHash, $user->fresh()->password);
    }

    public function test_new_password_must_be_confirmed_and_long_enough(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post(route('password.store'), [
            'token' => $token, 'email' => $user->email,
            'password' => 'pendek', 'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');

        $this->post(route('password.store'), [
            'token' => $token, 'email' => $user->email,
            'password' => 'passwordbaru123', 'password_confirmation' => 'beda-sekali',
        ])->assertSessionHasErrors('password');
    }

    public function test_logged_in_user_is_redirected_away_from_forgot_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('password.request'))
            ->assertRedirect();
    }
}
