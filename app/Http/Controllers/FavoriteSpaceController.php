<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteSpaceController extends Controller
{
    public function toggle(Request $request, Space $space): RedirectResponse
    {
        $favorites = $request->user()->favoriteSpaces();

        if ($favorites->whereKey($space->getKey())->exists()) {
            $favorites->detach($space->getKey());
            $message = 'Place removed from favorites.';
        } else {
            $favorites->syncWithoutDetaching([$space->getKey()]);
            $message = 'Place added to favorites.';
        }

        return back()->with('favourite_status', $message);
    }
}