/**
 * Paradise SoundCloud Widget — playlist interactivity (Modes 3 + 4).
 *
 * Each `.paradise-soundcloud-playlist` block has one iframe and a list of
 * track `<button>`s. We:
 *   1. Defer loading SoundCloud's Widget API JS (~10 KB) until the first
 *      click — pages where nobody touches a playlist pay nothing extra.
 *   2. On click, call `widget.load(url, opts)` to swap tracks INSIDE the
 *      existing iframe rather than replacing the iframe — one player
 *      load (~500 KB) regardless of track count.
 *   3. Re-apply the original embed params (visual/color/show_user/…) on
 *      every load so the player keeps the user's chosen style across
 *      track switches.
 *   4. On the SC FINISH event, auto-click the next track button →
 *      continuous play through the list, stopping at the end.
 */
(function () {
    'use strict';

    var SC_API_URL = 'https://w.soundcloud.com/player/api.js';
    var apiLoader  = null;

    function loadSCApi() {
        if (apiLoader) return apiLoader;
        apiLoader = new Promise(function (resolve, reject) {
            if (window.SC && window.SC.Widget) {
                resolve(window.SC);
                return;
            }
            var s = document.createElement('script');
            s.src = SC_API_URL;
            s.async = true;
            s.onload  = function () { resolve(window.SC); };
            s.onerror = function () { reject(new Error('SoundCloud Widget API failed to load')); };
            document.head.appendChild(s);
        });
        return apiLoader;
    }

    function parseEmbedParams(iframe) {
        try {
            var url = new URL(iframe.src);
            var params = {};
            url.searchParams.forEach(function (value, key) {
                if (key !== 'url') {
                    params[key] = value;
                }
            });
            return params;
        } catch (e) {
            return {};
        }
    }

    function setPlaying(buttons, target) {
        buttons.forEach(function (btn) {
            var isTarget = btn === target;
            btn.classList.toggle('is-playing', isTarget);
            btn.setAttribute('aria-current', isTarget ? 'true' : 'false');
        });
    }

    function init(root) {
        if (root.dataset.paradiseScInit) return;
        root.dataset.paradiseScInit = '1';

        var iframe = root.querySelector('.paradise-soundcloud-frame');
        if (!iframe) return;

        var buttons = Array.prototype.slice.call(root.querySelectorAll('.paradise-soundcloud-track'));
        if (buttons.length === 0) return;

        var baseParams     = parseEmbedParams(iframe);
        var widgetPromise  = null;

        function getWidget() {
            if (widgetPromise) return widgetPromise;
            widgetPromise = loadSCApi().then(function (SC) {
                var widget = SC.Widget(iframe);
                widget.bind(SC.Widget.Events.FINISH, function () {
                    var current = root.querySelector('.paradise-soundcloud-track.is-playing');
                    if (!current) return;
                    var li     = current.closest('li');
                    var nextLi = li && li.nextElementSibling;
                    if (!nextLi) return;
                    var nextBtn = nextLi.querySelector('.paradise-soundcloud-track');
                    if (nextBtn) nextBtn.click();
                });
                return widget;
            });
            return widgetPromise;
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var url = btn.dataset.url;
                if (!url) return;
                setPlaying(buttons, btn);
                getWidget().then(function (widget) {
                    var loadParams = Object.assign({}, baseParams, { auto_play: true });
                    widget.load(url, loadParams);
                });
            });
        });
    }

    function initAll() {
        var playlists = document.querySelectorAll('.paradise-soundcloud-playlist');
        Array.prototype.forEach.call(playlists, init);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
