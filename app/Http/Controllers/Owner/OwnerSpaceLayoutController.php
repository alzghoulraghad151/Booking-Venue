<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use App\Models\Space;
use App\Models\SpaceLayout;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OwnerSpaceLayoutController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'space_id' => 'nullable|integer|min:1',
        ]);

        $search = $filters['search'] ?? '';
        $spaceId = $filters['space_id'] ?? null;
        $query = $this->ownerLayouts()->with(['space', 'eventTypes']);

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($spaceId !== null) {
            $query->where('space_id', $spaceId);
        }

        $layouts = $query->orderBy('name')->get();
        $spaces = $this->ownerSpaces()->orderBy('name')->get(['id', 'name']);

        return view('owner.space-layouts.index', compact('layouts', 'spaces', 'search', 'spaceId'));
    }

    public function create()
    {
        $spaces = $this->ownerSpaces()->orderBy('name')->get(['id', 'name', 'capacity']);
        $eventTypes = EventType::orderBy('name')->get(['id', 'name']);

        return view('owner.space-layouts.create', compact('spaces', 'eventTypes'));
    }

    public function store(Request $request)
    {
        $this->saveLayout($request, new SpaceLayout());

        return redirect()->route('owner.space-layouts.index');
    }

    public function show(string $id)
    {
        $layout = $this->ownerLayouts()->with(['space', 'eventTypes'])->findOrFail($id);

        return view('owner.space-layouts.show', compact('layout'));
    }

    public function edit(string $id)
    {
        $layout = $this->ownerLayouts()->findOrFail($id);
        $spaces = $this->ownerSpaces()->orderBy('name')->get(['id', 'name', 'capacity']);
        $eventTypes = EventType::orderBy('name')->get(['id', 'name']);

        return view('owner.space-layouts.edit', compact('layout', 'spaces', 'eventTypes'));
    }

    public function update(Request $request, string $id)
    {
        $this->saveLayout($request, $this->ownerLayouts()->findOrFail($id));

        return redirect()->route('owner.space-layouts.index');
    }

    public function destroy(string $id)
    {
        $layout = $this->ownerLayouts()->findOrFail($id);

        $layout->getConnection()->transaction(function () use ($id) {
            $layout = $this->ownerLayouts()->lockForUpdate()->findOrFail($id);

            if ($layout->getConnection()->table('booking_details')->where('space_layout_id', $id)->exists()) {
                throw ValidationException::withMessages([
                    'layout' => 'This layout is used in a booking and cannot be deleted.',
                ]);
            }

            $layout->delete();
        });

        return redirect()->route('owner.space-layouts.index');
    }

    private function ownerSpaces()
    {
        return Space::whereRelation('venue', 'owner_id', auth()->id());
    }

    private function ownerLayouts()
    {
        return SpaceLayout::whereRelation('space.venue', 'owner_id', auth()->id());
    }

    private function saveLayout(Request $request, SpaceLayout $layout): void
    {
        $data = $request->validate([
            'space_id' => 'required|integer|min:1',
            'name' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'event_type_ids' => 'required|array|min:1',
            'event_type_ids.*' => 'required|integer|distinct|exists:event_types,id',
        ]);

        $space = $this->ownerSpaces()->find($data['space_id']);

        if (!$space) {
            throw ValidationException::withMessages([
                'space_id' => 'Choose a space that belongs to your venues.',
            ]);
        }

        if ($data['capacity'] > $space->capacity) {
            throw ValidationException::withMessages([
                'capacity' => 'Layout capacity cannot exceed the space capacity of ' . $space->capacity . '.',
            ]);
        }

        $layout->getConnection()->transaction(function () use ($layout, $data) {
            if ($layout->exists) {
                $layout = $this->ownerLayouts()->lockForUpdate()->findOrFail($layout->id);

                if ((string) $layout->space_id !== (string) $data['space_id']
                    && $layout->getConnection()->table('booking_details')->where('space_layout_id', $layout->id)->exists()) {
                    throw ValidationException::withMessages([
                        'space_id' => 'A layout used in a booking cannot move to another space.',
                    ]);
                }
            }

            $layout->space_id = $data['space_id'];
            $layout->name = $data['name'];
            $layout->capacity = $data['capacity'];
            $layout->save();
            $layout->eventTypes()->sync($data['event_type_ids']);
        });
    }
}
