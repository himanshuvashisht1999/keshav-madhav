/**
 * Floating sideways scrollbar for wide admin tables.
 *
 * A wide table sits in a scroll box (.table-responsive, or the DataTables scroll body) whose sideways
 * scrollbar is under its last row - on a long list far below the screen, so the admin had to scroll to the
 * end of the page to move the table right. While such a box is on screen and its own scrollbar is not, a
 * copy of that scrollbar is pinned to the bottom of the window; moving either one moves the other.
 * It hides once the box's own scrollbar comes into view, for tables that fit, inside popups, and on touch
 * screens (they swipe the table itself). No per-page changes: every admin page loads this file.
 */
(function () {
    'use strict';

    var BOXES = '.table-responsive, .dataTables_scrollBody';
    var MIN_VISIBLE = 60; // px of the box that must be on screen (header + a row) before the bar shows
    var bar, inner, active = null, queued = false;

    function overflowsSideways(el) {
        if (el.scrollWidth <= el.clientWidth + 1 || !el.offsetParent) return false;
        var ox = window.getComputedStyle(el).overflowX;
        return ox === 'auto' || ox === 'scroll';
    }

    // the wide box that fills most of the screen while its own scrollbar is below the screen
    function pickBox() {
        if (document.body.classList.contains('modal-open')) return null;
        var vh = window.innerHeight, best = null, bestArea = 0;
        var boxes = document.querySelectorAll(BOXES);
        for (var i = 0; i < boxes.length; i++) {
            var el = boxes[i];
            if (el.closest('.modal') || !overflowsSideways(el)) continue;
            var r = el.getBoundingClientRect();
            if (r.bottom <= vh || r.top > vh - MIN_VISIBLE || r.bottom < MIN_VISIBLE) continue;
            var area = Math.min(r.bottom, vh) - Math.max(r.top, 0);
            if (area > bestArea) { best = el; bestArea = area; }
        }
        return best;
    }

    function update() {
        queued = false;
        var box = pickBox();
        if (!box) {
            active = null;
            bar.style.display = 'none';
            return;
        }
        var r = box.getBoundingClientRect();
        active = box;
        bar.style.display = 'block';
        bar.style.left = (r.left + box.clientLeft) + 'px';
        bar.style.width = box.clientWidth + 'px';
        inner.style.width = box.scrollWidth + 'px';
        if (bar.scrollLeft !== box.scrollLeft) bar.scrollLeft = box.scrollLeft;
    }

    // at most one re-check every 40 ms (a timer, not requestAnimationFrame: that one does not run in a window
    // the browser treats as hidden / covered, which left the bar never shown)
    function schedule() {
        if (!queued) {
            queued = true;
            setTimeout(update, 40);
        }
    }

    function init() {
        bar = document.createElement('div');
        bar.className = 'erp-float-scroll';
        bar.setAttribute('aria-hidden', 'true');
        inner = document.createElement('div');
        inner.className = 'erp-float-scroll-inner';
        bar.appendChild(inner);
        document.body.appendChild(bar);

        // bar -> table
        bar.addEventListener('scroll', function () {
            if (active && active.scrollLeft !== bar.scrollLeft) active.scrollLeft = bar.scrollLeft;
        });
        // table -> bar (scroll does not bubble, so listen in the capture phase); page scroll / resize -> re-check
        document.addEventListener('scroll', function (e) {
            if (e.target === active) {
                if (bar.scrollLeft !== active.scrollLeft) bar.scrollLeft = active.scrollLeft;
            } else {
                schedule();
            }
        }, true);
        window.addEventListener('resize', schedule);

        // tables drawn or filled later (DataTables / Ajax), tabs and collapsed cards opening
        if (window.jQuery) {
            window.jQuery(document).on('draw.dt shown.bs.tab shown.bs.collapse shown.bs.modal hidden.bs.modal collapsed.lte.pushmenu shown.lte.pushmenu', schedule);
        }
        if (window.MutationObserver) {
            new MutationObserver(schedule).observe(document.body, { childList: true, subtree: true });
        }
        // layout changes nothing reports (sidebar animation, images loading): a light re-check
        setInterval(function () { if (!document.hidden) schedule(); }, 1000);

        schedule();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
