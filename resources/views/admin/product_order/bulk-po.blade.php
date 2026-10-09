@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="erp-header-title mb-0">
                <i class="fas fa-boxes text-warning mr-1"></i> Bulk Production Purchase Order
            </h5>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.product_order.poList') }}" class="btn btn-xs btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to PO List
            </a>
        </div>
    </div>

    <form id="bulkPoForm">
        @csrf
        <input type="hidden" name="order_id" id="form_order_id">
        <div class="row">
            <!-- LEFT COLUMN: AVAILABLE SETS -->
            <div class="col-lg-4 col-md-5 mb-2">
                <div class="erp-card h-100">
                    <div class="erp-card-header d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="erp-card-title mb-0 font-weight-bold" style="font-size:13px;">
                            <i class="fas fa-list text-muted mr-1"></i> Available Order Sets
                        </span>
                        <span class="text-muted" style="font-size: 11px;">Search & select</span>
                    </div>
                    <div class="card-body p-2">
                        <div class="input-group mb-2">
                            <input type="text" id="setSearch" class="form-control form-control-sm erp-input" placeholder="Search Design / SKU / Order...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-xs btn-erp-primary" onclick="loadSets()" style="border-radius: 0 4px 4px 0;">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div id="availableSetsList" style="max-height: 620px; overflow-y: auto; padding-right: 2px;">
                            <div class="text-center text-muted p-4" style="font-size: 12px;">Search or click to load unassigned sets</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: PO DETAILS -->
            <div class="col-lg-8 col-md-7 mb-2">
                <div class="erp-card h-100">
                    <div class="erp-card-header d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="erp-card-title mb-0 font-weight-bold" style="font-size:13px;">
                            <i class="fas fa-file-signature text-muted mr-1"></i> PO Header & Selected Items
                        </span>
                        <span class="badge badge-light border text-secondary" style="font-size: 11px;">
                            Bulk Generation
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <!-- HEADER INFO -->
                        <div class="bg-light p-2 rounded mb-3 border">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">PO To <span class="text-danger">*</span></label>
                                    <select name="po_type" id="po_type" class="form-control form-control-sm erp-input" onchange="togglePoTo(this.value)">
                                        <option value="vendor">Vendor</option>
                                        <option value="customer">Customer</option>
                                    </select>
                                </div>
                                <div class="col-md-5 mb-2" id="vendor_group">
                                    <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Vendor <span class="text-danger">*</span></label>
                                    <select name="vendor_id" class="form-control form-control-sm erp-input select2">
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5 mb-2" id="customer_group" style="display:none;">
                                    <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" class="form-control form-control-sm erp-input select2">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Delivery Date <span class="text-danger">*</span></label>
                                    <input type="date" name="delivery_date" class="form-control form-control-sm erp-input" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Global Remark</label>
                                    <textarea name="remark" class="form-control form-control-sm erp-input" rows="1" placeholder="Common remark for this PO..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- SELECTED ITEMS TABLE -->
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered erp-table mb-0" id="selectedItemsTable">
                                <thead>
                                    <tr>
                                        <th style="min-width: 140px;">ITEM DETAILS</th>
                                        <th style="width: 110px;">QTY (PCS)</th>
                                        <th style="width: 100px;">RATE</th>
                                        <th style="min-width: 220px;">FABRIC / PATTERN / FITTING</th>
                                        <th style="width: 45px;" class="text-center"><i class="fa fa-cog"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="emptyPlaceholder">
                                        <td colspan="5" class="text-center p-4 text-muted" style="font-size: 12px;">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block text-muted"></i>
                                            No sets selected yet. Choose an order and check items from the left panel to add.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light d-flex justify-content-between align-items-center py-2 px-3 border-top">
                        <a href="{{ route('admin.product_order.poList') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                        <button type="submit" id="submitBtn" class="btn btn-sm btn-erp-primary px-3" disabled>
                            <i class="fas fa-check-circle mr-1"></i> Create Bulk Purchase Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ITEM ROW TEMPLATE -->
