<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Space;
use App\Models\SpaceLayout;
use Illuminate\Http\Request;

class OwnerSpaceLayoutController extends Controller
{
    public function index()
    {
        $layouts = SpaceLayout::with('space')
            ->whereRelation('space.venue', 'owner_id', auth()->guard()->id())
            ->get();

        return view('owner.space-layouts.index', compact('layouts'));
    }

    public function create()
    {
        $spaces = Space::whereRelation('venue', 'owner_id', auth()->guard->id())
            ->orderBy('name')
            ->get();

        return view('owner.space-layouts.create', compact('spaces'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'space_id' => 'required|integer|min:1',
            'name' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
        ]);

        Space::whereRelation('venue', 'owner_id', auth()->id())
            ->findOrFail($data['space_id']);

        SpaceLayout::create($data);

        return redirect()->route('owner.space-layouts.index');
    }

    public function edit(int $id)
    {
        $layout = SpaceLayout::whereRelation('space.venue', 'owner_id', auth()->id())
            ->findOrFail($id);

        return view('owner.space-layouts.edit', compact('layout'));
    }
}