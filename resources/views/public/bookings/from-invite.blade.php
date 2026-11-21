@extends('layouts.users')

@section('title', 'AI Booking Assistant')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/booking-invite.css') }}">
@endpush

@section('content')
@php
    $arabicResult = isset($invitation)
        ? preg_match('/\p{Arabic}/u', $invitation) === 1
        : ($draft['result_language'] ?? old('result_language')) === 'ar';
    $reviewFields = [
        'city' => ['City', 'المدينة', 'text'],
        'date' => ['Date', 'التاريخ', 'date'],
        'start_time' => ['Start time', 'وقت البداية', 'time'],
        'duration_minutes' => ['Duration in minutes', 'المدة بالدقائق', 'number'],
        'in_person_count' => ['Guests in the room', 'الحضور في القاعة', 'number'],
    ];
    $hasDraft = isset($draft) || old('result_language') !== null;
@endphp

<div class="invite-assistant">
    <header class="invite-intro">
        <h1>AI Booking Assistant</h1>
        <p>Paste a meeting invitation in Arabic or English to start a booking draft.</p>
    </header>

    <div class="invite-workspace">
        <section class="invite-card" aria-labelledby="invite-form-heading">
            <h2 id="invite-form-heading">Your meeting invitation</h2>
            <form method="POST" action="{{ route('bookings.from-invite.analyze') }}">
                @csrf
                <div class="invite-field-heading">
                    <label for="invitation">Email or message</label>
                    <button class="invite-link" type="button" data-invite-example>Try an example</button>
                </div>
                <textarea id="invitation" name="invitation" rows="7" maxlength="6000" dir="auto"
                          placeholder="Paste the email subject and message here…" required>{{ old('invitation', $invitation ?? '') }}</textarea>
                @error('invitation')
                    <p class="invite-error" role="alert">{{ $message }}</p>
                @enderror
                @isset($analysisError)
                    <p class="invite-error" role="alert">We couldn’t analyze the invitation. Please try again.</p>
                @endisset
                <button class="invite-primary" type="submit">Analyze invitation</button>
            </form>
        </section>

        <aside class="invite-card invite-guide" aria-labelledby="invite-guide-heading">
            <h2 id="invite-guide-heading">How it works</h2>
            <ol class="invite-steps">
                <li><strong>Paste your invitation</strong><span>A meeting email or a short message works.</span></li>
                <li><strong>Review the details</strong><span>Check the time, guest count, and equipment requests.</span></li>
                <li><strong>Find a space</strong><span>See options based on the time and in-person guest count.</span></li>
            </ol>
        </aside>
    </div>

    @if ($hasDraft)
        <noscript>
            <p><a href="#invite-review">Review the extracted details</a></p>
        </noscript>
        <button class="invite-link" type="button" data-invite-review-open hidden>Edit meeting details</button>

        <dialog id="invite-review" class="invite-dialog"
                dir="{{ $arabicResult ? 'rtl' : 'ltr' }}" lang="{{ $arabicResult ? 'ar' : 'en' }}"
                aria-labelledby="invite-review-heading"
                open @if (! isset($suggestions)) data-invite-auto-open @endif>
            <header class="invite-dialog-header">
                <div>
                    <h2 id="invite-review-heading">{{ $arabicResult ? 'راجعي تفاصيل الاجتماع' : 'Review meeting details' }}</h2>
                    <p>{{ $arabicResult ? 'عدّلي أي معلومة قبل البحث عن قاعة.' : 'Edit any details before searching for a space.' }}</p>
                </div>
                <form method="dialog">
                    <button class="invite-link" type="submit" aria-label="{{ $arabicResult ? 'إغلاق' : 'Close' }}">×</button>
                </form>
            </header>

            <form method="POST" action="{{ route('bookings.from-invite.suggest') }}">
                @csrf
                <input type="hidden" name="result_language" value="{{ $arabicResult ? 'ar' : 'en' }}">
                <div class="invite-field-grid">
                    @foreach ($reviewFields as $field => [$english, $arabic, $type])
                        <div>
                            <label for="invite-{{ $field }}">{{ $arabicResult ? $arabic : $english }}</label>
                            <input id="invite-{{ $field }}" name="{{ $field }}" type="{{ $type }}"
                                   value="{{ old($field, $draft[$field] ?? '') }}" required
                                   @if ($type === 'text') dir="auto" maxlength="100" @endif
                                   @if ($type === 'number') min="1" max="{{ $field === 'duration_minutes' ? 1440 : 100000 }}" @endif>
                            @error($field)
                                <p class="invite-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
                @if (isset($draft['total_attendees'], $draft['remote_count']))
                    <p>{{ $arabicResult ? 'الإجمالي' : 'Total attendees' }}: {{ $draft['total_attendees'] }}
                        · {{ $arabicResult ? 'عن بُعد' : 'Remote' }}: {{ $draft['remote_count'] }}</p>
                @endif
                @if (! empty($draft['requirements']))
                    <p class="invite-requirements"><strong>{{ $arabicResult ? 'التجهيزات المطلوبة' : 'Requested equipment and services' }}</strong></p>
                    <ul class="invite-tags">
                        @foreach ($draft['requirements'] as $requirement)
                            <li dir="auto">{{ $requirement }}</li>
                        @endforeach
                    </ul>
                @endif
                <button class="invite-primary" type="submit">{{ $arabicResult ? 'اقترح قاعات' : 'Find matching spaces' }}</button>
            </form>
        </dialog>
    @endif

    @isset($suggestions)
        <section class="invite-results" aria-labelledby="invite-results-heading"
                 dir="{{ $arabicResult ? 'rtl' : 'ltr' }}" lang="{{ $arabicResult ? 'ar' : 'en' }}">
            <h2 id="invite-results-heading">{{ $arabicResult ? 'القاعات المقترحة' : 'Suggested spaces' }}</h2>
            <p>{{ $arabicResult ? 'التوفّر مبدئي لحد تأكيد المالك.' : 'Availability is provisional until the venue owner confirms.' }}</p>
            <div class="invite-results-grid">
                @forelse ($suggestions as $space)
                    <article class="invite-card">
                        <h3 dir="auto">{{ $space->name }}</h3>
                        <p dir="auto">{{ $space->venue->name }} · {{ $space->venue->address }}</p>
                        <a href="{{ route('spaces.show', ['space' => $space] + $bookingPrefill) }}">
                            {{ $arabicResult ? 'عرض القاعة' : 'View space' }}
                        </a>
                    </article>
                @empty
                    <p>{{ $arabicResult ? 'ما لقينا قاعات مناسبة. جرّبي تعديل الموعد أو المدينة.' : 'No spaces matched. Try changing the date, time, or city.' }}</p>
                @endforelse
            </div>
        </section>
    @endisset
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/booking-invite.js') }}" defer></script>
@endpush
