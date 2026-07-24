@extends('admin.layouts.app')

@section('content')
<style>
    /* ENTERPRISE ERP DESIGN (SAP / ZOHO STYLE) */
    :root {
        --erp-bg: #f5f6f8;
        --erp-panel-bg: #ffffff;
        --erp-border: #d1d5db;
        --erp-primary: #0f62fe;
        --erp-primary-light: #e0e8ff;
        --erp-text-main: #111827;
        --erp-text-muted: #6b7280;
        --erp-active-bg: #f4f8ff;
        --erp-radius: 4px;
        --font-base: 13px;
    }

    body {
        background-color: var(--erp-bg);
        font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
    }

    .spa-container {
        display: flex;
        height: calc(100vh - 60px);
        width: 100%;
        background: var(--erp-bg);
        overflow-x: auto;
        overflow-y: hidden;
        gap: 8px;
        padding: 8px;
        box-sizing: border-box;
    }

    .spa-panel {
        background: var(--erp-panel-bg);
        border: 1px solid var(--erp-border);
        border-radius: var(--erp-radius);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .panel-customers { flex: 0 0 240px; }
    .panel-orders { flex: 0 0 240px; }
    .panel-designs { flex: 0 0 240px; }
    .panel-lots { flex: 0 0 240px; }
    .panel-details { flex: 1 0 400px; min-width: 400px; }

    .spa-panel-header {
        padding: 12px 16px;
        background: #f9fafb;
        border-bottom: 1px solid var(--erp-border);
    }

    .spa-panel-title {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: var(--erp-text-main);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .spa-panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 12px;
        background: #fff;
    }

    .spa-search-box { position: relative; margin-bottom: 12px; }
    .spa-search-box input {
        width: 100%;
        padding: 8px 10px 8px 30px;
        border: 1px solid var(--erp-border);
        border-radius: var(--erp-radius);
        font-size: var(--font-base);
        outline: none;
        transition: border-color 0.15s;
    }
    .spa-search-box input:focus { border-color: var(--erp-primary); box-shadow: 0 0 0 2px rgba(15, 98, 254, 0.1); }
    .spa-search-box i { position: absolute; left: 10px; top: 10px; color: var(--erp-text-muted); font-size: 12px; }
    .spa-search-box button {
        background: var(--erp-primary);
        color: #fff;
        border: none;
        border-radius: var(--erp-radius);
        font-size: var(--font-base);
        font-weight: 600;
        padding: 6px;
        margin-top: 8px;
        cursor: pointer;
        width: 100%;
    }
    .spa-search-box button:hover { background: #0050e6; }

    .list-item {
        background: #fff;
        padding: 10px 12px;
        border-radius: var(--erp-radius);
        margin-bottom: 8px;
        border: 1px solid #e5e7eb;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: all 0.15s ease;
        position: relative;
    }
    .list-item:hover { background: #f9fafb; border-color: #d1d5db; }
    .list-item.active { background: var(--erp-active-bg); border-color: var(--erp-primary-light); border-left: 4px solid var(--erp-primary); }
    .list-item-title { font-size: 13px; font-weight: 600; color: var(--erp-text-main); margin: 0; }
    
    .qty-badge {
        font-size: 12px;
        font-weight: 700;
        background: #f3f4f6;
        color: #374151;
        padding: 2px 8px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }
    .list-item.active .qty-badge { background: #fff; border-color: var(--erp-primary-light); color: var(--erp-primary); }

    .lot-item {
        background: #fff;
        padding: 10px 12px;
        border-radius: var(--erp-radius);
        margin-bottom: 8px;
        border: 1px solid #e5e7eb;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: all 0.15s ease;
    }
    .lot-item:hover { background: #f9fafb; }
    .lot-item.active { background: var(--erp-active-bg); border-color: var(--erp-primary-light); border-left: 4px solid var(--erp-primary); }
    
    .lot-icon {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        color: var(--erp-text-muted);
        font-size: 11px;
    }
    .lot-item.active .lot-icon { background: var(--erp-primary-light); color: var(--erp-primary); }

    .details-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--erp-text-muted);
        text-align: center;
        font-size: 13px;
    }
    .details-placeholder i { font-size: 2.5rem; margin-bottom: 12px; color: #d1d5db; }

    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    .loader { border: 2px solid #f3f3f3; border-top: 2px solid var(--erp-primary); border-radius: 50%; width: 20px; height: 20px; animation: spin 1s linear infinite; margin: 20px auto; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    .panel-details .content-wrapper { min-height: auto !important; padding: 0 !important; background: transparent !important; }
    .panel-details .content { padding: 0 !important; }
    .panel-details .container-fluid { padding-left: 0; padding-right: 0; }

    </style>

<div class="content-wrapper">
    <!-- <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 style="font-weight: 800; color: #1e293b; margin: 0;">
            <i class="fas fa-chart-pie mr-2" style="color: var(--primary);"></i> Design WIP Dashboard
        </h2>
        <div>
            <button class="btn btn-primary" onclick="loadDesigns()" style="border-radius: 8px; font-weight: 600;">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>
        </div>
    </div> -->

    <div class="spa-container">
        <!-- 0a. Customers Panel -->
        <div class="spa-panel panel-customers">
            <div class="spa-panel-header">
                <h3 class="spa-panel-title" style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fas fa-users"></i> Customers</span>
                    <button onclick="clearAllFilters()" class="btn btn-sm btn-light" style="padding: 2px 8px; font-size: 11px; font-weight: 600; color: #dc2626; background: #fee2e2; border: none; border-radius: 4px;">Clear All</button>
                </h3>
                <div class="spa-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="filter_customer" placeholder="Search Customer...">
                </div>
            </div>
            <div class="spa-panel-body" id="customers_list">
                <div class="loader"></div>
            </div>
        </div>

        <!-- 0b. Orders Panel -->
        <div class="spa-panel panel-orders">
            <div class="spa-panel-header">
                <h3 class="spa-panel-title">
                    <i class="fas fa-file-invoice"></i> Orders
                </h3>
                <div class="spa-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="filter_order" placeholder="Search Order SKU/PO...">
                </div>
            </div>
            <div class="spa-panel-body" id="orders_list">
                <div class="loader"></div>
            </div>
        </div>

        <!-- 1. Designs Panel -->
        <div class="spa-panel panel-designs">
            <div class="spa-panel-header">
                <h3 class="spa-panel-title">
                    <i class="fas fa-tshirt"></i> Designs
                </h3>
                <div class="spa-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="filter_design" placeholder="Search Design or Product...">
                </div>
            </div>
            <div class="spa-panel-body" id="designs_list">
                <div class="loader"></div>
            </div>
        </div>

        <!-- 2. Lots Panel -->
        <div class="spa-panel panel-lots">
            <div class="spa-panel-header">
                <h3 class="spa-panel-title">
                    <i class="fas fa-layer-group"></i> Lots
                </h3>
                <div class="spa-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="filter_lot" placeholder="Search Lot No...">
                </div>
            </div>
            <div class="spa-panel-body" id="lots_list">
                <div class="details-placeholder">
                    <i class="fas fa-hand-pointer"></i>
                    <p>Select a design<br>from the left panel.</p>
                </div>
            </div>
        </div>

        <!-- 3. Details Panel -->
        <div class="spa-panel panel-details">
            <div class="spa-panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 class="spa-panel-title">
                        <i class="fas fa-chart-bar"></i> WIP Details
                    </h3>
                    <div id="details_subtitle" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px; font-weight: 600;">
                        Select a lot to view its current stage breakdown
                    </div>
                </div>
                <div>
                    <!-- Stage filter could go here if needed -->
                </div>
            </div>
            <div class="spa-panel-body">
                <div id="details_list">
                    <div class="details-placeholder">
                        <i class="fas fa-project-diagram"></i>
                        <p>Select a lot<br>to view WIP details.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
    let activeCustomer = null;
    let activeOrder = null;
    let activeDesign = null;
    let activeLot = null;

    let customerPage = 1;
    let hasMoreCustomers = true;
    let isCustomerLoading = false;

    let orderPage = 1;
    let hasMoreOrders = true;
    let isOrderLoading = false;

    let designPage = 1;
    let hasMoreDesigns = true;
    let isDesignLoading = false;

    let lotPage = 1;
    let hasMoreLots = true;
    let isLotLoading = false;

    function clearAllFilters() {
        $('#filter_customer, #filter_order, #filter_design, #filter_lot').val('');
        activeCustomer = null;
        activeOrder = null;
        activeDesign = null;
        activeLot = null;
        
        loadCustomers();
        loadOrders();
        loadDesigns();
        // loadDesigns already calls resetDetails() which will call loadLots()
    }

    $(document).ready(function() {
        loadCustomers();
        loadOrders();
        loadDesigns();
        
        let typingTimerCustomer;
        $('#filter_customer').on('keyup', function () {
            clearTimeout(typingTimerCustomer);
            typingTimerCustomer = setTimeout(loadCustomers, 500);
        });

        let typingTimerOrder;
        $('#filter_order').on('keyup', function () {
            clearTimeout(typingTimerOrder);
            typingTimerOrder = setTimeout(loadOrders, 500);
        });

        let typingTimerDesign;
        $('#filter_design').on('keyup', function () {
            clearTimeout(typingTimerDesign);
            typingTimerDesign = setTimeout(loadDesigns, 500);
        });

        let typingTimerLot;
        $('#filter_lot').on('keyup', function () {
            clearTimeout(typingTimerLot);
            typingTimerLot = setTimeout(function() {
                loadLots(activeDesign);
            }, 500);
        });

        // Scroll listeners
        $('#customers_list').on('scroll', function() {
            if($(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight - 20) {
                if(hasMoreCustomers && !isCustomerLoading) {
                    customerPage++;
                    loadCustomers(true);
                }
            }
        });
        
        $('#orders_list').on('scroll', function() {
            if($(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight - 20) {
                if(hasMoreOrders && !isOrderLoading) {
                    orderPage++;
                    loadOrders(true);
                }
            }
        });

        $('#designs_list').on('scroll', function() {
            if($(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight - 20) {
                if(hasMoreDesigns && !isDesignLoading) {
                    designPage++;
                    loadDesigns(true);
                }
            }
        });

        $('#lots_list').on('scroll', function() {
            if($(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight - 20) {
                if(hasMoreLots && !isLotLoading) {
                    lotPage++;
                    loadLots(activeDesign, true);
                }
            }
        });
    });

    function loadCustomers(append = false) {
        if(!append) {
            customerPage = 1;
            hasMoreCustomers = true;
            $('#customers_list').html('<div class="loader"></div>');
        } else {
            $('#customers_list').append('<div class="loader-append" style="text-align: center; padding: 10px;"><i class="fas fa-circle-notch fa-spin"></i> Loading...</div>');
        }
        
        isCustomerLoading = true;
        $.ajax({
            url: "{{ route('admin.reports.design-wip.api.customers') }}",
            type: "GET",
            data: { 
                search: $('#filter_customer').val(),
                page: customerPage
            },
            success: function(res) {
                $('.loader-append').remove();
                if(res.status) {
                    hasMoreCustomers = res.has_more;
                    let html = '';
                    if(res.data.length === 0 && !append) {
                        html = '<div class="text-center text-muted mt-4" style="font-weight: 600;">No customers found.</div>';
                        $('#customers_list').html(html);
                    } else {
                        res.data.forEach(function(item) {
                            let isActive = (activeCustomer == item.id) ? 'active' : '';
                            html += `
                                <div class="list-item ${isActive}" onclick="selectCustomer('${item.id}', this)" style="justify-content: space-between;">
                                    <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 0;">
                                        <div style="width: 32px; height: 32px; border-radius: 4px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-size: 13px; font-weight: 700; color: var(--erp-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.name}</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        if (append) {
                            $('#customers_list').append(html);
                        } else {
                            $('#customers_list').html(html);
                        }
                    }
                }
            },
            complete: function() {
                isCustomerLoading = false;
            }
        });
    }

    function selectCustomer(id, element) {
        if(activeCustomer == id) {
            // Deselect
            activeCustomer = null;
            $('.panel-customers .list-item').removeClass('active');
        } else {
            $('.panel-customers .list-item').removeClass('active');
            $(element).addClass('active');
            activeCustomer = id;
        }
        
        // Reset children
        activeOrder = null;
        activeDesign = null;
        loadOrders();
        loadDesigns();
        resetDetails();
    }

    function loadOrders(append = false) {
        if(!append) {
            orderPage = 1;
            hasMoreOrders = true;
            $('#orders_list').html('<div class="loader"></div>');
        } else {
            $('#orders_list').append('<div class="loader-append" style="text-align: center; padding: 10px;"><i class="fas fa-circle-notch fa-spin"></i> Loading...</div>');
        }
        
        isOrderLoading = true;
        $.ajax({
            url: "{{ route('admin.reports.design-wip.api.orders') }}",
            type: "GET",
            data: { 
                search: $('#filter_order').val(),
                customer_id: activeCustomer,
                page: orderPage
            },
            success: function(res) {
                $('.loader-append').remove();
                if(res.status) {
                    hasMoreOrders = res.has_more;
                    let html = '';
                    if(res.data.length === 0 && !append) {
                        html = '<div class="text-center text-muted mt-4" style="font-weight: 600;">No orders found.</div>';
                        $('#orders_list').html(html);
                    } else {
                        res.data.forEach(function(item) {
                            let isActive = (activeOrder == item.id) ? 'active' : '';
                            html += `
                                <div class="list-item ${isActive}" onclick="selectOrder('${item.id}', this)" style="justify-content: space-between;">
                                    <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 0;">
                                        <div style="width: 32px; height: 32px; border-radius: 4px; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-file-invoice"></i>
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-size: 13px; font-weight: 700; color: var(--erp-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.sku}</div>
                                            <div style="font-size: 10px; font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase;">PO: ${item.po_number || 'N/A'}</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        if (append) {
                            $('#orders_list').append(html);
                        } else {
                            $('#orders_list').html(html);
                        }
                    }
                }
            },
            complete: function() {
                isOrderLoading = false;
            }
        });
    }

    function selectOrder(id, element) {
        if(activeOrder == id) {
            activeOrder = null;
            $('.panel-orders .list-item').removeClass('active');
        } else {
            $('.panel-orders .list-item').removeClass('active');
            $(element).addClass('active');
            activeOrder = id;
        }
        
        activeDesign = null;
        loadDesigns();
        resetDetails();
    }

    function resetDetails() {
        loadLots(activeDesign);
        $('#details_list').html(`
            <div class="details-placeholder">
                <i class="fas fa-project-diagram"></i>
                <p>Select a lot<br>to view WIP details.</p>
            </div>
        `);
        $('#details_subtitle').text('Select a lot to view its current stage breakdown');
    }

    function loadDesigns(append = false) {
        if(!append) {
            designPage = 1;
            hasMoreDesigns = true;
            $('#designs_list').html('<div class="loader"></div>');
            resetDetails();
        } else {
            $('#designs_list').append('<div class="loader-append" style="text-align: center; padding: 10px;"><i class="fas fa-circle-notch fa-spin"></i> Loading...</div>');
        }

        let data = {
            design_no: $('#filter_design').val(),
            customer_id: activeCustomer,
            order_id: activeOrder,
            page: designPage
        };
        
        isDesignLoading = true;
        $.ajax({
            url: "{{ route('admin.reports.design-wip.api.designs') }}",
            type: "GET",
            data: data,
            success: function(res) {
                $('.loader-append').remove();
                if(res.status) {
                    hasMoreDesigns = res.has_more;
                    let html = '';
                    if(res.data.length === 0 && !append) {
                        html = '<div class="text-center text-muted mt-4" style="font-weight: 600;">No designs found.</div>';
                        $('#designs_list').html(html);
                    } else {
                        res.data.forEach(function(item) {
                            let productName = item.product_name ? item.product_name.trim() : '';
                            if (productName === item.design_no.toString().trim()) { productName = 'Base Design'; }
                            
                            html += `
                                <div class="list-item" onclick="selectDesign('${item.design_no}', this)" style="justify-content: space-between;">
                                    <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 0;">
                                        <div style="width: 32px; height: 32px; border-radius: 4px; background: var(--erp-primary-light); color: var(--erp-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-tshirt" style="font-size: 14px;"></i>
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-size: 13px; font-weight: 700; color: var(--erp-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.design_no}</div>
                                            <div style="font-size: 10px; font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">${productName}</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; margin-left: 10px;">
                                        <div style="font-size: 10px; font-weight: 700; color: #b45309; background: #fef3c7; padding: 2px 6px; border-radius: 4px;">
                                            <i class="fas fa-layer-group" style="margin-right: 3px;"></i>${item.lot_count} Lots
                                        </div>
                                        <div style="font-size: 11px; font-weight: 700; color: var(--erp-primary); background: var(--erp-primary-light); padding: 2px 6px; border-radius: 4px;">
                                            ${item.total_qty} Pcs
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        if (append) {
                            $('#designs_list').append(html);
                        } else {
                            $('#designs_list').html(html);
                        }
                    }
                }
            },
            error: function() {
                $('.loader-append').remove();
                if(!append) $('#designs_list').html('<div class="text-danger text-center mt-3">Error loading designs.</div>');
            },
            complete: function() {
                isDesignLoading = false;
            }
        });
    }

    function selectDesign(designNo, element) {
        $('.list-item').removeClass('active');
        $(element).addClass('active');
        activeDesign = designNo;
        
        // Reset details
        $('#details_list').html(`
            <div class="details-placeholder">
                <i class="fas fa-project-diagram"></i>
                <p>Select a lot<br>to view WIP details.</p>
            </div>
        `);
        $('#details_subtitle').text('Select a lot to view its current stage breakdown');

        loadLots(designNo);
    }

    function loadLots(designNo, append = false) {
        if(!append) {
            lotPage = 1;
            hasMoreLots = true;
            $('#lots_list').html('<div class="loader"></div>');
        } else {
            $('#lots_list').append('<div class="loader-append" style="text-align: center; padding: 10px;"><i class="fas fa-circle-notch fa-spin"></i> Loading...</div>');
        }
        
        isLotLoading = true;
        $.ajax({
            url: "{{ route('admin.reports.design-wip.api.lots') }}",
            type: "GET",
            data: { 
                design_no: designNo,
                order_id: activeOrder,
                customer_id: activeCustomer,
                search: $('#filter_lot').val(),
                page: lotPage
            },
            success: function(res) {
                $('.loader-append').remove();
                if(res.status) {
                    hasMoreLots = res.has_more;
                    let html = '';
                    if(res.data.length === 0 && !append) {
                        html = '<div class="text-center text-muted mt-4" style="font-weight: 600;">No lots found.</div>';
                        $('#lots_list').html(html);
                    } else {
                        res.data.forEach(function(item) {
                            let lotStr = item.lot_no;
                            let isBase = lotStr.startsWith('UNASSIGNED_');
                            let displayLot = isBase ? 'Unassigned Pieces' : lotStr;
                            
                            html += `
                                <div class="lot-item" onclick="selectLot('${item.lot_no}', '${displayLot}', this)">
                                    <div class="lot-icon">
                                        <i class="fas ${isBase ? 'fa-box' : 'fa-layer-group'}"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-size: 0.95rem; font-weight: 700;">${displayLot}</div>
                                        ${isBase ? '<div style="font-size: 0.7rem; color: #9ca3af;">Pending to cut</div>' : ''}
                                    </div>
                                    <div class="qty-badge" style="background: #eef2ff; color: var(--primary); padding: 4px 10px;">${item.qty}</div>
                                </div>
                            `;
                        });
                        if (append) {
                            $('#lots_list').append(html);
                        } else {
                            $('#lots_list').html(html);
                            // If only 1 lot on initial load, auto-click it
                            if(res.data.length === 1) {
                                $('.lot-item').first().trigger('click');
                            }
                        }
                    }
                }
            },
            error: function() {
                $('.loader-append').remove();
                if(!append) $('#lots_list').html('<div class="text-danger text-center mt-3">Error loading lots.</div>');
            },
            complete: function() {
                isLotLoading = false;
            }
        });
    }

    function selectLot(lotNo, displayLot, element) {
        $('.lot-item').removeClass('active');
        if(element) {
            $(element).addClass('active');
        }
        activeLot = lotNo;
        $('#details_subtitle').html(`Viewing WIP details for: <strong style="background: #e5e7eb; color: #374151; padding: 2px 6px; border-radius: 4px;">${displayLot}</strong>`);
        loadLotDetails(lotNo, activeDesign);
    }

    function loadLotDetails(lotNo, designNo) {
        $('#details_list').html('<div class="loader"></div>');
        
        $.ajax({
            url: "{{ route('admin.reports.design-wip.api.lot-details') }}",
            type: "GET",
            data: { lot_no: lotNo, design_no: designNo },
            success: function(res) {
                if(res.status) {
                    $('#details_list').html(res.html);
                } else {
                    $('#details_list').html(`
                        <div class="details-placeholder">
                            <i class="fas fa-exclamation-circle" style="color: var(--warning); opacity: 1;"></i>
                            <p style="font-weight: 600; color: var(--text-main); font-size: 1.1rem;">${res.message || 'No data found.'}</p>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#details_list').html('<div class="text-danger text-center mt-3">Error loading details.</div>');
            }
        });
    }

</script>
@endsection






