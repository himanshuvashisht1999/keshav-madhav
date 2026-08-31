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
                                <label class="small text-muted mb-0">Source</label>
                                <select name="source_type" class="form-control form-control-sm">
                                    <option value="">All Sources</option>
                                    <option value="agent" {{ request('source_type') === 'agent' ? 'selected' : '' }}>Agent Orders</option>
                                    <option value="corporate" {{ request('source_type') === 'corporate' ? 'selected' : '' }}>Corporate</option>
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
                                <div class="d-flex w-100">
                                    <button type="submit" class="btn btn-primary btn-sm shadow-sm flex-fill mr-1">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <a href="{{ route('admin.agent-orders.dispatches.index') }}" class="btn btn-secondary btn-sm shadow-sm flex-fill ml-1">
                                        <i class="fas fa-sync-alt"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold">Dispatch Records</h5>
                        <div class="d-flex align-items-center">
                            @php $qs = http_build_query(request()->except('page')); @endphp
                            <a href="{{ route('admin.agent-orders.dispatches.export-pdf') . ($qs ? '?' . $qs : '') }}"
                               class="btn btn-sm btn-outline-danger px-3 shadow-sm mr-2"
                               title="Download PDF"
                               target="_blank">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </a>
                            <a href="{{ route('admin.agent-orders.dispatches.export-excel') . ($qs ? '?' . $qs : '') }}"
                               class="btn btn-sm btn-outline-success px-3 shadow-sm"
                               title="Download Excel">
                                <i class="fas fa-file-excel mr-1"></i> Excel
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="font-weight-normal">Dispatch ID</th>
                                    <th class="font-weight-normal">Source</th>
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
                                            @if($dispatch->source_type === 'corporate')
                                                <span class="badge badge-secondary">Corporate</span>
                                            @else
                                                <span class="badge badge-primary">Agent</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($dispatch->party_type === 'vendor')
                                                {{ $dispatch->vendor_name ?? 'N/A' }} <span class="badge badge-warning ml-1">Vendor</span>
                                            @else
                                                {{ $dispatch->customer_name ?? 'N/A' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($dispatch->source_type === 'corporate')
                                                <span class="text-muted"><small>Direct</small></span>
                                            @else
                                                <span class="badge badge-info">{{ $dispatch->agent_name ?? 'Direct' }}</span>
                                            @endif
                                        </td>
                                        <td><span class="text-primary">₹{{ number_format($dispatch->grand_total, 2) }}</span></td>
                                        <td>{{ $dispatch->bill_no ?? '-' }}</td>
                                        <td>{{ $dispatch->dispatch_date ? date('d M Y', strtotime($dispatch->dispatch_date)) : 'N/A' }}</td>
                                        <td><small class="text-muted">{{ Str::limit($dispatch->remark, 30) }}</small></td>
                                        <td class="text-right text-nowrap">
                                            @if($dispatch->source_type === 'corporate')
                                                <a href="{{ route('admin.order-dispatch.view', ['id' => $dispatch->id]) }}"
                                                    class="btn btn-primary btn-sm px-2 shadow-sm" style="border-radius: 6px;" title="View Dispatch">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.order-dispatch.download-invoice', ['id' => $dispatch->id]) }}"
                                                    class="btn btn-info btn-sm px-2 shadow-sm" style="border-radius: 6px;" title="Download Invoice" target="_blank">
                                                    <i class="fas fa-file-invoice"></i>
                                                </a>
                                                <a href="{{ route('admin.order-dispatch.download-pdf', ['id' => $dispatch->id]) }}"
                                                    class="btn btn-danger btn-sm px-2 shadow-sm" style="border-radius: 6px;" title="Download PDF" target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                <a href="{{ route('admin.order-dispatch.download-packing-slip', ['id' => $dispatch->id]) }}"
                                                    class="btn btn-warning btn-sm px-2 shadow-sm text-dark" style="border-radius: 6px;" title="Packing Slip" target="_blank">
                                                    <i class="fas fa-box-open"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('admin.agent-orders.dispatches.show', $dispatch->id) }}"
                                                    class="btn btn-primary btn-sm px-2 shadow-sm" style="border-radius: 6px;" title="View Dispatch">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <div class="dropdown d-inline-block">
                                                    <button class="btn btn-info btn-sm px-2 shadow-sm dropdown-toggle" type="button" id="invoiceDropdown{{ $dispatch->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 6px;" title="Invoices">
                                                        <i class="fas fa-file-invoice"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="invoiceDropdown{{ $dispatch->id }}">
                                                        <a class="dropdown-item" href="{{ route('admin.agent-orders.dispatches.download-invoice', $dispatch->id) }}" target="_blank">
                                                            <i class="fas fa-file-pdf text-danger mr-2"></i> Standard Invoice
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.agent-orders.dispatches.download-retail-invoice', $dispatch->id) }}" target="_blank">
                                                            <i class="fas fa-file-pdf text-danger mr-2"></i> Retail Invoice (PDF)
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.agent-orders.dispatches.download-retail-invoice-excel', $dispatch->id) }}" target="_blank">
                                                            <i class="fas fa-file-excel text-success mr-2"></i> Retail Invoice (Excel)
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="{{ route('admin.agent-orders.dispatches.send-whatsapp-invoice', $dispatch->id) }}">
                                                            <i class="fab fa-whatsapp text-success mr-2"></i> Send Standard Invoice via WhatsApp
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="dropdown d-inline-block">
                                                    <button class="btn btn-warning btn-sm px-2 shadow-sm text-dark dropdown-toggle" type="button" id="packingSlipDropdown{{ $dispatch->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 6px;" title="Packing Slips">
                                                        <i class="fas fa-box-open"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="packingSlipDropdown{{ $dispatch->id }}">
                                                        <a class="dropdown-item" href="{{ route('admin.agent-orders.dispatches.download-packing-slip', $dispatch->id) }}" target="_blank">
                                                            <i class="fas fa-file-pdf text-danger mr-2"></i> Download Packing Slip
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.agent-orders.dispatches.send-whatsapp-packing-slip', $dispatch->id) }}">
                                                            <i class="fab fa-whatsapp text-success mr-2"></i> Send Packing Slip via WhatsApp
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
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
                            {{ $dispatches->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