<script type="text/template" id="itemRowTemplate">
    <tr class="item-row" data-id="{id}">
        <td>
            <input type="hidden" name="items[{idx}][order_product_set_id]" value="{id}">
            <span class="font-weight-bold text-dark d-block" style="font-size: 12px;">{design_number}</span>
            <small class="text-muted d-block" style="font-size: 11px;">{sku}</small>
            <div class="mt-1">
                <span class="badge badge-info" style="font-size: 10px;">{color}</span>
                <span class="badge badge-secondary" style="font-size: 10px;">{size}</span>
            </div>
        </td>
        <td>
            <input type="number" name="items[{idx}][quantity]" class="form-control form-control-sm erp-input" value="{remain_qty}" max="{remain_qty}" min="1">
            <small class="text-muted d-block mt-1 font-weight-bold" style="font-size: 10px;">Max: {remain_qty}</small>
        </td>
        <td>
            <input type="number" step="0.01" name="items[{idx}][rate]" class="form-control form-control-sm erp-input" placeholder="Rate">
        </td>
        <td>
            <div class="row">
                <div class="col-12 mb-1">
                    <select name="items[{idx}][fabric_ids][]" class="form-control form-control-sm erp-input select2" multiple data-placeholder="Select Fabric">
                        @foreach($fabrics as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 pr-1">
                    <select name="items[{idx}][pattern_id]" class="form-control form-control-sm erp-input select2">
                        <option value="">Pattern</option>
                        @foreach($patterns as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 pl-1">
                    <select name="items[{idx}][fitting_id]" class="form-control form-control-sm erp-input select2">
                        <option value="">Fitting</option>
                        @foreach($fittings as $fit)
                            <option value="{{ $fit->id }}">{{ $fit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mt-1">
                    <input type="text" name="items[{idx}][belt]" class="form-control form-control-sm erp-input" placeholder="Belt details">
                </div>
            </div>
        </td>
        <td class="text-center align-middle">
            <button type="button" class="erp-action-btn erp-btn-delete remove-item" title="Remove">
                <i class="fa fa-times"></i>
            </button>
        </td>
    </tr>
</script>
@endsection

@section('scripts')
<script>
let itemCount = 0;
let selectedIds = [];

$(document).ready(function() {
    $('.select2').select2({ width: '100%' });
    loadSets();

    // Add enter key listener for search
    $('#setSearch').on('keyup', function(e) {
        if (e.keyCode === 13) {
            loadSets();
        }
    });

    // Check for query parameters to auto-add items
    const urlParams = new URLSearchParams(window.location.search);
    const setIds = urlParams.get('set_ids');
    const singleSetId = urlParams.get('set_id');
    const orderId = urlParams.get('order_id');

    if (orderId) {
        $('#form_order_id').val(orderId);
        // If coming from a specific order, load its sets directly on the left
        loadSetsByOrder(orderId);
    } else if (setIds || singleSetId) {
        const idsToFetch = setIds ? setIds.split(',') : [singleSetId];
        fetchAndAddSets(idsToFetch);
    }

    $('#bulkPoForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#submitBtn');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Processing...');

        $.ajax({
            url: "{{ route('admin.product_order.storeBulkPO') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(res) {
                if (res.status) {
                    toastr.success(res.message);
                    window.location.reload();
                } else {
                    toastr.error(res.message);
                    btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Create Bulk Purchase Order');
                }
            },
            error: function() {
                toastr.error('Something went wrong');
                btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Create Bulk Purchase Order');
            }
        });
    });

    $(document).on('click', '.add-to-po', function() {
        const data = $(this).data();
        addItemToPo(data);
    });

    $(document).on('click', '.remove-item', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        selectedIds = selectedIds.filter(sid => sid != id);
        
        // Uncheck the box on the left if it exists
        $(`#check-${id}`).prop('checked', false);
        $(`#available-card-${id}`).removeClass('border-success bg-light');

        row.remove();
        updateSubmitButton();
        if ($('.item-row').length === 0) {
            $('#emptyPlaceholder').show();
        }
    });
});

