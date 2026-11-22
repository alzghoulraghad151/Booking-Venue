@extends('layouts.users')

@section('title', 'AI Booking Assistant')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            <form class="invite-form" method="POST" action="{{ route('bookings.from-invite.analyze') }}">
                @csrf
                <div class="invite-field-heading">
                    <label for="invitation">Email or message</label>
                    <button class="invite-textlink" type="button" data-invite-example>Try an example</button>
                </div>
                <textarea id="invitation" name="invitation" rows="7" maxlength="6000" dir="auto"
                          placeholder="Paste the email subject and message here…" required>{{ old('invitation', $invitation ?? '') }}</textarea>
                @error('invitation')
                    <p class="invite-error" role="alert">{{ $message }}</p>
                @enderror
                @isset($analysisError)
                    <p class="invite-error" role="alert">We couldn’t analyze the invitation. Please try again.</p>
                @endisset
                <div class="invite-actions">
                    <button class="invite-primary" type="submit">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2l1.9 5.6L19.5 9.5l-5.6 1.9L12 17l-1.9-5.6L4.5 9.5l5.6-1.9L12 2zm7 11l.9 2.6 2.6.9-2.6.9L19 20l-.9-2.6-2.6-.9 2.6-.9L19 13zM5 14l.7 1.8 1.8.7-1.8.7L5 19l-.7-1.8-1.8-.7 1.8-.7L5 14z"/></svg>
                        <span>Analyze invitation</span>
                    </button>
                    @if ($hasDraft)
                        <button class="invite-textlink" type="button" data-invite-review-open aria-controls="invite-review" hidden>Edit meeting details</button>
                    @endif
                </div>
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

    {{-- Step 3: review the extracted details (open by default, collapsed once suggestions are shown) --}}
    @if ($hasDraft)
        <noscript>
            <p><a href="#invite-review">Review the extracted details</a></p>
        </noscript>

        <section id="invite-review" class="invite-review"
                 dir="{{ $arabicResult ? 'rtl' : 'ltr' }}" lang="{{ $arabicResult ? 'ar' : 'en' }}"
                 aria-labelledby="invite-review-heading"
                 @if (isset($suggestions)) data-invite-collapsed @endif>
            <header class="invite-review__header">
                <div>
                    <h2 id="invite-review-heading" tabindex="-1">{{ $arabicResult ? 'راجعي تفاصيل الاجتماع' : 'Review meeting details' }}</h2>
                    <p>{{ $arabicResult ? 'عدّلي أي معلومة قبل البحث عن قاعة.' : 'Edit any details before searching for a space.' }}</p>
                </div>
                <button class="invite-close" type="button" data-invite-review-close aria-controls="invite-review"
                        aria-label="{{ $arabicResult ? 'إغلاق' : 'Close' }}" hidden>
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </header>

            <form class="invite-review__form" method="POST" action="{{ route('bookings.from-invite.suggest') }}">
                @csrf
                <input type="hidden" name="result_language" value="{{ $arabicResult ? 'ar' : 'en' }}">
                <div class="invite-field-grid">
                    @foreach ($reviewFields as $field => [$english, $arabic, $type])
                        <div class="invite-field">
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

                <div class="invite-review__footer">
                    <div class="invite-review__meta">
                        @if (isset($draft['total_attendees'], $draft['remote_count']))
                            <p class="invite-attendees">{{ $arabicResult ? 'الإجمالي' : 'Total attendees' }}: {{ $draft['total_attendees'] }}
                                · {{ $arabicResult ? 'عن بُعد' : 'Remote' }}: {{ $draft['remote_count'] }}</p>
                        @endif
                        @if (! empty($draft['requirements']))
                            <div class="invite-requirements">
                                <strong>{{ $arabicResult ? 'التجهيزات المطلوبة' : 'Requested equipment and services' }}</strong>
                                <ul class="invite-tags">
                                    @foreach ($draft['requirements'] as $requirement)
                                        <li dir="auto">{{ $requirement }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <button class="invite-primary" type="submit">{{ $arabicResult ? 'اقترح قاعات' : 'Find matching spaces' }}</button>
                </div>
            </form>
        </section>
    @endif

    {{-- Step 4: suggested spaces --}}
    @isset($suggestions)
        @php
            // One extra query for all suggested spaces (primary photo first), no controller change needed.
            $suggestions->loadMissing(['images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('id')]);
        @endphp
        <section id="invite-results" class="invite-results" aria-labelledby="invite-results-heading"
                 dir="{{ $arabicResult ? 'rtl' : 'ltr' }}" lang="{{ $arabicResult ? 'ar' : 'en' }}">
            <div class="invite-results__heading">
                <h2 id="invite-results-heading" tabindex="-1">{{ $arabicResult ? 'القاعات المقترحة' : 'Suggested spaces' }}</h2>
                <p>{{ $arabicResult ? 'التوفّر مبدئي لحد تأكيد المالك.' : 'Availability is provisional until the venue owner confirms.' }}</p>
            </div>
            <div class="invite-results-grid">
                @forelse ($suggestions as $space)
                    @php($image = $space->images->first())
                    <article class="invite-space">
                        <div class="invite-space__media">
                            @if ($image)
                                <img src="{{ $image->image_url }}" alt="{{ $space->name }}" loading="lazy">
                            @else
                                <span class="invite-space__empty" aria-hidden="true"></span>
                            @endif
                        </div>
                        <div class="invite-space__body">
                            <div>
                                <h3 dir="auto">{{ $space->name }}</h3>
                                <p dir="auto">{{ $space->venue->name }} · {{ $space->venue->address }}</p>
                            </div>
                            <footer>
                                <a class="invite-space__link" href="{{ route('spaces.show', ['space' => $space] + $bookingPrefill) }}">
                                    {{ $arabicResult ? 'عرض القاعة' : 'View space' }}
                                </a>
                            </footer>
                        </div>
                    </article>
                @empty
                    <p class="invite-empty">{{ $arabicResult ? 'ما لقينا قاعات مناسبة. جرّبي تعديل الموعد أو المدينة.' : 'No spaces matched. Try changing the date, time, or city.' }}</p>
                @endforelse
            </div>
        </section>
    @endisset
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/booking-invite.js') }}" defer></script>
@endpush