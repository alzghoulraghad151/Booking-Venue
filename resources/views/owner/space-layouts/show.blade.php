<h1>{{ $layout->name }}</h1>

<p>Space: {{ $layout->space->name }}</p>
<p>Capacity: {{ $layout->capacity }}</p>
<p>Event Types: {{ $layout->eventTypes->pluck('name')->join(', ') ?: 'ΓÇö' }}</p>

<a href="{{ route('owner.space-layouts.index') }}">Back to Space Layouts</a>
