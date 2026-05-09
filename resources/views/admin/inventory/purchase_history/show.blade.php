@extends('admin.layouts.app')

@section('content')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --bg-main: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        .content-wrapper { background-color: var(--bg-main); font-family: 'Inter', sans-serif; }
        .premium-page-header { padding: 1.5rem 0; background: #fff; border-bottom: 1px solid #e2e8f0; margin-bottom: 2rem; }
        .page-title { font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin: 0; }
        .card-premium { border: none; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); overflow: hidden; background: #fff; margin-bottom: 2rem; }
        .detail-label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
        .detail-value { font-size: 1rem; font-weight: 600; color: var(--text-main); }
        .table-premium thead th { background: #f8fafc; text-transform: uppercase; font-size: 0.7rem; font-weight: 700; color: var(--text-muted); border: none; padding: 1rem; }
        .table-premium td { padding: 1rem; font-size: 0.875rem; border-color: #f1f5f9; vertical-align: middle; }
        .badge-soft-info { background: #eff6ff; color: #2563eb; }
        .summary-box { background: #f8fafc; border-radius: 0.75rem; padding: 1.5rem; }
        .total-row { font-size: 1.1rem; font-weight: 800; color: #4f46e5; border-top: 2px solid #e2e8f0; padding-top: 1rem; margin-top: 1rem; }
    </style>

    <div class="content-wrapper">
        <div class="premium-page-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">Purchase Details</h1>
                        <p class="text-muted mb-0 small">Transaction #{{ $purchase->id }} | Created by {{ $purchase->user->name ?? 'System' }}</p>
                    </div>
                    <div class="d-flex" style="gap: 10px;">
                        <a href="{{ route('admin.inventory.purchase_history.download-prn', $purchase->id) }}" class="btn btn-dark px-4" style="border-radius: 0.5rem; font-weight: 600;">
                            <i class="fas fa-barcode mr-2"></i>Download PRN
                        </a>
                        <a href="{{ route('admin.inventory.purchase_history.edit', $purchase->id) }}" class="btn btn-soft-primary px-4" style="border-radius: 0.5rem; font-weight: 600;">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                        <a href="{{ route('admin.inventory.purchase_history.index') }}" class="btn btn-outline-secondary px-4" style="border-radius: 0.5rem; font-weight: 600;">
                            <i class="fas fa-arrow-left mr-2"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Header Info -->
                <div class="card card-premium">
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="detail-label">Source</div>
                                <div class="detail-value">
                                    @if($purchase->vendor_id)
                                        <span class="text-primary"><i class="fas fa-truck mr-2"></i>{{ $purchase->vendor->company_name ?? $purchase->vendor->name }}</span>
                                    @elseif($purchase->customer_id)
                                        <span class="text-success"><i class="fas fa-user-tag mr-2"></i>{{ $purchase->customer->company_name ?? $purchase->customer->name }}</span>
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="detail-label">Production PO</div>
                                <div class="detail-value">
                                    @if($purchase->productionPO)
                                        <span class="badge badge-soft-info px-3 py-2" style="font-size: 0.9rem;">{{ $purchase->productionPO->po_number }}</span>
                                    @else
                                        <span class="text-muted italic">Manual Entry</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="detail-label">Date & Time</div>
                                <div class="detail-value">{{ $purchase->created_at->format('d M Y, h:i A') }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="detail-label">Total Items</div>
                                <div class="detail-value">{{ $purchase->items->count() }} Variants</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="card card-premium">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-premium mb-0">
                                <thead>
                                    <tr>
                                        <th class="pl-4">Product Details</th>
                                        <th>Style / Fitting</th>
                                        <th>Location</th>
                                        <th>Variation</th>
                                        <th class="text-center">Boxes</th>
                                        <th class="text-center">Pcs/Box</th>
                                        <th class="text-right">Rate</th>
                                        <th class="text-right pr-4">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->items as $item)
                                    <tr>
                                        <td class="pl-4">
                                            <div class="font-weight-bold text-dark">
                                                @if($item->newProduct->series)
                                                    <span class="text-muted mr-1">{{ $item->newProduct->series->name }}</span>
                                                @endif
                                                {{ $item->newProduct->name_of_garment }}
                                            </div>
                                            <div class="small text-muted">#{{ $item->newProduct->design_number }}</div>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $item->newPattern->name ?? 'N/A' }}</div>
                                            <div class="small text-muted">{{ $item->newFitting->name ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            <div class="text-dark">
                                                {{ $item->newWarehouse->name ?? $item->newRack->storeroom->name ?? 'N/A' }}
                                            </div>
                                            <div class="small text-muted">Rack: {{ $item->newRack->name ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            <div class="badge badge-soft-info">{{ $item->newSizeSet->name ?? 'N/A' }}</div>
                                            <div class="small text-muted mt-1">{{ $item->newColor->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="text-center font-weight-bold">{{ $item->box_quantity }}</td>
                                        <td class="text-center">{{ $item->pieces_per_box }}</td>
                                        <td class="text-right">₹{{ number_format($item->purchase_rate, 2) }}</td>
                                        <td class="text-right pr-4 font-weight-bold text-primary">₹{{ number_format($item->box_quantity * $item->pieces_per_box * $item->purchase_rate, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="row justify-content-end">
                    <div class="col-md-4">
                        <div class="card card-premium">
                            <div class="card-body p-4 summary-box">
                                <h6 class="font-weight-bold mb-4 text-dark"><i class="fas fa-file-invoice-dollar mr-2"></i>Financial Summary</h6>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Sub Total</span>
                                    <span class="font-weight-bold">₹{{ number_format($purchase->sub_total, 2) }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">GST ({{ $purchase->gst_type == 'percentage' ? $purchase->gst_value.'%' : 'Flat' }})</span>
                                    <span class="text-dark font-weight-bold">+ ₹{{ number_format($purchase->gst, 2) }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Other Amount</span>
                                    <span class="text-dark font-weight-bold">+ ₹{{ number_format($purchase->other_amount, 2) }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Discount</span>
                                    <span class="text-danger font-weight-bold">- ₹{{ number_format($purchase->discount, 2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between total-row">
                                    <span>Grand Total</span>
                                    <span>₹{{ number_format($purchase->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
