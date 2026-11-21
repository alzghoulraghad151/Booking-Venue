<?php

namespace App\Http\Controllers;

use App\Services\CloudinaryImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.personal-details', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $request->user()->update($data);

        return redirect()
            ->route('account.personal-details.edit')
            ->with('status', 'Your details were saved.');
    }

    public function updatePhoto(Request $request, CloudinaryImages $images): RedirectResponse
    {
        $data = $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = $request->user();
        $oldPublicId = $user->profile_photo_public_id;
        $publicId = 'convene/profile-photos/' . Str::uuid();

        try {
            $user->profile_photo_url = $images->upload($data['photo'], $publicId);
            $user->profile_photo_public_id = $publicId;

            if (!$user->saveOrFail()) {
                throw new \RuntimeException('Could not save the profile photo.');
            }
        } catch (\Exception $exception) {
            report($exception);

            try {
                $images->destroy($publicId);
            } catch (\Exception $cleanupException) {
                report($cleanupException);
            }

            return back()->withErrors([
                'photo' => 'Could not save the profile photo. Please try again.',
            ]);
        }

        if ($oldPublicId) {
            try {
                $images->destroy($oldPublicId);
            } catch (\Exception $exception) {
                report($exception);
            }
        }

        return redirect()
            ->route('account.personal-details.edit')
            ->with('status', 'Your profile photo was saved.');
    }

    public function destroyPhoto(Request $request, CloudinaryImages $images): RedirectResponse
{
    $user = $request->user();
    $publicId = $user->profile_photo_public_id;

    $user->profile_photo_url = null;
    $user->profile_photo_public_id = null;

    if (!$user->saveOrFail()) {
        return back()->withErrors([
            'photo' => 'Could not remove the profile photo. Please try again.',
        ]);
    }

    if ($publicId) {
        try {
            $images->destroy($publicId);
        } catch (\Exception $exception) {
            report($exception);

            return back()->withErrors([
                'photo' => 'The photo was removed from your profile, but the image file could not be deleted.',
            ]);
        }
    }

    return redirect()
        ->route('account.personal-details.edit')
        ->with('status', 'Your profile photo was removed.');
}

public function updateEmail(Request $request): RedirectResponse
{
    $user = $request->user();

    $data = $request->validate([
        'email' => [
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($user),
        ],
        'current_password' => ['required', 'current_password'],
    ]);

    if ($user->email !== $data['email']) {
        $user->email = $data['email'];
        $user->email_verified_at = null;
        $user->save();
    }

    return redirect()
        ->route('account.personal-details.edit')
        ->with('status', 'Your email address was saved.');
}

public function updatePassword(Request $request): RedirectResponse
{
    $data = $request->validate([
        'current_password' => ['required', 'string', 'current_password'],
        'password' => [
            'required',
            'string',
            'confirmed',
            Password::min(8)->mixedCase()->symbols(),
        ],
    ]);

    $user = $request->user();
    $user->password = $data['password'];
    $user->saveOrFail();

    $request->session()->regenerate();

    return redirect()
        ->route('account.settings')
        ->with('status', 'Your password was changed.');
}

public function destroyAccount(Request $request): RedirectResponse
{
    $user = $request->user();

    abort_unless($user->role === 'organizer', 403);

    $request->validateWithBag('deleteAccount', [
        'current_password' => ['required', 'string', 'current_password'],
    ]);

    if (!$user->delete()) {
        return back()->withErrors([
            'account' => 'Your account could not be deleted.',
        ], 'deleteAccount');
    }

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login')
        ->with('status', 'Your account was deleted.');
}
}