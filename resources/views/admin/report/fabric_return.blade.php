@extends('admin.layouts.app')
@section('title', 'Fabric Return Report')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-undo"></i> Fabric return report
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="fas fa-list text-muted"></i> Vendor return transactions</span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-clock text-muted"></i> {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="erp-header-actions">
            @if(Route::has('admin.fabric_receipt.return'))
            <a href="{{ route('admin.fabric_receipt.return') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus mr-1"></i> Create fabric return
            </a>
            @endif
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="erp-card mb-2 p-2">
        <form method="GET" action="{{ route('admin.report.fabric_return') }}" class="mb-0">
            <div class="form-row align-items-end">
                <div class="col-md-3 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Supplier</label>
                    <select name="vendor_id" class="form-control form-control-sm select2 erp-filter-select">
                        <option value="">All suppliers</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">From date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm erp-filter-input" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">To date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm erp-filter-input" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Shipment SKU</label>
                    <input type="text" name="search" class="form-control form-control-sm erp-filter-input" placeholder="Search shipment SKU..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 col-sm-12 mb-1 d-flex gap-1">
                    <button type="submit" class="btn-erp btn-erp-primary btn-sm flex-fill" title="Apply filter">
                        <i class="fas fa-filter mr-1"></i> Apply
                    </button>
                    <a href="{{ route('admin.report.fabric_return') }}" class="btn-erp btn-erp-outline btn-sm" title="Reset filter">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Report Table Card --}}
    <div class="erp-card p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover erp-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Return date</th>
                        <th>Supplier</th>
                        <th>Shipment SKU</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">Tax amount</th>
                        <th class="text-right">Total amount</th>
                        <th class="text-center" style="width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                        <td><strong>{{ $row->receipt->vendor->name ?? '-' }}</strong></td>
                        <td><code>{{ $row->receipt->sku ?? '-' }}</code></td>
                        <td class="text-right">₹ {{ number_format($row->sub_total, 2) }}</td>
                        <td class="text-right">₹ {{ number_format($row->gst_amount, 2) }}</td>
                        <td class="text-right font-weight-bold text-danger">₹ {{ number_format($row->total_amount, 2) }}</td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('admin.report.fabric_return_view', $row->id) }}" class="btn-erp btn-erp-outline btn-sm py-0 px-2 mr-1" style="font-size: 11px;" title="View detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(Route::has('admin.fabric_receipt.edit_return'))
                            <a href="{{ route('admin.fabric_receipt.edit_return', $row->id) }}" class="btn-erp btn-erp-outline btn-sm py-0 px-2 mr-1" style="font-size: 11px;" title="Edit return">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                            @if(Route::has('admin.fabric_receipt.delete_return'))
                            <a href="javascript:void(0)" onclick="confirmDelete('{{ route('admin.fabric_receipt.delete_return', $row->id) }}')" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 11px;" title="Delete return">
                                <i class="fas fa-trash"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No fabric return records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data->hasPages())
            <div class="p-2 border-top bg-light">
                {{ $data->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(url) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will revert the stock and vendor balance!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>
@endsection
