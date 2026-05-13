@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1 class="text-center">Bulk Production Purchase Order</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <form id="bulkPoForm">
                @csrf
                <div class="row">
                    <!-- LEFT COLUMN: AVAILABLE SETS -->
                    <div class="col-md-4">
                        <div class="card card-outline card-primary h-100">
                            <div class="card-header">
                                <h3 class="card-title">Available Order Sets</h3>
                            </div>
                            <div class="card-body">
                                <div class="input-group mb-3">
                                    <input type="text" id="setSearch" class="form-control" placeholder="Search Design / SKU...">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" onclick="loadSets()">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="availableSetsList" style="max-height: 600px; overflow-y: auto;">
                                    <div class="text-center text-muted p-5">Search or click to load unassigned sets</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: PO DETAILS -->
                    <div class="col-md-8">
                        <div class="card card-outline card-success h-100">
                            <div class="card-header">
                                <h3 class="card-title">PO Header & Selected Items</h3>
                            </div>
                            <div class="card-body">
                                <!-- HEADER INFO -->
                                <div class="row border-bottom pb-3 mb-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>PO To</label>
                                            <select name="po_type" id="po_type" class="form-control" onchange="togglePoTo(this.value)">
                                                <option value="vendor">Vendor</option>
                                                <option value="customer">Customer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="vendor_group">
                                        <div class="form-group">
                                            <label>Vendor</label>
                                            <select name="vendor_id" class="form-control select2">
                                                <option value="">Select Vendor</option>
                                                @foreach($vendors as $v)
                                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="customer_group" style="display:none;">
                                        <div class="form-group">
                                            <label>Customer</label>
                                            <select name="customer_id" class="form-control select2">
                                                <option value="">Select Customer</option>
                                                @foreach($customers as $c)
                                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Delivery Date</label>
                                            <input type="date" name="delivery_date" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Global Remark</label>
                                            <textarea name="remark" class="form-control" rows="1" placeholder="Common remark for all POs..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- SELECTED ITEMS TABLE -->
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" id="selectedItemsTable">
                                        <thead>
                                            <tr class="bg-light">
                                                <th>Item Details</th>
                                                <th style="width: 100px;">Qty</th>
                                                <th style="width: 100px;">Rate</th>
                                                <th>Fabric / Pattern / Fitting</th>
                                                <th style="width: 50px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="emptyPlaceholder">
                                                <td colspan="5" class="text-center p-4 text-muted">
                                                    No sets selected yet. Click on an item from the left to add.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" id="submitBtn" class="btn btn-lg btn-success" disabled>
                                    Create Bulk Purchase Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<!-- ITEM ROW TEMPLATE -->
