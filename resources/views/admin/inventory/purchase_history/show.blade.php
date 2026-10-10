@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- PAGE HEADER -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title m-0">Purchase Details</h1>
                <p class="erp-page-subtitle m-0">Transaction #{{ $purchase->id }} &bull; Created by {{ $purchase->user->name ?? 'System' }}</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.inventory.purchase_history.download-prn', $purchase->id) }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-barcode mr-1"></i> Download PRN
                </a>
                <a href="{{ route('admin.inventory.purchase_history.edit', $purchase->id) }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <a href="{{ route('admin.inventory.purchase_history.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>

        <!-- HEADER INFO CARD -->
        <div class="erp-card mb-2 p-3">
            <div class="row">
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="erp-label mb-1 text-muted">Source</label>
                    <div class="font-weight-bold text-dark">
                        @if($purchase->vendor_id)
                            <span class="text-primary"><i class="fas fa-truck mr-1"></i>{{ $purchase->vendor->company_name ?? $purchase->vendor->name }}</span>
                        @elseif($purchase->customer_id)
                            <span class="text-success"><i class="fas fa-user-tag mr-1"></i>{{ $purchase->customer->company_name ?? $purchase->customer->name }}</span>
                        @else
                            N/A
                        @endif
                    </div>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="erp-label mb-1 text-muted">Bill / Inv no.</label>
                    <div class="font-weight-bold text-dark">
                        @if($purchase->bill_no)
                            <span class="badge badge-light border px-2 py-1 font-weight-bold">{{ $purchase->bill_no }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="erp-label mb-1 text-muted">Production PO</label>
                    <div class="font-weight-bold text-dark">
                        @if($purchase->productionPO)
                            <span class="badge badge-light border text-primary font-weight-bold px-2 py-1">{{ $purchase->productionPO->po_number }}</span>
                        @else
                            <span class="text-muted">Manual entry</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="erp-label mb-1 text-muted">Date & time</label>
                    <div class="font-weight-bold text-dark">{{ $purchase->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div class="col-md-1 mb-2 mb-md-0">
                    <label class="erp-label mb-1 text-muted">Total items</label>
                    <div class="font-weight-bold text-dark">{{ $purchase->items->count() }} Variants</div>
                </div>
                <div class="col-md-1 mb-2 mb-md-0">
                    <label class="erp-label mb-1 text-muted">Total boxes</label>
                    <div class="font-weight-bold text-primary">{{ $purchase->items->sum('box_quantity') }}</div>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="erp-label mb-1 text-muted">Total pieces</label>
                    <div class="font-weight-bold text-success">{{ $purchase->items->sum(function($item) { return $item->box_quantity * $item->pieces_per_box; }) }}</div>
                </div>
                @if($purchase->remarks)
                <div class="col-12 mt-2 pt-2 border-top">
                    <label class="erp-label mb-1 text-muted"><i class="fas fa-comment-alt text-secondary mr-1"></i> Remarks</label>
                    <div class="font-weight-bold text-dark" style="font-size: 0.9rem;">
                        {{ $purchase->remarks }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- ITEMS TABLE CARD -->
        <div class="erp-card mb-2 p-0">
            <div class="p-2 border-bottom d-flex align-items-center justify-content-between" style="background-color: #f8fafc;">
                <h6 class="m-0 font-weight-bold text-dark" style="font-size: 0.85rem;">
                    <i class="fas fa-boxes mr-1 text-primary"></i> Inward product items
                </h6>
                <span class="badge badge-light border text-dark font-weight-bold">
                    {{ $purchase->items->count() }} Items
                </span>
            </div>
            <div class="table-responsive">
                <table class="erp-table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Product details</th>
                            <th>Style / fitting</th>
                            <th>Location</th>
                            <th>Variation</th>
                            <th style="width: 70px; text-align: center;">Boxes</th>
                            <th style="width: 70px; text-align: center;">Pcs/box</th>
                            <th style="width: 100px; text-align: right;">Rate</th>
                            <th style="width: 120px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                        <tr>
                            <td>
                                <div class="font-weight-bold text-dark">
                                    @if($item->newProduct->series)
                                        <span class="text-muted mr-1">{{ $item->newProduct->series->name }}</span>
                                    @endif
                                    {{ $item->newProduct->name_of_garment }}
                                </div>
                                <div class="small text-muted font-weight-bold">#{{ $item->newProduct->design_number }}</div>
                            </td>
                            <td>
                                <div class="text-dark font-weight-bold">{{ $item->newPattern->name ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $item->newFitting->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="text-dark font-weight-bold">
                                    {{ $item->newWarehouse->name ?? $item->newRack->storeroom->name ?? 'N/A' }}
                                </div>
                                <div class="small text-muted">Rack: {{ $item->newRack->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light border font-weight-bold">{{ $item->newSizeSet->name ?? 'N/A' }}</span>
                                <div class="small text-muted mt-1">{{ $item->newColor->name ?? 'N/A' }}</div>
                            </td>
                            <td class="text-center font-weight-bold">{{ $item->box_quantity }}</td>
                            <td class="text-center">{{ $item->pieces_per_box }}</td>
                            <td class="text-right font-weight-bold">₹{{ number_format($item->purchase_rate, 2) }}</td>
                            <td class="text-right font-weight-bold" style="color: #05421c;">₹{{ number_format($item->box_quantity * $item->pieces_per_box * $item->purchase_rate, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FINANCIAL SUMMARY CARD -->
        <div class="row justify-content-end">
            <div class="col-md-5 col-lg-4">
                <div class="erp-card p-3">
                    <h6 class="font-weight-bold mb-3 text-dark border-bottom pb-2" style="font-size: 0.85rem;">
                        <i class="fas fa-file-invoice-dollar mr-1 text-primary"></i> Financial summary
                    </h6>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="erp-label mb-0 text-muted">Sub total</span>
                        <span class="font-weight-bold text-dark">₹{{ number_format($purchase->sub_total, 2) }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="erp-label mb-0 text-muted">GST ({{ $purchase->gst_type == 'percentage' ? $purchase->gst_value.'%' : 'Flat' }})</span>
                        <span class="text-dark font-weight-bold">+ ₹{{ number_format($purchase->gst, 2) }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="erp-label mb-0 text-muted">Other amount</span>
                        <span class="text-dark font-weight-bold">+ ₹{{ number_format($purchase->other_amount, 2) }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="erp-label mb-0 text-muted">Discount</span>
                        <span class="text-danger font-weight-bold">- ₹{{ number_format($purchase->discount, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between pt-2 border-top mt-2" style="font-size: 1.05rem;">
                        <span class="font-weight-bold text-dark">Grand total</span>
                        <span class="font-weight-bold" style="color: #05421c;">₹{{ number_format($purchase->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
