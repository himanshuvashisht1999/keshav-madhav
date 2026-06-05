@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Journal Vouchers</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Journal Vouchers</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Vouchers</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.payment.journal-voucher.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Journal Voucher
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Voucher No</th>
                                <th>Date</th>
                                <th>Total Debit</th>
                                <th>Total Credit</th>
                                <th>Narration</th>
                                <th>Created At</th>
                                <th style="width: 150px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vouchers as $voucher)
                            <tr>
                                <td><span class="badge badge-info">{{ $voucher->voucher_no }}</span></td>
                                <td>{{ date('d M Y', strtotime($voucher->date)) }}</td>
                                <td class="text-success font-weight-bold">₹{{ number_format($voucher->total_debit, 2) }}</td>
                                <td class="text-danger font-weight-bold">₹{{ number_format($voucher->total_credit, 2) }}</td>
                                <td title="{{ $voucher->narration }}">{{ \Str::limit($voucher->narration, 30) }}</td>
                                <td>{{ $voucher->created_at->format('d M Y h:i A') }}</td>
                                <td>
                                    <a href="{{ route('admin.payment.journal-voucher.show', $voucher->id) }}" class="btn btn-primary btn-xs">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.payment.journal-voucher.edit', $voucher->id) }}" class="btn btn-info btn-xs">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button onclick="deleteVoucher({{ $voucher->id }})" class="btn btn-danger btn-xs">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    <form id="delete-form-{{ $voucher->id }}" action="{{ route('admin.payment.journal-voucher.delete', $voucher->id) }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No vouchers found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $vouchers->links() }}
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
function deleteVoucher(id) {
    if (confirm('Are you sure you want to delete this voucher? All financial entries will be reversed.')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush
@endsection
