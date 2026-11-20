<?php

namespace App\Http\Controllers\Owner;

use App\Models\EventType;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class OwnerPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
    $filters = $request->validate([
        'search' => 'nullable|string|max:255',
        'status' => 'nullable|in:active,inactive',
    ]);

    $query = Package::where('owner_id', Auth::id())
        ->with('eventTypes');

    $search = $filters['search'] ?? '';
    if ($search !== '') {
        $query->where('name', 'like', '%' . $search . '%');
    }

    $status = $filters['status'] ?? '';
    if ($status !== '') {
        $query->where('is_active', $status === 'active');
    }

    $packages = $query->get();

    return view('owner.packages.index', compact('packages', 'search', 'status'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    { 
       $spaces = Space::whereRelation('venue', 'owner_id', Auth::id())
        ->orderBy('name')
        ->get(['id', 'name']);

    $eventTypes = EventType::orderBy('name')->get(['id', 'name']);

    return view('owner.packages.create', compact('spaces', 'eventTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|decimal:0,2|min:0.01|max:99999999.99',
        'duration_minutes' => 'required|integer|min:1|max:4294967295',
        'min_guests' => 'required|integer|min:1|max:4294967295',
        'max_guests' => 'required|integer|gte:min_guests|max:4294967295',
        'space_ids' => 'required|array|min:1',
        'space_ids.*' => 'required|integer|distinct',
        'event_type_ids' => 'sometimes|array',
        'event_type_ids.*' => 'required|integer|distinct|exists:event_types,id',
        'services' => 'sometimes|array',
        'services.*' => 'array:name',
        'services.*.name' => 'nullable|string|max:255',
    ]);

    $ownerId = $request->user()->id;

    $spaces = Space::whereRelation('venue', 'owner_id', $ownerId)
        ->whereIn('id', $data['space_ids'])
        ->get(['id', 'name']);

    if ($spaces->count() !== count($data['space_ids'])) {
        return back()->withErrors([
            'space_ids' => 'Choose spaces that belong to your venues.',
        ])->withInput();
    }

    $package = new Package();
    $package->owner_id = $ownerId;
    $package->name = $data['name'];
    $package->description = $data['description'] ?? null;
    $package->price = $data['price'];
    $package->duration_minutes = $data['duration_minutes'];
    $package->min_guests = $data['min_guests'];
    $package->max_guests = $data['max_guests'];

    $connection = $package->getConnection();

    $connection->transaction(function () use ($package, $spaces, $data) {
        $package->save();

        foreach ($spaces as $space) {
            $package->items()->create([
                'space_id' => $space->id,
                'name' => $space->name,
            ]);
        }

        foreach ($data['services'] ?? [] as $service) {
            $name = trim($service['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $package->items()->create([
                'space_id' => null,
                'name' => $name,
            ]);
        }

        $package->eventTypes()->sync($data['event_type_ids'] ?? []);
    });

    return redirect()->route('owner.packages.index');
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
{
    $package = Package::where('owner_id', Auth::id())
        ->with(['items.space', 'eventTypes'])
        ->findOrFail($id);

    return view('owner.packages.show', compact('package'));
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
{
    $ownerId = Auth::id();

    $package = Package::where('owner_id', $ownerId)->findOrFail($id);

    $spaces = Space::whereRelation('venue', 'owner_id', $ownerId)
        ->get(['id', 'name']);

    $selectedSpaceIds = $package->items()
        ->whereNotNull('space_id')
        ->pluck('space_id')
        ->all();

    $eventTypes = EventType::orderBy('name')->get(['id', 'name']);
    $selectedEventTypeIds = $package->eventTypes()->pluck('event_types.id')->all();

    $services = $package->items()
    ->whereNull('space_id')
    ->orderBy('id')
    ->get(['id', 'name']);

    return view('owner.packages.edit', compact(
        'package',
        'spaces',
        'selectedSpaceIds',
        'eventTypes',
        'selectedEventTypeIds',
        'services'
    ));
}

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, string $id)
{
    $ownerId = Auth::id();
    $package = Package::where('owner_id', $ownerId)->findOrFail($id);

    $data = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|decimal:0,2|min:0.01|max:99999999.99',
        'duration_minutes' => 'required|integer|min:1|max:4294967295',
        'min_guests' => 'required|integer|min:1|max:4294967295',
        'max_guests' => 'required|integer|gte:min_guests|max:4294967295',
        'space_ids' => 'required|array|min:1',
        'space_ids.*' => 'required|integer|distinct',
        'event_type_ids' => 'sometimes|array',
        'event_type_ids.*' => 'required|integer|distinct|exists:event_types,id',
        'services' => 'sometimes|array',
        'services.*' => 'array:name,remove',
        'services.*.name' => 'nullable|string|max:255',
        'services.*.remove' => 'sometimes|boolean',
    ]);

    $spaces = Space::whereRelation('venue', 'owner_id', $ownerId)
        ->whereIn('id', $data['space_ids'])
        ->get(['id', 'name']);

    if ($spaces->count() !== count($data['space_ids'])) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'space_ids' => 'Select spaces that belong to your venues.',
        ]);
    }

    $package->name = $data['name'];
    $package->price = $data['price'];
    $package->description = $data['description'] ?? null;
    $package->duration_minutes = $data['duration_minutes'];
    $package->min_guests = $data['min_guests'];
    $package->max_guests = $data['max_guests'];

    $package->getConnection()->transaction(function () use ($package, $spaces, $data) {
        $package->save();

        $package->items()
            ->whereNotNull('space_id')
            ->whereNotIn('space_id', $spaces->modelKeys())
            ->delete();

        foreach ($spaces as $space) {
            $package->items()->firstOrCreate(
                ['space_id' => $space->id],
                ['name' => $space->name]
            );
        }

        foreach ($data['services'] ?? [] as $serviceId => $serviceData) {
            $name = trim($serviceData['name'] ?? '');

            if ($serviceId === 'new') {
                if ($name !== '') {
                    $package->items()->create([
                        'space_id' => null,
                        'name' => $name,
                    ]);
                }

                continue;
            }

            if (!ctype_digit((string) $serviceId)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'services' => 'Invalid service reference.',
                ]);
            }

            $service = $package->items()
                ->whereNull('space_id')
                ->findOrFail($serviceId);

            if ($serviceData['remove'] ?? false) {
                $service->delete();

                continue;
            }

            if ($name !== '') {
                $service->name = $name;
                $service->save();
            }
        }

        $package->eventTypes()->sync($data['event_type_ids'] ?? []);
    });

    return redirect()->route('owner.packages.index');
}
public function updateStatus(Request $request, string $id)
{
    $package = Package::where('owner_id', Auth::id())->findOrFail($id);

    $request->validate([
        'is_active' => 'required|boolean',
    ]);

    $package->is_active = $request->boolean('is_active');
    $package->save();

    return redirect()->route('owner.packages.index');
}
    /**
     * Remove the specified resource from storage.
     */
 public function destroy(string $id)
{
    $ownerId = Auth::id();
    $connection = (new Package())->getConnection();

    $connection->transaction(function () use ($connection, $ownerId, $id) {
        $package = Package::where('owner_id', $ownerId)
            ->lockForUpdate()
            ->findOrFail($id);

        $hasBookings = $connection->table('booking_details')
            ->where('package_id', $package->id)
            ->exists();

        if ($hasBookings) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'package' => 'This package is used in a booking. Deactivate it instead.',
            ]);
        }

        $package->delete();
    });

    return redirect()->route('owner.packages.index');
}
}
