@extends('sales_agent.layouts.app', ['title' => 'Offline Orders'])

@section('content')
<div class="container py-2" style="max-width: 600px; padding-bottom: 90px;">

    <!-- HEADER / STATUS CARD -->
    <div class="app-card shadow-sm border-0 mb-3 text-center p-3" style="border-radius: 16px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small font-weight-bold text-uppercase" style="letter-spacing: 1px; color: #94a3b8;">Connection Status</span>
            <span id="networkStatusBadge" class="badge badge-pill badge-success px-3 py-1 font-weight-bold" style="font-size: 12px;">
                <i class="fas fa-wifi mr-1"></i> Checking...
            </span>
        </div>
        <h4 class="font-weight-bold mb-1">Without Internet Mode</h4>
        <p class="small text-muted mb-0" style="color: #cbd5e1 !important;">
            Take orders inside shops with zero mobile signal. Everything syncs when you reconnect.
        </p>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="row mb-3">
        <div class="col-6 pr-1">
            <a href="{{ route('agent.offline.create') }}" class="btn btn-primary btn-block py-3 shadow-sm font-weight-bold text-center d-flex flex-column align-items-center justify-content-center" style="border-radius: 14px; height: 100%;">
                <i class="fas fa-cart-plus fa-2x mb-2"></i>
                <span>Take Order</span>
                <small class="font-weight-normal opacity-75">Without Internet</small>
            </a>
        </div>
        <div class="col-6 pl-1">
            <button type="button" id="btnDownloadCatalog" class="btn btn-outline-dark bg-white btn-block py-3 shadow-sm font-weight-bold text-center d-flex flex-column align-items-center justify-content-center border" style="border-radius: 14px; height: 100%;">
                <i class="fas fa-cloud-download-alt fa-2x mb-2 text-primary" id="downloadIcon"></i>
                <span id="downloadBtnText">Sync Catalog</span>
                <small class="text-muted font-weight-normal" id="lastSyncLabel">Download Data</small>
            </button>
        </div>
    </div>

    <!-- PROGRESS BAR (HIDDEN INITIALLY) -->
    <div id="syncProgressContainer" class="card border-0 shadow-sm p-3 mb-3" style="display: none; border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small font-weight-bold text-dark" id="syncStatusText">Downloading catalog data...</span>
            <span class="small font-weight-bold text-primary" id="syncPercentText">0%</span>
        </div>
        <div class="progress" style="height: 8px; border-radius: 4px;">
            <div id="syncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%"></div>
        </div>
    </div>

    <!-- LOCAL STORAGE SUMMARY -->
    <div class="app-card border-0 shadow-sm p-3 mb-3 bg-white" style="border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="small text-muted d-block uppercase font-weight-bold" style="font-size: 11px;">Offline Catalog Status</span>
                <h6 class="font-weight-bold text-dark mb-0" id="offlineStatsLabel">Loading offline data...</h6>
            </div>
            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" id="btnRefreshStats">
                <i class="fas fa-sync-alt mr-1"></i> Check
            </button>
        </div>
    </div>

    <!-- PENDING ORDERS SECTION -->
    <div class="d-flex justify-content-between align-items-center mb-2 mt-4">
        <h5 class="font-weight-bold text-dark mb-0" style="font-size: 16px;">
            <i class="fas fa-clock text-warning mr-1"></i> Offline Orders Queue
        </h5>
        <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" id="pendingCountBadge">0 Pending</span>
    </div>

    <!-- SYNC ALL BUTTON -->
    <div id="syncAllContainer" class="mb-3" style="display: none;">
        <button type="button" id="btnSyncAll" class="btn btn-success btn-block py-2 rounded-pill font-weight-bold shadow-sm">
            <i class="fas fa-cloud-upload-alt mr-2"></i> Upload <span id="syncAllCount">0</span> Orders to Server
        </button>
    </div>

    <!-- ORDERS LIST CONTAINER -->
    <div id="ordersListContainer">
        <div class="text-center py-4 text-muted" id="noOrdersMessage">
            <i class="fas fa-clipboard-check fa-3x mb-2 text-muted opacity-50"></i>
            <p class="mb-0 small font-weight-bold">No pending offline orders</p>
            <small>Orders taken offline will appear here and upload when you connect.</small>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/offline-db.js') }}"></script>
