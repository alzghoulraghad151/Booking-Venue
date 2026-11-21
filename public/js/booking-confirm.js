// Live price preview only. The server recalculates the real price in BookingController@store.
(function () {
    var form = document.getElementById('bc-form');
    if (!form) { return; }

    var rate = parseFloat(form.dataset.hourlyRate) || 0;
    var packagePrice = parseFloat(form.dataset.packagePrice) || 0;
    // Hours of the space already included in the package (1 with a package, 0 without).
    var includedHours = parseFloat(form.dataset.includedHours) || 0;

    var start = form.elements['start_time'];
    var end = form.elements['end_time'];
    var hoursEl = document.getElementById('bc-hours');
    var spaceEl = document.getElementById('bc-space-price');
    var extraHoursEl = document.getElementById('bc-extra-hours');
    var totalEl = document.getElementById('bc-total');
    var hintEl = document.getElementById('bc-price-hint');

    function toMinutes(value) {
        var match = /^(\d{2}):(\d{2})/.exec(value || '');
        return match ? parseInt(match[1], 10) * 60 + parseInt(match[2], 10) : null;
    }

    function update() {
        var s = toMinutes(start.value);
        var e = toMinutes(end.value);

        if (s === null || e === null || e <= s || (e - s) < 60) {
            hoursEl.textContent = '\u2014';
            spaceEl.textContent = '\u2014';
            if (extraHoursEl) { extraHoursEl.textContent = '\u2014'; }
            totalEl.textContent = '\u2014';
            if (s === null || e === null) {
                hintEl.textContent = 'Choose a start and end time to see the total.';
            } else if (e <= s) {
                hintEl.textContent = 'The end time must be after the start time.';
            } else {
                hintEl.textContent = 'The minimum booking duration is 1 hour.';
            }
            return;
        }

        var hours = (e - s) / 60;
        // Same rule as BookingPriceCalculator (PHP):
        //   no package: total = hourly_rate x hours
        //   package:    total = package price + hourly_rate x (hours - 1), extra hours never below 0
        var billableHours = Math.max(0, hours - includedHours);
        var spacePrice = Math.round(rate * billableHours * 100) / 100;
        var total = Math.round((spacePrice + packagePrice) * 100) / 100;

        hoursEl.textContent = (Math.round(hours * 100) / 100).toFixed(2);
        spaceEl.textContent = spacePrice.toFixed(2);
        if (extraHoursEl) { extraHoursEl.textContent = (Math.round(billableHours * 100) / 100).toFixed(2); }
        totalEl.textContent = total.toFixed(2);
        hintEl.textContent = '';
    }

    start.addEventListener('input', update);
    end.addEventListener('input', update);
    update();
})();