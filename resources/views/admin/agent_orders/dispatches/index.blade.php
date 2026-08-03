@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header pt-2 pb-1">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="m-0 text-dark"><i class="fas fa-history mr-2"></i>Dispatch Logs</h4>
                    <p class="text-muted small mb-0">Track consolidated shipments dispatched to shops.</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-primary text-white px-3 py-1 rounded shadow-sm">
                        <span class="d-block small text-uppercase text-white-50">Total Grand Total</span>
                        <span class="h5 mb-0">₹{{ number_format($totalGrandTotal ?? 0, 2) }}</span>
                    </div>
                </div>

                <div>
                    <a href="{{ route('admin.agent-orders.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm px-3" style="border-radius: 6px;">
                        <i class="fas fa-arrow-left mr-1"></i> BACK TO ORDERS
                    </a>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- FILTER CARD -->
                <div class="card shadow-sm border-0 mb-3 bg-light">
                    <div class="card-body p-2">
                        <form action="{{ route('admin.agent-orders.dispatches.index') }}" method="GET" class="row align-items-end">
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
                                <label class="small text-muted mb-0">Filter by Agent</label>
                                <select name="agent_id" class="form-control form-control-sm select2">
                                    <option value="">All Agents</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-1">
                                <label class="small text-muted mb-0">Dispatch Type</label>
                                <select name="dispatch_type" class="form-control form-control-sm">
                                    <option value="">All Types</option>
                                    <option value="item" {{ request('dispatch_type') === 'item' ? 'selected' : '' }}>Item</option>
                                    <option value="fabric" {{ request('dispatch_type') === 'fabric' ? 'selected' : '' }}>Fabric</option>
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
                                <label class="small text-muted mb-0">Bill No</label>
                                <input type="text" name="bill_no" class="form-control form-control-sm" value="{{ request('bill_no') }}" placeholder="Enter Bill No">
                            </div>
                            <div class="col-md-2 mb-1">
                                <button type="submit" class="btn btn-primary btn-sm btn-block shadow-sm">
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
                                    <th class="font-weight-normal">Dispatch ID</th>
                                    <th class="font-weight-normal">Party Name</th>
                                    <th class="font-weight-normal">Agent</th>
                                    <th class="font-weight-normal">Grand Total</th>
                                    <th class="font-weight-normal">Bill No</th>
                                    <th class="font-weight-normal">Date</th>
                                    <th class="font-weight-normal">Remark</th>
                                    <th class="font-weight-normal text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dispatches as $dispatch)
                                    <tr>
                                        <td><small>#DSP-{{ str_pad($dispatch->id, 5, '0', STR_PAD_LEFT) }}</small></td>
                                        <td>
                                            @if($dispatch->party_type === 'vendor')
                                                {{ $dispatch->vendor->name ?? 'N/A' }} <span class="badge badge-warning ml-1">Vendor</span>
                                            @else
                                                {{ $dispatch->shop->name ?? 'N/A' }}
                                            @endif
                                        </td>
                                        <td><span class="badge badge-info">{{ $dispatch->agent->name ?? 'Direct' }}</span></td>
                                        <td><span class="text-primary">₹{{ number_format($dispatch->grand_total, 2) }}</span></td>
                                        <td>{{ $dispatch->bill_no ?? '-' }}</td>
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
