<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.spacehub');
        Gate::define('admin', fn (User $user) => $user->role === 'admin');

        // Email reset password berbahasa Indonesia.
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Atur ulang password SpaceHub')
                ->greeting('Halo, '.$notifiable->name)
                ->line('Kami menerima permintaan untuk mengatur ulang password akun SpaceHub Anda.')
                ->action('Atur ulang password', $url)
                ->line("Tautan ini berlaku selama {$minutes} menit.")
                ->line('Jika Anda tidak merasa memintanya, abaikan email ini. Password Anda tidak akan berubah.')
                ->salutation('Terima kasih, SpaceHub');
        });
    }
}
