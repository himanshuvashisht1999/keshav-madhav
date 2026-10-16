/**
 * ERP list filters - one behaviour for every DataTables listing in the admin:
 *   - filters are NEVER applied while typing / selecting
 *   - they apply only on the "Filter" button (or Enter) and are cleared by "Reset"
 *
 * How it works (no per-page changes needed):
 *   1. Tables with a header filter row (<tr class="filter"> with inputs/selects):
 *      the inputs are moved into a tidy .erp-filter-bar above the table card.
 *   2. Tables using the default DataTables search box: gets Filter / Reset buttons.
 *   3. Pages that already render their own .erp-filter-bar: any redraw that is
 *      caused by typing/selecting inside that bar is cancelled (preDraw), and a
 *      Filter button is added when the page does not have one.
 */
(function ($) {
    'use strict';
    if (!$ || !$.fn || !$.fn.dataTable) return;

    /* ---- 1. mark "live" events inside filter bars so redraws they cause can be cancelled ---- */
    window.__erpLive = 0;
    var LIVE = { change: 1, keyup: 1, input: 1, paste: 1, cut: 1, 'select2:select': 1, 'select2:clear': 1 };
    var origDispatch = $.event.dispatch;
    $.event.dispatch = function (ev) {
        var live = ev && LIVE[ev.type] && ev.target && ev.target.nodeType === 1 &&
            $(ev.target).closest('.erp-filter-bar, .erp-filter-actions').length;
        if (!live) return origDispatch.apply(this, arguments);
        window.__erpLive++;
        try { return origDispatch.apply(this, arguments); }
        finally { window.__erpLive--; }
    };

    function cancelLiveDraws($table) {
        $table.off('preDraw.dt.erpf').on('preDraw.dt.erpf', function () {
            if (window.__erpLive > 0) return false;   // draw triggered by typing/selecting -> ignore
        });
    }

    /* ---- helpers ---- */
    function refresh(dt) {
        var s = dt.settings()[0];
        if (!s.oFeatures.bServerSide && s.ajax) dt.ajax.reload(); else dt.draw();
    }
    function allTables() {
        var out = [];
        $.fn.dataTable.tables({ visible: false, api: true }).each(function () { out.push(this); });
        return out;
    }
    function refreshAll() {
        $($.fn.dataTable.tables()).each(function () { refresh($(this).DataTable()); });
    }

    function labelFor($cell) {
        var $firstCtl = $cell.find('input,select').first();
        var custom = $firstCtl.attr('data-label') || $cell.attr('data-label') || $firstCtl.attr('title');
        if (custom) return custom;

        var idx = $cell.index();
        var $ths = $cell.closest('thead').find('tr:not(.filter)').last().children();
        var txt = $.trim($ths.eq(idx).text());
        if (!txt) {
            var $c = $cell.find('input,select').first();
            txt = $c.attr('placeholder') || $c.attr('name') || '';
            txt = txt.replace(/^Search\s*/i, '').replace(/\.\.\.$/, '').replace(/_/g, ' ');
        }
        return txt;
    }

    function buttons(applyFn, resetFn) {
        var $wrap = $('<div class="col-auto mb-1 ml-auto"><div class="erp-filter-actions"></div></div>');
        var $apply = $('<button type="button" class="btn-erp btn-erp-primary erp-auto-apply" title="Apply Filter"><i class="fas fa-filter"></i> Filter</button>').on('click', applyFn);
        var $reset = $('<button type="button" class="btn-erp btn-erp-outline erp-auto-reset" title="Reset Filters"><i class="fas fa-undo"></i> Reset</button>').on('click', resetFn);
        $wrap.find('.erp-filter-actions').append($apply, $reset);
        return $wrap;
    }

    /* ---- 2. header filter row -> filter bar ---- */
    function transformHeaderFilters(dt, $table) {
        var $filterRow = $table.find('thead tr.filter').first();
        if (!$filterRow.length || $table.data('erpFilterDone')) return;
        if (!$filterRow.find('input:not([type=hidden]), select').length) return;

        var $bar = $('<div class="erp-filter-bar erp-auto-filter mb-2"><div class="row align-items-end"></div></div>');
        var $row = $bar.find('.row');
        var originals = [];

        $filterRow.children().each(function () {
            var $cell = $(this);
            var $ctl = $cell.find('input:not([type=hidden]):not(.select2-search__field), select').not('.select2-container *').not('.select2 *');
            if (!$ctl.length) return;
            var $col = $('<div class="col-lg-2 col-md-3 col-sm-6 mb-1"></div>');
            $col.append($('<label class="erp-filter-label"></label>').text(labelFor($cell)));
            $ctl.each(function () {
                var $orig = $(this);
                var isSelect = $orig.is('select');
                var hadSelect2 = $orig.hasClass('select2-hidden-accessible') || $orig.hasClass('select2bs4') || $orig.data('select2');
                if (hadSelect2 && $orig.data('select2')) {
                    try { $orig.select2('destroy'); } catch (e) {}
                }
                var $clone = $orig.clone(false, false);
                $clone.val($orig.val());
                $clone.addClass('form-control erp-input').removeClass('erp-filter-input erp-filter-select');
                if (isSelect && (hadSelect2 || $orig.hasClass('select2') || $orig.prop('multiple'))) {
                    $clone.removeClass('select2-hidden-accessible select2bs4').removeAttr('data-select2-id aria-hidden tabindex').css('width', '100%');
                    $clone.find('option').removeAttr('data-select2-id');
                    $clone.addClass('erp-needs-select2');
                }
                $col.append($clone);
                originals.push($orig);
            });
            $row.append($col);
        });

        // retire originals: drop id/name so page code (by id) only finds the bar's copies
        originals.forEach(function ($o) { $o.removeAttr('id').removeAttr('name').prop('disabled', true); });
        $filterRow.hide();

        var apply = function () { refresh(dt); };
        var reset = function () {
            $bar.find('input').val('');
            $bar.find('select').each(function () {
                var $s = $(this);
                var $def = $s.find('option[selected]');
                if ($def.length) {
                    $s.val($def.val());
                } else {
                    this.selectedIndex = 0;
                }
                if ($s.data('select2')) {
                    $s.trigger('change');
                }
            });
            refresh(dt);
        };
        $row.append(buttons(apply, reset));
        $bar.on('keydown', 'input', function (e) { if (e.which === 13) { e.preventDefault(); apply(); } });

        var $anchor = $table.closest('.erp-card');
        if (!$anchor.length) $anchor = $table.closest('.card');
        if (!$anchor.length) $anchor = $table.closest('.dataTables_wrapper');
        $anchor.before($bar);
        if ($.fn.select2) {
            $bar.find('select.erp-needs-select2').each(function () {
                var $s = $(this);
                var ph = $s.attr('data-placeholder') || $s.attr('placeholder') || 'Select';
                $s.removeClass('erp-needs-select2').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: ph,
                    allowClear: !$s.prop('multiple')
                });
            });
        }
        $table.data('erpFilterDone', true);
    }

    /* ---- 3. default DataTables search box ---- */
    function transformDefaultSearch(dt, $table) {
        if ($table.data('erpSearchDone')) return;
        var $box = $table.closest('.dataTables_wrapper').find('.dataTables_filter').first();
        var $input = $box.find('input').first();
        if (!$input.length) return;

        $input.off('.DT').off('keyup input paste cut search');
        var apply = function () { dt.search($input.val()).draw(); };
        var reset = function () { $input.val(''); dt.search('').draw(); };
        var $btns = $('<span class="erp-search-btns"></span>')
            .append($('<button type="button" class="btn-erp btn-erp-primary ml-1"><i class="fas fa-filter"></i> Filter</button>').on('click', apply))
            .append($('<button type="button" class="btn-erp btn-erp-outline ml-1"><i class="fas fa-undo"></i> Reset</button>').on('click', reset));
        $box.find('label').append($btns);
        $input.on('keydown', function (e) { if (e.which === 13) { e.preventDefault(); apply(); } });
        $table.data('erpSearchDone', true);
    }

    /* ---- 4. page-owned filter bars: make sure there is a Filter button + Enter key ---- */
    function enhanceOwnBars() {
        $('.erp-filter-bar:not(.erp-auto-filter)').each(function () {
            var $bar = $(this);
            if ($bar.data('erpEnhanced')) return;
            $bar.data('erpEnhanced', true);
            if ($bar.closest('form').length || $bar.find('form').length) return;   // submit-style bars already work
            var $actions = $bar.find('.erp-filter-actions').first();
            var hasApply = $actions.find('button, a').filter(function () {
                var t = $.trim($(this).text()).toLowerCase();
                return /^(filter|search|apply|go)\b/.test(t) || /apply|filter(btn|_btn)/i.test(this.id || '');
            }).length;
            if ($actions.length && !hasApply) {
                $actions.prepend($('<button type="button" class="btn-erp btn-erp-primary erp-auto-apply" title="Apply Filter"><i class="fas fa-filter"></i> Filter</button>').on('click', refreshAll));
            }
            // Reset: if the page's own reset relied on live change events (now ignored), redraw ourselves
            $bar.find('.erp-filter-actions').find('[id*=eset], [class*=eset]').on('click', function () {
                var t0 = Date.now();
                setTimeout(function () { if ((window.__erpLastDraw || 0) < t0) refreshAll(); }, 80);
            });
            $bar.on('keydown', 'input', function (e) {
                if (e.which === 13) {
                    var $apply = $actions.find('.erp-auto-apply, [id*=pply], [id*=ilter]').not('[id*=eset]').first();
                    e.preventDefault();
                    if ($apply.length) $apply.trigger('click'); else refreshAll();
                }
            });
        });
    }

    $(document).on('init.dt', function (e, settings) {
        var $table = $(settings.nTable);
        // let the page finish binding its own handlers first
        setTimeout(function () {
            var dt = $table.DataTable();
            if ($table.find('thead tr.filter').length) transformHeaderFilters(dt, $table);
            // the built-in DataTables search box (if shown) must also wait for the Filter button
            transformDefaultSearch(dt, $table);
            $table.on('draw.dt xhr.dt', function () { window.__erpLastDraw = Date.now(); });
            cancelLiveDraws($table);
            enhanceOwnBars();
        }, 0);
    });
})(window.jQuery);

