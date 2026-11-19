<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\SpaceLayout;
use App\Models\Space;
use Illuminate\Http\Request;

class OwnerSpaceLayoutController extends Controller
{
    public function index()
{
    $layouts = SpaceLayout::with('space')
        ->whereHas('space.venue', function ($query) {
            $query->where('owner_id', auth()->id());
        })
        ->get();
    return view('owner.space-layouts.index', compact('layouts'));
}

public function create()
{
    $spaces = Space::whereHas('venue', function ($query) {
        $query->where('owner_id', auth()->id());
    })->orderBy('name')->get();

    return view('owner.space-layouts.create', compact('spaces'));
}
public function store(Request $request)
{
    $data = $request->validate([
        'space_id' => 'required|integer|min:1',
        'name' => 'required|string|max:255',
        'capacity' => 'required|integer|min:1',
    ]);

    Space::whereHas('venue', function ($query) {
        $query->where('owner_id', auth()->id());
    })->findOrFail($data['space_id']);

    SpaceLayout::create($data);

    return redirect()->route('owner.space-layouts.index');
}
}
