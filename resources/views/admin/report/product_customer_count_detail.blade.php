@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- HEADER BAR --}}
    <div class="erp-header-bar mb-3">
        <div>
            <h1 class="erp-header-title">Design Details: {{ $design_number }}</h1>
            <div class="erp-report-meta">
                Date : {{ now()->format('d M Y h:i A') }}
            </div>
        </div>
        <div class="d-flex align-items-center" style="gap: 12px;">
            <span class="erp-stat-pill">Total Sold: <strong>{{ number_format($totalQuantitySold) }} Pcs</strong></span>
            <a href="{{ route('admin.report.product-customer-count') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to Report
            </a>
        </div>
    </div>

    {{-- FILTERS CARD --}}
    <div class="erp-card mb-3">
        <div class="card-body p-3">
            <form action="{{ route('admin.report.product-customer-count.detail', $design_number) }}" method="GET">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">Order Number (SKU)</label>
                        <input type="text" name="order_number" class="form-control" placeholder="Search SKU..." value="{{ request('order_number') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="erp-form-label">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" placeholder="Search Customer..." value="{{ request('customer_name') }}">
                    </div>
                    <div class="col-md-3 mb-2" id="parent-agents">
                        <label class="erp-form-label">Agent</label>
                        <select class="form-control select2" name="agent_id[]" multiple data-placeholder="Select Agents">
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ (is_array(request('agent_id')) && in_array($agent->id, request('agent_id'))) ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2" id="parent-products">
                        <label class="erp-form-label">Product Name</label>
                        <select class="form-control select2" name="product_name[]" multiple data-placeholder="Select Products">
                            @foreach($products as $product)
                                <option value="{{ $product }}" {{ (is_array(request('product_name')) && in_array($product, request('product_name'))) ? 'selected' : '' }}>
                                    {{ $product }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2" id="parent-sizesets">
                        <label class="erp-form-label">Size Set</label>
                        <select class="form-control select2" name="size_set_name[]" multiple data-placeholder="Select Size Sets">
                            @foreach($sizeSets as $set)
                                <option value="{{ $set }}" {{ (is_array(request('size_set_name')) && in_array($set, request('size_set_name'))) ? 'selected' : '' }}>
                                    {{ $set }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2" id="parent-colors">
                        <label class="erp-form-label">Color</label>
                        <select class="form-control select2" name="color_name[]" multiple data-placeholder="Select Colors">
                            @foreach($colors as $color)
                                <option value="{{ $color }}" {{ (is_array(request('color_name')) && in_array($color, request('color_name'))) ? 'selected' : '' }}>
                                    {{ $color }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 d-flex align-items-center" style="gap: 8px;">
                        <button type="submit" class="btn-erp btn-erp-primary">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.report.product-customer-count.detail', $design_number) }}" class="btn-erp btn-erp-secondary">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="erp-card">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="erp-table mb-0">
                    <thead>
                        <tr>
                            <th>Order Date</th>
                            <th>Order Number (SKU)</th>
                            <th>Customer Name</th>
                            <th>Sales Agent</th>
                            <th>Product Name</th>
                            <th>Color</th>
                            <th>Size Set</th>
                            <th class="text-right">Quantity Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                            <tr>
                                <td>{{ $row->order ? \Carbon\Carbon::parse($row->order->order_date)->format('d-m-Y') : 'N/A' }}</td>
                                <td>
                                    @if($row->order)
                                        <a href="{{ route('admin.agent-orders.show', $row->order->id) }}" target="_blank" class="font-weight-bold">
                                            #ORD-{{ str_pad($row->order->id, 5, '0', STR_PAD_LEFT) }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->order && $row->order->shop)
                                        {{ $row->order->shop->name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->order && $row->order->shop && $row->order->shop->agent)
                                        <span class="badge badge-light border">{{ $row->order->shop->agent->name }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $row->product_name }}</td>
                                <td>{{ $row->color_name }}</td>
                                <td>{{ $row->size_set_name }}</td>
                                <td class="text-right font-weight-bold">
                                    {{ number_format($row->quantity) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No order details found for this design.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($items->hasPages())
        <div class="card-footer bg-white border-top py-2 d-flex justify-content-end">
            {{ $items->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            let configs = [
                { selector: 'select[name="agent_id[]"]', parent: '#parent-agents' },
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
