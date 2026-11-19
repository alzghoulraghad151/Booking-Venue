<?php

namespace App\Http\Controllers;
use App\Models\SpaceLayout;
use App\Models\Space;
use Illuminate\Http\Request;

class OwnerSpaceLayoutController extends Controller
{
    public function index()
{
    $layouts = SpaceLayout::with('space')
        ->whereHas('space.venue', function ($query) {
            $query->where('owner_id', auth()->guard()->id());
        })
        ->get();
    return view('owner.space-layouts.index', compact('layouts'));
}

public function create()
{
    $spaces = Space::whereHas('venue', function ($query) {
        $query->where('owner_id', auth()->guard()->id());
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
        $query->where('owner_id', auth()->guard()->id());
    })->findOrFail($data['space_id']);

    SpaceLayout::create($data);

    return redirect()->route('owner.space-layouts.index');
}
public function edit(int $id)
{
    $layout = SpaceLayout::whereHas('space.venue', function ($query) {
        $query->where('owner_id', auth()->guard()->id());
    })->findOrFail($id);

    return view('owner.space-layouts.edit', compact('layout'));
}
}
