@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Packaging</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Packaging</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Create Packaging</h3>
                </div>
                <form action="{{route('admin.warehouse.packagingStore')}}" method="post">
                    @csrf
                    <input type="hidden" name="order_id" value="{{$order_data->id}}">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Warehouse</label>
                                    <select name="warehouse_id" id="warehouse_id" class="form-control select2" style="width: 100%;" required>
                                        <option value="">Select warehouse</option>
                                        @foreach($warehouses as $warehouse)
                                        <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('warehouse_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('warehouse_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Rack</label>
                                    <select name="master_warehouse_block_id"
                                            id="master_warehouse_block_id"
                                            class="form-control select2"
                                            style="width: 100%;"
                                            required>
                                        <option value="">Select rack</option>
                                        {{-- Options will be filled by JS --}}
                                    </select>
                                    @if ($errors->has('master_warehouse_block_id'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('master_warehouse_block_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Quantity (Per Box)</label>
                                    <input type="number" name="quantity" id="quantity" class="form-control" placeholder="Enter quantity" value="{{old('quantity')}}">
                                    @if ($errors->has('quantity'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('quantity') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- <div class="col-md-12">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Description</label>
                                    <textarea name="description" id="" class="form-control" ></textarea>
                                    @if ($errors->has('description'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('description') }}
                                        </span>
                                    @endif
                                </div>
                            </div> -->
                            {{-- Dynamic product sku selects will be appended here --}}
                            <div class="col-md-12">
                                <div class="row" id="product_sku_container"></div>
                            </div>

                            
                            <div class="col-md-12">
                                <div class="" style="float:right">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
            
        </div>
    </section>
</div>

<script>
    $(document).ready(function () {
        $('.select2').select2();

        // ====== WAREHOUSE → RACK (your existing code) ======
        $('#warehouse_id').on('change', function () {
            let warehouseId = $(this).val();
            let $blockSelect = $('#master_warehouse_block_id');

            $blockSelect.empty().append('<option value="">Select rack</option>');

            if (!warehouseId) {
                $blockSelect.trigger('change');
                return;
            }

            $.ajax({
                url: "{{ route('admin.warehouse.getBlocks', ':id') }}".replace(':id', warehouseId),
                type: 'GET',
                dataType: 'json',
                success: function (blocks) {
                    $.each(blocks, function (index, block) {
                        $blockSelect.append(
                            $('<option>', {
                                value: block.id,
                                text: block.name
                            })
                        );
                    });

                    let oldBlockId = "{{ old('master_warehouse_block_id') }}";
                    if (oldBlockId) {
                        $blockSelect.val(oldBlockId);
                    }

                    $blockSelect.trigger('change');
                },
                error: function () {
                    alert('Unable to load racks for this warehouse.');
                }
            });
        });

        let initialWarehouse = "{{ old('warehouse_id') }}";
        if (initialWarehouse) {
            $('#warehouse_id').trigger('change');
        }

        // ====== QUANTITY → PRODUCT SKU SELECTS ======

        // Order products from backend (each has id, sku, etc.)
        const orderProducts = @json($order_data->order_products);
        const oldProductSkus = @json(old('product_skus', [])); // to restore old input after validation

        function renderProductSkuSelects(count) {
            const $container = $('#product_sku_container');
            $container.empty();

            if (!count || count <= 0 || !orderProducts.length) {
                return;
            }

            for (let i = 0; i < count; i++) {
                const selectId = 'product_sku_' + i;

                // Create column + form-group + select
                let html = `
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Product SKU ${i + 1}</label>
                            <select name="product_skus[]" id="${selectId}"
                                    class="form-control select2 product-sku-select" required>
                            </select>
                        </div>
                    </div>
                `;

                $container.append(html);

                let $select = $('#' + selectId);

                // Add options (all products)
                orderProducts.forEach(function (product) {
                    $select.append(
                        $('<option>', {
                            value: product.id, // or product.sku if you want sku as the value
                            text: product.product_sku
                        })
                    );
                });

                // Auto select value
                if (oldProductSkus[i]) {
                    // if coming back from validation with old input
                    $select.val(oldProductSkus[i]);
                } else {
                    // cyclic unique selection: 0..N-1 then repeat
                    const autoIndex = i % orderProducts.length;
                    $select.val(orderProducts[autoIndex].id);
                }
            }

            // Re-init select2 for newly added selects
            $('.product-sku-select').select2();
        }

        // When quantity changes
        $('#quantity').on('input change', function () {
            const qty = parseInt($(this).val());

            if (isNaN(qty) || qty <= 0) {
                $('#product_sku_container').empty();
                return;
            }

            renderProductSkuSelects(qty);
        });

        // If there was old quantity (after validation error), rebuild selects on page load
        const initialQty = parseInt("{{ old('quantity') }}");
        if (!isNaN(initialQty) && initialQty > 0) {
            renderProductSkuSelects(initialQty);
        }
    });
</script>

@endsection
