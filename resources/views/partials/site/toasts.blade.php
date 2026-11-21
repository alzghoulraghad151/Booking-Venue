@php
    $notifications = collect([
        'success' => session('success'),
        'status' => session('status'),
        'warning' => session('warning'),
        'error' => session('error'),
        'info' => session('info'),
    ])->filter(fn ($message) => is_string($message) && trim($message) !== '');

    if ($errors->any() && ! $notifications->has('error')) {
        $notifications->put('error', $errors->first());
    }
@endphp

@if ($notifications->isNotEmpty())
    <aside class="convene-toasts" aria-label="Notifications">
        @foreach ($notifications as $type => $message)
            <div class="convene-toast convene-toast--{{ $type }}"
                 role="{{ in_array($type, ['error', 'warning'], true) ? 'alert' : 'status' }}"
                 data-convene-toast>
                <span dir="auto">{{ $message }}</span>
                <button type="button" aria-label="Dismiss notification" data-convene-toast-close>×</button>
            </div>
        @endforeach
    </aside>
    <script src="{{ asset('js/convene-toasts.js') }}" defer></script>
@endif
