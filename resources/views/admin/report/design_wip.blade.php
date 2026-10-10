@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- HEADER BAR --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">Design WIP Dashboard</h1>
        </div>
        <div class="d-flex align-items-center" style="gap: 8px;">
            <div class="erp-report-meta">
                Date : {{ now()->format('d M Y h:i A') }}
            </div>
            <button type="button" class="btn-erp btn-erp-primary" onclick="loadDesigns()">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </div>
    </div>

    <div class="spa-container">
        <!-- 0a. Customers Panel -->
        <div class="spa-panel panel-customers">
            <div class="spa-panel-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="spa-panel-title"><i class="fas fa-users"></i> Customers</span>
                    <button type="button" onclick="clearAllFilters()" class="btn-erp btn-erp-danger" style="padding: 2px 6px; font-size: 10px;">Clear</button>
                </div>
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
                                <div class="list-item" onclick="selectDesign('${item.set_id}', this)" style="justify-content: space-between;">
                                    <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 0;">
                                        <div style="width: 32px; height: 32px; border-radius: 4px; background: var(--erp-primary-light); color: var(--erp-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-tshirt" style="font-size: 14px;"></i>
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-size: 13px; font-weight: 700; color: var(--erp-text-main); white-space: normal; word-break: break-word;">${item.design_no}</div>
                                            <div style="font-size: 10px; font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; white-space: normal; margin-top: 2px;">${productName}</div>
                                            <div style="font-size: 10px; font-weight: 600; color: #6b7280; margin-top: 4px; white-space: normal; word-break: break-word; line-height: 1.3;">
                                                <span style="color: #4b5563;">Sizes:</span> ${item.sizes || '-'}
                                            </div>
                                            <div style="font-size: 10px; font-weight: 600; color: #6b7280; margin-top: 2px; white-space: normal; word-break: break-word; line-height: 1.3;">
                                                <span style="color: #4b5563;">Colors:</span> ${item.colors || '-'}
                                            </div>
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

    let activeSetId = null;

    function selectDesign(setId, element) {
        $('#designs_list .list-item').removeClass('active');
        $(element).addClass('active');
        activeSetId = setId;
        
        // Reset details
        $('#details_list').html(`
            <div class="details-placeholder">
                <i class="fas fa-project-diagram"></i>
                <p>Select a lot<br>to view WIP details.</p>
            </div>
        `);
        $('#details_subtitle').text('Select a lot to view its current stage breakdown');

        loadLots(setId);
    }

    function loadLots(setId, append = false) {
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
                set_id: setId,
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
                                        ${isBase ? '<div style="font-size: 0.7rem; color: #9ca3af;">Pending to cut</div>' : 
                                        `<div style="font-size: 0.7rem; color: #6b7280; margin-top: 2px;"><i class="fas fa-cut" style="color: #9ca3af; margin-right: 4px;"></i>${item.cutting_master || '-'}</div>
                                         <div style="font-size: 0.7rem; color: #6b7280; margin-top: 1px;"><i class="far fa-calendar-alt" style="color: #9ca3af; margin-right: 4px;"></i>Assign: ${item.assign_date || '-'}</div>`
                                        }
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
        let subtitleHtml = `
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <span>Viewing WIP details for: <strong style="background: #e5e7eb; color: #374151; padding: 2px 6px; border-radius: 4px;">${displayLot}</strong></span>
        `;
        
        if (!lotNo.startsWith('UNASSIGNED_')) {
            subtitleHtml += `
                <a href="{{ url('admin/report/lots/lot-details') }}/${lotNo}" target="_blank" style="color: var(--erp-primary); text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 4px; font-size: 11px; background: var(--erp-primary-light); padding: 4px 8px; border-radius: 4px;">
                    <i class="fas fa-external-link-alt"></i> Full Details
                </a>
            `;
        }
        subtitleHtml += `</div>`;
        
        $('#details_subtitle').html(subtitleHtml);
        loadLotDetails(lotNo, activeSetId);
    }

    function loadLotDetails(lotNo, setId) {
        $('#details_list').html('<div class="loader"></div>');
        
        $.ajax({
            url: "{{ route('admin.reports.design-wip.api.lot-details') }}",
            type: "GET",
            data: { lot_no: lotNo, set_id: setId },
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






