<article class="package-option" data-event-type-ids="{{ implode(' ', $package->eventTypes->modelKeys()) }}" hidden>
    <button type="button" class="package-option__preview" data-package-open aria-haspopup="dialog">
        <span class="package-option__title">{{ $package->name }}</span>
        <span class="package-option__description">{{ $package->description }}</span>
        <span class="package-option__facts">
            <strong>{{ number_format((float) $package->price, 2) }} JOD</strong>
            <span>{{ $package->min_guests }}–{{ $package->max_guests }} guests</span>
        </span>
        <span class="package-option__services">
            @foreach ($package->items->whereNull('space_id')->take(2) as $service)
                <span>{{ $service->name }}</span>
            @endforeach
            @if ($package->items->whereNull('space_id')->count() > 2)
                <span aria-label="More included services">&hellip;</span>
            @endif
        </span>
        <span class="package-option__details">View details</span>
    </button>
    <label class="package-option__choice">
        <input type="radio" name="package_choice" value="{{ $package->id }}"
               @checked((string) old('package_id') === (string) $package->id) disabled>
        Select package
        <span class="package-option__sr-only">{{ $package->name }}</span>
    </label>

    <dialog class="package-details-dialog" aria-labelledby="package-title-{{ $package->id }}">
        <button type="button" class="space-details__more" data-package-close autofocus>Close</button>
        <h3 id="package-title-{{ $package->id }}">{{ $package->name }}</h3>
        @if (filled($package->description))
            <p class="package-details-dialog__description">{{ $package->description }}</p>
        @endif
        <p>{{ number_format((float) $package->price, 2) }} JOD · {{ $package->min_guests }}–{{ $package->max_guests }} guests</p>
        <h4>Included Services</h4>
        <ul>
            @forelse ($package->items->whereNull('space_id') as $service)
                <li>
                    <strong>{{ $service->name }}</strong>
                    @if (filled($service->description))
                        <p class="package-details-dialog__description">{{ $service->description }}</p>
                    @endif
                </li>
            @empty
                <li>No additional services listed.</li>
            @endforelse
        </ul>
    </dialog>
</article>
