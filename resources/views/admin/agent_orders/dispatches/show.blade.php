@extends('admin.layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        :root {
            --bg-main: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .content-wrapper {
            background-color: var(--bg-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding-bottom: 2rem;
        }

        .premium-page-header {
            padding: 1.5rem 0;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.025em;
            margin: 0 0 0.25rem 0;
            display: flex;
            align-items: center;
        }

        .page-title i {
            color: #6366f1;
            background: #eef2ff;
            padding: 0.5rem;
            border-radius: 10px;
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
            height: 100%;
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
            border-color: #cbd5e1;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .stat-info {
            flex-grow: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-main);
            margin: 0;
        }

        .btn-action {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            border: none;
            box-shadow: var(--shadow-sm);
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .bg-soft-primary { background: #eef2ff; color: #4f46e5; }
        .bg-soft-success { background: #ecfdf5; color: #059669; }
        .bg-soft-warning { background: #fffbeb; color: #d97706; }
        .bg-soft-info { background: #f0fdfa; color: #0d9488; }
        .bg-soft-danger { background: #fef2f2; color: #dc2626; }
        .bg-soft-secondary { background: #f8fafc; color: #475569; }
        .bg-primary-gradient { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #fff; }

        .order-card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            background: #fff;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .order-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: #fcfdfe;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table thead th {
            background: #f8fafc;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: var(--text-muted);
            border-top: none;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
        }

        .table td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            color: var(--text-main);
            font-size: 0.875rem;
            border-top: 1px solid #f1f5f9;
        }
    </style>

    <div class="content-wrapper">
        <div class="content-header" style="background: #fff; border-bottom: 1px solid #e2e8f0; padding: 1.25rem 0; margin-bottom: 1.5rem;">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.5rem; letter-spacing: -0.025em;">
                            <i class="fas fa-truck-loading text-primary mr-2"></i> Dispatch #DSP-{{ str_pad($dispatch->id, 5, '0', STR_PAD_LEFT) }}
                        </h1>
                        <p class="text-muted mb-0 mt-1" style="font-size: 0.875rem;">
                            Party: <strong class="text-dark">
                                @if ($dispatch->party_type === 'vendor')
                                    {{ $dispatch->vendor->name ?? 'N/A' }} <span class="badge badge-warning shadow-sm ml-1" style="font-size: 10px;">VENDOR</span>
                                @else
                                    {{ $dispatch->shop->name ?? 'N/A' }}
                                @endif
                            </strong> 
                            <span class="mx-2 text-light-gray">|</span>
                            Agent: <strong class="text-dark">{{ $dispatch->agent->name ?? 'Direct' }}</strong>
                        </p>
                    </div>
                    <div class="col-md-7 text-md-right mt-3 mt-md-0 d-flex flex-wrap align-items-center justify-content-md-end" style="gap: 0.5rem;">
                        @if(!$isFabric)
                        <div>
                            <select id="brandSelect" class="form-control form-control-sm shadow-sm"
                                style="border-radius: 8px; min-width: 120px; height: 36px; border: 1px solid #e2e8f0;">
                                <option value="actual">Actual Brand</option>
                                <option value="2">Surgical</option>
                                <option value="1">Snapkid</option>
                            </select>
                        </div>
                        @endif
                        
                        <button type="button" class="btn btn-action bg-white border text-warning" data-toggle="modal" data-target="#editInvoiceModal" title="Edit Invoice">
                            <i class="fas fa-edit"></i> <span class="d-none d-xl-inline">Edit Invoice</span>
                        </button>
                        
                        <a href="{{ route('admin.agent-orders.dispatches.return.create', $dispatch->id) }}" class="btn btn-action bg-white border text-danger" title="Sales Return">
                            <i class="fas fa-undo"></i> <span class="d-none d-xl-inline">Sales Return</span>
                        </a>
                        
                        <a href="{{ route('admin.agent-orders.dispatches.destroy', $dispatch->id) }}" class="btn btn-action bg-soft-danger text-danger border-0" onclick="return confirm('Are you sure you want to PERMANENTLY delete this dispatch? This will reverse stock and customer balance.')" title="Delete Dispatch">
                            <i class="fas fa-trash-alt"></i> <span class="d-none d-xl-inline">Delete</span>
                        </a>

                        <a href="{{ route('admin.agent-orders.dispatches.download-packing-slip', $dispatch->id) }}" id="packingSlipBtn" class="btn btn-action bg-soft-info text-info border-0">
                            <i class="fas fa-box-open"></i> Packing Slip
                        </a>
                        
                        <a href="{{ route('admin.agent-orders.dispatches.send-whatsapp-packing-slip', $dispatch->id) }}" id="waPackingSlipBtn" class="btn btn-action text-white" style="background-color: #25D366; border-color: #25D366;" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $dispatch->shop->phone ?? $dispatch->vendor->phone ?? '' }}'); if(phone) { window.location.href = this.href + (this.href.includes('?') ? '&' : '?') + 'phone=' + encodeURIComponent(phone); }">
                            <i class="fab fa-whatsapp"></i> WA Packing Slip
                        </a>
                        
                        <a href="{{ route('admin.agent-orders.dispatches.download-invoice', $dispatch->id) }}" id="invoiceBtn" class="btn btn-action bg-primary-gradient">
                            <i class="fas fa-file-invoice"></i> Download Invoice
                        </a>

                        <a href="{{ route('admin.agent-orders.dispatches.send-whatsapp-invoice', $dispatch->id) }}" id="waInvoiceBtn" class="btn btn-action text-white" style="background-color: #25D366; border-color: #25D366;" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $dispatch->shop->phone ?? $dispatch->vendor->phone ?? '' }}'); if(phone) { window.location.href = this.href + (this.href.includes('?') ? '&' : '?') + 'phone=' + encodeURIComponent(phone); }">
                            <i class="fab fa-whatsapp"></i> WA Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Invoice Modal -->
        <div class="modal fade" id="editInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="editInvoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
                    <div class="modal-header bg-white border-bottom-0" style="padding: 1.5rem 1.5rem 0.5rem;">
                        <h5 class="modal-title font-weight-bold text-dark" id="editInvoiceModalLabel">
                            <i class="fas fa-edit text-warning mr-2"></i> Update Dispatch Invoice
                        </h5>
                        <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="editInvoiceForm">
                        @csrf
                        <div class="modal-body px-4 pb-4">
                            <div class="form-group mb-3">
                                <label class="stat-label">Dispatch Date</label>
                                <input type="datetime-local" class="form-control" style="border-radius: 10px; height: 44px;" id="dispatch_date" name="dispatch_date" value="{{ date('Y-m-d\TH:i', strtotime($dispatch->dispatch_date)) }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="stat-label">Subtotal Amount (Pcs * Price)</label>
                                <input type="number" step="0.01" class="form-control" style="border-radius: 10px; height: 44px;" id="total_amount" name="total_amount" value="{{ $dispatch->total_amount }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="stat-label">Extra Discount</label>
                                <input type="number" step="0.01" class="form-control" style="border-radius: 10px; height: 44px;" id="discount_amount" name="discount_amount" value="{{ $dispatch->discount_amount ?? 0 }}">
                            </div>
                            <div class="form-group mb-3">
                                <label class="stat-label">Other Charges</label>
                                <input type="number" step="0.01" class="form-control" style="border-radius: 10px; height: 44px;" id="other_charges" name="other_charges" value="{{ $dispatch->other_charges ?? 0 }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="stat-label">GST %</label>
                                        <input type="number" step="any" class="form-control" style="border-radius: 10px; height: 44px;" id="gst_percentage" name="gst_percentage" value="{{ $dispatch->gst_percentage ?? 5 }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="stat-label">GST Amount</label>
                                        <input type="number" step="any" class="form-control" style="border-radius: 10px; height: 44px;" id="gst_amount_input" value="{{ round($dispatch->gst_amount, 2) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="stat-label">Remark</label>
                                <textarea name="remark" class="form-control" style="border-radius: 10px;" rows="2">{{ $dispatch->remark }}</textarea>
                            </div>
                            <div class="bg-light p-3 rounded text-center mt-4">
                                <h6 class="stat-label mb-1">Final Grand Total</h6>
                                <h3 class="mb-0 text-primary font-weight-bold" id="grand_total_display">₹{{ number_format($dispatch->grand_total, 2) }}</h3>
                            </div>
                        </div>
                        <div class="modal-footer bg-white border-top-0 px-4 pb-4">
                            <button type="button" class="btn btn-action bg-white border text-muted" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-action bg-warning text-dark font-weight-bold">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- KPI Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="stat-card">
                            <div class="stat-icon bg-soft-primary">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-label">Dispatch Date</div>
                                <div class="stat-value" style="font-size: 1rem;">{{ $dispatch->dispatch_date ? date('d M Y, h:i A', strtotime($dispatch->dispatch_date)) : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    @php
                        $overallQty = $groupedItems->sum('total_qty');
                        $overallFabricMeters = $fabricItems->sum('meter');
                    @endphp
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="stat-card">
                            <div class="stat-icon bg-soft-info">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-label">Total Units</div>
                                <div class="stat-value text-info">
                                    @if($overallQty > 0) {{ number_format($overallQty) }} PCs @endif
                                    @if($overallQty > 0 && $overallFabricMeters > 0) + @endif
                                    @if($overallFabricMeters > 0) {{ number_format($overallFabricMeters, 2) }} m @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="stat-card">
                            <div class="stat-icon bg-soft-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-label">Status</div>
                                <div class="stat-value text-success" style="font-size: 1.1rem;">{{ strtoupper($dispatch->status) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-soft-secondary">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-label">Subtotal</div>
                                <div class="stat-value">₹{{ number_format($dispatch->total_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="stat-card">
                            <div class="stat-icon bg-soft-danger">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-label">Extra Discount</div>
                                <div class="stat-value text-danger">₹{{ number_format($dispatch->discount_amount ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="stat-card">
                            <div class="stat-icon bg-soft-warning">
                                <i class="fas fa-percent"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-label">GST ({{ number_format($dispatch->gst_percentage ?? 5, 1) }}%)</div>
                                <div class="stat-value text-warning">₹{{ number_format($dispatch->gst_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="stat-card">
                            <div class="stat-icon bg-soft-secondary">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-label">Other Charges</div>
                                <div class="stat-value">₹{{ number_format($dispatch->other_charges ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                            <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: #fff;">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                            <div class="stat-info text-white">
                                <div class="stat-label text-white-50">Final Amount</div>
                                <div class="stat-value text-white">₹{{ number_format($dispatch->grand_total, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($dispatch->remark)
                <div class="card order-card mb-4">
                    <div class="card-body p-4 bg-soft-info">
                        <h6 class="stat-label text-info"><i class="fas fa-comment-dots mr-2"></i>Dispatch Remark</h6>
                        <p class="mb-0 text-dark font-weight-500">{{ $dispatch->remark }}</p>
                    </div>
                </div>
                @endif

                <!-- Items Grouped By Order -->
                @foreach($dispatch->orders as $order)
                    @php
                        $sessionItems = $order->items->where('agent_order_dispatch_id', $dispatch->id);
                        $sessionFabricItems = $fabricItems->where('agent_order_id', $order->id);
                        
                        if ($sessionItems->isEmpty() && $sessionFabricItems->isEmpty())
                            continue;

                        $orderDispatchedQty = $sessionItems->sum('quantity');
                        $orderDispatchedMeters = $sessionFabricItems->sum('meter');
                        $orderSubtotal = $sessionItems->sum(function ($i) {
                            return $i->quantity * $i->selling_price;
                        }) + $sessionFabricItems->sum(function ($i) {
                            return $i->meter * $i->selling_price;
                        });
                    @endphp

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 font-weight-bold">
                                <i class="fas fa-shopping-cart text-primary mr-2"></i>
                                Order <a href="{{ route('admin.agent-orders.show', $order->id) }}" class="text-dark"
                                    target="_blank">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</a>
                            </h5>
                            <div class="text-right">
                                <span class="badge badge-light border px-3 py-2 mr-2" style="font-size: 14px;">
                                    @if($orderDispatchedQty > 0) {{ number_format($orderDispatchedQty) }} PCs @endif
                                    @if($orderDispatchedQty > 0 && $orderDispatchedMeters > 0) + @endif
                                    @if($orderDispatchedMeters > 0) {{ number_format($orderDispatchedMeters, 2) }} m @endif
                                </span>
                                <span class="badge badge-success px-3 py-2" style="font-size: 14px;">₹{{ number_format($orderSubtotal, 2) }}</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0 text-nowrap">
                                    <thead class="bg-light">
                                        @if($sessionItems->isNotEmpty())
                                            <tr>
                                                <th>Design #</th>
                                                <th>Product Name</th>
                                                <th>Color</th>
                                                <th>Size Set</th>
                                                <th class="text-center">Boxes</th>
                                                <th class="text-center">Total Pcs</th>
                                                <th class="text-right">Price/Pc</th>
                                                <th class="text-right">Total Amount</th>
                                            </tr>
                                        @else
                                            <tr>
                                                <th>Roll #</th>
                                                <th>Fabric Name</th>
                                                <th class="text-center">Batch No</th>
                                                <th class="text-center">Meters</th>
                                                <th class="text-right">Price/m</th>
                                                <th class="text-right">Total Amount</th>
                                            </tr>
                                        @endif
                                    </thead>
                                    <tbody>
                                        @foreach($sessionItems as $item)
                                            <tr>
                                                <td class="font-weight-bold text-dark">{{ $item->design_number }}</td>
                                                <td>{{ $item->product_name }}</td>
                                                <td>{{ $item->color_name }}</td>
                                                <td>{{ $item->size_set_name ?? 'N/A' }}</td>
                                                <td class="text-center"><span class="badge badge-light border px-3 py-1"
                                                        style="font-size: 13px;">{{ number_format($item->box_qty, 0) }}</span></td>
                                                <td class="text-center font-weight-bold" style="font-size: 15px;">
                                                    {{ number_format($item->quantity, 0) }}
                                                </td>
                                                <td class="text-right text-muted">₹{{ number_format($item->selling_price, 2) }}</td>
                                                <td class="text-right font-weight-bold text-success" style="font-size: 15px;">
                                                    ₹{{ number_format($item->quantity * $item->selling_price, 2) }}</td>
                                            </tr>
                                        @endforeach

                                        @foreach($sessionFabricItems as $fItem)
                                            <tr>
                                                <td><span class="badge badge-primary">{{ $fItem->roll_number }}</span></td>
                                                <td><div class="font-weight-bold text-dark">{{ $fItem->fabric_name }}</div></td>
                                                <td class="text-center">{{ $fItem->batch_no }}</td>
                                                <td class="text-center font-weight-bold" style="font-size: 15px;">{{ number_format($fItem->meter, 2) }} m</td>
                                                <td class="text-right text-muted">₹{{ number_format($fItem->selling_price, 2) }}</td>
                                                <td class="text-right font-weight-bold text-success" style="font-size: 15px;">₹{{ number_format($fItem->meter * $fItem->selling_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const packingBaseUrl = "{{ route('admin.agent-orders.dispatches.download-packing-slip', $dispatch->id) }}";
            const invoiceBaseUrl = "{{ route('admin.agent-orders.dispatches.download-invoice', $dispatch->id) }}";
            const waPackingBaseUrl = "{{ route('admin.agent-orders.dispatches.send-whatsapp-packing-slip', $dispatch->id) }}";
            const waInvoiceBaseUrl = "{{ route('admin.agent-orders.dispatches.send-whatsapp-invoice', $dispatch->id) }}";

            function updateUrls() {
                const val = $('#brandSelect').val();
                let packingUrl = packingBaseUrl;
                let invoiceUrl = invoiceBaseUrl;
                let waPackingUrl = waPackingBaseUrl;
                let waInvoiceUrl = waInvoiceBaseUrl;

                if (val === 'actual') {
                    packingUrl += '?type=actual';
                    invoiceUrl += '?type=actual';
                    waPackingUrl += '?type=actual';
                    waInvoiceUrl += '?type=actual';
                } else if (val) {
                    packingUrl += '?brand_id=' + val;
                    invoiceUrl += '?brand_id=' + val;
                    waPackingUrl += '?brand_id=' + val;
                    waInvoiceUrl += '?brand_id=' + val;
                }

                $('#packingSlipBtn').attr('href', packingUrl);
                $('#invoiceBtn').attr('href', invoiceUrl);
                $('#waPackingSlipBtn').attr('href', waPackingUrl);
                $('#waInvoiceBtn').attr('href', waInvoiceUrl);
            }

            // On change
            $('#brandSelect').on('change', updateUrls);

            // 🔥 IMPORTANT: Run once on page load
            updateUrls();

            // Invoice Modal Calculations
            function calculateInvoice(source) {
                const totalAmount = parseFloat($('#total_amount').val()) || 0;
                const discountAmount = parseFloat($('#discount_amount').val()) || 0;
                const otherCharges = parseFloat($('#other_charges').val()) || 0;
                const taxableAmount = totalAmount - discountAmount;

                let gstPercentage = parseFloat($('#gst_percentage').val()) || 0;
                let gstAmount = parseFloat($('#gst_amount_input').val()) || 0;

                if (source === 'percentage') {
                    gstAmount = taxableAmount * (gstPercentage / 100);
                    $('#gst_amount_input').val(gstAmount.toFixed(2));
                } else if (source === 'amount') {
                    if (taxableAmount > 0) {
                        gstPercentage = (gstAmount / taxableAmount) * 100;
                        $('#gst_percentage').val(gstPercentage.toFixed(6));
                    } else {
                        $('#gst_percentage').val(0);
                    }
                } else {
                    // Default/Other fields changed - update amount from percentage
                    gstAmount = taxableAmount * (gstPercentage / 100);
                    $('#gst_amount_input').val(gstAmount.toFixed(2));
                }

                const grandTotal = taxableAmount + gstAmount + otherCharges;
                $('#grand_total_display').text('₹' + grandTotal.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }

            $('#gst_percentage').on('input', function() {
                calculateInvoice('percentage');
            });

            $('#gst_amount_input').on('input', function() {
                calculateInvoice('amount');
            });

            $('#total_amount, #discount_amount, #other_charges').on('input', function() {
                calculateInvoice('default');
            });

            // Invoice Modal Submission
            $('#editInvoiceForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $(this).find('button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> UPDATING...');

                $.ajax({
                    url: "{{ route('admin.agent-orders.dispatches.update-invoice', $dispatch->id) }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message);
                            btn.prop('disabled', false).text('UPDATE INVOICE');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong. Please try again.');
                        btn.prop('disabled', false).text('UPDATE INVOICE');
                    }
                });
            });
            // Brand selection for print/download buttons
            $('#brandSelect').on('change', function() {
                let brandId = $(this).val();
                let params = brandId !== 'actual' ? '?brand_id=' + brandId : '';

                let packingSlipBase = "{{ route('admin.agent-orders.dispatches.download-packing-slip', $dispatch->id) }}";
                let waPackingSlipBase = "{{ route('admin.agent-orders.dispatches.send-whatsapp-packing-slip', $dispatch->id) }}";
                let invoiceBase = "{{ route('admin.agent-orders.dispatches.download-invoice', $dispatch->id) }}";
                let waInvoiceBase = "{{ route('admin.agent-orders.dispatches.send-whatsapp-invoice', $dispatch->id) }}";

                $('#packingSlipBtn').attr('href', packingSlipBase + params);
                $('#waPackingSlipBtn').attr('href', waPackingSlipBase + params);
                $('#invoiceBtn').attr('href', invoiceBase + params);
                $('#waInvoiceBtn').attr('href', waInvoiceBase + params);
            });
        });
    </script>
@endpush