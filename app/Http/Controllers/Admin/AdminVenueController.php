<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminVenueIndexRequest;
use App\Models\Venue;
use Illuminate\View\View;
use Illuminate\Http\Request;

class AdminVenueController extends Controller
{
    public function index(AdminVenueIndexRequest $request): View
    {
        $filters = $request->validated();
        $search = $filters['search'] ?? '';
        $status = $filters['status'] ?? '';

        $query = Venue::with('owner');

        if ($search !== '') {
            $pattern = '%' . $search . '%';

            $query->where(function ($venues) use ($pattern) {
                $venues->where('name', 'like', $pattern)
                    ->orWhereHas('owner', function ($owners) use ($pattern) {
                        $owners->where('name', 'like', $pattern);
                    });
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $venues = $query->latest()
            ->paginate(15)
            ->appends($filters);

        return view('admin.venues.index', [
            'venues' => $venues,
            'search' => $search,
            'status' => $status,
        ]);
    }



    public function show(Venue $venue): View
{
   $venue->load(['owner', 'spaces', 'images']);

    return view('admin.venues.show', ['venue' => $venue]);
}









public function approve(Venue $venue): RedirectResponse
{
    return $this->review($venue, 'approved');
}

public function reject(Venue $venue): RedirectResponse
{
    return $this->review($venue, 'rejected');
}

private function review(Venue $venue, string $status): RedirectResponse
{
    $updated = Venue::query()
        ->whereKey($venue->getKey())
        ->where('status', 'pending')
        ->update(['status' => $status]);

    if ($updated === 0) {
        return redirect()
            ->route('admin.venues.show', $venue)
            ->withErrors(['review' => 'Only pending venues can be reviewed.']);
    }

    return redirect()
        ->route('admin.venues.show', $venue)
        ->with('success', "Venue {$status}.");
}




public function updateStatus(Request $request, Venue $venue): RedirectResponse
{
    $validated = $request->validate([
        'status' => ['required', 'string', 'in:approved,inactive'],
    ]);

    $status = $validated['status'];
    $expectedStatus = $status === 'approved' ? 'inactive' : 'approved';

    $updated = Venue::query()
        ->whereKey($venue->getKey())
        ->where('status', $expectedStatus)
        ->update(['status' => $status]);

    if ($updated === 0) {
        return redirect()
            ->route('admin.venues.show', $venue)
            ->withErrors(['status' => 'This action is unavailable for the current venue status.']);
    }

    return redirect()
        ->route('admin.venues.show', $venue)
        ->with('success', $status === 'approved' ? 'Venue activated.' : 'Venue deactivated.');
}

}