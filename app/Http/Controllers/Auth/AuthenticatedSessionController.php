<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;



class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }



    public function store(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => ['required', 'email', 'max:255'],
        'password' => ['required', 'string', 'max:255'],
    ]);

    if (!Auth::attempt($credentials + ['status' => 'active'])) {
        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    $request->session()->regenerate();

    return redirect()->route(match ($request->user()->role) {
        'venue_owner' => 'owner.venues.index',
        'admin' => 'admin.dashboard',
        default => 'home',
    });
}
}