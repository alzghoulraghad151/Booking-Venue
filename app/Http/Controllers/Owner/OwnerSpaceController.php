<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreSpaceRequest;
use App\Http\Requests\Owner\UpdateSpaceRequest;
use App\Models\Space;
use App\Services\SpaceImageStorage;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class OwnerSpaceController extends Controller
{
    public function index(Request $request): View
    {
        $allowedSorts = ['name', 'capacity', 'hourly_rate', 'status', 'created_at'];
        $filters = array_merge([
            'search' => '',
            'status' => '',
            'sort' => 'created_at',
            'direction' => 'desc',
            'per_page' => '10',
        ], array_filter(
            $request->validate([
                'search' => ['nullable', 'string', 'max:100'],
                'status' => ['nullable', Rule::in(['active', 'inactive', 'maintenance'])],
                'sort' => ['nullable', Rule::in($allowedSorts)],
                'direction' => ['nullable', Rule::in(['asc', 'desc'])],
                'per_page' => ['nullable', Rule::in(['5', '10', '20', '50'])],
            ]),
            static fn ($value) => $value !== null && $value !== ''
        ));

        $query = Space::query()
            ->with(['venue', 'images'])
            ->whereHas('venue', fn ($venueQuery) => $venueQuery->where('owner_id', $request->user()->getKey()));

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($spaceQuery) use ($search) {
                $spaceQuery->where('name', 'like', "%{$search}%")
                    ->orWhereHas('venue', fn ($venueQuery) => $venueQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        $spaces = $query
            ->orderBy($filters['sort'], $filters['direction'])
            ->paginate((int) $filters['per_page'])
            ->withQueryString();

        return view('owner.spaces.index', compact('spaces', 'filters'));
    }

    public function create(Request $request): View
    {
        $venues = $request->user()->venues()->orderBy('name')->get(['id', 'name']);

        return view('owner.spaces.create', compact('venues'));
    }

    public function store(StoreSpaceRequest $request, SpaceImageStorage $imageStorage): RedirectResponse
    {
        $data = $request->validated();
        $venue = $request->user()->venues()->findOrFail($data['venue_id']);
        $attributes = Arr::only($data, ['name', 'description', 'capacity', 'hourly_rate', 'status']);
        $space = $venue->spaces()->create($attributes);
        $uploadedImages = [];

        try {
            $uploadedImages = $imageStorage->upload($request->file('images', []), (int) $space->getKey());

            DB::transaction(function () use ($space, $uploadedImages): void {
                $needsPrimary = true;

                foreach ($uploadedImages as $image) {
                    $space->images()->create([
                        ...$image,
                        'is_primary' => $needsPrimary,
                    ]);

                    $needsPrimary = false;
                }
            });
        } catch (Throwable $exception) {
            $imageStorage->discard($uploadedImages);

            try {
                $space->delete();
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }

            if ($exception instanceof HttpClientException || $exception instanceof RuntimeException) {
                report($exception);

                return back()->withInput($request->safe()->except('images'))->withErrors([
                    'images' => 'The space and images could not be saved. Please try again.',
                ]);
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
        SpaceImageStorage $imageStorage
    ): RedirectResponse {
        $data = $request->validated();
        $attributes = Arr::only($data, ['name', 'description', 'capacity', 'hourly_rate', 'status']);
        $uploadedImages = [];
        $removedImages = [];

        try {
            $uploadedImages = $imageStorage->upload($request->file('images', []), (int) $space->getKey());

            DB::transaction(function () use ($space, $attributes, $data, $uploadedImages, &$removedImages): void {
                $space = Space::query()->lockForUpdate()->findOrFail($space->getKey());
                $removeIds = $data['remove_images'] ?? [];
                $imagesToRemove = $space->images()->whereKey($removeIds)->get();

                if ($imagesToRemove->count() !== count($removeIds)) {
                    throw ValidationException::withMessages([
                        'images' => 'One or more selected images are no longer available for removal.',
                    ]);
                }

                $finalCount = $space->images()->count() - $imagesToRemove->count() + count($uploadedImages);

                if ($finalCount < 1 || $finalCount > 3) {
                    throw ValidationException::withMessages([
                        'images' => 'A space must have between 1 and 3 images after saving.',
                    ]);
                }

                $space->update($attributes);

                foreach ($imagesToRemove as $image) {
                    $removedImages[] = [
                        'image_url' => $image->image_url,
                        'cloudinary_public_id' => $image->cloudinary_public_id,
                    ];
                    $image->delete();
                }

                if (! $space->images()->where('is_primary', true)->exists()) {
                    $space->images()->orderBy('id')->first()?->update(['is_primary' => true]);
                }

                $needsPrimary = ! $space->images()->where('is_primary', true)->exists();

                foreach ($uploadedImages as $image) {
                    $space->images()->create([
                        ...$image,
                        'is_primary' => $needsPrimary,
                    ]);

                    $needsPrimary = false;
                }
            });
        } catch (Throwable $exception) {
            $imageStorage->discard($uploadedImages);

            if ($exception instanceof HttpClientException || $exception instanceof RuntimeException) {
                report($exception);

                return back()->withInput($request->safe()->except('images'))->withErrors([
                    'images' => 'The space images could not be saved. Please try again.',
                ]);
            }

            throw $exception;
        }

        $imageStorage->delete($removedImages);

        return redirect()
            ->route('owner.spaces.index')
            ->with('success', 'Space updated successfully.');
    }

    public function destroy(Request $request, Space $space, SpaceImageStorage $imageStorage): RedirectResponse
    {
        $this->ensureOwner($request, $space);
        $images = $space->images()
            ->get(['image_url', 'cloudinary_public_id'])
            ->map(fn ($image) => [
                'image_url' => $image->image_url,
                'cloudinary_public_id' => $image->cloudinary_public_id,
            ])
            ->all();

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

        $imageStorage->delete($images);

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
