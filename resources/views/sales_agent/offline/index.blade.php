@extends('sales_agent.layouts.app', ['title' => 'Offline Orders'])

@section('content')
<div class="container py-2" style="max-width: 600px; padding-bottom: 90px;">

    <!-- HEADER / STATUS CARD -->
    <div class="app-card shadow-sm border-0 mb-3 text-center p-3" style="border-radius: 16px; background: linear-gradient(135deg, #0b6b31 0%, #05421c 100%); color: #fff;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small font-weight-bold text-uppercase" style="letter-spacing: 1px; color: #d6ebc4;">Connection</span>
            <span id="networkStatusBadge" class="badge badge-pill badge-success px-3 py-1 font-weight-bold" style="font-size: 12px;">
                <i class="fas fa-wifi mr-1"></i> Checking...
            </span>
        </div>
        <h4 class="font-weight-bold mb-1">Without Internet Mode</h4>
        <p class="small mb-0" style="color: #cbd5e1;">
            Book orders in shops with no signal. When you have internet, tap <strong>Sync</strong>: your orders go to the office first,
            then the latest shops, stock and prices are downloaded.
        </p>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="row mb-3">
        <div class="col-6 pr-1">
            <a href="{{ route('agent.offline.create') }}" class="btn btn-primary btn-block py-3 shadow-sm font-weight-bold text-center d-flex flex-column align-items-center justify-content-center" style="border-radius: 14px; height: 100%;">
                <i class="fas fa-cart-plus fa-2x mb-2"></i>
                <span>Take Order</span>
                <small class="font-weight-normal">Works without internet</small>
            </a>
        </div>
        <div class="col-6 pl-1">
            <button type="button" id="btnSync" class="btn btn-outline-dark bg-white btn-block py-3 shadow-sm font-weight-bold text-center d-flex flex-column align-items-center justify-content-center border" style="border-radius: 14px; height: 100%;">
                <i class="fas fa-sync-alt fa-2x mb-2 text-primary" id="syncIcon"></i>
                <span>Sync</span>
                <small class="text-muted font-weight-normal" id="lastSyncLabel">Send orders + get latest data</small>
            </button>
        </div>
    </div>

    <!-- PROGRESS -->
    <div id="syncProgressContainer" class="card border-0 shadow-sm p-3 mb-3" style="display: none; border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small font-weight-bold text-dark" id="syncStatusText">Starting...</span>
            <span class="small font-weight-bold text-primary" id="syncPercentText">0%</span>
        </div>
        <div class="progress" style="height: 8px; border-radius: 4px;">
            <div id="syncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%"></div>
        </div>
        <small class="text-muted mt-1">Keep this page open until it says Done.</small>
    </div>

    <!-- DATA ON THIS PHONE -->
    <div class="app-card border-0 shadow-sm p-3 mb-3 bg-white" style="border-radius: 14px;">
        <span class="small text-muted d-block font-weight-bold" style="font-size: 11px;">DATA ON THIS PHONE</span>
        <h6 class="font-weight-bold text-dark mb-0" id="offlineStatsLabel">Loading...</h6>
    </div>

    <!-- LAST SYNC RESULT -->
    <div id="lastSyncCard" class="app-card border-0 shadow-sm p-3 mb-3 bg-white" style="border-radius: 14px; display: none;"></div>

    <!-- ORDERS ON THIS PHONE -->
    <div class="d-flex justify-content-between align-items-center mb-2 mt-4">
        <h5 class="font-weight-bold text-dark mb-0" style="font-size: 16px;">
            <i class="fas fa-clock text-warning mr-1"></i> Orders not uploaded yet
        </h5>
        <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" id="pendingCountBadge">0</span>
    </div>
    <div id="ordersListContainer"></div>
    <div id="otherAgentsNote" class="small text-muted mt-2" style="display: none;"></div>
</div>
@endsection

