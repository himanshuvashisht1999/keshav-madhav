@extends('admin.layouts.app')
@section('title', 'Fabric Return Details - ' . ($return->return_number ?? '#' . $return->id))

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-undo-alt text-danger"></i> Fabric Return Voucher: <span style="color: var(--brand-dark-green, #05421c);">{{ $return->return_number ?? '#' . $return->id }}</span>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.fabric_return.download_report', $return->id) }}" class="btn-erp btn-erp-primary" style="background: #dc2626; border-color: #dc2626;">
                <i class="fas fa-file-pdf mr-1"></i> Download PDF
            </a>
            <a href="javascript:void(0)" onclick="confirmDeleteVoucher()" class="btn-erp btn-erp-outline text-danger">
                <i class="fas fa-trash-alt mr-1"></i> Delete & Revert
            </a>
            <a href="{{ route('admin.fabric_return.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to Returns
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-2 p-2" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close p-2" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Metric Cards Strip -->
    <div class="row mb-2">
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div>
                    <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase;">Return Date</div>
                    <div class="font-weight-bold" style="font-size: var(--erp-font-md); color: var(--erp-green-primary);">
                        {{ \Carbon\Carbon::parse($return->date)->format('d M Y') }}
                    </div>
                </div>
                <i class="fas fa-calendar-check fa-lg" style="color: var(--erp-green-primary); opacity: 0.8;"></i>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card py-2 px-3 d-flex align-items-center justify-content-between" style="border-left: 3.5px solid var(--brand-light-green, #8bc63e); background: #fff;">
                <div>
                    <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase;">Returned Rolls</div>
                    <div class="font-weight-bold" style="font-size: var(--erp-font-lg); color: var(--brand-dark-green, #05421c);">
                        {{ $return->details->count() }} Rolls
                    </div>
                </div>
                <i class="fas fa-scroll fa-lg" style="color: var(--brand-light-green, #8bc63e); opacity: 0.85;"></i>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card py-2 px-3 d-flex align-items-center justify-content-between" style="border-left: 3.5px solid var(--brand-wordmark-green, #36b54a); background: #fff;">
                <div>
                    <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase;">Total Meters</div>
                    <div class="font-weight-bold" style="font-size: var(--erp-font-lg); color: var(--brand-dark-green, #05421c);">
                        {{ number_format($return->details->sum('return_meter'), 2) }} M
                    </div>
                </div>
                <i class="fas fa-ruler-horizontal fa-lg" style="color: var(--brand-wordmark-green, #36b54a); opacity: 0.85;"></i>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between">
                <div>
                    <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase;">Total Debit Value</div>
                    <div class="font-weight-bold text-danger" style="font-size: var(--erp-font-lg);">
                        ₹ {{ number_format($return->total_amount, 2) }}
                    </div>
                </div>
                <i class="fas fa-coins fa-lg" style="color: var(--erp-yellow-dark);"></i>
            </div>
        </div>
    </div>

    <!-- Vendor and Financial Info Grid -->
    <div class="row mb-2">
        <!-- Vendor Info -->
        <div class="col-md-7 mb-2">
            <div class="erp-card h-100">
                <div class="erp-card-header bg-light py-2 px-3">
                    <span class="font-weight-bold" style="color: var(--brand-dark-green, #05421c);">
                        <i class="fas fa-user-tie mr-1"></i> Supplier / Vendor Details
                    </span>
                </div>
                <div class="erp-card-body p-3">
                    @php
                        $vendor = $return->vendor ?? ($return->receipt->vendor ?? null);
                    @endphp
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <div class="text-muted small">Vendor Name:</div>
                            <div class="font-weight-bold" style="font-size: var(--erp-font-md);">{{ $vendor->name ?? '-' }}</div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <div class="text-muted small">Phone / Contact:</div>
                            <div class="font-weight-bold">{{ $vendor->phone ?? '-' }}</div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <div class="text-muted small">Email:</div>
                            <div>{{ $vendor->email ?? '-' }}</div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <div class="text-muted small">Address:</div>
                            <div>{{ $vendor->address ?? '-' }}</div>
                        </div>
                        <div class="col-12 mt-1">
                            <div class="text-muted small">Remarks:</div>
                            <div class="font-italic bg-light p-2 rounded border">{{ $return->remarks ?: 'No remarks provided.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="col-md-5 mb-2">
            <div class="erp-card h-100">
                <div class="erp-card-header bg-light py-2 px-3">
                    <span class="font-weight-bold" style="color: var(--brand-dark-green, #05421c);">
                        <i class="fas fa-calculator mr-1"></i> Financial Summary
                    </span>
                </div>
                <div class="erp-card-body p-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Subtotal:</span>
                        <span class="font-weight-bold">₹ {{ number_format($return->sub_total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">GST ({{ (float)$return->gst_percentage }}%):</span>
                        <span class="font-weight-bold">₹ {{ number_format($return->gst_amount, 2) }}</span>
                    </div>
                    @if($return->other_charges > 0)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Other Charges (+):</span>
                        <span class="font-weight-bold">₹ {{ number_format($return->other_charges, 2) }}</span>
                    </div>
                    @endif
                    @if($return->discount > 0)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Discount (-):</span>
                        <span class="font-weight-bold text-success">- ₹ {{ number_format($return->discount, 2) }}</span>
                    </div>
                    @endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center py-2 px-3 rounded" style="background: #fef2f2; border: 1px solid #f87171;">
                        <span class="font-weight-bold text-danger text-uppercase" style="font-size: var(--erp-font-sm);">Grand Total:</span>
                        <span class="font-weight-bold text-danger" style="font-size: var(--erp-font-xl);">
                            ₹ {{ number_format($return->total_amount, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Returned Rolls Items Table -->
    <div class="erp-card">
        <div class="erp-card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
            <span class="font-weight-bold" style="color: var(--brand-dark-green, #05421c);">
                <i class="fas fa-list mr-1"></i> Returned Rolls (Multiple Shipments Breakdown)
            </span>
            <span class="badge px-2 py-1 font-weight-bold" style="background: #eaf7ec; color: var(--brand-dark-green, #05421c); border: 1px solid #b7e3bd;">{{ $return->details->count() }} Rolls</span>
        </div>
        <div class="erp-card-body p-0 table-responsive">
            <table class="erp-table table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="width: 120px;">Roll No.</th>
                        <th style="width: 160px;">Shipment No.</th>
                        <th style="width: 120px;">Bill No.</th>
                        <th>Fabric Name & SKU</th>
                        <th style="width: 140px;">Warehouse</th>
                        <th style="width: 110px;" class="text-right">Return (M)</th>
                        <th style="width: 100px;" class="text-right">Rate / M (₹)</th>
                        <th style="width: 130px;" class="text-right">Total (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $calcSubtotal = 0;
                        $calcMeters = 0;
                    @endphp
                    @foreach($return->details as $index => $detail)
                        @php
                            $receiptDetail = $detail->receipt_detail;
                            $receipt = $receiptDetail->fabric_receipt ?? null;
                            $lineTotal = $detail->return_meter * $detail->price_per_meter;
                            $calcSubtotal += $lineTotal;
                            $calcMeters += $detail->return_meter;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <span class="badge badge-light border text-dark font-weight-bold">
                                    {{ $receiptDetail->roll_number ?? ('#' . $receiptDetail->id) }}
                                </span>
                            </td>
                            <td>
                                @if($receipt)
                                    <span class="font-weight-bold" style="color: var(--brand-dark-green, #05421c);">
                                        {{ $receipt->shipment_id ?: $receipt->sku }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $receipt->bill_no ?? '-' }}</td>
                            <td>
                                <div class="font-weight-bold">{{ $detail->fabric->name ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $detail->fabric->sku ?? '' }}</div>
                            </td>
                            <td class="small text-muted">
                                {{ $receiptDetail->master_fabric_warehouse->cutting_master_name ?? ($receipt->master_fabric_warehouse->cutting_master_name ?? 'N/A') }}
                            </td>
                            <td class="text-right font-weight-bold" style="color: var(--brand-dark-green, #05421c);">
                                {{ number_format($detail->return_meter, 2) }} M
                            </td>
                            <td class="text-right">
                                ₹ {{ number_format($detail->price_per_meter, 2) }}
                            </td>
                            <td class="text-right font-weight-bold text-danger">
                                ₹ {{ number_format($lineTotal, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="erp-table-grand-total">
                        <td colspan="6" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">TOTAL:</td>
                        <td class="text-right font-weight-bold grand-total-val">{{ number_format($calcMeters, 2) }} M</td>
                        <td></td>
                        <td class="text-right font-weight-bold grand-total-val">₹ {{ number_format($calcSubtotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmDeleteVoucher() {
        Swal.fire({
            title: "Delete Fabric Return {{ $return->return_number }}?",
            text: "This will revert returned roll quantities and reverse the vendor ledger entry!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc2626",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete & restore rolls!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.fabric_return.delete', $return->id) }}";
            }
        });
    }
</script>
@endsection
