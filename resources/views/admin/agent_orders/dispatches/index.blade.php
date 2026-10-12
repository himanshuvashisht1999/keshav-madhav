@extends('admin.layouts.app')

@section('title', 'Dispatch Records')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-truck text-primary"></i> Dispatch Records
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.agent-orders.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-list"></i> Agent Orders
                </a>
                <a href="{{ route('admin.agent-orders.returns.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-undo"></i> Sales Returns
                </a>
            </div>
        </div>

        <!-- Compact ERP Filter Bar -->
        <div class="erp-filter-bar mb-2">
            <form action="{{ route('admin.agent-orders.dispatches.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-2 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-store mr-1"></i> Party</label>
                        <select name="shop_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- ALL PARTIES --</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                    {{ $shop->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Vendor</label>
                        <select name="vendor_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- ALL VENDORS --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-user mr-1"></i> Agent</label>
                        <select name="agent_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- ALL AGENTS --</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-layer-group mr-1"></i> Source</label>
                        <select name="source_type" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">ALL</option>
                            <option value="agent" {{ request('source_type') === 'agent' ? 'selected' : '' }}>Agent</option>
                            <option value="corporate" {{ request('source_type') === 'corporate' ? 'selected' : '' }}>Corporate</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-tag mr-1"></i> Type</label>
                        <select name="dispatch_type" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">ALL</option>
                            <option value="item" {{ request('dispatch_type') === 'item' ? 'selected' : '' }}>Item</option>
                            <option value="fabric" {{ request('dispatch_type') === 'fabric' ? 'selected' : '' }}>Fabric</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> From</label>
                        <input type="date" name="from_date" class="form-control erp-input" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-1 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> To</label>
                        <input type="date" name="to_date" class="form-control erp-input" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-1 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-file-invoice mr-1"></i> Bill No</label>
                        <input type="text" name="bill_no" class="form-control erp-input" value="{{ request('bill_no') }}" placeholder="Bill No..." autocomplete="off">
                    </div>
                    <div class="col-md-1 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> Order ID</label>
                        <input type="text" name="order_id" class="form-control erp-input" value="{{ request('order_id') }}" placeholder="Order ID..." autocomplete="off">
                    </div>
                    <div class="col-md-1 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-sort mr-1"></i> Sort ID</label>
                        <select name="sort_order" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>DESC</option>
                            <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>ASC</option>
                        </select>
                    </div>
                    <div class="col-auto mb-1 ml-auto">
                        <div class="erp-filter-actions">
                            <button type="submit" class="btn-erp btn-erp-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.agent-orders.dispatches.index') }}" class="btn-erp btn-erp-outline">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="erp-card p-0">
            <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                    <i class="fas fa-truck mr-1"></i> Dispatch Records
                </h6>
                <div class="d-flex align-items-center" style="gap: 6px;">
                    @php $qs = http_build_query(request()->except('page')); @endphp
                    <a href="{{ route('admin.agent-orders.dispatches.export-pdf') . ($qs ? '?' . $qs : '') }}"
                       class="btn-erp btn-erp-outline"
                       title="Download PDF"
                       target="_blank">
                        <i class="fas fa-file-pdf mr-1 text-danger"></i> PDF
                    </a>
                    <a href="{{ route('admin.agent-orders.dispatches.export-excel') . ($qs ? '?' . $qs : '') }}"
                       class="btn-erp btn-erp-outline"
                       title="Download Excel">
                        <i class="fas fa-file-excel mr-1 text-success"></i> Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table erp-table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 120px;">
                                @php
                                    $currSort = request('sort_order', 'desc');
                                    $toggleSort = $currSort === 'desc' ? 'asc' : 'desc';
                                    $sortUrl = request()->fullUrlWithQuery(['sort_order' => $toggleSort]);
                                @endphp
                                <a href="{{ $sortUrl }}" class="text-dark d-flex align-items-center justify-content-between text-decoration-none" title="Sort {{ strtoupper($toggleSort) }} by ID">
                                    <span>Dispatch ID</span>
                                    <i class="fas fa-sort-{{ $currSort === 'desc' ? 'down text-primary' : 'up text-primary' }} ml-1"></i>
                                </a>
                            </th>
                            <th>Order id</th>
                            <th>Source</th>
                            <th>Party name</th>
                            <th>Agent</th>
                            <th class="text-right">Grand total</th>
                            <th>Bill no</th>
                            <th>Date</th>
                            <th>Remark</th>
                            <th class="text-right" style="width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dispatches as $dispatch)
                            <tr>
                                <td>
                                    <span class="badge-erp-status badge-erp-primary font-weight-bold">
                                        #DSP-{{ str_pad($dispatch->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td>
                                    @if(!empty($dispatch->order_ids))
                                        @php
                                            $orderIds = array_filter(array_map('trim', explode(',', $dispatch->order_ids)));
                                        @endphp
                                        <div class="d-flex flex-wrap" style="gap: 4px; max-width: 190px;">
                                            @foreach($orderIds as $oid)
                                                @if($dispatch->source_type === 'corporate')
                                                    <span class="badge-erp-status badge-erp-secondary font-weight-bold" title="Corporate Order #{{ $oid }}">
                                                        #ORD-{{ is_numeric($oid) ? str_pad($oid, 5, '0', STR_PAD_LEFT) : $oid }}
                                                    </span>
                                                @else
                                                    <a href="{{ route('admin.agent-orders.show', $oid) }}" 
                                                       class="badge-erp-status badge-erp-info font-weight-bold text-decoration-none"
                                                       title="View Order #{{ $oid }}"
                                                       target="_blank">
                                                        #ORD-{{ is_numeric($oid) ? str_pad($oid, 5, '0', STR_PAD_LEFT) : $oid }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($dispatch->source_type === 'corporate')
                                        <span class="badge-erp-status badge-erp-secondary">Corporate</span>
                                    @else
                                        <span class="badge-erp-status badge-erp-info">Agent</span>
                                    @endif
                                </td>
                                <td>
                                    @if($dispatch->party_type === 'vendor')
                                        <strong>{{ $dispatch->vendor_name ?? 'N/A' }}</strong> <span class="badge-erp-status badge-erp-warning ml-1">Vendor</span>
                                    @else
                                        <strong>{{ $dispatch->customer_name ?? 'N/A' }}</strong>
                                    @endif
                                </td>
                                <td>
                                    @if($dispatch->source_type === 'corporate')
                                        <span class="text-muted"><small>Direct</small></span>
                                    @else
                                        <span class="badge-erp-status badge-erp-info">{{ $dispatch->agent_name ?? 'Direct' }}</span>
                                    @endif
                                </td>
                                <td class="text-right font-weight-bold text-dark">
                                    ₹{{ number_format($dispatch->grand_total, 2) }}
                                </td>
                                <td>{{ $dispatch->bill_no ?? '-' }}</td>
                                <td class="text-nowrap font-weight-bold text-dark">
                                    {{ $dispatch->dispatch_date ? date('d M Y', strtotime($dispatch->dispatch_date)) : 'N/A' }}
                                </td>
                                <td><small class="text-muted">{{ Str::limit($dispatch->remark, 30) }}</small></td>
                                <td class="text-right text-nowrap">
                                    @if($dispatch->source_type === 'corporate')
                                        <div class="d-flex justify-content-end align-items-center" style="gap: 4px;">
                                            <a href="{{ route('admin.order-dispatch.view', ['id' => $dispatch->id]) }}"
                                                class="erp-action-btn erp-btn-view" title="View Dispatch">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.order-dispatch.download-invoice', ['id' => $dispatch->id]) }}"
                                                class="erp-action-btn erp-btn-edit" title="Download Invoice" target="_blank">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                            <a href="{{ route('admin.order-dispatch.download-pdf', ['id' => $dispatch->id]) }}"
                                                class="erp-action-btn erp-btn-delete" title="Download PDF" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <a href="{{ route('admin.order-dispatch.download-packing-slip', ['id' => $dispatch->id]) }}"
                                                class="erp-action-btn erp-btn-edit" title="Packing Slip" target="_blank">
                                                <i class="fas fa-box-open"></i>
                                            </a>
                                        </div>
                                    @else
                                        <div class="d-flex justify-content-end align-items-center" style="gap: 4px;">
                                            <a href="{{ route('admin.agent-orders.dispatches.show', $dispatch->id) }}"
                                                class="erp-action-btn erp-btn-view" title="View Dispatch">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <div class="dropdown d-inline-block">
                                                <button class="erp-action-btn erp-btn-edit dropdown-toggle" type="button" id="invoiceDropdown{{ $dispatch->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Invoices">
                                                    <i class="fas fa-file-invoice"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right shadow border-0" aria-labelledby="invoiceDropdown{{ $dispatch->id }}">
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
                                                        <i class="fab fa-whatsapp text-success mr-2"></i> Send Invoice via WhatsApp
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="dropdown d-inline-block">
                                                <button class="erp-action-btn erp-btn-edit dropdown-toggle" type="button" id="packingSlipDropdown{{ $dispatch->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Packing Slips">
                                                    <i class="fas fa-box-open"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right shadow border-0" aria-labelledby="packingSlipDropdown{{ $dispatch->id }}">
                                                    <a class="dropdown-item" href="{{ route('admin.agent-orders.dispatches.download-packing-slip', $dispatch->id) }}" target="_blank">
                                                        <i class="fas fa-file-pdf text-danger mr-2"></i> Download Packing Slip
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('admin.agent-orders.dispatches.send-whatsapp-packing-slip', $dispatch->id) }}">
                                                        <i class="fab fa-whatsapp text-success mr-2"></i> Send Packing Slip via WhatsApp
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="dropdown d-inline-block">
                                                <button class="erp-action-btn erp-btn-edit dropdown-toggle" type="button" id="barcodeDropdown{{ $dispatch->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Barcodes">
                                                    <i class="fas fa-barcode"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right shadow border-0" aria-labelledby="barcodeDropdown{{ $dispatch->id }}">
                                                    <a class="dropdown-item" href="{{ route('admin.agent-orders.dispatches.generate-prn', $dispatch->id) }}">
                                                        <i class="fas fa-barcode text-primary mr-2"></i> Download PRN (Thermal)
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('admin.agent-orders.dispatches.download-barcode-pdf', $dispatch->id) }}" target="_blank">
                                                        <i class="fas fa-file-pdf text-danger mr-2"></i> Download Barcodes (PDF)
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fas fa-truck-loading fa-2x mb-2 text-secondary"></i>
                                    <div>No dispatch records found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($dispatches->hasPages())
                <div class="p-2 border-top bg-white">
                    {{ $dispatches->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
    });
</script>
@endsection
