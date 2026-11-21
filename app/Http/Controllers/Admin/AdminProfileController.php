<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', ['admin' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = $request->user();

        $profile = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin)],
            'phone' => ['nullable', 'string', 'max:20'],
            'current_password' => [
                Rule::requiredIf(fn () => $request->input('email') !== $admin->email),
                'nullable',
                'current_password:web',
            ],
        ]);

        $admin->fill([
            'name' => $profile['name'],
            'email' => $profile['email'],
            'phone' => $profile['phone'] ?? null,
        ]);

        if ($admin->isDirty('email')) {
            $admin->email_verified_at = null;
        }

        $admin->save();

        return to_route('admin.profile.edit')->with('success', 'Your profile was updated.');
    }
}
