/**
 * Sticky Header — Paradise Elementor Widgets
 *
 * Finds the parent Elementor section for each .paradise-shdr-ctrl element
 * and makes it sticky. Applies scroll effects (bg, shadow, shrink) via
 * inline styles when the configured threshold is reached.
 *
 * Supports both classic sections (.elementor-section) and
 * Flexbox containers (.e-con / .e-container).
 */
(function () {
    'use strict';

    var STICKY_CLASS   = 'paradise-shdr-sticky';
    var SCROLLED_CLASS = 'paradise-shdr-scrolled';

    function initAll() {
        document.querySelectorAll('.paradise-shdr-ctrl').forEach(initCtrl);
    }

    function initCtrl(ctrl) {
        var isEditor    = ctrl.getAttribute('data-psh-edit') === 'true';
        var threshold   = parseInt(ctrl.getAttribute('data-psh-threshold'), 10) || 50;
        var zIndex      = parseInt(ctrl.getAttribute('data-psh-z'), 10) || 9990;
        var scrolledBg  = ctrl.getAttribute('data-psh-bg') || '';
        var showShadow  = ctrl.getAttribute('data-psh-shadow') === 'yes';
        var shadowColor = ctrl.getAttribute('data-psh-shadow-color') || 'rgba(0,0,0,0.12)';
        var duration    = parseInt(ctrl.getAttribute('data-psh-duration'), 10) || 300;
        var shrink      = ctrl.getAttribute('data-psh-shrink') === 'yes';
        var scrolledPad = ctrl.getAttribute('data-psh-pad') || '8px';

        // Find parent section (classic or container)
        var section = ctrl.closest('.elementor-section')
                   || ctrl.closest('.e-con, .e-container');

        if (!section) return;

        // Snapshot the section's original inline styles so the "not scrolled"
        // state restores exactly what Elementor set (e.g. responsive padding),
        // instead of blanking them after the first scroll cycle.
        section._pshOrig = {
            bg:     section.style.backgroundColor,
            shadow: section.style.boxShadow,
            pt:     section.style.paddingTop,
            pb:     section.style.paddingBottom
        };

        // Apply sticky positioning
        section.classList.add(STICKY_CLASS);
        section.style.zIndex = zIndex;
        section.style.transition =
            'background-color ' + duration + 'ms ease, ' +
            'box-shadow '       + duration + 'ms ease, ' +
            'padding-top '      + duration + 'ms ease, ' +
            'padding-bottom '   + duration + 'ms ease';

        if (isEditor) {
            // Show scrolled state as preview in editor
            applyScrolled(section, scrolledBg, showShadow, shadowColor, shrink, scrolledPad);
            return;
        }

        // Set initial state
        updateScrollState(section, threshold, scrolledBg, showShadow, shadowColor, shrink, scrolledPad);

        // Scroll listener (RAF-throttled)
        var ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                requestAnimationFrame(function () {
                    updateScrollState(section, threshold, scrolledBg, showShadow, shadowColor, shrink, scrolledPad);
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

    function updateScrollState(section, threshold, bg, shadow, shadowColor, shrink, pad) {
        if (window.scrollY > threshold) {
            applyScrolled(section, bg, shadow, shadowColor, shrink, pad);
        } else {
            removeScrolled(section);
        }
    }

    function applyScrolled(section, bg, showShadow, shadowColor, shrink, pad) {
        section.classList.add(SCROLLED_CLASS);

        if (bg) {
            section.style.backgroundColor = bg;
        }
        if (showShadow) {
            section.style.boxShadow = '0 2px 12px ' + shadowColor;
        }
        if (shrink) {
            section.style.paddingTop    = pad;
            section.style.paddingBottom = pad;
        }
    }

    function removeScrolled(section) {
        section.classList.remove(SCROLLED_CLASS);
        var o = section._pshOrig || {};
        section.style.backgroundColor = o.bg     || '';
        section.style.boxShadow       = o.shadow || '';
        section.style.paddingTop      = o.pt     || '';
        section.style.paddingBottom   = o.pb     || '';
    }

    // ── Bootstrap ─────────────────────────────────────────────────────────────

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

})();
