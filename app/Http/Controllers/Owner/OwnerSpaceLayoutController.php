<?php

namespace App\Http\Controllers;
use App\Models\SpaceLayout;
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
}
