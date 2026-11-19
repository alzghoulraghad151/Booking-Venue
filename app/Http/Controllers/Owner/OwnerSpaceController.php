<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreSpaceRequest;
use App\Http\Requests\Owner\UpdateSpaceRequest;
use App\Models\Space;
use App\Services\ImageService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class OwnerSpaceController extends Controller
{
    public function index(Request $request): View
    {
        $spaces = Space::query()
            ->with(['venue', 'images'])
            ->whereHas('venue', fn ($query) => $query->where('owner_id', $request->user()->getKey()))
            ->latest()
            ->paginate(15);

        return view('owner.spaces.index', compact('spaces'));
    }

    public function create(Request $request): View
    {
        $venues = $request->user()->venues()->orderBy('name')->get(['id', 'name']);

        return view('owner.spaces.create', compact('venues'));
    }

    public function store(StoreSpaceRequest $request, ImageService $imageService): RedirectResponse
    {
        $data = $request->validated();
        $venue = $request->user()->venues()->findOrFail($data['venue_id']);
        $attributes = Arr::only($data, ['name', 'description', 'capacity', 'hourly_rate', 'status']);
        $uploadedImages = $request->file('images', []);
        $storedPaths = [];
        try {
            DB::transaction(function () use ($venue, $attributes, $uploadedImages, $imageService, &$storedPaths): void {
                $space = $venue->spaces()->create($attributes);

                foreach ($uploadedImages as $image) {
                    $path = $imageService->store($image, 'spaces/'.$space->getKey());
                    $storedPaths[] = $path;

                    $space->images()->create([
                        'image_url' => $path,
                        'is_primary' => ! $space->images()->where('is_primary', true)->exists(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                $imageService->delete($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('owner.spaces.index')
            ->with('success', 'Space created successfully.');
    }

    public function edit(Request $request, Space $space): View
    {
        $this->ensureOwner($request, $space);
        $space->load(['venue', 'images']);

        return view('owner.spaces.edit', compact('space'));
    }

    public function update(
        UpdateSpaceRequest $request,
        Space $space,
        ImageService $imageService
    ): RedirectResponse {
        $data = $request->validated();
        $attributes = Arr::only($data, ['name', 'description', 'capacity', 'hourly_rate', 'status']);
        $uploadedImages = $request->file('images', []);
        $storedPaths = [];
        $removedPaths = [];

        try {
            DB::transaction(function () use ($space, $attributes, $data, $uploadedImages, $imageService, &$storedPaths, &$removedPaths): void {
                $space->update($attributes);

                foreach ($data['remove_images'] ?? [] as $imageId) {
                    $image = $space->images()->findOrFail($imageId);
                    $removedPaths[] = $image->image_url;
                    $image->delete();
                }

                if (! $space->images()->where('is_primary', true)->exists()) {
                    $space->images()->orderBy('id')->first()?->update(['is_primary' => true]);
                }

                foreach ($uploadedImages as $image) {
                    $path = $imageService->store($image, 'spaces/'.$space->getKey());
                    $storedPaths[] = $path;

                    $space->images()->create([
                        'image_url' => $path,
                        'is_primary' => ! $space->images()->where('is_primary', true)->exists(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                $imageService->delete($path);
            }

            throw $exception;
        }

        foreach ($removedPaths as $path) {
            $imageService->delete($path);
        }

        return redirect()
            ->route('owner.spaces.index')
            ->with('success', 'Space updated successfully.');
    }

    public function destroy(Request $request, Space $space, ImageService $imageService): RedirectResponse
    {
        $this->ensureOwner($request, $space);
        $imagePaths = $space->images()->pluck('image_url')->all();

        try {
            DB::transaction(function () use ($space): void {
                $space->images()->delete();
                $space->delete();
            });
        } catch (QueryException) {
            return back()->withErrors([
                'space' => 'This space cannot be deleted while it is used by a layout, package, or booking.',
            ]);
        }

        foreach ($imagePaths as $path) {
            $imageService->delete($path);
        }

        return redirect()
            ->route('owner.spaces.index')
            ->with('success', 'Space deleted successfully.');
    }

    private function ensureOwner(Request $request, Space $space): void
    {
        abort_unless(
            $space->venue()->where('owner_id', $request->user()->getKey())->exists(),
            404
        );
    }
}
