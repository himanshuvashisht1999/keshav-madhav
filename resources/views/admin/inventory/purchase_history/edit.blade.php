@extends('admin.layouts.app')

@section('content')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --bg-main: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        .content-wrapper { background-color: var(--bg-main); font-family: 'Inter', sans-serif; }
        .premium-page-header { padding: 1rem 0; background: #fff; border-bottom: 1px solid #e2e8f0; margin-bottom: 1rem; }
        .page-title { font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0; }
        .card-premium { border: none; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); overflow: hidden; background: #fff; }
        .label-premium { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; display: block; }
        .form-control-premium { border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-weight: 500; transition: all 0.2s; }
        .form-control-premium:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
        .bg-soft-primary { background-color: #f5f7ff; }
        .table-premium thead th { background: #f8fafc; text-transform: uppercase; font-size: 0.7rem; font-weight: 700; color: var(--text-muted); border: none; padding: 0.75rem 1rem; }
        .table-premium td { padding: 0.75rem 1rem; font-size: 0.875rem; border-color: #f1f5f9; vertical-align: middle; }
        .btn-confirm { background: var(--primary-gradient); color: white; border: none; border-radius: 0.5rem; padding: 0.75rem 2rem; font-weight: 700; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3); }
        .btn-confirm:hover { transform: translateY(-1px); box-shadow: 0 6px 15px rgba(79, 70, 229, 0.4); color: white; }
        .badge-soft-info { background: #eff6ff; color: #2563eb; }
        .btn-soft-danger { background: #fef2f2; color: #ef4444; border: none; border-radius: 0.4rem; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; }
        .btn-soft-danger:hover { background: #fee2e2; color: #dc2626; }
        .sticky-summary { position: sticky; bottom: 0; background: #fff; border-top: 2px solid #e2e8f0; padding: 1rem 0; z-index: 100; box-shadow: 0 -10px 15px -3px rgb(0 0 0 / 0.05); }
    </style>

    <div class="content-wrapper">
        <div class="premium-page-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">Edit Purchase Order</h1>
                        <p class="text-muted mb-0 small">Update items and financial details for PO #{{ $purchase->id }}</p>
                    </div>
                    <a href="{{ route('admin.inventory.purchase_history.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm" style="border-radius: 0.5rem; font-weight: 600;">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <form action="{{ route('admin.inventory.purchase_history.update', $purchase->id) }}" method="POST" id="editStockForm">
                    @csrf
                    
                    <!-- Source Selection -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="label-premium">Source Type</label>
                            <select name="source_type" id="sourceType" class="form-control select2">
                                <option value="vendor" {{ $purchase->vendor_id ? 'selected' : '' }}>Vendor</option>
                                <option value="customer" {{ $purchase->customer_id ? 'selected' : '' }}>Customer</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="label-premium text-primary">Load from Production PO</label>
                            <select name="production_po_id" id="loadProductionPO" class="form-control select2">
                                <option value="">Select PO (Optional)</option>
                                @foreach($productionPOs as $po)
                                    <option value="{{ $po->id }}" {{ $purchase->production_po_id == $po->id ? 'selected' : '' }}>{{ $po->po_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3" id="vendorContainer">
                            <label class="label-premium">Select Vendor</label>
                            <select name="vendor_id" id="vendorSelect" class="form-control select2">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ $purchase->vendor_id == $vendor->id ? 'selected' : '' }}>{{ $vendor->company_name ?? $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3" id="customerContainer" style="display: none;">
                            <label class="label-premium">Select Customer</label>
                            <select name="customer_id" id="customerSelect" class="form-control select2">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $purchase->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->company_name ?? $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- PO Reference Panel (Initially Hidden) -->
                    <div id="poReferenceContainer" class="card card-premium mb-3 animate-in" style="display: none; border-left: 4px solid #6366f1;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-list-ul mr-2"></i>PO Items Reference</h6>
                                <span class="badge badge-primary px-3" id="poRefNumber">PO #000</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" style="font-size: 0.85rem;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Design No</th>
                                            <th>Pattern / Fitting</th>
                                            <th>Size / Color</th>
                                            <th>PO Quantity</th>
                                            <th>PO Rate</th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="poRefBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Add Header Section -->
                    <div class="card shadow-sm border-0 mb-3 bg-soft-primary animate-in">
                        <div class="card-body p-3">
                            <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-cart-plus mr-2"></i>Add More Items</h6>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-2">
                                    <label class="label-premium text-primary">Warehouse *</label>
                                    <select id="headerWarehouse" class="form-control select2">
                                        <option value="">Warehouse</option>
                                        @foreach($storerooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="label-premium text-primary">Rack *</label>
                                    <select id="headerRack" class="form-control select2">
                                        <option value="">Rack</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="label-premium text-primary">Design No *</label>
                                    <select id="headerDesign" class="form-control select2">
                                        <option value="">Select Design</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-name="{{ $product->name_of_garment }}">{{ $product->design_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="label-premium text-primary">Size Set *</label>
                                    <select id="headerSizeSet" class="form-control select2">
                                        <option value="">Size Set</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="label-premium text-primary">Color *</label>
                                    <select id="headerColor" class="form-control select2">
                                        <option value="">Color</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="label-premium">Boxes *</label>
                                    <input type="number" id="headerTotalBoxes" class="form-control form-control-premium" placeholder="Qty">
                                </div>
                                <div class="col-md-1 text-right">
                                    <button type="button" id="btnAddToList" class="btn btn-primary btn-block" style="height: 38px; border-radius: 0.5rem;"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <!-- Hidden Fields for Pattern/Fitting/MRP/Pcs -->
                            <input type="hidden" id="headerPattern"><input type="hidden" id="headerPatternDisplay">
                            <input type="hidden" id="headerFitting"><input type="hidden" id="headerFittingDisplay">
                            <input type="hidden" id="headerPcsPerBox"><input type="hidden" id="headerMRP">
                            <input type="hidden" id="headerPurchaseRate" value="0">
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="card card-premium mb-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-premium mb-0">
                                    <thead>
                                        <tr>
                                            <th class="pl-4">Product Details</th>
                                            <th>Style Details</th>
                                            <th>Location</th>
                                            <th>Variation</th>
                                            <th width="100">Boxes</th>
                                            <th width="100">Pcs/Box</th>
                                            <th>Rate</th>
                                            <th class="text-right pr-4">Total</th>
                                            <th class="text-right pr-4">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsContainer">
                                        @foreach($purchase->items as $idx => $item)
                                        <tr data-index="{{ $idx }}">
                                            <td class="pl-4">
                                                <span class="font-weight-bold text-dark">{{ $item->newProduct->name_of_garment }}</span>
                                                <div class="small text-muted">#{{ $item->newProduct->design_number }}</div>
                                                <input type="hidden" name="products[{{ $idx }}][product_id]" value="{{ $item->new_product_id }}">
                                            </td>
                                            <td>
                                                <div class="small text-muted">{{ $item->newPattern->name ?? 'N/A' }} / {{ $item->newFitting->name ?? 'N/A' }}</div>
                                                <input type="hidden" name="products[{{ $idx }}][pattern_id]" value="{{ $item->new_pattern_id }}">
                                                <input type="hidden" name="products[{{ $idx }}][fitting_id]" value="{{ $item->new_fitting_id }}">
                                            </td>
                                            <td>
                                                <div class="small text-muted">{{ $item->newWarehouse->name ?? 'N/A' }} / {{ $item->newRack->name ?? 'N/A' }}</div>
                                                <input type="hidden" name="products[{{ $idx }}][warehouse_id]" value="{{ $item->new_warehouse_id }}">
                                                <input type="hidden" name="products[{{ $idx }}][rack_id]" value="{{ $item->new_rack_id }}">
                                            </td>
                                            <td>
                                                <div class="badge badge-soft-info">{{ $item->newSizeSet->name ?? 'N/A' }}</div>
                                                <div class="small text-muted">{{ $item->newColor->name ?? 'N/A' }}</div>
                                                <input type="hidden" name="products[{{ $idx }}][size_set_id]" value="{{ $item->new_size_set_id }}">
                                                <input type="hidden" name="products[{{ $idx }}][color_id]" value="{{ $item->new_color_id }}">
                                            </td>
                                            <td>
                                                <input type="number" name="products[{{ $idx }}][total_boxes]" value="{{ $item->box_quantity }}" class="form-control form-control-sm row-boxes" style="width: 70px;">
                                            </td>
                                            <td>
                                                <input type="number" name="products[{{ $idx }}][pieces_per_box]" value="{{ $item->pieces_per_box }}" class="form-control form-control-sm row-pcs" style="width: 70px;">
                                            </td>
                                            <td>
                                                <input type="number" name="products[{{ $idx }}][purchase_rate]" value="{{ $item->purchase_rate }}" class="form-control form-control-sm row-rate" step="0.01" style="width: 90px;">
                                            </td>
                                            <td class="text-right pr-4">
                                                <span class="text-primary font-weight-bold row-total">₹{{ number_format($item->box_quantity * $item->pieces_per_box * $item->purchase_rate, 2) }}</span>
                                            </td>
                                            <td class="text-right pr-4">
                                                <button type="button" class="btn btn-soft-danger btn-sm btn-remove-row"><i class="fas fa-times"></i></button>
                                                <input type="hidden" name="products[{{ $idx }}][mrp]" value="{{ $item->mrp }}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Sticky Footer -->
                    <div class="sticky-summary">
                        <div class="container-fluid">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <label class="label-premium">Sub Total</label>
                                    <input type="number" name="sub_total" id="global_sub_total" class="form-control form-control-premium bg-light" value="{{ $purchase->sub_total }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="label-premium">GST Adjust</label>
                                    <div class="input-group">
                                        <input type="number" name="gst_value" id="global_gst_value" class="form-control form-control-premium" value="{{ $purchase->gst_value }}" step="0.01">
                                        <div class="input-group-append">
                                            <select name="gst_type" id="global_gst_type" class="custom-select bg-light" style="border-radius: 0 0.5rem 0.5rem 0;">
                                                <option value="percentage" {{ $purchase->gst_type == 'percentage' ? 'selected' : '' }}>%</option>
                                                <option value="amount" {{ $purchase->gst_type == 'amount' ? 'selected' : '' }}>₹</option>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" name="gst" id="global_gst_amount" value="{{ $purchase->gst }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="label-premium">Other Amount (+)</label>
                                    <input type="number" name="other_amount" id="global_other_amount" class="form-control form-control-premium" value="{{ $purchase->other_amount }}" step="0.01">
                                </div>
                                <div class="col-md-2">
                                    <label class="label-premium">Discount (-)</label>
                                    <input type="number" name="discount" id="global_discount" class="form-control form-control-premium text-danger" value="{{ $purchase->discount }}" step="0.01">
                                </div>
                                <div class="col-md-2">
                                    <label class="label-premium text-primary font-weight-bold">Grand Total</label>
                                    <input type="number" name="total_amount" id="global_total_amount" class="form-control form-control-premium bg-soft-primary font-weight-bold text-primary" value="{{ $purchase->total_amount }}" readonly>
                                </div>
                                <div class="col-md-2 text-right">
                                    <button type="submit" class="btn btn-confirm">Update Purchase</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

@push('scripts')
<script>
    $(function () {
        let itemCount = {{ count($purchase->items) }};
        let currentVariants = [];
        const originalDesignHtml = $('#headerDesign').html();
        let currentPOItems = [];

        function initSelect2(container) {
            container.find('.select2').each(function () {
                $(this).select2({ theme: 'bootstrap4', width: '100%' });
            });
        }
        initSelect2($('.content-wrapper'));
        $('#loadProductionPO').trigger('change');

        $('#sourceType').on('change', function () {
            let type = $(this).val();
            if (type === 'vendor') { $('#vendorContainer').show(); $('#customerContainer').hide(); }
            else { $('#vendorContainer').hide(); $('#customerContainer').show(); }
        }).trigger('change');

        $('#headerWarehouse').on('change', function () {
            let warehouseId = $(this).val();
            let rackSelect = $('#headerRack');
            rackSelect.empty().append('<option value="">Rack</option>');
            if (warehouseId) {
                $.get("{{ url('admin/inventory/warehouse-stock/racks') }}/" + warehouseId, function (data) {
                    data.forEach(rack => rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`));
                    rackSelect.trigger('change.select2');
                });
            }
        });

        $('#headerDesign').on('change', function () {
            let productId = $(this).val();
            let sizeSelect = $('#headerSizeSet');
            let colorSelect = $('#headerColor');
            $('#headerPatternDisplay, #headerPattern, #headerFittingDisplay, #headerFitting, #headerPcsPerBox, #headerMRP').val('');
            sizeSelect.empty().append('<option value="">Size Set</option>').trigger('change.select2');
            colorSelect.empty().append('<option value="">Color</option>').trigger('change.select2');
            currentVariants = [];

            if (productId) {
                $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function (data) {
                    if (data.success) {
                        $('#headerPatternDisplay').val(data.pattern_name); $('#headerPattern').val(data.pattern_id);
                        $('#headerFittingDisplay').val(data.fitting_name); $('#headerFitting').val(data.fitting_id);
                        currentVariants = data.variants;
                        data.variants.forEach(v => sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`));
                        sizeSelect.trigger('change.select2');
                    }
                });
            }
        });

        $('#headerSizeSet').on('change', function () {
            let sizeSetId = $(this).val();
            let colorSelect = $('#headerColor');
            colorSelect.empty().append('<option value="">Color</option>').trigger('change.select2');
            $('#headerPcsPerBox, #headerMRP').val('');

            if (sizeSetId) {
                $.get("{{ url('admin/inventory/get-size-set-info') }}/" + sizeSetId, function (data) {
                    $('#headerPcsPerBox').val(data.no_of_pcs);
                });
                let variant = currentVariants.find(v => v.size_set_id == sizeSetId);
                if (variant) {
                    $('#headerMRP').val(variant.mrp);
                    variant.colors.forEach(c => colorSelect.append(`<option value="${c.id}">${c.name}</option>`));
                    colorSelect.trigger('change.select2');
                }
            }
        });

        $('#loadProductionPO').on('change', function() {
            const poId = $(this).val();
            if (!poId) { $('#poReferenceContainer').hide(); currentPOItems = []; return; }
            Swal.fire({ title: 'Loading PO Data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            $.get(`{{ url('admin/inventory/get-po-items') }}/${poId}`, function(response) {
                Swal.close();
                if (response.success) {
                    currentPOItems = response.items;
                    if (response.vendor_id) { $('#sourceType').val('vendor').trigger('change'); $('#vendorSelect').val(response.vendor_id).trigger('change'); }
                    else if (response.customer_id) { $('#sourceType').val('customer').trigger('change'); $('#customerSelect').val(response.customer_id).trigger('change'); }
                    let refHtml = '';
                    response.items.forEach(item => {
                        refHtml += `<tr><td>${item.design_number}</td><td>${item.pattern_name}/${item.fitting_name}</td><td>${item.size_set_name}/${item.color_name}</td><td>${item.total_boxes} Box</td><td>₹${item.purchase_rate}</td><td class="text-right"><button type="button" class="btn btn-primary btn-xs btn-apply-po-item" data-product="${item.product_id}" data-size="${item.size_set_id}" data-color="${item.color_id}" data-boxes="${item.total_boxes}" data-rate="${item.purchase_rate}">Select</button></td></tr>`;
                    });
                    $('#poRefNumber').text($('#loadProductionPO option:selected').text());
                    $('#poRefBody').html(refHtml);
                    $('#poReferenceContainer').fadeIn();
                }
            });
        });

        $(document).on('click', '.btn-apply-po-item', function() {
            const d = $(this).data();
            $('#headerDesign').val(d.product).trigger('change');
            setTimeout(() => {
                $('#headerSizeSet').val(d.size).trigger('change');
                setTimeout(() => {
                    $('#headerColor').val(d.color).trigger('change.select2');
                    $('#headerTotalBoxes').val(d.boxes);
                    $('#headerPurchaseRate').val(d.rate);
                }, 500);
            }, 800);
        });

        $('#btnAddToList').on('click', function () {
            const data = {
                warehouse_id: $('#headerWarehouse').val(), warehouse_name: $('#headerWarehouse option:selected').text(),
                rack_id: $('#headerRack').val(), rack_name: $('#headerRack option:selected').text(),
                product_id: $('#headerDesign').val(), product_name: $('#headerDesign option:selected').text(),
                design_number: $('#headerDesign option:selected').text().split('(')[0].trim(),
                pattern_id: $('#headerPattern').val(), pattern_name: $('#headerPatternDisplay').val(),
                fitting_id: $('#headerFitting').val(), fitting_name: $('#headerFittingDisplay').val(),
                size_set_id: $('#headerSizeSet').val(), size_set_name: $('#headerSizeSet option:selected').text(),
                color_id: $('#headerColor').val(), color_name: $('#headerColor option:selected').text(),
                pieces_per_box: $('#headerPcsPerBox').val(), mrp: $('#headerMRP').val(),
                total_boxes: $('#headerTotalBoxes').val(), purchase_rate: $('#headerPurchaseRate').val()
            };
            if (!data.warehouse_id || !data.product_id || !data.size_set_id || !data.color_id || !data.total_boxes) {
                toastr.warning('Fill all fields'); return;
            }
            addToTable(data);
            $('#headerTotalBoxes').val('');
        });

        function addToTable(data) {
            const idx = itemCount++;
            const total = (parseFloat(data.total_boxes) * parseFloat(data.pieces_per_box) * parseFloat(data.purchase_rate)).toFixed(2);
            const rowHtml = `<tr data-index="${idx}"><td class="pl-4 font-weight-bold">${data.product_name}<input type="hidden" name="products[${idx}][product_id]" value="${data.product_id}"></td><td>${data.pattern_name}/${data.fitting_name}<input type="hidden" name="products[${idx}][pattern_id]" value="${data.pattern_id}"><input type="hidden" name="products[${idx}][fitting_id]" value="${data.fitting_id}"></td><td>${data.warehouse_name}/${data.rack_name}<input type="hidden" name="products[${idx}][warehouse_id]" value="${data.warehouse_id}"><input type="hidden" name="products[${idx}][rack_id]" value="${data.rack_id}"></td><td><span class="badge badge-soft-info">${data.size_set_name}</span>/${data.color_name}<input type="hidden" name="products[${idx}][size_set_id]" value="${data.size_set_id}"><input type="hidden" name="products[${idx}][color_id]" value="${data.color_id}"></td><td><input type="number" name="products[${idx}][total_boxes]" value="${data.total_boxes}" class="form-control form-control-sm row-boxes" style="width: 70px;"></td><td><input type="number" name="products[${idx}][pieces_per_box]" value="${data.pieces_per_box}" class="form-control form-control-sm row-pcs" style="width: 70px;"></td><td><input type="number" name="products[${idx}][purchase_rate]" value="${data.purchase_rate}" class="form-control form-control-sm row-rate" style="width: 90px;"></td><td class="text-right pr-4 font-weight-bold row-total">₹${total}</td><td class="text-right pr-4"><button type="button" class="btn btn-soft-danger btn-sm btn-remove-row"><i class="fas fa-times"></i></button><input type="hidden" name="products[${idx}][mrp]" value="${data.mrp}"></td></tr>`;
            $('#itemsContainer').append(rowHtml);
            calculateGlobalTotal();
        }

        $(document).on('click', '.btn-remove-row', function() { $(this).closest('tr').remove(); calculateGlobalTotal(); });
        $(document).on('input', '.row-boxes, .row-pcs, .row-rate', function() {
            const row = $(this).closest('tr');
            const total = (parseFloat(row.find('.row-boxes').val())*parseFloat(row.find('.row-pcs').val())*parseFloat(row.find('.row-rate').val())).toFixed(2);
            row.find('.row-total').text('₹'+total);
            calculateGlobalTotal();
        });

        function calculateGlobalTotal() {
            let sub = 0;
            $('.row-total').each(function() { 
                let val = $(this).text().replace('₹','').replace(/,/g, '').trim();
                sub += parseFloat(val) || 0; 
            });
            $('#global_sub_total').val(sub.toFixed(2));
            let gstVal = parseFloat($('#global_gst_value').val()) || 0;
            let gstAmt = $('#global_gst_type').val() === 'percentage' ? (sub * gstVal / 100) : gstVal;
            $('#global_gst_amount').val(gstAmt.toFixed(2));
            let total = sub + gstAmt + (parseFloat($('#global_other_amount').val()) || 0) - (parseFloat($('#global_discount').val()) || 0);
            $('#global_total_amount').val(total.toFixed(2));
        }

        $('#global_gst_value, #global_gst_type, #global_other_amount, #global_discount').on('input change', calculateGlobalTotal);
        calculateGlobalTotal();
    });
</script>
@endpush
@endsection
