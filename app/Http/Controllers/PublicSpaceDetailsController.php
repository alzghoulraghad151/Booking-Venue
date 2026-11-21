<?php

namespace App\Http\Controllers;
use App\Models\Package;
use App\Models\Review;
use App\Models\Space;
use Illuminate\View\View;
use App\Models\EventType;

class PublicSpaceDetailsController extends Controller
{
    public function show(Space $space): View
    {
        $space->load('venue');
        $venue = $space->venue;

        abort_unless(
            $space->status === 'active'
                && $venue?->status === 'approved'
                && $venue->is_active_by_owner,
            404
        );
        $images = $space->images()
    ->orderByDesc('is_primary')
    ->orderBy('id')
    ->get(['id', 'image_url']);

        $layouts = $space->spaceLayouts()
    ->with('eventTypes:id')
    ->orderBy('name')
    ->orderBy('id')
    ->get();


    $eventTypes = EventType::query()
    ->orderBy('name')
    ->get(['id', 'name']);


$packages = Package::query()
    ->with(['items', 'eventTypes'])
    ->where('is_active', true)
    ->whereHas('items', fn ($items) => $items->where('space_id', $space->getKey()))
    ->latest()
    ->orderByDesc('id')
    ->whereHas('eventTypes')

    ->get();

    $reviews = Review::query()
    ->with('booking.customer:id,name')
    ->whereHas('booking.details', function ($details) use ($space) {
        $details->where('space_id', $space->getKey());
    })
    ->orderByDesc('id')
    ->get(['id', 'booking_id', 'rating', 'comment']);

return view('public.spaces.show', compact(
    'space', 'venue', 'images', 'layouts', 'packages', 'reviews','eventTypes'
));
    }
}