<script type="text/template" id="itemRowTemplate">
    <tr class="item-row" data-id="{id}">
        <td>
            <input type="hidden" name="items[{idx}][order_product_set_id]" value="{id}">
            <strong>{design_number}</strong><br>
            <small class="text-muted">{sku}</small><br>
            <span class="badge badge-info">{color}</span> | <span class="badge badge-secondary">{size}</span>
        </td>
        <td>
            <input type="number" name="items[{idx}][quantity]" class="form-control form-control-sm" value="{remain_qty}" max="{remain_qty}" min="1">
            <small class="text-muted">Max: {remain_qty}</small>
        </td>
        <td>
            <input type="number" step="0.01" name="items[{idx}][rate]" class="form-control form-control-sm" placeholder="Rate">
        </td>
        <td>
            <div class="row">
                <div class="col-12 mb-1">
                    <select name="items[{idx}][fabric_ids][]" class="form-control form-control-sm select2" multiple data-placeholder="Fabric">
                        @foreach($fabrics as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <select name="items[{idx}][pattern_id]" class="form-control form-control-sm select2">
                        <option value="">Pattern</option>
                        @foreach($patterns as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <select name="items[{idx}][fitting_id]" class="form-control form-control-sm select2">
                        <option value="">Fitting</option>
                        @foreach($fittings as $fit)
                            <option value="{{ $fit->id }}">{{ $fit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mt-1">
                    <input type="text" name="items[{idx}][belt]" class="form-control form-control-sm" placeholder="Belt details">
                </div>
            </div>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-item">
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
        // If coming from a specific order, load its sets directly on the left
        loadSetsByOrder(orderId);
    } else if (setIds || singleSetId) {
        const idsToFetch = setIds ? setIds.split(',') : [singleSetId];
        fetchAndAddSets(idsToFetch);
    }

    $('#bulkPoForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#submitBtn');
        btn.prop('disabled', true).text('Processing...');

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
                    btn.prop('disabled', false).text('Create Bulk Purchase Order');
                }
            },
            error: function() {
                toastr.error('Something went wrong');
                btn.prop('disabled', false).text('Create Bulk Purchase Order');
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
        $(`#available-card-${id}`).removeClass('bg-light');

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
    list.html('<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Loading Sets...</div>');

    const search = $('#setSearch').val();
    $.get("{{ route('admin.product_order.getUnassignedSets') }}", { order_id: orderId, search: search }, function(data) {
        list.empty();
        if (data.length === 0) {
            list.html('<div class="text-center text-muted p-3">No unassigned sets found for this order.</div>');
            return;
        }

        data.forEach(set => {
            const isSelected = selectedIds.includes(set.id);
            list.append(`
                <div class="card card-widget mb-2 border ${isSelected ? 'bg-light' : ''}" id="available-card-${set.id}">
                    <div class="card-body p-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input set-checkbox" id="check-${set.id}" 
                                   ${isSelected ? 'checked' : ''}
                                   data-id="${set.id}"
                                   data-design="${set.design_number}"
                                   data-sku="${set.sku}"
                                   data-color="${set.colors ? set.colors.name : 'N/A'}"
                                   data-size="${set.set_size}"
                                   data-remain="${set.remain_total_quantity}">
                            <label class="custom-control-label d-block cursor-pointer" for="check-${set.id}">
                                <strong>${set.design_number}</strong><br>
                                <small>${set.sku}</small><br>
                                <span class="badge badge-info">${set.colors ? set.colors.name : 'N/A'}</span>
                                <small class="float-right text-muted">Rem: ${set.remain_total_quantity} Pcs</small>
                            </label>
                        </div>
                    </div>
                </div>
            `);
        });
    });
}

function loadOrders() {
    const search = $('#setSearch').val();
    const list = $('#availableSetsList');
    list.html('<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Loading Orders...</div>');

    $.get("{{ route('admin.product_order.getUnassignedOrders') }}", { search: search }, function(data) {
        list.empty();
        if (data.length === 0) {
            list.html('<div class="text-center text-muted p-3">No unassigned orders found.</div>');
            return;
        }

        data.forEach(order => {
            list.append(`
                <div class="card card-widget mb-2 border order-card" data-order-id="${order.id}">
                    <div class="card-header bg-light p-2 cursor-pointer toggle-order" style="cursor:pointer">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>${order.sku}</strong>
                            <i class="fa fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="card-body p-2 order-sets-container" id="order-sets-${order.id}" style="display:none;">
                        <div class="text-center"><i class="fa fa-spinner fa-spin"></i></div>
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
        $(`#available-card-${data.id}`).addClass('bg-light');
    } else {
        $(`tr[data-id="${data.id}"]`).find('.remove-item').click();
        $(`#available-card-${data.id}`).removeClass('bg-light');
    }
});

$(document).on('click', '.toggle-order', function() {
    const card = $(this).closest('.order-card');
    const orderId = card.data('order-id');
    const container = $(`#order-sets-${orderId}`);
    const icon = $(this).find('i');

    if (container.is(':visible')) {
        container.slideUp();
        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
    } else {
        // Load sets for this order if not loaded or refresh
        container.html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i></div>').slideDown();
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');

        $.get("{{ route('admin.product_order.getUnassignedSets') }}", { order_id: orderId }, function(data) {
            container.empty();
            if (data.length === 0) {
                container.html('<small class="text-muted">No unassigned sets</small>');
                return;
            }

            data.forEach(set => {
                const isSelected = selectedIds.includes(set.id);
                container.append(`
                    <div class="border rounded p-2 mb-1 ${isSelected ? 'bg-light' : ''}" id="available-card-${set.id}">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input set-checkbox" id="check-${set.id}" 
                                   ${isSelected ? 'checked' : ''}
                                   data-id="${set.id}"
                                   data-design="${set.design_number}"
                                   data-sku="${set.sku}"
                                   data-color="${set.colors ? set.colors.name : 'N/A'}"
                                   data-size="${set.set_size}"
                                   data-remain="${set.remain_total_quantity}">
                            <label class="custom-control-label d-block cursor-pointer" for="check-${set.id}">
                                <strong>${set.design_number}</strong> (${set.colors ? set.colors.name : 'N/A'})<br>
                                <small>Rem: ${set.remain_total_quantity} Pcs</small>
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