function togglePoTo(val) {
    if (val === 'vendor') {
        $('#vendor_group').show();
        $('#customer_group').hide();
    } else {
        $('#vendor_group').hide();
        $('#customer_group').show();
    }
}

function fetchAndAddSets(ids) {
    $.get("{{ route('admin.product_order.getUnassignedSets') }}", { ids: ids }, function(data) {
        data.forEach(set => {
            addItemToPo({
                id: set.id,
                design: set.design_number,
                sku: set.sku,
                color: set.colors ? set.colors.name : 'N/A',
                size: set.set_size,
                remain: set.remain_total_quantity
            });
        });
    });
}

function loadSetsByOrder(orderId) {
    const list = $('#availableSetsList');
    list.html('<div class="text-center p-3 text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Loading Sets...</div>');

    const search = $('#setSearch').val();
    $.get("{{ route('admin.product_order.getUnassignedSets') }}", { order_id: orderId, search: search }, function(data) {
        list.empty();
        if (data.length === 0) {
            list.html('<div class="text-center text-muted p-3" style="font-size:12px;">No unassigned sets found for this order.</div>');
            return;
        }

        data.forEach(set => {
            const isSelected = selectedIds.includes(set.id);
            list.append(`
                <div class="p-2 mb-2 rounded border ${isSelected ? 'border-success bg-light' : 'bg-white'}" id="available-card-${set.id}" style="transition: all 0.2s ease;">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input set-checkbox" id="check-${set.id}" 
                               ${isSelected ? 'checked' : ''}
                               data-id="${set.id}"
                               data-design="${set.design_number}"
                               data-sku="${set.sku}"
                               data-color="${set.colors ? set.colors.name : 'N/A'}"
                               data-size="${set.set_size}"
                               data-remain="${set.remain_total_quantity}">
                        <label class="custom-control-label d-block cursor-pointer" for="check-${set.id}" style="cursor:pointer;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong class="text-dark" style="font-size: 12px;">${set.design_number}</strong>
                                    <small class="text-muted d-block" style="font-size: 10px;">${set.sku}</small>
                                </div>
                                <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 10px;">
                                    ${set.remain_total_quantity} Pcs
                                </span>
                            </div>
                            <div class="mt-1">
                                <span class="badge badge-info" style="font-size: 10px;">${set.colors ? set.colors.name : 'N/A'}</span>
                                <span class="badge badge-secondary" style="font-size: 10px;">${set.set_size}</span>
                            </div>
                        </label>
                    </div>
                </div>
            `);
        });
    });
}

function loadOrders() {
    const search = $('#setSearch').val();
    const list = $('#availableSetsList');
    list.html('<div class="text-center p-3 text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Loading Orders...</div>');

    $.get("{{ route('admin.product_order.getUnassignedOrders') }}", { search: search }, function(data) {
        list.empty();
        if (data.length === 0) {
            list.html('<div class="text-center text-muted p-3" style="font-size:12px;">No unassigned orders found.</div>');
            return;
        }

        data.forEach(order => {
            list.append(`
                <div class="mb-2 rounded border order-card bg-white" data-order-id="${order.id}">
                    <div class="p-2 cursor-pointer toggle-order d-flex justify-content-between align-items-center bg-light" style="cursor:pointer; border-radius: 4px;">
                        <span class="font-weight-bold text-dark" style="font-size: 12px;">
                            <i class="fas fa-folder text-warning mr-1"></i> ${order.sku}
                        </span>
                        <i class="fa fa-chevron-down text-muted" style="font-size: 11px;"></i>
                    </div>
                    <div class="p-2 order-sets-container" id="order-sets-${order.id}" style="display:none; background: #fafbfc;">
                        <div class="text-center p-2"><i class="fa fa-spinner fa-spin text-muted"></i></div>
                    </div>
                </div>
            `);
        });
    });
}

