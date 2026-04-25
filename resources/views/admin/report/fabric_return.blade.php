@extends('admin.layouts.app')
@section('title', 'Fabric Return Report')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="m-0">Fabric Return Report</h3>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.report.fabric_return') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small">Supplier</label>
                            <select name="vendor_id" class="form-control form-control-sm select2">
                                <option value="">All Suppliers</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Shipment No</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search Shipment SKU..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                            <a href="{{ route('admin.report.fabric_return') }}" class="btn btn-sm btn-secondary ms-2"><i class="fas fa-sync"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Sr.No</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Shipment SKU</th>
                                    <th class="text-end">Sub Total</th>
                                    <th class="text-end">Tax Amount</th>
                                    <th class="text-end">Total Amount</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                <tr>
                                    <td class="ps-3">{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                                    <td>{{ $row->receipt->vendor->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $row->receipt->sku ?? '-' }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format($row->sub_total, 2) }}</td>
                                    <td class="text-end">{{ number_format($row->gst_amount, 2) }}</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($row->total_amount, 2) }}</td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.report.fabric_return_view', $row->id) }}" class="btn btn-sm btn-outline-primary py-0" title="View Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.fabric_receipt.edit_return', $row->id) }}" class="btn btn-sm btn-outline-info py-0" title="Edit Return">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" onclick="confirmDelete('{{ route('admin.fabric_receipt.delete_return', $row->id) }}')" class="btn btn-sm btn-outline-danger py-0" title="Delete Return">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
@empty
@endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($data->hasPages())
                <div class="card-footer bg-white">
                    {{ $data->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </section>
</div>

{{-- Delete Confirmation Modal or JS --}}
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
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    })
}
</script>
@endsection
