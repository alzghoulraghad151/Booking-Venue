<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;


class OwnerVenueController extends Controller
{
    public function index(Request $request): View
    {
        $venues = $request->user()
            ->venues()
            ->latest()
            ->paginate(15);

        return view('owner.venues.index', ['venues' => $venues]);
    }
}