$(document).on('change', '.set-checkbox', function() {
    const data = $(this).data();
    if ($(this).is(':checked')) {
        addItemToPo(data);
        $(`#available-card-${data.id}`).addClass('border-success bg-light');
    } else {
        $(`tr[data-id="${data.id}"]`).find('.remove-item').click();
        $(`#available-card-${data.id}`).removeClass('border-success bg-light');
    }
});

$(document).on('click', '.toggle-order', function() {
    const card = $(this).closest('.order-card');
    const orderId = card.data('order-id');
    const container = $(`#order-sets-${orderId}`);
    const icon = $(this).find('i.fa-chevron-down, i.fa-chevron-up');

    if (container.is(':visible')) {
        container.slideUp();
        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
    } else {
        // Load sets for this order if not loaded or refresh
        container.html('<div class="text-center p-2"><i class="fa fa-spinner fa-spin text-muted"></i></div>').slideDown();
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');

        $.get("{{ route('admin.product_order.getUnassignedSets') }}", { order_id: orderId }, function(data) {
            container.empty();
            if (data.length === 0) {
                container.html('<small class="text-muted d-block text-center p-2">No unassigned sets</small>');
                return;
            }

            data.forEach(set => {
                const isSelected = selectedIds.includes(set.id);
                container.append(`
                    <div class="border rounded p-2 mb-1 ${isSelected ? 'border-success bg-white' : 'bg-white'}" id="available-card-${set.id}" style="transition: all 0.2s ease;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input set-checkbox" id="check-${set.id}" 
                                   ${isSelected ? 'checked' : ''}
                                   data-id="${set.id}"
                                   data-design="${set.design_number}"
                                   data-sku="${set.sku}"
                                   data-color="${set.colors ? set.colors.name : 'N/A'}"
                                   data-size="${set.set_size}"
                                   data-remain="${set.remain_total_quantity}">
                            <label class="custom-control-label d-block cursor-pointer" for="check-${set.id}" style="cursor:pointer;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <strong class="text-dark" style="font-size: 11px;">${set.design_number}</strong>
                                    <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 9px;">${set.remain_total_quantity} Pcs</span>
                                </div>
                                <small class="text-muted d-block" style="font-size: 10px;">${set.colors ? set.colors.name : 'N/A'} | ${set.set_size}</small>
                            </label>
                        </div>
                    </div>
                `);
            });
        });
    }
});

function loadSets() {
    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get('order_id');
    if (orderId) {
        loadSetsByOrder(orderId);
    } else {
        loadOrders();
    }
}

function addItemToPo(data) {
    if (selectedIds.includes(data.id)) {
        toastr.warning('Already added');
        return;
    }

    selectedIds.push(data.id);
    $('#emptyPlaceholder').hide();
    
    let template = $('#itemRowTemplate').html();
    template = template.replace(/{id}/g, data.id)
                       .replace(/{idx}/g, itemCount)
                       .replace(/{design_number}/g, data.design)
                       .replace(/{sku}/g, data.sku)
                       .replace(/{color}/g, data.color)
                       .replace(/{size}/g, data.size)
                       .replace(/{remain_qty}/g, data.remain);

    $('#selectedItemsTable tbody').append(template);
    
    // Initialize Select2 for NEW elements
    $(`tr[data-id="${data.id}"] .select2`).select2({ width: '100%' });
    
    itemCount++;
    updateSubmitButton();
    loadSets(); // Refresh available list
}

function updateSubmitButton() {
    const hasItems = $('.item-row').length > 0;
    $('#submitBtn').prop('disabled', !hasItems);
}
</script>
@endsection
