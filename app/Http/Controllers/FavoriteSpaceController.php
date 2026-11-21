<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteSpaceController extends Controller
{
    public function toggle(Request $request, Space $space): JsonResponse|RedirectResponse
    {
        $favorites = $request->user()->favoriteSpaces();

        $isFavorite = ! $favorites->whereKey($space->getKey())->exists();

        if (! $isFavorite) {
            $favorites->detach($space->getKey());
            $message = 'Place removed from favorites.';
        } else {
            $favorites->syncWithoutDetaching([$space->getKey()]);
            $message = 'Place added to favorites.';
        }

        if ($request->expectsJson()) {
            return response()->json(['is_favorite' => $isFavorite]);
        }

        return back()->with('favourite_status', $message);
    }
}