<script>
$(document).ready(function () {
    // 1. Monitor Online / Offline Status
    function updateOnlineStatus() {
        const isOnline = navigator.onLine;
        const badge = $('#networkStatusBadge');
        if (isOnline) {
            badge.removeClass('badge-danger').addClass('badge-success')
                .html('<i class="fas fa-wifi mr-1"></i> Online');
            $('#btnDownloadCatalog').prop('disabled', false);
            checkPendingOrders();
        } else {
            badge.removeClass('badge-success').addClass('badge-danger')
                .html('<i class="fas fa-wifi-slash mr-1"></i> Offline');
            $('#btnDownloadCatalog').prop('disabled', true);
        }
    }

    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    updateOnlineStatus();

    // 2. Load Local Storage Meta Info
    async function loadMetaInfo() {
        try {
            const meta = await SnapKidOfflineDB.getMeta();
            if (meta) {
                const dateStr = meta.last_synced ? new Date(meta.last_synced).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : 'Never';
                $('#lastSyncLabel').text('Synced: ' + dateStr);
                $('#offlineStatsLabel').text(meta.shops_count + ' Shops | ' + meta.products_count + ' Products Available');
            } else {
                $('#lastSyncLabel').text('Never Synced');
                $('#offlineStatsLabel').text('No data cached yet. Click "Sync Catalog" above.');
            }
        } catch (e) {
            console.error(e);
        }
    }
    loadMetaInfo();

    $('#btnRefreshStats').click(loadMetaInfo);

    // 3. Download Catalog Button
    $('#btnDownloadCatalog').click(async function () {
        if (!navigator.onLine) {
            Swal.fire('No Internet', 'Please connect to Wi-Fi or mobile data to download catalog.', 'warning');
            return;
        }

        const btn = $(this);
        const icon = $('#downloadIcon');
        btn.prop('disabled', true);
        icon.addClass('fa-spin');
        $('#syncProgressContainer').slideDown();
        $('#syncStatusText').text('Downloading shops & products from server...');
        $('#syncProgressBar').css('width', '25%');
        $('#syncPercentText').text('25%');

        try {
            const res = await $.ajax({
                url: "{{ route('agent.offline.catalog-data') }}",
                method: "GET"
            });

            if (res.success) {
                $('#syncStatusText').text('Saving into phone offline database...');
                $('#syncProgressBar').css('width', '60%');
                $('#syncPercentText').text('60%');

                await SnapKidOfflineDB.saveCatalogData(res, function(cached, total) {
                    const pct = Math.round(60 + ((cached / total) * 40));
                    $('#syncProgressBar').css('width', pct + '%');
                    $('#syncPercentText').text(pct + '%');
                    $('#syncStatusText').text('Caching photos (' + cached + '/' + total + ')...');
                });

                $('#syncProgressBar').css('width', '100%');
                $('#syncPercentText').text('100%');
                $('#syncStatusText').text('Completed!');

                setTimeout(() => {
                    $('#syncProgressContainer').slideUp();
                    btn.prop('disabled', false);
                    icon.removeClass('fa-spin');
                    loadMetaInfo();
                    Swal.fire({
                        icon: 'success',
                        title: 'Catalog Synced!',
                        text: res.shops.length + ' shops and ' + res.products.length + ' products are ready for offline use.',
                        timer: 2500
                    });
                }, 800);
            } else {
                throw new Error(res.message || 'Unknown error');
            }
        } catch (err) {
            $('#syncProgressContainer').slideUp();
            btn.prop('disabled', false);
            icon.removeClass('fa-spin');
            let errorMsg = 'Unknown error';
            if (err.responseJSON && err.responseJSON.message) {
                errorMsg = err.responseJSON.message;
            } else if (err.responseText) {
                try {
                    const parsed = JSON.parse(err.responseText);
                    errorMsg = parsed.message || err.responseText;
                } catch (_) {
                    errorMsg = err.statusText || 'Server Error (' + (err.status || 500) + ')';
                }
            } else if (err.message) {
                errorMsg = err.message;
            } else if (err.statusText) {
                errorMsg = err.statusText;
            }
            Swal.fire('Error', 'Failed to download catalog: ' + errorMsg, 'error');
        }
    });

    // 4. Load Pending Orders from IndexedDB
    async function checkPendingOrders() {
        try {
            const orders = await SnapKidOfflineDB.getPendingOrders();
            const listContainer = $('#ordersListContainer');
            const pendingOrders = orders.filter(o => o.status === 'pending_sync');

            $('#pendingCountBadge').text(pendingOrders.length + ' Pending');
            $('#syncAllCount').text(pendingOrders.length);

            if (pendingOrders.length > 0 && navigator.onLine) {
                $('#syncAllContainer').slideDown();
            } else {
                $('#syncAllContainer').slideUp();
            }

            if (orders.length === 0) {
                listContainer.html(`
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-clipboard-check fa-3x mb-2 text-muted opacity-50"></i>
                        <p class="mb-0 small font-weight-bold">No offline orders in queue</p>
                        <small>Orders taken without internet will appear here.</small>
                    </div>
                `);
                return;
            }

            let html = '';
            orders.forEach(ord => {
                const isPending = ord.status === 'pending_sync';
                const totalBoxes = ord.variations.reduce((sum, v) => sum + (parseInt(v.qty) || 0), 0);
                const createdDate = new Date(ord.created_at).toLocaleString();

                html += `
                    <div class="app-card border-0 shadow-sm p-3 mb-2 bg-white" style="border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="font-weight-bold text-dark mb-0">${ord.shop_name || 'Shop'}</h6>
                                <small class="text-muted"><i class="fas fa-calendar-alt mr-1"></i> ${createdDate}</small>
                            </div>
                            ${isPending 
                                ? `<span class="badge badge-warning text-dark font-weight-bold px-2 py-1"><i class="fas fa-hourglass-half mr-1"></i> Pending Sync</span>`
                                : `<span class="badge badge-success font-weight-bold px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Synced: ${ord.server_order_no || 'Saved'}</span>`}
                        </div>
                        <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded mb-2">
                            <span class="small font-weight-bold text-muted">${ord.variations.length} Items (${totalBoxes} Boxes)</span>
                            <span class="font-weight-bold text-primary">₹${ord.grand_total ? ord.grand_total.toLocaleString() : '0'}</span>
                        </div>
                        <div class="d-flex justify-content-end align-items-center">
                            ${isPending ? `
                                <button type="button" class="btn btn-sm btn-outline-danger mr-2 btn-delete-order" data-id="${ord.local_id}">
                                    <i class="fas fa-trash-alt mr-1"></i> Discard
                                </button>
                                <button type="button" class="btn btn-sm btn-primary btn-sync-single" data-id="${ord.local_id}">
                                    <i class="fas fa-upload mr-1"></i> Sync Now
                                </button>
                            ` : `
                                ${ord.server_order_id ? `
                                    <a href="/agent/orders/${ord.server_order_id}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye mr-1"></i> View Order
                                    </a>
                                ` : ''}
                            `}
                        </div>
                    </div>
                `;
            });

            listContainer.html(html);
        } catch (e) {
            console.error('Error fetching offline orders:', e);
        }
    }

    checkPendingOrders();

    // 5. Delete Order
    $(document).on('click', '.btn-delete-order', async function () {
        const id = $(this).data('id');
        const confirm = await Swal.fire({
            title: 'Discard Order?',
            text: 'Are you sure you want to delete this offline order draft?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Discard',
            confirmButtonColor: '#dc2626'
        });

        if (confirm.isConfirmed) {
            await SnapKidOfflineDB.removeOfflineOrder(id);
            checkPendingOrders();
            Swal.fire('Deleted', 'Order removed from local queue.', 'success');
        }
    });

    // 6. Sync Functionality
    async function syncOrdersList(ordersToSync) {
        if (!navigator.onLine) {
            Swal.fire('Offline', 'Cannot upload while offline. Please connect to internet first.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Uploading Orders...',
            text: 'Please wait while orders are being synced with server.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            const res = await $.ajax({
                url: "{{ route('agent.offline.sync') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    orders: ordersToSync
                }
            });

            if (res.success && res.synced && res.synced.length > 0) {
                for (const syn of res.synced) {
                    await SnapKidOfflineDB.markOrderSynced(syn.local_id, syn);
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Orders Synced!',
                    text: res.synced.length + ' offline order(s) successfully created on server.'
                });
            } else if (res.errors && res.errors.length > 0) {
                Swal.fire('Sync Error', res.errors[0].error, 'error');
            } else {
                Swal.fire('Notice', 'No orders were uploaded.', 'info');
            }

            checkPendingOrders();
        } catch (err) {
            Swal.fire('Error', 'Failed to upload orders: ' + (err.responseJSON ? err.responseJSON.message : err.statusText), 'error');
        }
    }

    // Sync Single
    $(document).on('click', '.btn-sync-single', async function () {
        const id = $(this).data('id');
        const allOrders = await SnapKidOfflineDB.getPendingOrders();
        const target = allOrders.find(o => o.local_id === id);
        if (target) {
            syncOrdersList([target]);
        }
    });

    // Sync All
    $('#btnSyncAll').click(async function () {
        const allOrders = await SnapKidOfflineDB.getPendingOrders();
        const pending = allOrders.filter(o => o.status === 'pending_sync');
        if (pending.length > 0) {
            syncOrdersList(pending);
        }
    });
});
</script>
@endpush
