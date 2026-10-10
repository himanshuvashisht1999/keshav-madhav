@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- HEADER BAR --}}
    <div class="erp-header-bar mb-3">
        <div>
            <h1 class="erp-header-title">Product Popularity (Customer Count)</h1>
        </div>
        <div class="erp-report-meta">
            Date : {{ now()->format('d M Y h:i A') }}
        </div>
    </div>

    {{-- FILTERS CARD --}}
    <div class="erp-card mb-3">
        <div class="card-body p-3">
            <form action="{{ route('admin.report.product-customer-count') }}" method="GET">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="erp-form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="erp-form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
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
                    <div class="col-md-3 mb-2" id="parent-designs">
                        <label class="erp-form-label">Design Number</label>
                        <select class="form-control select2" name="design_number[]" multiple data-placeholder="Select Designs">
                            @foreach($designs as $design)
                                <option value="{{ $design }}" {{ (is_array(request('design_number')) && in_array($design, request('design_number'))) ? 'selected' : '' }}>
                                    {{ $design }}
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
                        <a href="{{ route('admin.report.product-customer-count') }}" class="btn-erp btn-erp-secondary">
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
                            <th>Design Number</th>
                            <th>Product Name</th>
                            <th>Unique Customers</th>
                            <th class="text-right">Total Quantity Sold</th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                <td><strong>{{ $row->design_number }}</strong></td>
                                <td>{{ $row->product_name }}</td>
                                <td>
                                    <span class="erp-badge-status erp-badge-status-closed">
                                        {{ $row->customer_count }} / {{ $totalCustomers }} ({{ round(($row->customer_count / $totalCustomers) * 100, 1) }}%) Customers
                                    </span>
                                </td>
                                <td class="text-right font-weight-bold">{{ number_format($row->total_quantity) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.report.product-customer-count.detail', $row->design_number) }}" class="erp-action-btn erp-btn-view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No data found for the selected criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($data->hasPages())
        <div class="card-footer bg-white border-top py-2 d-flex justify-content-end">
            {{ $data->appends(request()->query())->links() }}
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
                { selector: 'select[name="design_number[]"]', parent: '#parent-designs' },
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
