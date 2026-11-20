<?php

namespace App\Http\Requests;

use App\Models\EventType;
use App\Models\Package;
use App\Models\Space;
use App\Models\SpaceLayout;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

/**
 * Used by BOTH bookings.create (GET) and bookings.store (POST).
 *
 * GET : validates what the space details page sent
 *       (event_type_id, space_layout_id, package_id, guest_count).
 * POST: validates the same fields again + date, start_time, end_time.
 */
class PublicBookingDetailsRequest extends FormRequest
{
    public const TIMEZONE = 'Asia/Amman';

    private const SELECTION_FIELDS = ['event_type_id', 'space_layout_id', 'package_id', 'guest_count'];

    /** @var array{space: Space, layout: SpaceLayout, eventType: EventType, package: ?Package, start: ?CarbonImmutable, end: ?CarbonImmutable}|null */
    private ?array $resolved = null;

    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->role === 'organizer' && $user->status === 'active';
    }

    protected function prepareForValidation(): void
    {
        /** @var Space|null $space */
        $space = $this->route('space');
        $venue = $space?->venue; // null when the venue is soft-deleted

        abort_unless(
            $space
            && $venue
            && $venue->status === 'approved'
            && $venue->is_active_by_owner
            && $space->status === 'active',
            404
        );
    }

    public function rules(): array
    {
        $isStore = $this->isMethod('POST');

        return [
            'event_type_id' => ['required', 'integer', 'exists:event_types,id'],
            'space_layout_id' => ['required', 'integer'],
            'package_id' => ['nullable', 'integer'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:100000'],

            'date' => [$isStore ? 'required' : 'nullable', 'date_format:Y-m-d'],
            'start_time' => [$isStore ? 'required' : 'nullable', 'date_format:H:i'],
            'end_time' => [$isStore ? 'required' : 'nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            // Basic rules failed: do not run the business rules on bad input.
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->resolveSelection($validator);
        });
    }

    /**
     * The validated objects (call only after validation passed).
     *
     * @return array{space: Space, layout: SpaceLayout, eventType: EventType, package: ?Package, start: ?CarbonImmutable, end: ?CarbonImmutable}
     */
    public function resolved(): array
    {
        return $this->resolved
            ?? throw new \LogicException('The booking selection has not been resolved yet.');
    }

    private function resolveSelection(Validator $validator): void
    {
        $errors = $validator->errors();

        /** @var Space $space */
        $space = $this->route('space');
        $space->loadMissing('venue');

        $eventTypeId = $this->integer('event_type_id');
        $guests = $this->integer('guest_count');

        $eventType = EventType::query()->find($eventTypeId);

        // Layout must belong to THIS space and support the event type.
        $layout = SpaceLayout::query()
            ->where('space_id', $space->getKey())
            ->find($this->integer('space_layout_id'));

        if (! $layout) {
            $errors->add('space_layout_id', 'The selected layout does not belong to this space.');
        } else {
            if ($layout->eventTypes()->exists()
                && ! $layout->eventTypes()->whereKey($eventTypeId)->exists()) {
                $errors->add('space_layout_id', 'The selected layout does not support this event type.');
            }

            if ($guests > $layout->capacity) {
                $errors->add('guest_count', "The guest count exceeds the capacity of the selected layout ({$layout->capacity}).");
            }
        }

        if ($guests > $space->capacity) {
            $errors->add('guest_count', "The guest count exceeds the capacity of this space ({$space->capacity}).");
        }

        // Package is optional.
        $package = null;

        if ($this->filled('package_id')) {
            $package = Package::query()->find($this->integer('package_id'));

            if (! $package
                || ! $package->is_active
                || (int) $package->owner_id !== (int) $space->venue->owner_id) {
                $errors->add('package_id', 'The selected package is not available for this venue.');
                $package = null;
            } elseif ($package->eventTypes()->exists()
                && ! $package->eventTypes()->whereKey($eventTypeId)->exists()) {
                $errors->add('package_id', "The package '{$package->name}' does not support this event type.");
            } elseif ($guests < $package->min_guests || $guests > $package->max_guests) {
                $errors->add('package_id', "The package '{$package->name}' accepts {$package->min_guests} to {$package->max_guests} guests.");
            }
        }

        // Date and time (store only). Entered in Asia/Amman, same timezone the owner calendar uses.
        $start = null;
        $end = null;

        if ($this->isMethod('POST')) {
            try {
                $start = CarbonImmutable::createFromFormat(
                    '!Y-m-d H:i',
                    $this->input('date').' '.$this->input('start_time'),
                    self::TIMEZONE
                );
                $end = CarbonImmutable::createFromFormat(
                    '!Y-m-d H:i',
                    $this->input('date').' '.$this->input('end_time'),
                    self::TIMEZONE
                );
            } catch (\Throwable) {
                $start = $end = false;
            }

            if ($start === false || $end === false) {
                $errors->add('date', 'The booking date or time is invalid.');
            } else {
                if ($start->lessThanOrEqualTo(CarbonImmutable::now(self::TIMEZONE))) {
                    $errors->add('start_time', 'The booking must start in the future.');
                }

                if ($end->lessThanOrEqualTo($start)) {
                    $errors->add('end_time', 'The end time must be after the start time.');
                }
            }
        }

        if ($errors->isNotEmpty()) {
            return;
        }

        $this->resolved = [
            'space' => $space,
            'layout' => $layout,
            'eventType' => $eventType,
            'package' => $package,
            'start' => $start ?: null,
            'end' => $end ?: null,
        ];
    }

    protected function getRedirectUrl(): string
    {
        $space = $this->route('space');

        // Failed POST: back to the confirmation page with the same selection.
        if ($this->isMethod('POST')) {
            return route('bookings.create', ['space' => $space] + $this->only(self::SELECTION_FIELDS));
        }

        // Failed GET: the selection itself is invalid, send the user back to the space page.
        return Route::has('spaces.show')
            ? route('spaces.show', $space)
            : url()->previous();
    }
}
