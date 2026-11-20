<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ابحث عن مساحة | Convene</title>
    <link rel="stylesheet" href="{{ asset('css/convene-listing-cards.css') }}">
</head>
<body>
    <main>
        <h1>ابحث عن مساحة</h1>

        <form method="GET" action="{{ route('spaces.search') }}">
          <label for="address">الموقع أو العنوان</label>
<input id="address" name="address" dir="ltr"
       value="{{ old('address', $address) }}" maxlength="255">
            @error('address')
                <p role="alert">{{ $message }}</p>
            @enderror

            <label for="capacity">عدد الأشخاص</label>
            <input id="capacity" name="capacity" type="number"
                   min="1" value="{{ old('capacity', $capacity) }}">
            @error('capacity')
                <p role="alert">{{ $message }}</p>
            @enderror

            <button type="submit">ابحث</button>
        </form>



        <button type="button" id="voice-search"
        data-url="{{ route('spaces.search.voice') }}"
        data-csrf="{{ csrf_token() }}">
    ابحث بصوتك
</button>

<p id="voice-status" role="status" aria-live="polite"></p>
<p id="voice-transcript" dir="auto"></p>

<script src="{{ asset('js/space-voice-search.js') }}" defer></script>

        @if ($searched)
            <h2>نتائج البحث</h2>

         @if ($voiceSummary)
    @if ($voiceSummary)
    <p lang="{{ $isEnglish ? 'en' : 'ar' }}" dir="{{ $isEnglish ? 'ltr' : 'rtl' }}">
        {{ $voiceSummary }}
    </p>

    {{-- خلي عنصر audio وباقي كود التشغيل هون --}}
@endif
    <audio id="voice-summary-audio" src="{{ $voiceSummaryAudioUrl }}" preload="none"></audio>
    <button type="button" id="voice-summary-play" hidden>اسمع الملخّص</button>
    <p id="voice-summary-status" role="status"></p>
  <script type="module" src="{{ asset('js/space-search-summary-audio.js') }}"></script>
@endif
            <div class="listing-card-grid">
                @forelse ($spaces as $space)
                    @php($image = $space->images->firstWhere('is_primary', true) ?? $space->images->first())
                    <article class="listing-card {{ $image ? '' : 'listing-card--empty' }}" @if ($image) style="background-image: url(&quot;{{ $image->image_url }}&quot;)" @endif>
                        <div class="listing-card__body">
                            <h3 class="listing-card__title">{{ $space->name }}</h3>
                            <p class="listing-card__location">{{ $space->venue->name }} · {{ $space->venue->address }}</p>
                            @if ($space->description)
                                <p class="listing-card__description">{{ \Illuminate\Support\Str::limit($space->description, 110) }}</p>
                            @endif
                            <div class="listing-card__meta">
                                <span>السعة {{ $space->capacity }} شخص</span>
                                @if ($space->hourly_rate !== null)
                                    <span>{{ number_format((float) $space->hourly_rate, 2) }} JOD / ساعة</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    @unless ($voiceSummary)
                        <p>ما لقينا مساحة بتطابق هالشروط. جرّبي تغيّري الموقع أو العدد.</p>
                    @endunless
                @endforelse
            </div>

            {{ $spaces->links() }}
        @endif
    </main>
</body>
</html>
