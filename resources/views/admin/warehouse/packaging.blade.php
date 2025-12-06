@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">

    <!-- Page Title / Breadcrumb -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                <div>
                    <h1 class="mb-0">Packaging</h1>
                    <small class="text-muted">Order #{{ $order_data->id }} &mdash; {{ $order_data->sku }}</small>
                </div>
                <ol class="breadcrumb float-sm-right mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Create Packaging</li>
                </ol>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            {{-- ========= COMPACT ORDER SUMMARY STRIP ========= --}}
            <div class="card card-outline card-secondary mb-3">
                <div class="card-body py-2">
                    <div class="row text-sm">
                        <div class="col-md-3 col-6 mb-2">
                            <div class="order-summary-item">
                                <span class="order-summary-label">Order:</span>
                                <span class="order-summary-value">#{{ $order_data->id }}</span>
                            </div>
                            <div class="order-summary-item">
                                <span class="order-summary-label">Order SKU:</span>
                                <span class="order-summary-value">{{ $order_data->sku }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <div class="order-summary-item">
                                <span class="order-summary-label">Customer:</span>
                                <span class="order-summary-value">{{ $order_data->customer->name ?? '-' }}</span>
                            </div>
                            <div class="order-summary-item">
                                <span class="order-summary-label">Order Date:</span>
                                <span class="order-summary-value">{{ optional($order_data->created_at)->format('d M Y') }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            @php
                                $status_label = $order_data->status == 2 ? 'Completed' : 'In Progress';
                                $status_badge = $order_data->status == 2 ? 'badge-success' : 'badge-warning';
                            @endphp
                            <div class="order-summary-item">
                                <span class="order-summary-label">Status:</span>
                                <span class="order-summary-value">
                                    <span class="badge {{ $status_badge }}">{{ $status_label }}</span>
                                </span>
                            </div>
                            <div class="order-summary-item">
                                <span class="order-summary-label">Total Ordered Qty:</span>
                                <span class="order-summary-value">{{ total_ordered_quantity($order_data->id) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <div class="order-summary-item">
                                <span class="order-summary-label">Total Packed Qty:</span>
                                <span class="order-summary-value">{{ total_packed_quantity($order_data->id) }}</span>
                            </div>
                            <div class="order-summary-item">
                                <span class="order-summary-label">Total Remaining Qty:</span>
                                <span class="order-summary-value">
                                    {{ total_ordered_quantity($order_data->id) - total_packed_quantity($order_data->id) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========= MAIN LAYOUT: LEFT = PACKAGING FORM, RIGHT = STOCK TABLE ========= --}}
            <div class="row">
                {{-- LEFT: Packaging Form --}}
                <div class="col-lg-7">
                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title">Create Packaging</h3>
                        </div>
                        <form action="{{ route('admin.warehouse.packagingStore') }}" method="post">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order_data->id }}">
                            <div class="card-body">
                                <div class="row">

                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Warehouse</label>
                                            <select name="warehouse_id" id="warehouse_id" class="form-control select2" style="width: 100%;" required>
                                                <option value="">Select warehouse</option>
                                                @foreach($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
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

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Quantity (Per Box)</label>
                                            <input type="number" name="quantity" id="quantity" class="form-control"
                                                   placeholder="Enter quantity" value="{{ old('quantity') }}">
                                            @if ($errors->has('quantity'))
                                                <span class="invalid-feedback d-block">
                                                    {{ $errors->first('quantity') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- <div class="col-md-8">
                                        <div class="alert alert-info mt-4 mb-0">
                                            <small>
                                                Type the quantity to auto-generate product SKU selections below.
                                                You can change each SKU manually if needed.
                                            </small>
                                        </div>
                                    </div> -->

                                    {{-- Dynamic product sku selects --}}
                                    <div class="col-md-12 mt-3">
                                        <div class="row" id="product_sku_container"></div>
                                    </div>

                                </div>
                            </div>

                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-box-open mr-1"></i> Save Packaging
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- RIGHT: Order Products & Warehouse Stock --}}
                <div class="col-lg-5">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Order Products & Warehouse Stock</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="stock-table-wrapper">
                                <table class="table table-bordered table-striped table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>SKU</th>
                                            <th>Ordered</th>
                                            <th>In Warehouse</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($order_data->order_products as $index => $product)
                                            @php
                                                $warehouseRemaining = $warehouse_quantities[$product->id] ?? 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $product->product_sku }}</td>
                                                <td>{{ $product->quantity }}</td>
                                                <td>{{ $warehouseRemaining }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">No products found for this order.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- <div class="px-3 py-2 text-muted text-xs">
                                <small>Scroll if the list is long.</small>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div> {{-- /row --}}

        </div>
    </section>
</div>

{{-- Small styling tweaks --}}
<style>
    .order-summary-item {
        font-size: 13px;
        margin-bottom: 4px;
    }
    .order-summary-label {
        font-weight: 600;
        color: #6c757d;
        margin-right: 4px;
    }
    .order-summary-value {
        font-weight: 600;
    }
    .stock-table-wrapper {
        max-height: 320px;
        overflow-y: auto;
    }
</style>

<script>
    $(document).ready(function () {
        $('.select2').select2();

        // ====== WAREHOUSE → RACK ======
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

                let html = `
                    <div class="col-md-6">
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
                            value: product.id,
                            text: product.product_sku
                        })
                    );
                });

                // Auto select value
                if (oldProductSkus[i]) {
                    $select.val(oldProductSkus[i]);
                } else {
                    const autoIndex = i % orderProducts.length;
                    $select.val(orderProducts[autoIndex].id);
                }
            }

            $('.product-sku-select').select2();
        }

        $('#quantity').on('input change', function () {
            const qty = parseInt($(this).val());

            if (isNaN(qty) || qty <= 0) {
                $('#product_sku_container').empty();
                return;
            }

            renderProductSkuSelects(qty);
        });

        const initialQty = parseInt("{{ old('quantity') }}");
        if (!isNaN(initialQty) && initialQty > 0) {
            renderProductSkuSelects(initialQty);
        }
    });
</script>
@endsection
