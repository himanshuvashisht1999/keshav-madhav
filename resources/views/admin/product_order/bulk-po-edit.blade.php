@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="erp-header-title mb-0">
                <i class="fas fa-file-invoice text-warning mr-1"></i> Edit Bulk Production PO: <span class="text-primary">{{ $po->po_number }}</span>
            </h5>
            @if(!empty($po->orderMain->sku))
                <span class="badge badge-info ml-2" style="font-size:11px; font-weight:600;">Order: {{ $po->orderMain->sku }}</span>
            @endif
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.product_order.poList') }}" class="btn btn-xs btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to PO List
            </a>
        </div>
    </div>

    <form id="editPoForm">
        @csrf
        <div class="row">
            <!-- LEFT COLUMN: AVAILABLE SETS -->
            <div class="col-lg-4 col-md-5 mb-2">
                <div class="erp-card h-100">
                    <div class="erp-card-header d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="erp-card-title mb-0 font-weight-bold" style="font-size:13px;">
                            <i class="fas fa-boxes text-muted mr-1"></i> Available Order Sets
                        </span>
                        <span class="text-muted" style="font-size: 11px;">Select to add</span>
                    </div>
                    <div class="card-body p-2">
                        <div class="input-group mb-2">
                            <input type="text" id="setSearch" class="form-control form-control-sm erp-input" placeholder="Search Design / SKU...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-xs btn-erp-primary" onclick="loadSets()" style="border-radius: 0 4px 4px 0;">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div id="availableSetsList" style="max-height: 620px; overflow-y: auto; padding-right: 2px;">
                            <div class="text-center text-muted p-4" style="font-size:12px;">Search or click to load unassigned sets</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: PO DETAILS -->
            <div class="col-lg-8 col-md-7 mb-2">
                <div class="erp-card h-100">
                    <div class="erp-card-header d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="erp-card-title mb-0 font-weight-bold" style="font-size:13px;">
                            <i class="fas fa-file-signature text-muted mr-1"></i> PO Header & Assigned Items
                        </span>
                        @if(!empty($po->orderMain->sku))
                            <span class="badge badge-light border text-secondary" style="font-size: 11px;">
                                Order #{{ $po->orderMain->sku }}
                            </span>
                        @endif
                    </div>
                    <div class="card-body p-3">
                        <!-- HEADER INFO -->
                        <div class="bg-light p-2 rounded mb-3 border">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">PO To <span class="text-danger">*</span></label>
                                    <select name="po_type" id="po_type" class="form-control form-control-sm erp-input" onchange="togglePoTo(this.value)">
                                        <option value="vendor" {{ $po->vendor_id ? 'selected' : '' }}>Vendor</option>
                                        <option value="customer" {{ $po->customer_id ? 'selected' : '' }}>Customer</option>
                                    </select>
                                </div>
                                <div class="col-md-5 mb-2" id="vendor_group" style="{{ $po->vendor_id ? '' : 'display:none;' }}">
                                    <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Vendor <span class="text-danger">*</span></label>
                                    <select name="vendor_id" class="form-control form-control-sm erp-input select2">
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id }}" {{ $po->vendor_id == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5 mb-2" id="customer_group" style="{{ $po->customer_id ? '' : 'display:none;' }}">
                                    <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" class="form-control form-control-sm erp-input select2">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}" {{ $po->customer_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Delivery Date <span class="text-danger">*</span></label>
                                    <input type="date" name="delivery_date" class="form-control form-control-sm erp-input" value="{{ $po->delivery_date }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Global Remark</label>
                                    <textarea name="remark" class="form-control form-control-sm erp-input" rows="1" placeholder="Common remark for this PO...">{{ $po->remark }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- ITEMS TABLE -->
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
                                    @php $itemCount = 0; $selectedSetIds = []; @endphp
                                    @foreach($po->items as $item)
                                    @php 
                                        $itemCount++; 
                                        $selectedSetIds[] = $item->set_product_id;
                                        $selectedFabrics = explode(',', $item->fabric_id);
                                        $maxQty = ($item->productSet->remain_total_quantity ?? 0) + $item->quantity;
                                    @endphp
                                    <tr class="item-row" data-id="{{ $item->id }}" data-set-id="{{ $item->set_product_id }}">
                                        <td>
                                            <input type="hidden" name="items[{{ $itemCount }}][id]" value="{{ $item->id }}">
                                            <input type="hidden" name="items[{{ $itemCount }}][order_product_set_id]" value="{{ $item->set_product_id }}">
                                            <span class="font-weight-bold text-dark d-block" style="font-size: 12px;">{{ $item->productSet->design_number ?? 'N/A' }}</span>
                                            <small class="text-muted d-block" style="font-size: 11px;">{{ $item->productSet->sku ?? 'N/A' }}</small>
                                            <div class="mt-1">
                                                <span class="badge badge-info" style="font-size: 10px;">{{ $item->productSet->colors->name ?? 'N/A' }}</span>
                                                <span class="badge badge-secondary" style="font-size: 10px;">{{ $item->productSet->set_size ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $itemCount }}][quantity]" class="form-control form-control-sm erp-input" value="{{ $item->quantity }}" max="{{ $maxQty }}" min="1">
                                            <small class="text-muted d-block mt-1 font-weight-bold" style="font-size: 10px;">Max: {{ $maxQty }}</small>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[{{ $itemCount }}][rate]" class="form-control form-control-sm erp-input" value="{{ $item->rate }}" placeholder="Rate">
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-12 mb-1">
                                                    <select name="items[{{ $itemCount }}][fabric_ids][]" class="form-control form-control-sm erp-input select2" multiple data-placeholder="Select Fabric">
                                                        @foreach($fabrics as $f)
                                                            <option value="{{ $f->id }}" {{ in_array($f->id, $selectedFabrics) ? 'selected' : '' }}>{{ $f->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-6 pr-1">
                                                    <select name="items[{{ $itemCount }}][pattern_id]" class="form-control form-control-sm erp-input select2">
                                                        <option value="">Pattern</option>
                                                        @foreach($patterns as $p)
                                                            <option value="{{ $p->id }}" {{ $item->master_pattern_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-6 pl-1">
                                                    <select name="items[{{ $itemCount }}][fitting_id]" class="form-control form-control-sm erp-input select2">
                                                        <option value="">Fitting</option>
                                                        @foreach($fittings as $fit)
                                                            <option value="{{ $fit->id }}" {{ $item->master_fitting_id == $fit->id ? 'selected' : '' }}>{{ $fit->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12 mt-1">
                                                    <input type="text" name="items[{{ $itemCount }}][belt]" class="form-control form-control-sm erp-input" value="{{ $item->belt }}" placeholder="Belt details">
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="erp-action-btn erp-btn-delete remove-item" title="Remove">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr id="emptyPlaceholder" style="{{ count($po->items) > 0 ? 'display:none;' : '' }}">
                                        <td colspan="5" class="text-center p-4 text-muted" style="font-size: 12px;">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block text-muted"></i>
                                            No sets selected yet. Check an item from the left panel to add.
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
                        <button type="submit" id="submitBtn" class="btn btn-sm btn-erp-primary px-3">
                            <i class="fas fa-check-circle mr-1"></i> Update Purchase Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ITEM ROW TEMPLATE -->
<script type="text/template" id="itemRowTemplate">
    <tr class="item-row" data-id="" data-set-id="{id}">
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
let itemCount = {{ $itemCount + 1 }};
let selectedSetIds = @json($selectedSetIds);

$(document).ready(function() {
    $('.select2').select2({ width: '100%' });
    loadSets();

    // Add enter key listener for search
    $('#setSearch').on('keyup', function(e) {
        if (e.keyCode === 13) {
            loadSets();
        }
    });

    $('#editPoForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#submitBtn');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Updating...');

        $.ajax({
            url: "{{ route('admin.product_order.updateBulkPO', $po->id) }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(res) {
                if (res.status) {
                    toastr.success(res.message);
                    window.location.href = "{{ route('admin.product_order.poList') }}";
                } else {
                    toastr.error(res.message);
                    btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Update Purchase Order');
                }
            },
            error: function() {
                toastr.error('Something went wrong');
                btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Update Purchase Order');
            }
        });
    });

    $(document).on('click', '.remove-item', function() {
        const row = $(this).closest('tr');
        const setId = row.data('set-id');
        selectedSetIds = selectedSetIds.filter(sid => sid != setId);
        
        $(`#check-${setId}`).prop('checked', false);
        $(`#available-card-${setId}`).removeClass('border-success bg-light');

        row.remove();
        updateSubmitButton();
        if ($('.item-row').length === 0) {
            $('#emptyPlaceholder').show();
        }
    });

    $(document).on('change', '.set-checkbox', function() {
        const data = $(this).data();
        if ($(this).is(':checked')) {
            addItemToPo(data);
            $(`#available-card-${data.id}`).addClass('border-success bg-light');
        } else {
            $(`tr[data-set-id="${data.id}"]`).find('.remove-item').click();
            $(`#available-card-${data.id}`).removeClass('border-success bg-light');
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

function loadSets() {
    const list = $('#availableSetsList');
    list.html('<div class="text-center p-3 text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Loading sets...</div>');

    const search = $('#setSearch').val();
    const orderId = "{{ $po->order_main_id }}";
    
    $.get("{{ route('admin.product_order.getUnassignedSets') }}", { order_id: orderId, search: search }, function(data) {
        list.empty();
        if (data.length === 0) {
            list.html('<div class="text-center text-muted p-3" style="font-size:12px;">No unassigned sets found for this order.</div>');
            return;
        }

        data.forEach(set => {
            const isSelected = selectedSetIds.includes(set.id);
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

function addItemToPo(data) {
    if (selectedSetIds.includes(data.id)) {
        return;
    }

    selectedSetIds.push(data.id);
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
    $(`tr[data-set-id="${data.id}"] .select2`).select2({ width: '100%' });
    
    itemCount++;
    updateSubmitButton();
}

function updateSubmitButton() {
    const hasItems = $('.item-row').length > 0;
    $('#submitBtn').prop('disabled', !hasItems);
}
</script>
@endsection
