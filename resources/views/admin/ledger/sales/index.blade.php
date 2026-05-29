@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-book mr-2"></i>Sales Ledger</h1>
                    <p class="text-muted">Invoice-wise details of all dispatched sales.</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-primary text-white px-4 py-2 rounded shadow-sm">
                        <span class="d-block small font-weight-bold text-uppercase text-white-50">Total Grand Total</span>
                        <span class="h4 mb-0 font-weight-bold">₹{{ number_format($totalGrandTotal ?? 0, 2) }}</span>
                    </div>
                </div>

                <div>
                    <!-- Blank for symmetry -->
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- FILTER CARD -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.ledger.sales.index') }}" method="GET" class="row align-items-end">
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">Filter by Party</label>
                                <select name="party_id" class="form-control select2">
                                    <option value="">All Parties</option>
                                    @foreach($parties as $party)
                                        <option value="{{ $party->id }}" {{ request('party_id') == $party->id ? 'selected' : '' }}>
                                            {{ $party->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
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
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">Dispatch Type</label>
                                <select name="item_type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="Product" {{ request('item_type') === 'Product' ? 'selected' : '' }}>Product</option>
                                    <option value="Fabric" {{ request('item_type') === 'Fabric' ? 'selected' : '' }}>Fabric</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">From Date</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">To Date</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-md-2 mb-2">
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
                                    <th>Grand Total</th>
                                    <th>Date</th>
                                    <th>Remark</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $dispatch)
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
                                        <td><span class="text-primary font-weight-bold">₹{{ number_format($dispatch->grand_total, 2) }}</span></td>
                                        <td>{{ $dispatch->dispatch_date ? date('d M Y', strtotime($dispatch->dispatch_date)) : 'N/A' }}</td>
                                        <td><small class="text-muted">{{ Str::limit($dispatch->remark, 30) }}</small></td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.agent-orders.dispatches.show', $dispatch->id) }}"
                                                class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">No sales items found matching the criteria.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($sales->hasPages())
                        <div class="card-footer bg-white">
                            {{ $sales->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
