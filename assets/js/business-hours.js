/**
 * Paradise Business Hours — client-side open/closed detection
 *
 * Reads hours from data-bh-hours JSON and the site timezone offset from
 * data-bh-tz-offset (minutes), computes the current day/time in the
 * site's timezone, and updates the badge and today highlight accordingly.
 */
(function () {
    'use strict';

    var DAY_SLUGS = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

    function pad(n) {
        return n < 10 ? '0' + n : '' + n;
    }

    // Open if `time` falls in [from, to], handling overnight ranges
    // (e.g. from=22:00, to=02:00) where `to` is on the next day.
    function inRange(time, from, to) {
        return (from <= to) ? (time >= from && time <= to)
                            : (time >= from || time <= to);
    }

    function initWidget(wrap) {
        var hoursData  = JSON.parse(wrap.getAttribute('data-bh-hours') || '{}');
        var tzOffset   = parseInt(wrap.getAttribute('data-bh-tz-offset') || '0', 10);
        var showBadge  = wrap.getAttribute('data-bh-show-badge') === '1';
        var highlight  = wrap.getAttribute('data-bh-highlight') === '1';

        // Build a Date whose UTC fields equal the site's wall-clock time, then
        // read it with the getUTC* accessors. Using getHours()/getDay() here
        // would re-apply the visitor's own offset and give the wrong day/time.
        var now         = new Date();
        var utcMs       = now.getTime() + now.getTimezoneOffset() * 60000;
        var siteNow     = new Date(utcMs + tzOffset * 60000);

        var todayIdx    = siteNow.getUTCDay();
        var todaySlug   = DAY_SLUGS[todayIdx];
        var currentTime = pad(siteNow.getUTCHours()) + ':' + pad(siteNow.getUTCMinutes());

        // Highlight today's row.
        if (highlight) {
            var rows = wrap.querySelectorAll('.paradise-bh-row');
            rows.forEach(function (row) {
                if (row.getAttribute('data-bh-day') === todaySlug) {
                    row.classList.add('paradise-bh-row--today');
                }
            });
        }

        // Update badge.
        if (showBadge) {
            var badge = wrap.querySelector('.paradise-bh-badge');
            if (!badge) return;

            var entry  = hoursData[todaySlug];
            var isOpen = false;

            if (entry && entry.open && entry.from && entry.to) {
                isOpen = inRange(currentTime, entry.from, entry.to);
            }

            // Still-open overnight span that started yesterday (e.g. yesterday
            // 22:00 → today 02:00, and it is now 01:00).
            if (!isOpen) {
                var y = hoursData[DAY_SLUGS[(todayIdx + 6) % 7]];
                if (y && y.open && y.from && y.to && y.from > y.to && currentTime <= y.to) {
                    isOpen = true;
                }
            }

            badge.classList.remove('paradise-bh-badge--open', 'paradise-bh-badge--closed');
            badge.classList.add(isOpen ? 'paradise-bh-badge--open' : 'paradise-bh-badge--closed');
            badge.textContent = isOpen ? 'Open Now' : 'Closed';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.paradise-bh-wrap').forEach(initWidget);
    });

})();
