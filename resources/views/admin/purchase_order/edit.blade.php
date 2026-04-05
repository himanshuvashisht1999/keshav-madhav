@extends('admin.layouts.app')
@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 text-center">
                    <h1>Edit Purchase Order For Fabric</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">

                <div class="card-header mb-1" style="background: #007bff;">
                    <h3 style="color:white;text-align:center;font-size:1.1rem;font-weight:600;margin:0;">
                        Purchase Order: {{ $data->sku }}
                    </h3>
                </div>

                <form action="{{ route('admin.purchase_order.update') }}" method="post">
                    @csrf
                    <input type="hidden" name="id" value="{{ $data->id }}">

                    <div class="card-body">
                        <div class="row">

                            {{-- PO Date --}}
                            <div class="col-md-6">
                                <label>Purchase Order Date</label>
                                <input type="text"
                                    id="po_date"
                                    class="form-control"
                                    placeholder="Select PO Date">

                                <input type="hidden"
                                    name="date"
                                    id="po_date_hidden"
                                    value="{{ $data->date }}">
                            </div>

                            {{-- Vendor --}}
                            <div class="col-md-6">
                                <label>Vendor</label>
                                <select name="vendor_id" class="form-control select2">
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}"
                                            {{ $data->vendor_id == $v->id ? 'selected' : '' }}>
                                            {{ $v->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Warehouse --}}
                            <div class="col-md-6 mt-2">
                                <label>Delivery Warehouse</label>
                                <select name="fabric_warehouse_id" class="form-control select2">
                                    @foreach($fabric_warehouses as $w)
                                        <option value="{{ $w->id }}" {{ $data->fabric_warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->cutting_master_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Delivery Date --}}
                            <div class="col-md-6 mt-2">
                                <label>Expected Delivery Date</label>
                                <input type="text"
                                    id="delivery_date"
                                    class="form-control"
                                    placeholder="Select Expected Delivery Date">

                                <input type="hidden"
                                    name="delivery_date"
                                    id="delivery_date_hidden"
                                    value="{{ $data->delivery_date }}">
                            </div>

                            {{-- Company --}}
                            <div class="col-md-6 mt-2">
                                <label>Company</label>
                                <select name="master_company_id" class="form-control select2">
                                    <option value="">Select Company</option>
                                    @foreach($companies as $c)
                                        <option value="{{ $c->id }}" {{ $data->master_company_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Transport --}}
                            <div class="col-md-6 mt-2">
                                <label>Transport</label>
                                <input type="text" name="transport" class="form-control" placeholder="Enter Transport Details" value="{{ $data->transport }}">
                            </div>

                            {{-- Remark --}}
                            <div class="col-md-12 mt-2">
                                <label>Remark</label>
                                <textarea name="remark" class="form-control" rows="2" placeholder="Enter Remark">{{ $data->remark }}</textarea>
                            </div>

                            {{-- Hidden SKU --}}
                            <input type="hidden" name="sku" value="{{ $data->sku }}">

                            {{-- FABRIC SECTION --}}
                            <div class="col-md-12 mt-3">
                                <div class="card-header mb-1" style="background: #007bff;">
                                    <h3 style="color:white;text-align:center;font-size:1.1rem;font-weight:600;margin:0;">
                                        Fabric & Prices
                                    </h3>
                                </div>

                                <div id="fabricRollContainer">
                                    @foreach($data->items as $index => $item)
                                    <div class="row fabric-roll-row mb-2">
                                        <div class="col-md-4">
                                            <select name="fabrics[{{ $index }}][fabric_id]"
                                                    class="form-control fabric-select select2" required>
                                                <option value="">Select Fabric</option>
                                                @foreach($fabrics as $f)
                                                    <option value="{{ $f->id }}" data-sku="{{ $f->sku }}" {{ $item->fabric_id == $f->id ? 'selected' : '' }}>
                                                        {{ $f->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <input type="number" name="fabrics[{{ $index }}][meter]"
                                                   class="form-control meter-input"
                                                   placeholder="Meters" step="any" required value="{{ $item->meter }}">
                                        </div>

                                        <div class="col-md-2">
                                            <input type="number" name="fabrics[{{ $index }}][price]"
                                                   class="form-control meter-input"
                                                   placeholder="Price" step="any" value="{{ $item->price }}">
                                        </div>

                                        <div class="col-md-3">
                                            <input type="number" name="fabrics[{{ $index }}][total_price]"
                                                   class="form-control" readonly value="{{ $item->total_price }}" step="any">
                                        </div>

                                        <input type="hidden" name="fabrics[{{ $index }}][sku]" class="item-sku" value="{{ $item->fabric_sku }}">

                                        <div class="col-md-1">
                                            @if($index == 0)
                                                <button type="button" class="btn btn-success addRow">+</button>
                                            @else
                                                <button type="button" class="btn btn-danger removeRow">-</button>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="col-md-12 mt-3 text-right">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>

                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>

<script>
    let currentVendorFabrics = @json($fabrics);
    let rowIndex = {{ count($data->items) }};
</script>

<script>
    function buildFabricOptions(fabrics) {
        let html = '<option value="">Select Fabric</option>';
        fabrics.forEach(f => {
            html += `<option value="${f.id}" data-sku="${f.sku ?? ''}">${f.name}</option>`;
        });
        return html;
    }

    $(document).ready(function(){
        $('.select2').select2({ width:'100%' });

        $('select[name="vendor_id"]').on('change', function () {
            let vendorId = $(this).val();
            let url = "{{ route('admin.purchase_order.vendor_fabrics', 'VID') }}".replace('VID', vendorId);

            $.getJSON(url, function (data) {
                currentVendorFabrics = data;
                let options = buildFabricOptions(data);

                $('.fabric-select').each(function () {
                    let $s = $(this);
                    if ($s.hasClass('select2-hidden-accessible')) {
                        $s.select2('destroy');
                    }
                    $s.html(options).val('').select2({ width:'100%' });
                });
            });
        });

        $(document).on('click', '.addRow', function () {
            let options = buildFabricOptions(currentVendorFabrics);
            let row = `
                <div class="row fabric-roll-row mb-2">
                    <div class="col-md-4">
                        <select name="fabrics[${rowIndex}][fabric_id]"
                                class="form-control fabric-select select2" required>
                            ${options}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="fabrics[${rowIndex}][meter]"
                               class="form-control meter-input" required placeholder="Meters" step="any">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="fabrics[${rowIndex}][price]"
                               class="form-control meter-input" placeholder="Price" step="any">
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="fabrics[${rowIndex}][total_price]"
                               class="form-control" readonly value="0" step="any">
                    </div>
                    <input type="hidden" name="fabrics[${rowIndex}][sku]" class="item-sku">
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger removeRow">-</button>
                    </div>
                </div>
            `;
            $('#fabricRollContainer').append(row);
            $('#fabricRollContainer .fabric-select').last().select2({ width:'100%' });
            rowIndex++;
        });

        $(document).on('click', '.removeRow', function(){
            $(this).closest('.fabric-roll-row').remove();
        });

        $(document).on('keyup change', '.meter-input, .fabric-select', function(){
            let row = $(this).closest('.fabric-roll-row');
            let meter = parseFloat(row.find('[name*="[meter]"]').val()) || 0;
            let price = parseFloat(row.find('[name*="[price]"]').val()) || 0;
            row.find('[name*="[total_price]"]').val((meter * price).toFixed(2));
        });

        flatpickr("#po_date", {
            altInput: true,
            altFormat: "d M Y",
            dateFormat: "Y-m-d",
            defaultDate: "{{ \Carbon\Carbon::parse($data->date)->format('Y-m-d') }}",
            onChange: function(selectedDates) {
                document.getElementById("po_date_hidden").value =
                    flatpickr.formatDate(selectedDates[0], "Y-m-d");
            }
        });

        flatpickr("#delivery_date", {
            altInput: true,
            altFormat: "d M Y",
            dateFormat: "Y-m-d",
            defaultDate: "{{ \Carbon\Carbon::parse($data->delivery_date)->format('Y-m-d') }}",
            onChange: function(selectedDates) {
                document.getElementById("delivery_date_hidden").value =
                    flatpickr.formatDate(selectedDates[0], "Y-m-d");
            }
        });
    });
</script>

@endsection
