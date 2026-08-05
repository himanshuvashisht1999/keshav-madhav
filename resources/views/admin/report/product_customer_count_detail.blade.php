@extends('admin.layouts.app')

@section('content')
<style>
    /* ENTERPRISE ERP DESIGN (SAP / ZOHO STYLE) */
    :root {
        --erp-bg: #f5f6f8;
        --erp-panel-bg: #ffffff;
        --erp-border: #d1d5db;
        --erp-primary: #0f62fe;
        --erp-primary-light: #e0e8ff;
        --erp-text-main: #111827;
        --erp-text-muted: #6b7280;
        --erp-active-bg: #f4f8ff;
        --erp-radius: 4px;
        --font-base: 13px;
    }

    .erp-container {
        padding: 16px;
        background: var(--erp-bg);
        min-height: calc(100vh - 60px);
        font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
        font-size: var(--font-base);
        color: var(--erp-text-main);
    }

    .erp-card {
        background: var(--erp-panel-bg);
        border: 1px solid var(--erp-border);
        border-radius: var(--erp-radius);
        margin-bottom: 16px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .erp-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid var(--erp-border);
        background-color: #fafafa;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .erp-card-body {
        padding: 16px;
    }

    .erp-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .erp-table th, .erp-table td {
        border: 1px solid var(--erp-border);
        padding: 8px 12px;
        text-align: left;
        white-space: nowrap;
    }

    .erp-table th {
        background-color: #f3f4f6;
        color: var(--erp-text-muted);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .erp-table tbody tr:hover {
        background-color: var(--erp-active-bg);
    }
</style>

<div class="content-wrapper">
    <section class="content" style="padding-top: 20px;">
        <div class="container-fluid">
            <div class="erp-container" style="min-height: auto; padding: 0;">
                <div class="erp-card">
                    <div class="erp-card-header">
                        <div>
                            <i class="fas fa-list mr-2"></i> Details for Design: <strong>{{ $design_number }}</strong>
                        </div>
                        <div>
                            <a href="{{ route('admin.report.product-customer-count') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Report
                            </a>
                        </div>
                    </div>
                    <div class="erp-card-body">
                        <form action="{{ route('admin.report.product-customer-count.detail', $design_number) }}" method="GET" class="mb-3">
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                </div>
                                <div class="col-md-4">
                                    <label>Order Number (SKU)</label>
                                    <input type="text" name="order_number" class="form-control" placeholder="Search Order No..." value="{{ request('order_number') }}">
                                </div>
                                <div class="col-md-4">
                                    <label>Customer Name</label>
                                    <input type="text" name="customer_name" class="form-control" placeholder="Search Customer..." value="{{ request('customer_name') }}">
                                </div>
                            </div>
                            <div class="row align-items-end">
                                <div class="col-md-3" id="parent-products">
                                    <label>Product Name</label>
                                    <select class="form-control select2" name="product_name[]" multiple data-placeholder="Select Products">
                                        @foreach($products as $product)
                                            <option value="{{ $product }}" {{ (is_array(request('product_name')) && in_array($product, request('product_name'))) ? 'selected' : '' }}>
                                                {{ $product }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3" id="parent-sizesets">
                                    <label>Size Set</label>
                                    <select class="form-control select2" name="size_set_name[]" multiple data-placeholder="Select Size Sets">
                                        @foreach($sizeSets as $set)
                                            <option value="{{ $set }}" {{ (is_array(request('size_set_name')) && in_array($set, request('size_set_name'))) ? 'selected' : '' }}>
                                                {{ $set }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3" id="parent-colors">
                                    <label>Color</label>
                                    <select class="form-control select2" name="color_name[]" multiple data-placeholder="Select Colors">
                                        @foreach($colors as $color)
                                            <option value="{{ $color }}" {{ (is_array(request('color_name')) && in_array($color, request('color_name'))) ? 'selected' : '' }}>
                                                {{ $color }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 text-right">
                                    <button type="submit" class="btn btn-primary" style="background-color: var(--erp-primary); border-color: var(--erp-primary);">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.report.product-customer-count.detail', $design_number) }}" class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="erp-card">
                    <div class="erp-card-body" style="padding: 0;">
                        <div style="overflow-x: auto; max-height: 600px;">
                            <table class="erp-table">
                                <thead>
                                    <tr>
                                        <th>Order Date</th>
                                        <th>Order No (SKU)</th>
                                        <th>Customer Name</th>
                                        <th>Product Name</th>
                                        <th>Color</th>
                                        <th>Size Set</th>
                                        <th>Quantity Sold</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $row)
                                        <tr>
                                            <td>{{ $row->order ? \Carbon\Carbon::parse($row->order->order_date)->format('d-m-Y') : 'N/A' }}</td>
                                            <td>
                                                @if($row->order)
                                                    <strong>
                                                        <a href="{{ route('admin.agent-orders.show', $row->order->id) }}" target="_blank" style="color: var(--erp-primary); text-decoration: underline;">
                                                            #ORD-{{ str_pad($row->order->id, 5, '0', STR_PAD_LEFT) }}
                                                        </a>
                                                    </strong>
                                                @else
                                                    <strong>N/A</strong>
                                                @endif
                                            </td>
                                            <td>
                                                @if($row->order && $row->order->shop)
                                                    {{ $row->order->shop->name }}
                                                @else
                                                    <span class="text-muted">Unknown Customer</span>
                                                @endif
                                            </td>
                                            <td>{{ $row->product_name }}</td>
                                            <td>{{ $row->color_name }}</td>
                                            <td>{{ $row->size_set_name }}</td>
                                            <td>
                                                <span class="badge badge-info" style="font-size: 12px; background-color: var(--erp-primary-light); color: var(--erp-primary);">
                                                    {{ $row->quantity }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No order details found for this design.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($items->hasPages())
                    <div class="erp-card-body" style="border-top: 1px solid var(--erp-border); background-color: #fafafa;">
                        {{ $items->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            let configs = [
                { selector: 'select[name="product_name[]"]', parent: '#parent-products' },
                { selector: 'select[name="size_set_name[]"]', parent: '#parent-sizesets' },
                { selector: 'select[name="color_name[]"]', parent: '#parent-colors' }
            ];
            
            configs.forEach(function(item) {
                let $el = $(item.selector);
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.select2({
                    dropdownParent: $(item.parent),
                    width: '100%',
                    allowClear: true,
                    theme: 'bootstrap4'
                }).on('select2:open', function() {
                    // Fix focus jump bug
                    setTimeout(function() {
                        let searchField = $(item.parent).find('.select2-search__field');
                        if (searchField.length) {
                            searchField.focus();
                        }
                    }, 50);
                });
            });
        }
    });
</script>
@endpush
@endsection
