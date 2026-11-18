<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        // Jawaban sama untuk email terdaftar maupun tidak, supaya daftar akun tidak bisa ditebak.
        return back()->with('success', 'Jika email itu terdaftar, tautan untuk mengatur ulang password sudah dikirim. Cek kotak masuk dan folder spam.');
    }

    public function showReset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PasswordReset) {
            return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan masuk dengan password baru.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa. Minta tautan baru.']);
    }
}
