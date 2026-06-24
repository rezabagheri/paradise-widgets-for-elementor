/**
 * Off-Canvas Menu — Paradise Elementor Widgets
 *
 * Wires up trigger, overlay, and close button for each .paradise-ocm-wrap.
 * Manages body scroll lock when any panel is open.
 *
 * Public API (window.Paradise):
 *   Paradise.openOffCanvas(id)     — open panel by widget id
 *   Paradise.closeOffCanvas(id)    — close panel by widget id
 *   Paradise.toggleOffCanvas(id)   — toggle panel
 */
(function () {
    'use strict';

    window.Paradise = window.Paradise || {};

    var OPEN_CLASS = 'paradise-ocm--open';
    var BODY_LOCK  = 'paradise-ocm-body-lock';

    /** Map of id → { panel, overlay, trigger } */
    var instances = {};

    // ── Initialisation ────────────────────────────────────────────────────────

    function initAll() {
        document.querySelectorAll('.paradise-ocm-wrap').forEach(initWrap);
    }

    function initWrap(wrap) {
        var id            = wrap.getAttribute('data-ocm-id');
        var isEditor      = wrap.getAttribute('data-ocm-edit') === 'true';
        var closeOnOverlay = wrap.getAttribute('data-ocm-overlay') !== 'false';

        var trigger  = wrap.querySelector('.paradise-ocm-trigger');
        var panel    = wrap.querySelector('.paradise-ocm-panel');
        var overlay  = wrap.querySelector('.paradise-ocm-overlay');
        var closeBtn = wrap.querySelector('.paradise-ocm-close');

        if (!panel) return;

        instances[id] = { panel: panel, overlay: overlay, trigger: trigger };

        // No interaction in editor
        if (isEditor) return;

        // Closed panels are inert + hidden from the a11y tree so keyboard users
        // can't Tab into a visually-hidden menu. openPanel() reverses this.
        panel.setAttribute('inert', '');
        panel.setAttribute('aria-hidden', 'true');
        if (!panel.hasAttribute('tabindex')) panel.setAttribute('tabindex', '-1');

        if (trigger) {
            trigger.addEventListener('click', function () { openPanel(id); });
        }

        if (overlay && closeOnOverlay) {
            overlay.addEventListener('click', function () { closePanel(id); });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function () { closePanel(id); });
        }

        // ESC key closes the panel
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isPanelOpen(id)) {
                closePanel(id);
            }
        });
    }

    // ── Open / close ──────────────────────────────────────────────────────────

    function isPanelOpen(id) {
        var inst = instances[id];
        return inst && inst.panel.classList.contains(OPEN_CLASS);
    }

    var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
    var lastFocus = null;
    var trapHandlers = {};

    function getFocusable(panel) {
        return Array.prototype.slice.call(panel.querySelectorAll(FOCUSABLE))
            .filter(function (el) { return el.offsetWidth || el.offsetHeight || el.getClientRects().length; });
    }

    function openPanel(id) {
        var inst = instances[id];
        if (!inst) return;

        lastFocus = document.activeElement;
        inst.panel.removeAttribute('inert');
        inst.panel.setAttribute('aria-hidden', 'false');
        inst.panel.classList.add(OPEN_CLASS);
        if (inst.overlay) inst.overlay.classList.add(OPEN_CLASS);
        if (inst.trigger) inst.trigger.setAttribute('aria-expanded', 'true');

        document.body.classList.add(BODY_LOCK);

        // Move focus into the panel and trap Tab within it.
        var f = getFocusable(inst.panel);
        (f[0] || inst.panel).focus();

        var trap = function (e) {
            if (e.key !== 'Tab') return;
            var items = getFocusable(inst.panel);
            if (!items.length) return;
            var first = items[0], last = items[items.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        };
        trapHandlers[id] = trap;
        inst.panel.addEventListener('keydown', trap);
    }

    function closePanel(id) {
        var inst = instances[id];
        if (!inst) return;

        inst.panel.classList.remove(OPEN_CLASS);
        if (inst.overlay) inst.overlay.classList.remove(OPEN_CLASS);
        if (inst.trigger) inst.trigger.setAttribute('aria-expanded', 'false');
        inst.panel.setAttribute('aria-hidden', 'true');
        inst.panel.setAttribute('inert', '');

        if (trapHandlers[id]) {
            inst.panel.removeEventListener('keydown', trapHandlers[id]);
            delete trapHandlers[id];
        }

        // Only remove body lock when no other panels remain open
        var anyOpen = Object.keys(instances).some(function (k) {
            return k !== id && isPanelOpen(k);
        });
        if (!anyOpen) {
            document.body.classList.remove(BODY_LOCK);
        }

        // Return focus to the trigger (or wherever it was before opening).
        if (inst.trigger) { inst.trigger.focus(); }
        else if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
    }

    // ── Public API ────────────────────────────────────────────────────────────

    Paradise.openOffCanvas = openPanel;
    Paradise.closeOffCanvas = closePanel;
    Paradise.toggleOffCanvas = function (id) {
        isPanelOpen(id) ? closePanel(id) : openPanel(id);
    };

    // ── Bootstrap ─────────────────────────────────────────────────────────────

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

})();
