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

                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.ledger.sales.export-pdf', request()->all()) }}" class="btn btn-danger btn-sm mr-2 shadow-sm font-weight-bold" style="border-radius: 6px;">
                        <i class="fas fa-file-pdf mr-1"></i> PDF
                    </a>
                    <a href="{{ route('admin.ledger.sales.export-excel', request()->all()) }}" class="btn btn-success btn-sm shadow-sm font-weight-bold" style="border-radius: 6px;">
                        <i class="fas fa-file-excel mr-1"></i> Excel
                    </a>
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
                                <label class="small text-muted font-weight-bold">Bill No.</label>
                                <input type="text" name="bill_no" class="form-control" placeholder="Search Bill No" value="{{ request('bill_no') }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">Dispatch Type</label>
                                <select name="item_type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="Product" {{ request('item_type') === 'Product' ? 'selected' : '' }}>Product</option>
                                    <option value="Fabric" {{ request('item_type') === 'Fabric' ? 'selected' : '' }}>Fabric</option>
                                    <option value="Corporate" {{ request('item_type') === 'Corporate' ? 'selected' : '' }}>Corporate</option>
                                </select>
                            </div>
                            <div class="col-md-1 mb-2">
                                <label class="small text-muted font-weight-bold">From</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-md-1 mb-2">
                                <label class="small text-muted font-weight-bold">To</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-md-2 mb-2 d-flex">
                                <button type="submit" class="btn btn-primary flex-fill mr-1 shadow-sm font-weight-bold">
                                    <i class="fas fa-filter mr-1"></i> APPLY
                                </button>
                                <a href="{{ route('admin.ledger.sales.index') }}" class="btn btn-outline-secondary shadow-sm" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </a>
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
                                    <th>Agent / Type</th>
                                    <th>Grand Total</th>
                                    <th>Date</th>
                                    <th>Remark</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $dispatch)
                                    <tr>
                                        <td>
                                            <strong>{{ $dispatch->dispatch_no }}</strong>
                                            @if($dispatch->source_type === 'corporate')
                                                <span class="badge badge-info ml-1">Corporate</span>
                                            @endif
                                            @if(!empty($dispatch->bill_no))
                                                <br><small class="text-muted">Bill: {{ $dispatch->bill_no }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>
                                                @if($dispatch->party_type === 'vendor')
                                                    {{ $dispatch->party_name ?? 'N/A' }} <span class="badge badge-warning ml-1">Vendor</span>
                                                @else
                                                    {{ $dispatch->party_name ?? 'N/A' }}
                                                @endif
                                            </strong>
                                        </td>
                                        <td>
                                            @if($dispatch->source_type === 'corporate')
                                                <span class="badge text-white shadow-sm" style="background-color: #6f42c1; color: #ffffff !important;">{{ $dispatch->agent_name ?? 'Corporate' }}</span>
                                            @else
                                                <span class="badge badge-info">{{ $dispatch->agent_name ?? 'Direct' }}</span>
                                            @endif
                                        </td>
                                        <td><span class="text-primary font-weight-bold">₹{{ number_format($dispatch->grand_total, 2) }}</span></td>
                                        <td>{{ $dispatch->dispatch_date ? date('d M Y', strtotime($dispatch->dispatch_date)) : 'N/A' }}</td>
                                        <td><small class="text-muted">{{ Str::limit($dispatch->remark, 30) }}</small></td>
                                        <td class="text-right">
                                            @if($dispatch->source_type === 'corporate')
                                                <a href="{{ route('admin.order-dispatch.view', ['id' => $dispatch->id]) }}"
                                                    class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                                                    <i class="fas fa-eye mr-1"></i> View
                                                </a>
                                            @else
                                                <a href="{{ route('admin.agent-orders.dispatches.show', $dispatch->id) }}"
                                                    class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                                                    <i class="fas fa-eye mr-1"></i> View
                                                </a>
                                            @endif
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
