// Live price preview only. The server recalculates the real price in BookingController@store.
(function () {
    var form = document.getElementById('bc-form');
    if (!form) { return; }

    var rate = parseFloat(form.dataset.hourlyRate) || 0;
    var packagePrice = parseFloat(form.dataset.packagePrice) || 0;

    var start = form.elements['start_time'];
    var end = form.elements['end_time'];
    var hoursEl = document.getElementById('bc-hours');
    var spaceEl = document.getElementById('bc-space-price');
    var totalEl = document.getElementById('bc-total');
    var hintEl = document.getElementById('bc-price-hint');

    function toMinutes(value) {
        var match = /^(\d{2}):(\d{2})/.exec(value || '');
        return match ? parseInt(match[1], 10) * 60 + parseInt(match[2], 10) : null;
    }

    function update() {
        var s = toMinutes(start.value);
        var e = toMinutes(end.value);

        if (s === null || e === null || e <= s) {
            hoursEl.textContent = '\u2014';
            spaceEl.textContent = '\u2014';
            totalEl.textContent = '\u2014';
            hintEl.textContent = (s !== null && e !== null)
                ? 'The end time must be after the start time.'
                : 'Choose a start and end time to see the total.';
            return;
        }

        var hours = (e - s) / 60;
        var spacePrice = Math.round(rate * hours * 100) / 100;
        var total = Math.round((spacePrice + packagePrice) * 100) / 100;

        hoursEl.textContent = (Math.round(hours * 100) / 100).toFixed(2);
        spaceEl.textContent = spacePrice.toFixed(2);
        totalEl.textContent = total.toFixed(2);
        hintEl.textContent = '';
    }

    start.addEventListener('input', update);
    end.addEventListener('input', update);
    update();
})();
