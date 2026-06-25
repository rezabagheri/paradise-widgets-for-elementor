/* ═══════════════════════════════════════════════════════════
   Paradise Bottom Navigation Bar  —  v2.1.0
   paradise-bn-bottom-nav-script (handle name updated for consistency)
   ═══════════════════════════════════════════════════════════ */
(function () {
    'use strict';

    /* ── Public API ─────────────────────────────────────────────
       Paradise.setBadge(cssId, count)  — set badge value from JS
    ─────────────────────────────────────────────────────────── */
    var Paradise = window.Paradise || {};

    Paradise.setBadge = function (cssId, count) {
        var el = document.getElementById(cssId);
        if (!el) return;
        var badge = el.querySelector('.paradise-bn-badge[data-paradise-bn-badge-target="' + cssId + '"]');
        if (!badge) return;
        badge.textContent = count > 99 ? '99+' : String(count);
        badge.style.display = (count === 0) ? 'none' : '';
    };

    window.Paradise = Paradise;

    /* ── Init ───────────────────────────────────────────────── */
    function init() {
        document.querySelectorAll('.paradise-bn-wrapper').forEach(initBar);
    }

    function initBar(wrapper) {
        // Guard against double-init: init() runs on DOMContentLoaded AND the
        // Elementor `element_ready` hook fires for the same node (and re-fires on
        // editor re-render). Without this, listeners/overlays are bound twice.
        if (wrapper.dataset.paradiseBnInit) return;
        wrapper.dataset.paradiseBnInit = '1';

        var cfg = parseConfig(wrapper);

        if (cfg.isEditMode) {
            initEditorPreview(wrapper, cfg);
        } else {
            applyResponsiveVisibility(wrapper, cfg);
            applyAnimation(wrapper, cfg);
            applyBodyPadding(wrapper);
        }

        markActive(wrapper, cfg);
        initIndicator(wrapper, cfg);
        initSpeedDial(wrapper, cfg);
        initJsHooks(wrapper);
    }

    /* ── Config ─────────────────────────────────────────────── */
    function parseConfig(wrapper) {
        try {
            return JSON.parse(wrapper.getAttribute('data-paradise-bn') || '{}');
        } catch (e) {
            return {};
        }
    }

    /* ═══════════════════════════════════════════════════════════
       EDITOR PREVIEW
       Positions the bar fixed at the bottom of the Elementor
       iframe canvas — pixel-perfect match with frontend.
    ═══════════════════════════════════════════════════════════ */
    function initEditorPreview(wrapper, cfg) {
        // The bar is inside an iframe. position:fixed is already
        // relative to the iframe viewport — we just need to keep
        // bottom:0 correct as the panel resizes.
        //
        // For floating bars with a bottom offset, Elementor's
        // own style controls handle `bottom` via selectors,
        // so we only override for full-width bars (bottom:0).

        if (cfg.barPos !== 'floating') {
            wrapper.style.setProperty('--paradise-bn-editor-bottom', '0px');
        }

        // Re-run active state and indicator immediately —
        // no waiting for resize events.
        requestAnimationFrame(function () {
            var active = wrapper.querySelector('.paradise-bn-item--active');
            if (active) updateIndicator(wrapper, active, cfg, false);
        });
    }

    /* ── Responsive visibility (frontend only) ─────────────── */
    function applyResponsiveVisibility(wrapper, cfg) {
        // Mirrors Elementor's default breakpoints:
        //   mobile ≤ 767 px  |  tablet 768–1024 px  |  desktop > 1024 px
        // cfg.showOn* booleans are set by PHP from the bar_display responsive control.
        // undefined (old saved widgets) → fall back to true so bar stays visible.
        var showMobile  = cfg.showOnMobile  !== false;
        var showTablet  = cfg.showOnTablet  !== false;
        var showDesktop = cfg.showOnDesktop === true;   // desktop default is 'none'

        function update() {
            var w = window.innerWidth;
            var visible = w <= 767 ? showMobile : (w <= 1024 ? showTablet : showDesktop);
            wrapper.style.display = visible ? 'block' : 'none';
        }

        update();
        window.addEventListener('resize', debounce(update, 100));
    }

    /* ── Entrance animation ─────────────────────────────────── */
    function applyAnimation(wrapper, cfg) {
        if (!cfg.animEnabled) return;
        var styleMap = { slide_up: 'paradise-bn-anim-slide-up', fade: 'paradise-bn-anim-fade', both: 'paradise-bn-anim-both' };
        var cls = styleMap[cfg.animStyle] || 'paradise-bn-anim-slide-up';
        wrapper.style.setProperty('--paradise-bn-anim-duration', (cfg.animDuration || 350) + 'ms');
        wrapper.classList.add(cls);
    }

    /* ── Body padding (frontend only) ───────────────────────── */
    function applyBodyPadding(wrapper) {
        var bar = wrapper.querySelector('.paradise-bn-bar');
        if (!bar) return;

        function update() {
            // Only add padding when bar is actually visible
            var visible = getComputedStyle(wrapper).display !== 'none';
            document.body.classList.toggle('paradise-bn-active', visible);
            if (visible) {
                document.body.style.setProperty('--paradise-bn-bar-height', bar.offsetHeight + 'px');
            }
        }

        update();
        if (window.ResizeObserver) {
            new ResizeObserver(update).observe(bar);
        }
        window.addEventListener('resize', debounce(update, 100));
    }

    /* ── Active state ───────────────────────────────────────── */
    function markActive(wrapper, cfg) {
        var detection = cfg.detection  || 'both';
        var matchMode = cfg.matchMode  || 'pathname';
        var manualIdx = (cfg.manualIndex || 1) - 1; // 0-based
        var items     = Array.from(wrapper.querySelectorAll('.paradise-bn-item'));
        var matched   = false;

        if (detection === 'url' || detection === 'both') {
            var current = normalizeUrl(window.location.href, matchMode);
            items.forEach(function (item) {
                var href = normalizeUrl(item.getAttribute('href') || '', matchMode);
                if (href && href === current) {
                    setActive(item);
                    matched = true;
                }
            });
        }

        if (!matched && (detection === 'manual' || detection === 'both')) {
            var fallback = items[manualIdx] || items[0];
            if (fallback) setActive(fallback);
        }

        // Update active on click (frontend only — editor items have pointer-events:none)
        items.forEach(function (item) {
            item.addEventListener('click', function () {
                items.forEach(clearActive);
                setActive(item);
                requestAnimationFrame(function () {
                    updateIndicator(wrapper, item, cfg, true);
                });
            });
        });
    }

    function setActive(item) {
        item.classList.add('paradise-bn-item--active');
        item.setAttribute('aria-current', 'page');
    }

    function clearActive(item) {
        item.classList.remove('paradise-bn-item--active');
        item.removeAttribute('aria-current');
    }

    /* ── Sliding indicator ──────────────────────────────────── */
    function initIndicator(wrapper, cfg) {
        var style = cfg.indicator || 'top_bar';
        if (style !== 'top_bar' && style !== 'bot_bar') return;

        var active = wrapper.querySelector('.paradise-bn-item--active');
        if (active) updateIndicator(wrapper, active, cfg, false);
    }

    function updateIndicator(wrapper, activeItem, cfg, animate) {
        var style = cfg.indicator || 'top_bar';
        if (style !== 'top_bar' && style !== 'bot_bar') return;

        var indicator = wrapper.querySelector('.paradise-bn-indicator');
        var bar       = wrapper.querySelector('.paradise-bn-bar');
        if (!indicator || !bar || !activeItem) return;

        var animated = cfg.animated !== false;
        var barRect  = bar.getBoundingClientRect();
        var itemRect = activeItem.getBoundingClientRect();
        var indicW   = indicator.offsetWidth || 24;
        var center   = itemRect.left - barRect.left + itemRect.width / 2;
        var x        = Math.round(center - indicW / 2);

        if (!animate || !animated) {
            indicator.setAttribute('data-no-anim', '');
            indicator.style.transform = 'translateX(' + x + 'px)';
            requestAnimationFrame(function () {
                indicator.removeAttribute('data-no-anim');
            });
        } else {
            indicator.style.transform = 'translateX(' + x + 'px)';
        }
    }

    /* ── Speed Dial ─────────────────────────────────────────── */
    function initSpeedDial(wrapper, cfg) {
        var btn  = wrapper.querySelector('.paradise-bn-center-btn[data-paradise-bn-action="speed_dial"]');
        var dial = wrapper.querySelector('.paradise-bn-speed-dial');
        if (!btn || !dial) return;

        // In editor: dial is already open (PHP renders it open).
        // Clicking center btn in editor just toggles visually —
        // no overlay needed, no navigation.
        if (cfg.isEditMode) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                dial.classList.toggle('paradise-bn-speed-dial--open');
                var isOpen = dial.classList.contains('paradise-bn-speed-dial--open');
                dial.setAttribute('aria-hidden', String(!isOpen));
                btn.setAttribute('aria-expanded', String(isOpen));
            });
            return;
        }

        // Frontend: full behavior with overlay
        var overlay = getOrCreateOverlay();

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            toggle(); // flip open/closed — re-tapping the button now closes it
        });

        overlay.addEventListener('click', function () { toggle(false); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') toggle(false);
        });
        dial.querySelectorAll('.paradise-bn-dial-item').forEach(function (item) {
            item.addEventListener('click', function () { toggle(false); });
        });

        function toggle(open) {
            var isOpen   = dial.classList.contains('paradise-bn-speed-dial--open');
            var newState = open === undefined ? !isOpen : open;
            dial.classList.toggle('paradise-bn-speed-dial--open', newState);
            dial.setAttribute('aria-hidden', String(!newState));
            overlay.classList.toggle('paradise-bn-overlay--active', newState);
            btn.setAttribute('aria-expanded', String(newState));
        }
    }

    /* ── JS Hooks ───────────────────────────────────────────── */
    function initJsHooks(wrapper) {
        wrapper.querySelectorAll('.paradise-bn-center-btn[data-paradise-bn-action="js_hook"]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var hookName = btn.getAttribute('data-paradise-bn-hook');
                if (!hookName) return;
                document.dispatchEvent(new CustomEvent('ebn:hook:' + hookName, {
                    bubbles: true,
                    detail: { button: btn, wrapper: wrapper },
                }));
            });
        });
    }

    /* ── Utilities ──────────────────────────────────────────── */
    function normalizeUrl(url, mode) {
        try {
            var u = new URL(url, window.location.origin);
            if (mode === 'full') return (u.pathname.replace(/\/$/, '') || '/') + u.search;
            return u.pathname.replace(/\/$/, '') || '/';
        } catch (e) {
            return url;
        }
    }

    function getOrCreateOverlay() {
        var existing = document.querySelector('.paradise-bn-overlay');
        if (existing) return existing;
        var overlay = document.createElement('div');
        overlay.className = 'paradise-bn-overlay';
        document.body.appendChild(overlay);
        return overlay;
    }

    function debounce(fn, delay) {
        var timer;
        return function () { clearTimeout(timer); timer = setTimeout(fn, delay); };
    }

    /* ── Bootstrap ──────────────────────────────────────────── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Elementor editor: re-init when widget renders/updates
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
        window.elementorFrontend.hooks.addAction(
            'frontend/element_ready/paradise_bottom_nav.default',
            function ($el) { initBar($el[0]); }
        );
    }

})();
