@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1 class="text-center">Edit Bulk Production PO: {{ $po->po_number }}</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <form id="editPoForm">
                @csrf
                <div class="row">
                    <!-- LEFT COLUMN: AVAILABLE SETS (Same as Create) -->
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
                                <h3 class="card-title">PO Header & Items</h3>
                                <div class="card-tools">
                                    <span class="badge badge-info">Order: {{ $po->orderMain->sku ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- HEADER INFO -->
                                <div class="row border-bottom pb-3 mb-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>PO To</label>
                                            <select name="po_type" id="po_type" class="form-control" onchange="togglePoTo(this.value)">
                                                <option value="vendor" {{ $po->vendor_id ? 'selected' : '' }}>Vendor</option>
                                                <option value="customer" {{ $po->customer_id ? 'selected' : '' }}>Customer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="vendor_group" style="{{ $po->vendor_id ? '' : 'display:none;' }}">
                                        <div class="form-group">
                                            <label>Vendor</label>
                                            <select name="vendor_id" class="form-control select2">
                                                <option value="">Select Vendor</option>
                                                @foreach($vendors as $v)
                                                    <option value="{{ $v->id }}" {{ $po->vendor_id == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="customer_group" style="{{ $po->customer_id ? '' : 'display:none;' }}">
                                        <div class="form-group">
                                            <label>Customer</label>
                                            <select name="customer_id" class="form-control select2">
                                                <option value="">Select Customer</option>
                                                @foreach($customers as $c)
                                                    <option value="{{ $c->id }}" {{ $po->customer_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Delivery Date</label>
                                            <input type="date" name="delivery_date" class="form-control" value="{{ $po->delivery_date }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Global Remark</label>
                                            <textarea name="remark" class="form-control" rows="1" placeholder="Common remark for all POs...">{{ $po->remark }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- ITEMS TABLE -->
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
                                                    <strong>{{ $item->productSet->design_number ?? 'N/A' }}</strong><br>
                                                    <small class="text-muted">{{ $item->productSet->sku ?? 'N/A' }}</small><br>
                                                    <span class="badge badge-info">{{ $item->productSet->colors->name ?? 'N/A' }}</span> | 
                                                    <span class="badge badge-secondary">{{ $item->productSet->set_size ?? 'N/A' }}</span>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $itemCount }}][quantity]" class="form-control form-control-sm" value="{{ $item->quantity }}" max="{{ $maxQty }}" min="1">
                                                    <small class="text-muted">Max: {{ $maxQty }}</small>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="items[{{ $itemCount }}][rate]" class="form-control form-control-sm" value="{{ $item->rate }}" placeholder="Rate">
                                                </td>
                                                <td>
                                                    <div class="row">
                                                        <div class="col-12 mb-1">
                                                            <select name="items[{{ $itemCount }}][fabric_ids][]" class="form-control form-control-sm select2" multiple data-placeholder="Fabric">
                                                                @foreach($fabrics as $f)
                                                                    <option value="{{ $f->id }}" {{ in_array($f->id, $selectedFabrics) ? 'selected' : '' }}>{{ $f->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <select name="items[{{ $itemCount }}][pattern_id]" class="form-control form-control-sm select2">
                                                                <option value="">Pattern</option>
                                                                @foreach($patterns as $p)
                                                                    <option value="{{ $p->id }}" {{ $item->master_pattern_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <select name="items[{{ $itemCount }}][fitting_id]" class="form-control form-control-sm select2">
                                                                <option value="">Fitting</option>
                                                                @foreach($fittings as $fit)
                                                                    <option value="{{ $fit->id }}" {{ $item->master_fitting_id == $fit->id ? 'selected' : '' }}>{{ $fit->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12 mt-1">
                                                            <input type="text" name="items[{{ $itemCount }}][belt]" class="form-control form-control-sm" value="{{ $item->belt }}" placeholder="Belt details">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-item">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                            <tr id="emptyPlaceholder" style="{{ count($po->items) > 0 ? 'display:none;' : '' }}">
                                                <td colspan="5" class="text-center p-4 text-muted">
                                                    No sets selected yet. Click on an item from the left to add.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <a href="{{ route('admin.product_order.poList') }}" class="btn btn-default">Cancel</a>
                                <button type="submit" id="submitBtn" class="btn btn-lg btn-success">
                                    Update Purchase Order
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
    <tr class="item-row" data-id="" data-set-id="{id}">
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
        btn.prop('disabled', true).text('Updating...');

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
                    btn.prop('disabled', false).text('Update Purchase Order');
                }
            },
            error: function() {
                toastr.error('Something went wrong');
                btn.prop('disabled', false).text('Update Purchase Order');
            }
        });
    });

    $(document).on('click', '.remove-item', function() {
        const row = $(this).closest('tr');
        const setId = row.data('set-id');
        selectedSetIds = selectedSetIds.filter(sid => sid != setId);
        
        $(`#check-${setId}`).prop('checked', false);
        $(`#available-card-${setId}`).removeClass('bg-light');

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
            $(`#available-card-${data.id}`).addClass('bg-light');
        } else {
            $(`tr[data-set-id="${data.id}"]`).find('.remove-item').click();
            $(`#available-card-${data.id}`).removeClass('bg-light');
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
    list.html('<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');

    const search = $('#setSearch').val();
    const orderId = "{{ $po->order_main_id }}";
    
    $.get("{{ route('admin.product_order.getUnassignedSets') }}", { order_id: orderId, search: search }, function(data) {
        list.empty();
        if (data.length === 0) {
            list.html('<div class="text-center text-muted p-3">No unassigned sets found for this order.</div>');
            return;
        }

        data.forEach(set => {
            const isSelected = selectedSetIds.includes(set.id);
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
