<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;


use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
  public function requestForm(): View
{
    return view('auth.forgot-password');
}

public function sendLink(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => ['required', 'email', 'max:255'],
    ]);

    Password::sendResetLink($credentials);

    return back()->with(
        'status',
        'If an account exists for this email, we’ll send you a password reset link.'
    );
}








public function resetForm(Request $request, string $token): View
{
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $request->string('email')->toString(),
    ]);
}

public function resetPassword(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'token' => ['required', 'string'],
        'email' => ['required', 'email', 'max:255'],
        'password' => [
            'required',
            'string',
            'confirmed',
            PasswordRule::min(8)->mixedCase()->symbols(),
            'max:64',
        ],
    ]);

    $status = Password::reset($credentials, function (User $user, string $password): void {
        $user->forceFill([
            'password' => Hash::make($password),
        ]);

        $user->setRememberToken(Str::random(60));
        $user->save();

        event(new PasswordReset($user));
    });

    if ($status === Password::PASSWORD_RESET) {
        return redirect()->route('login')->with('status', 'Password updated. You can now sign in.');
    }

    return back()
        ->withInput($request->only('email'))
        ->withErrors(['email' => 'This reset link is invalid or expired. Request a new one.']);
}
}
