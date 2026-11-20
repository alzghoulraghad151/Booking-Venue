<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ابحث عن مساحة | Convene</title>
</head>
<body>
    <main>
        <h1>ابحث عن مساحة</h1>

        <form method="GET" action="{{ route('spaces.search') }}">
            <label for="city">المدينة</label>
            <input id="city" name="city" value="{{ old('city', $city) }}" maxlength="100">
            @error('city')
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
    <p>{{ $voiceSummary }}</p>
    <audio id="voice-summary-audio" src="{{ $voiceSummaryAudioUrl }}" preload="none"></audio>
    <button type="button" id="voice-summary-play" hidden>اسمع الملخّص</button>
    <p id="voice-summary-status" role="status"></p>
  <script type="module" src="{{ asset('js/space-search-summary-audio.js') }}"></script>
@endif
            @forelse ($spaces as $space)
                <article>
                    <h3>{{ $space->name }}</h3>
                    <p>{{ $space->venue->name }} · {{ $space->venue->address }}</p>
                    <p>السعة {{ $space->capacity }} شخص</p>
                </article>
            @empty
              @unless ($voiceSummary)
                <p>ما لقينا مساحة بتطابق هالشروط. جرّبي تغيّري المدينة أو العدد.</p>

                 @endunless
            @endforelse

            {{ $spaces->links() }}
        @endif
    </main>
</body>
</html>