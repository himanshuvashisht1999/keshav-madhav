@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header pt-2 pb-1">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="m-0 font-weight-bold text-dark"><i class="fas fa-undo-alt mr-2"></i>Sales Returns</h4>
                </div>
                <div class="text-right">
                    <a href="{{ route('admin.agent-orders.dispatches.index') }}" class="btn btn-primary btn-sm rounded shadow-sm px-3">
                        <i class="fas fa-plus mr-1"></i> New Return (From Dispatch)
                    </a>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <!-- FILTER CARD -->
            <div class="card shadow-sm border-0 mb-3 bg-light">
                <div class="card-body p-2">
                    <form action="{{ route('admin.agent-orders.returns.index') }}" method="GET" class="row align-items-end">
                        <div class="col-md-2 mb-1">
                            <label class="small text-muted mb-0">Filter by Party</label>
                            <select name="shop_id" class="form-control form-control-sm select2">
                                <option value="">All Parties</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                        {{ $shop->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="small text-muted mb-0">Filter by Vendor</label>
                            <select name="vendor_id" class="form-control form-control-sm select2">
                                <option value="">All Vendors</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="small text-muted mb-0">From Date</label>
                            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="small text-muted mb-0">To Date</label>
                            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-2 mb-1">
                            <button type="submit" class="btn btn-primary btn-sm btn-block shadow-sm">
                                <i class="fas fa-filter mr-1"></i> APPLY
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Return ID</th>
                                    <th>Dispatch #</th>
                                    <th>Party Name</th>
                                    <th>Agent</th>
                                    <th>Return Date</th>
                                    <th class="text-right">Grand Total</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returns as $return)
                                    <tr>
                                        <td><span class="font-weight-bold text-danger">#SR-{{ $return->id }}</span></td>
                                        <td>#{{ $return->agent_order_dispatch_id }}</td>
                                        <td>{{ $return->dispatch->party->name ?? 'N/A' }}</td>
                                        <td>{{ $return->dispatch->agent->name ?? 'Direct' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}</td>
                                        <td class="text-right font-weight-bold text-primary">₹{{ number_format($return->grand_total, 2) }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.agent-orders.returns.show', $return->id) }}" class="btn btn-sm btn-info rounded-pill px-3">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-undo-alt fa-3x mb-3 opacity-25"></i>
                                            <p>No sales returns recorded yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($returns->hasPages())
                    <div class="card-footer bg-white">
                        {{ $returns->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
