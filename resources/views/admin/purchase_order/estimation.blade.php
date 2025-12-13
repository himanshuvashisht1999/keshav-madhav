@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 text-center">
                    <h1>Create Purchase Order For Fabric</h1>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default p-3">
                <div class="card-header mb-2" style="background: blue;">
                    <h3 class="" style="color:white;text-align:center !important;font-size: 1.1rem;font-weight: 600;margin: 0;">Estimation Of Fabric</h3>
                </div>
                <form action="{{ route('admin.purchase_order.create') }}" method="get" enctype="multipart/form-data">

                <input type="hidden" name="vendor_id" id="input-vendor-id" value="">
                <input type="hidden" name="fabric_id" id="input-fabric-id" value="">
                <input type="hidden" name="total_meter" id="input-total-meter" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Purchase Fabric for Design</label>
                            <select id="product-select" name="product_id" class="form-control select2" style="width:100%;" onChange="showDiv()">
                                <option value="">Select</option>
                                @foreach($products as $single_data)
                                    <option value="{{ $single_data['product_id'] }}"
                                        data-product_image="{{ $single_data['product_image'] ?? '' }}"
                                        data-fabric_image="{{ $single_data['fabric_image'] ?? '' }}"
                                        data-fabric_meter="{{ $single_data['fabric_meter'] ?? 1 }}"
                                        data-vendor_id="{{ $single_data['vendor_id'] ?? '' }}"
                                        data-fabric_id="{{ $single_data['fabric_id'] ?? '' }}">
                                        {{ $single_data['design_number'] }} ({{ $single_data['name_of_garment'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" min="1"  id="quantity" class="form-control" value="1">
                            @if ($errors->has('quantity'))
                                <span class="invalid-feedback d-block">{{ $errors->first('quantity') }}</span>
                            @endif
                        </div>

                        <div class="col-md-6 mt-2">
                            <label for="quantity" class="form-label">Main Product</label>
                            <img id="product-image" src="{{asset('images/image-placeholder.png')}}" alt="Product" style="max-width:150px; max-height:150px; display:block;">

                        </div>
                        <div class="col-md-6 mt-2">
                            <label for="fabric-image" class="form-label">Main Fabric</label>
                            <img id="fabric-image" src="{{asset('images/image-placeholder.png')}}" alt="fabric" style="max-width 150px; max-height:150px; display:block;">

                        </div>
                        <div class="col-md-12">
                            <div id="estimated-box" style="margin-top:15px; padding:12px; border:1px solid #ccc; border-radius:6px;display:none;">
                                <h5 style="font-weight:600; margin-bottom:8px;">Estimated Fabric</h5>

                                <div style="display:flex; justify-content:space-between; gap:10px; align-items:center;">
                                    <div>
                                        <div style="font-size:13px; color:#000000;">Total quantity</div>
                                        <div id="est-total-qty" style="font-weight:700; font-size:16px;">0</div>
                                    </div>

                                    <div>
                                        <div style="font-size:13px; color:#000000;">Fabric per piece</div>
                                        <div id="est-fabric-per" style="font-weight:700; font-size:16px;">0 m</div>
                                    </div>

                                    <div>
                                        <div style="font-size:13px; color:#000000;">Estimated Fabric Qty</div>
                                        <div id="est-total-fabric" style="font-weight:700; font-size:16px; color:#1f6feb;">0 m</div>
                                    </div>
                                </div>

                                <div id="est-extra" style="margin-top:8px; font-size:12px; color:#000000;"></div>
                            </div>

                        </div>

                        
                        <!-- Submit -->
                        <div class="col-12 text-end mt-3">
                            <button type="submit" style="float:right;" class="btn btn-primary">Order Fabric</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>


<script>
(function(){
    function formatNumber(n){
        return (Math.round(n) === n) ? String(n) : Number(n).toFixed(2);
    }

    function updateEstimationUI(qty, fabricMeter){
        var totalFabric = fabricMeter * qty;
        var qtyText = formatNumber(qty);
        var perText = formatNumber(fabricMeter);
        var totalText = formatNumber(totalFabric);

        if (typeof window.$ === 'function' && $('#est-total-qty').length) {
            $('#est-total-qty').text(qtyText);
            $('#est-fabric-per').text(perText + ' m');
            $('#est-total-fabric').text(totalText + ' m');
            $('#est-extra').text(qtyText + ' × ' + perText + ' m = ' + totalText + ' m');
        } else {
            var elQty = document.getElementById('est-total-qty');
            var elPer = document.getElementById('est-fabric-per');
            var elTot = document.getElementById('est-total-fabric');
            var elExtra = document.getElementById('est-extra');
            if (elQty) elQty.textContent = qtyText;
            if (elPer) elPer.textContent = perText + ' m';
            if (elTot) elTot.textContent = totalText + ' m';
            if (elExtra) elExtra.textContent = qtyText + ' × ' + perText + ' m = ' + totalText + ' m';
        }

        // set hidden input for total meters (so backend receives it)
        var totalMeterInput = document.getElementById('input-total-meter');
        if (totalMeterInput) totalMeterInput.value = totalFabric;
    }

    function loadSelectedProduct(e){
        
        // prefer jQuery path
        if (typeof window.$ === 'function' && $('#product-select').length) {
            var $selected = $('#product-select').find('option:selected');

            var productImg   = $selected.attr('data-product_image') || '';
            var fabricImg    = $selected.attr('data-fabric_image')  || '';
            var fabricMeter  = parseFloat($selected.attr('data-fabric_meter')) || 1;
            var vendorId     = $selected.attr('data-vendor_id') || '';
            var fabricId     = $selected.attr('data-fabric_id') || '';
            var qty          = parseFloat($('#quantity').val()) || 0;

            $('#product-image').attr('src', productImg || '{{ asset("images/image-placeholder.png") }}');
            $('#fabric-image').attr('src', fabricImg || '{{ asset("images/image-placeholder.png") }}');

            // set hidden inputs
            $('#input-vendor-id').val(vendorId);
            $('#input-fabric-id').val(fabricId);

            updateEstimationUI(qty, fabricMeter);
            return;
        }

        // vanilla fallback
        var sel = document.getElementById('product-select');
        var qtyEl = document.getElementById('quantity');
        if (!sel) return;

        var option = sel.options[sel.selectedIndex];
        if (!option) return;

        var productImg  = option.getAttribute('data-product_image') || '';
        var fabricImg   = option.getAttribute('data-fabric_image') || '';
        var fabricMeter = parseFloat(option.getAttribute('data-fabric_meter')) || 1;
        var vendorId    = option.getAttribute('data-vendor_id') || '';
        var fabricId    = option.getAttribute('data-fabric_id') || '';
        var qty         = qtyEl ? (parseFloat(qtyEl.value) || 0) : 0;

        var prodImgEl = document.getElementById('product-image');
        var fabImgEl  = document.getElementById('fabric-image');

        if (prodImgEl) prodImgEl.src = productImg || '{{ asset("images/image-placeholder.png") }}';
        if (fabImgEl)  fabImgEl.src  = fabricImg  || '{{ asset("images/image-placeholder.png") }}';

        // set hidden inputs (vanilla)
        var hidVendor = document.getElementById('input-vendor-id');
        var hidFabric = document.getElementById('input-fabric-id');
        if (hidVendor) hidVendor.value = vendorId;
        if (hidFabric) hidFabric.value = fabricId;

        updateEstimationUI(qty, fabricMeter);
    }

    // DOM ready and bindings (keep as in your existing code)
    function onReady(fn){ if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn); else fn(); }

    function bootstrap(){
        if (typeof window.$ === 'function' && typeof window.$.fn === 'object') {
            try { $('#product-select').select2 && $('#product-select').select2({ width: 'resolve' }); } catch(e){}
            $('#product-select').on('change', loadSelectedProduct);
            $('#quantity').on('input change', loadSelectedProduct);
            loadSelectedProduct();
        } else {
            var sel = document.getElementById('product-select');
            var qty = document.getElementById('quantity');
            if (sel) sel.addEventListener('change', loadSelectedProduct);
            if (qty) qty.addEventListener('input', loadSelectedProduct);
            loadSelectedProduct();
        }
    }

    onReady(bootstrap);
})();

function showDiv(){
    $('#estimated-box').show();
}
</script>


@endsection