/**
 * ErpPager - numbered pagination for the AJAX "rows html" listings that used to
 * load more rows while scrolling.  Usage:
 *   ErpPager.render($('#myTable'), res.meta, function (page) { ...load that page... });
 * `meta` = { current, last, total, from, to }
 */
window.ErpPager = (function ($) {
    function esc(n) { return String(n); }
    function render($table, meta, go) {
        var $host = $table.closest('.erp-card').find('.erp-pager').first();
        if (!$host.length) {
            $host = $('<div class="erp-pager d-flex justify-content-between align-items-center flex-wrap px-2 py-2"></div>');
            ($table.closest('.table-responsive').length ? $table.closest('.table-responsive') : $table).after($host);
        }
        if (!meta || !meta.total) { $host.html('<small class="text-muted">Showing 0 entries</small>'); return; }

        var cur = +meta.current, last = +meta.last, pages = [];
        var push = function (p) { if (pages.indexOf(p) < 0 && p >= 1 && p <= last) pages.push(p); };
        push(1); push(2);
        for (var i = cur - 2; i <= cur + 2; i++) push(i);
        push(last - 1); push(last);
        pages.sort(function (a, b) { return a - b; });

        var html = '<small class="text-muted">Showing ' + esc(meta.from) + ' to ' + esc(meta.to) + ' of ' + esc(meta.total) + ' entries</small>';
        html += '<ul class="pagination pagination-sm mb-0">';
        html += '<li class="page-item' + (cur <= 1 ? ' disabled' : '') + '"><a class="page-link" href="#" data-p="' + (cur - 1) + '">Previous</a></li>';
        var prev = 0;
        pages.forEach(function (p) {
            if (prev && p - prev > 1) html += '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
            html += '<li class="page-item' + (p === cur ? ' active' : '') + '"><a class="page-link" href="#" data-p="' + p + '">' + p + '</a></li>';
            prev = p;
        });
        html += '<li class="page-item' + (cur >= last ? ' disabled' : '') + '"><a class="page-link" href="#" data-p="' + (cur + 1) + '">Next</a></li>';
        html += '</ul>';
        $host.html(html);
        $host.find('a.page-link').on('click', function (e) {
            e.preventDefault();
            var p = +$(this).data('p');
            if (!p || p === cur || $(this).parent().hasClass('disabled')) return;
            go(p);
        });
    }
    return { render: render };
})(window.jQuery);
