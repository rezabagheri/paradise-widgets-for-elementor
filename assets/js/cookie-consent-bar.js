/**
 * Cookie Consent Bar — Paradise Elementor Widgets
 *
 * Handles consent state for each .paradise-ccb-wrap on the page.
 * Storage key: 'paradise-ccb-' + bar id
 * Storage value: JSON { accepted: true|false, expires: timestamp }
 *
 * Custom events dispatched on document:
 *   paradise:ccb:accepted  — user clicked Accept (detail: { id })
 *   paradise:ccb:declined  — user clicked Decline (detail: { id })
 */
(function () {
    'use strict';

    var STORAGE_PREFIX = 'paradise-ccb-';

    // Storage can throw (Safari private mode, disabled cookies, partitioned
    // iframes). Never let that abort init — fall back to safe defaults so the
    // Accept/Decline buttons still get wired.
    function ls(method, key, val) {
        try { return localStorage[method](key, val); } catch (e) { return null; }
    }

    /**
     * Return the stored consent record for a bar id, or null if absent/expired.
     */
    function getConsent(id) {
        var raw = ls('getItem', STORAGE_PREFIX + id);
        if (!raw) return null;
        try {
            var data = JSON.parse(raw);
            if (data && data.expires && Date.now() < data.expires) {
                return data;
            }
            // Expired — clean up
            ls('removeItem', STORAGE_PREFIX + id);
        } catch (e) {
            ls('removeItem', STORAGE_PREFIX + id);
        }
        return null;
    }

    /**
     * Save consent decision to localStorage.
     */
    function saveConsent(id, accepted, expiryDays) {
        var expires = Date.now() + (parseInt(expiryDays, 10) || 365) * 24 * 60 * 60 * 1000;
        ls('setItem', STORAGE_PREFIX + id, JSON.stringify({ accepted: accepted, expires: expires }));
    }

    /**
     * Dispatch a consent event on document.
     */
    function dispatchEvent(name, id) {
        var event;
        try {
            event = new CustomEvent(name, { bubbles: true, detail: { id: id } });
        } catch (e) {
            // IE11 fallback
            event = document.createEvent('CustomEvent');
            event.initCustomEvent(name, true, false, { id: id });
        }
        document.dispatchEvent(event);
    }

    /**
     * Initialise all cookie consent bars on the page.
     */
    function initBars() {
        var bars = document.querySelectorAll('.paradise-ccb-wrap');

        bars.forEach(function (wrap) {
            var isEditor   = wrap.getAttribute('data-ccb-edit') === 'true';
            var id         = wrap.getAttribute('data-ccb-id');
            var expiry     = wrap.getAttribute('data-ccb-expiry') || '365';
            var acceptBtn  = wrap.querySelector('.paradise-ccb-accept');
            var declineBtn = wrap.querySelector('.paradise-ccb-decline');

            // In editor mode: always visible, no storage logic
            if (isEditor) {
                return;
            }

            // Hide if consent already recorded
            if (getConsent(id) !== null) {
                wrap.setAttribute('data-ccb-hidden', 'true');
                return;
            }

            // Accept
            if (acceptBtn) {
                acceptBtn.addEventListener('click', function () {
                    saveConsent(id, true, expiry);
                    wrap.setAttribute('data-ccb-hidden', 'true');
                    dispatchEvent('paradise:ccb:accepted', id);
                });
            }

            // Decline
            if (declineBtn) {
                declineBtn.addEventListener('click', function () {
                    saveConsent(id, false, expiry);
                    wrap.setAttribute('data-ccb-hidden', 'true');
                    dispatchEvent('paradise:ccb:declined', id);
                });
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBars);
    } else {
        initBars();
    }

    // Public API
    window.Paradise = window.Paradise || {};

    /**
     * Check if the user has accepted cookies for a given bar id.
     * Returns true | false | null (null = no decision recorded yet).
     */
    Paradise.getCookieConsent = function (id) {
        var consent = getConsent(id);
        if (consent === null) return null;
        return consent.accepted === true;
    };

})();