@push('scripts')
{{-- offline-db.js is loaded and set up for this agent by the layout --}}
<script>
$(document).ready(function () {
    if (SK_AGENT.otherAgentCopy) return; // a saved copy of another agent login's page (the layout says so)
    const DB = SnapKidOfflineDB;
    const agentSeePrice = {{ $agent->see_price ? 'true' : 'false' }};
    const pageRenderedAt = {{ (int) round(microtime(true) * 1000) }};
    const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const money = v => '₹' + Number(v || 0).toLocaleString('en-IN');
    const when = iso => iso ? new Date(iso).toLocaleString('en-IN', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-';
    let seePrice = agentSeePrice;
    let syncing = false;

    function updateOnlineStatus() {
        const badge = $('#networkStatusBadge');
        if (navigator.onLine) {
            badge.removeClass('badge-danger').addClass('badge-success').html('<i class="fas fa-wifi mr-1"></i> Online');
        } else {
            badge.removeClass('badge-success').addClass('badge-danger').html('<i class="fas fa-plane mr-1"></i> No internet');
        }
    }
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    updateOnlineStatus();

    async function loadMetaInfo() {
        const meta = await DB.getMeta();
        if (meta) {
            $('#lastSyncLabel').text('Last sync ' + when(meta.last_synced));
            let txt = meta.shops_count + ' shops';
            if (meta.vendors_count) txt += ', ' + meta.vendors_count + ' vendors';
            $('#offlineStatsLabel').text(txt + ' | ' + meta.products_count + ' products (synced ' + when(meta.last_synced) + ')');
        } else {
            $('#lastSyncLabel').text('Never synced on this phone');
            $('#offlineStatsLabel').text('No shops or products yet. Tap Sync when you have internet.');
        }
    }

    function renderLastSync(rep) {
        const card = $('#lastSyncCard');
        if (!rep) { card.hide(); return; }
        let html = '<div class="d-flex justify-content-between"><span class="small text-muted font-weight-bold" style="font-size:11px;">LAST SYNC</span><span class="small text-muted">' + when(rep.at) + '</span></div>';
        if (rep.error) {
            html += '<div class="text-danger small font-weight-bold mt-1"><i class="fas fa-exclamation-triangle mr-1"></i>' + esc(rep.error) + '</div>';
        } else {
            html += '<div class="text-success small font-weight-bold mt-1"><i class="fas fa-check-circle mr-1"></i>Completed. Data on this phone is fresh.</div>';
        }
        const sent = (rep.saved || []).concat(rep.already || []);
        if (sent.length) {
            html += '<div class="small font-weight-bold mt-2">Uploaded to the office (' + sent.length + '):</div>';
            sent.forEach(s => {
                html += '<div class="d-flex justify-content-between align-items-center small border-bottom py-1"><span>' + esc(s.order_no) + ' · ' + esc(s.shop_name || '') +
                    (seePrice && s.grand_total != null ? ' · ' + money(s.grand_total) : '') + '</span>' +
                    (s.order_id ? '<a class="btn btn-xs btn-outline-primary py-0" href="' + SK_AGENT.urls.orderShow.replace('__ID__', s.order_id) + '">View</a>' : '') + '</div>';
            });
        }
        if ((rep.refused || []).length) {
            html += '<div class="small font-weight-bold text-danger mt-2">Refused by the office (' + rep.refused.length + '): open them below to fix.</div>';
        }
        if ((rep.retry || []).length) {
            html += '<div class="small font-weight-bold text-info mt-2">Sent, not confirmed by the office yet (' + rep.retry.length + '): tap Sync again in a minute.</div>';
        }
        card.html(html).show();
    }

    async function checkPendingOrders() {
        try {
            seePrice = await DB.resolveSeePrice(agentSeePrice, pageRenderedAt);
            const orders = (await DB.getOrders()).filter(DB.isWaiting);
            $('#pendingCountBadge').text(orders.length);
            $('#navOfflineBadge').text(orders.length).toggle(orders.length > 0);
            if (!orders.length) {
                $('#ordersListContainer').html('<div class="text-center py-4 text-muted"><i class="fas fa-clipboard-check fa-3x mb-2 opacity-50"></i>' +
                    '<p class="mb-0 small font-weight-bold">Nothing waiting</p><small>Orders you book without internet appear here until Sync sends them.</small></div>');
            } else {
                let html = '';
                orders.forEach(ord => {
                    const lines = ord.variations || [];
                    const boxes = lines.reduce((s, v) => s + (parseInt(v.qty) || 0), 0);
                    let badge, note = '';
                    if (ord.status === 'refused') {
                        badge = '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Refused</span>';
                        note = '<div class="small text-danger mb-2"><i class="fas fa-exclamation-circle mr-1"></i>' + esc(ord.last_error) + '<br><span class="text-muted">Tap Edit to change it, then Sync again.</span></div>';
                    } else if (ord.sent_unconfirmed) {
                        badge = '<span class="badge badge-info px-2 py-1"><i class="fas fa-paper-plane mr-1"></i>Sent, no answer</span>';
                        note = '<div class="small text-muted mb-2">' + (ord.last_error ? esc(ord.last_error) : 'The office may already have it. Tap Sync to check (it is never saved twice).') + '</div>';
                    } else {
                        badge = '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-hourglass-half mr-1"></i>Waiting</span>';
                    }
                    html += '<div class="app-card border-0 shadow-sm p-3 mb-2 bg-white" style="border-radius: 14px;">' +
                        '<div class="d-flex justify-content-between align-items-start mb-2"><div>' +
                        '<h6 class="font-weight-bold text-dark mb-0">' + esc(ord.shop_name || 'Shop') + (ord.party_type === 'vendor' ? ' <small class="text-muted">(vendor)</small>' : '') + '</h6>' +
                        '<small class="text-muted"><i class="fas fa-calendar-alt mr-1"></i>Booked ' + when(ord.booked_at || ord.created_at) + (ord.is_sample_set ? ' · Sample set order' : '') + '</small></div>' + badge + '</div>' +
                        '<div class="d-flex justify-content-between align-items-center bg-light p-2 rounded mb-2"><span class="small font-weight-bold text-muted">' + lines.length + ' items (' + boxes + ' boxes)</span>' +
                        (seePrice ? '<span class="font-weight-bold text-primary">' + money(ord.grand_total) + '</span>' : '') + '</div>' + note +
                        '<div class="d-flex justify-content-end">' +
                        '<button type="button" class="btn btn-sm btn-outline-danger mr-2 btn-delete-order" data-id="' + esc(ord.local_id) + '"><i class="fas fa-trash-alt mr-1"></i>Discard</button>' +
                        (DB.isEditable(ord) ? '<a class="btn btn-sm btn-primary" href="' + SK_AGENT.urls.create + '?edit=' + encodeURIComponent(ord.local_id) + '"><i class="fas fa-edit mr-1"></i>Edit</a>' : '') +
                        '</div></div>';
                });
                $('#ordersListContainer').html(html);
            }
            const others = await DB.countOtherAgentsOrders();
            $('#otherAgentsNote').toggle(others > 0).text(others + ' order(s) booked by another agent login (or by the old app version) are also kept on this phone. They upload when the agent of that shop logs in and taps Sync.');
            renderLastSync(await DB.lastSyncReport());
        } catch (e) {
            console.error('Offline orders:', e);
            $('#ordersListContainer').html('<div class="alert alert-danger small">Could not read the orders on this phone: ' + esc(e.message) + '</div>');
        }
    }

    async function runSync() {
        if (syncing) return;
        if (!navigator.onLine) {
            Swal.fire('No internet', 'Connect to Wi-Fi or mobile data, then tap Sync. Your orders are safe on this phone.', 'warning');
            return;
        }
        syncing = true;
        $('#btnSync').prop('disabled', true);
        $('#syncIcon').addClass('fa-spin');
        $('#syncProgressContainer').slideDown();
        // no Discard / Edit while orders are being sent
        $('#ordersListContainer').css({ 'pointer-events': 'none', opacity: 0.6 });
        let rep;
        try {
            rep = await DB.syncAll(s => {
                $('#syncStatusText').text(s.text);
                $('#syncPercentText').text(s.pct + '%');
                $('#syncProgressBar').css('width', s.pct + '%');
            });
        } catch (e) {
            rep = { saved: [], already: [], refused: [], retry: [], error: e.message || String(e) };
        } finally {
            syncing = false;
            $('#btnSync').prop('disabled', false);
            $('#syncIcon').removeClass('fa-spin');
            $('#syncProgressContainer').slideUp();
            $('#ordersListContainer').css({ 'pointer-events': '', opacity: '' });
        }
        await loadMetaInfo();
        await checkPendingOrders();

        const sent = rep.saved.length + rep.already.length;
        const retry = (rep.retry || []).length;
        const retryText = retry ? ' ' + retry + ' order(s) were sent but the office has not confirmed them yet: tap Sync again in a minute (they are never saved twice).' : '';
        if (rep.error) {
            Swal.fire({ icon: 'error', title: 'Sync stopped', text: rep.error + (sent ? ' (' + sent + ' order(s) were uploaded before it stopped.)' : '') + retryText });
        } else {
            let text = sent + ' order(s) uploaded. ' + (rep.catalog ? rep.catalog.shops + ' shops/vendors and ' + rep.catalog.products + ' products downloaded.' : '');
            if (rep.photos && rep.photos.failed) text += ' ' + rep.photos.failed + ' photo(s) could not be downloaded.';
            if (rep.refused.length) text += ' ' + rep.refused.length + ' order(s) refused: open them below to fix.';
            text += retryText;
            const warn = rep.refused.length || retry;
            Swal.fire({ icon: warn ? 'warning' : 'success', title: rep.refused.length ? 'Synced, with refused orders' : (retry ? 'Synced, some orders not confirmed' : 'Sync complete'), text });
        }
    }

    $('#btnSync').click(runSync);

    $(document).on('click', '.btn-delete-order', async function () {
        const id = $(this).data('id');
        const ord = await DB.getOrder(String(id));
        if (!ord) return checkPendingOrders();
        const r = await Swal.fire({
            title: 'Discard this order?',
            text: ord.sent_unconfirmed
                ? 'This order was sent but the answer never came back: the office may already have it. Discard only if you are sure (tap Sync first to check).'
                : 'The order for ' + (ord.shop_name || 'this shop') + ' will be deleted from this phone. It has not reached the office.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, discard', confirmButtonColor: '#dc2626'
        });
        if (r.isConfirmed) {
            await DB.deleteOrder(ord.local_id);
            checkPendingOrders();
        }
    });

    (async function init() {
        await loadMetaInfo();
        await checkPendingOrders();
        if (new URLSearchParams(location.search).get('autosync') === '1') {
            history.replaceState(null, '', SK_AGENT.urls.hub);
            runSync();
        }
    })();
});
</script>
@endpush
