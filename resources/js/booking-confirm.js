// Live price preview only. The server recalculates the real price in PublicBookingController@store.
//   no package : total = hourly rate x hours
//   package    : total = package price (the space is included in it)
(function () {
    // Mobile menu of the page header.
    var menuButton = document.querySelector('[data-bc-menu]');
    var mobileNav = document.getElementById('bc-mobile-nav');
    if (menuButton && mobileNav) {
        menuButton.addEventListener('click', function () {
            var open = mobileNav.hasAttribute('hidden');
            if (open) { mobileNav.removeAttribute('hidden'); } else { mobileNav.setAttribute('hidden', ''); }
            menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    var form = document.getElementById('bc-form');
    if (!form) { return; }

    var rate = parseFloat(form.dataset.hourlyRate) || 0;
    var packagePrice = parseFloat(form.dataset.packagePrice) || 0;
    var hasPackage = form.dataset.hasPackage === '1';

    var start = form.elements['start_time'];
    var end = form.elements['end_time'];
    var hoursEl = document.getElementById('bc-hours');
    var spaceEl = document.getElementById('bc-space-price'); // not rendered when a package is selected
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
            if (spaceEl) { spaceEl.textContent = '\u2014'; }
            if (!hasPackage) { totalEl.textContent = '\u2014'; }
            hintEl.textContent = (s !== null && e !== null)
                ? 'The end time must be after the start time.'
                : (hasPackage ? 'Choose a start and end time.' : 'Choose a start and end time to see the total.');
            return;
        }

        var hours = (e - s) / 60;
        var hoursText = (Math.round(hours * 100) / 100).toFixed(2);
        hoursEl.textContent = hoursText;

        if (hasPackage) {
            totalEl.textContent = packagePrice.toFixed(2);
            hintEl.textContent = 'Fixed package price for ' + hoursText + ' hours. The space is included.';
            return;
        }

        var spacePrice = Math.round(rate * hours * 100) / 100;
        spaceEl.textContent = spacePrice.toFixed(2);
        totalEl.textContent = spacePrice.toFixed(2);
        hintEl.textContent = 'Calculated for ' + hoursText + ' hours at ' + rate.toFixed(2) + ' JOD/hr.';
    }

    start.addEventListener('input', update);
    end.addEventListener('input', update);
    update();
})();