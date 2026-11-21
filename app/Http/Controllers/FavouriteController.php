<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\Space;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;

class FavouriteController extends Controller
{
public function index(Request $request): View|RedirectResponse
{
    $spaces = $request->user()->favoriteSpaces()
        ->where('spaces.status', 'active')
        ->whereHas('venue', function ($query) {
            $query->where('status', 'approved')
                ->where('is_active_by_owner', true);
        })
        ->with([
            'venue',
            'images' => fn ($query) => $query
                ->orderByDesc('is_primary')
                ->orderBy('id'),
        ])
        ->orderBy('spaces.name')
        ->orderBy('spaces.id')
        ->paginate(5);

    if ($spaces->currentPage() > $spaces->lastPage()) {
        return redirect()->to($spaces->url($spaces->lastPage()));
    }

    return view('account.favourites', compact('spaces'));
}

public function store(Request $request, string $space): RedirectResponse
{
    $space = Space::query()
        ->where('status', 'active')
        ->whereHas('venue', function ($query) {
            $query->where('status', 'approved')
                ->where('is_active_by_owner', true);
        })
        ->findOrFail($space);

    try {
        $request->user()->favoriteSpaces()->syncWithoutDetaching([$space->id]);
    } catch (UniqueConstraintViolationException) {

    }

    return back()->with('favourite_status', 'Space saved to favourites.');
}

public function destroy(Request $request, string $space): RedirectResponse
{
    $request->user()->favoriteSpaces()->detach($space);

    return redirect()
        ->route('account.favourites.index')
        ->with('favourite_status', 'Space removed from favourites.');
}

}
