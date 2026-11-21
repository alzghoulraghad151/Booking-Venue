<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdateOwnerProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('owner.profile', [
            'user' => $user,
            'venuesCount' => $user->venues()->count(),
        ]);
    }

    public function update(UpdateOwnerProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->safe()->only(['name', 'email', 'phone']));

        return redirect()
            ->route('owner.profile.edit')
            ->with('success', 'Your profile was updated.');
    }
}
