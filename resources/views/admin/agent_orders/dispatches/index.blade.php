@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-history mr-2"></i>Dispatch Logs</h1>
                    <p class="text-muted">Track consolidated shipments dispatched to shops.</p>
                </div>
                <div>
                    <a href="{{ route('admin.agent-orders.index') }}" class="btn btn-outline-secondary shadow-sm px-4" style="border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-2"></i> BACK TO ORDERS
                    </a>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- FILTER CARD -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.agent-orders.dispatches.index') }}" method="GET" class="row align-items-end">
                            <div class="col-md-3">
                                <label class="small text-muted font-weight-bold">Filter by Party</label>
                                <select name="shop_id" class="form-control select2">
                                    <option value="">All Parties</option>
                                    @foreach($shops as $shop)
                                        <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                            {{ $shop->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted font-weight-bold">Filter by Vendor</label>
                                <select name="vendor_id" class="form-control select2">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted font-weight-bold">From Date</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted font-weight-bold">To Date</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-filter mr-1"></i> APPLY
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Dispatch ID</th>
                                    <th>Party Name</th>
                                    <th>Agent</th>
                                    <th>LR No.</th>
                                    <th>Transport</th>
                                    <th>Grand Total</th>
                                    <th>Other Charges</th>
                                    <th>Date</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dispatches as $dispatch)
                                    <tr>
                                        <td>#DSP-{{ str_pad($dispatch->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <strong>
                                                @if($dispatch->party_type === 'vendor')
                                                    {{ $dispatch->vendor->name ?? 'N/A' }} <span class="badge badge-warning ml-1">Vendor</span>
                                                @else
                                                    {{ $dispatch->shop->name ?? 'N/A' }}
                                                @endif
                                            </strong>
                                        </td>
                                        <td><span class="badge badge-info">{{ $dispatch->agent->name ?? 'Direct' }}</span></td>
                                        <td>{{ $dispatch->lr_no ?? 'Pending' }}</td>
                                        <td>{{ $dispatch->transport_name ?? 'N/A' }}</td>
                                        <td><span class="text-primary font-weight-bold">₹{{ number_format($dispatch->grand_total, 2) }}</span></td>
                                        <td>₹{{ number_format($dispatch->other_charges ?? 0, 2) }}</td>
                                        <td>{{ $dispatch->dispatch_date ? date('d M Y', strtotime($dispatch->dispatch_date)) : 'N/A' }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.agent-orders.dispatches.show', $dispatch->id) }}"
                                                class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">No dispatch records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($dispatches->hasPages())
                        <div class="card-footer bg-white">
                            {{ $dispatches->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
