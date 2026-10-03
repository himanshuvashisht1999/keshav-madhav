@extends('admin.layouts.app')

@section('content')
<style>
    .voucher-card {
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: none;
    }
    .voucher-header-banner {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: #ffffff;
        border-radius: 14px;
        padding: 24px 30px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(30, 60, 114, 0.2);
    }
    .meta-box {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        padding: 12px 18px;
    }
    .meta-box-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: rgba(255, 255, 255, 0.75);
        margin-bottom: 3px;
        font-weight: 600;
    }
    .meta-box-value {
        font-size: 16px;
        font-weight: 700;
        color: #ffffff;
    }
    .items-table thead th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #475569;
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .slip-card-preview {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    .slip-card-preview:hover {
        border-color: #3b82f6;
    }
    .slip-thumb-img {
        max-height: 280px;
        width: 100%;
        object-fit: contain;
        border-radius: 8px;
        cursor: pointer;
    }
    @media print {
        .no-print, .content-header, .main-sidebar, .main-header, .btn {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .voucher-header-banner {
            background: #1e3c72 !important;
            -webkit-print-color-adjust: exact;
            color: white !important;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header no-print">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-dark">
                        <i class="fas fa-file-invoice text-primary mr-2"></i>Consumable Voucher Details
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" onclick="window.print()" class="btn btn-outline-secondary shadow-sm mr-1" style="border-radius: 8px;">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                    <a href="{{ route('admin.payment.voucher.consumable.edit', ['id' => $data->id]) }}" class="btn btn-primary shadow-sm mr-1" style="border-radius: 8px;">
                        <i class="fas fa-edit mr-1"></i> Edit Voucher
                    </a>
                    @if($data->consumable_good_id)
                        <a href="{{ route('admin.ledger.party.show', ['type' => 'consumable good', 'id' => $data->consumable_good_id]) }}" class="btn btn-info shadow-sm mr-1" style="border-radius: 8px;">
                            <i class="fas fa-book-open mr-1"></i> Party Ledger
                        </a>
                    @endif
                    <a href="{{ route('admin.payment.voucher.consumable.index') }}" class="btn btn-outline-dark shadow-sm" style="border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            {{-- TOP BANNER --}}
            <div class="voucher-header-banner">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                        <div class="text-uppercase small font-weight-bold opacity-75 mb-1" style="letter-spacing: 1px;">
                            Consumable Material Voucher
                        </div>
                        <h2 class="font-weight-bold mb-1">
                            {{ $data->consumableGood->name ?? 'Party / Master' }}
                        </h2>
                        <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
                            <span class="badge badge-light text-primary font-weight-bold mr-2 px-2 py-1" style="font-size: 13px;">
                                <i class="fas fa-hashtag mr-1"></i>{{ $data->voucher_number ?: 'Voucher #' . $data->id }}
                            </span>
                            <span class="badge badge-success font-weight-bold mr-2 px-2 py-1" style="font-size: 12px;">
                                <i class="fas fa-check-circle mr-1"></i>Active
                            </span>
                            <span class="text-white-50 small">
                                <i class="fas fa-calendar-alt mr-1"></i>{{ $data->voucher_date ? \Carbon\Carbon::parse($data->voucher_date)->format('d F, Y') : '-' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="row">
                            <div class="col-sm-6 mb-2 mb-sm-0">
                                <div class="meta-box text-center">
                                    <div class="meta-box-label">Party Current Balance</div>
                                    <div class="meta-box-value">
                                        ₹ {{ number_format(abs($data->consumableGood->balance ?? 0), 2) }}
                                        <span class="badge {{ ($data->consumableGood->balance ?? 0) >= 0 ? 'badge-success' : 'badge-danger' }} ml-1" style="font-size: 11px;">
                                            {{ ($data->consumableGood->balance ?? 0) >= 0 ? 'CR' : 'DR' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="meta-box text-center bg-white text-dark border-0 shadow-sm" style="background: #ffffff !important;">
                                    <div class="meta-box-label text-muted">Voucher Total Amount</div>
                                    <div class="meta-box-value text-success" style="font-size: 22px;">
                                        ₹ {{ number_format($data->total_amount, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- LEFT COLUMN: ITEMS TABLE --}}
                <div class="col-lg-8">
                    <div class="card voucher-card mb-4 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="card-title font-weight-bold mb-0 text-dark">
                                <i class="fas fa-list-ul text-primary mr-2"></i>Voucher Line Items
                            </h5>
                            <span class="badge badge-light border text-muted px-2 py-1">
                                {{ count($data->items) }} {{ Str::plural('Item', count($data->items)) }}
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover items-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;" class="pl-4">#</th>
                                            <th>Item Description</th>
                                            <th class="text-right" style="width: 120px;">Quantity</th>
                                            <th class="text-right" style="width: 130px;">Rate (₹)</th>
                                            <th class="text-right pr-4" style="width: 150px;">Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data->items as $index => $item)
                                            <tr>
                                                <td class="pl-4 align-middle text-muted">{{ $index + 1 }}</td>
                                                <td class="align-middle font-weight-bold text-dark">
                                                    {{ $item->item_name }}
                                                </td>
                                                <td class="align-middle text-right font-weight-600">
                                                    {{ number_format($item->quantity, 2) }}
                                                </td>
                                                <td class="align-middle text-right text-muted">
                                                    ₹ {{ number_format($item->rate, 2) }}
                                                </td>
                                                <td class="align-middle text-right font-weight-bold text-dark pr-4">
                                                    ₹ {{ number_format($item->amount, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    No specific line items recorded.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="4" class="text-right font-weight-bold py-2">Sub Total:</td>
                                            <td class="text-right font-weight-bold py-2 pr-4">
                                                ₹ {{ number_format($data->sub_total, 2) }}
                                            </td>
                                        </tr>
                                        @if((float)$data->gst != 0)
                                            <tr>
                                                <td colspan="4" class="text-right text-muted py-2">GST:</td>
                                                <td class="text-right font-weight-600 py-2 pr-4 text-info">
                                                    + ₹ {{ number_format($data->gst, 2) }}
                                                </td>
                                            </tr>
                                        @endif
                                        @if((float)$data->other_charges != 0)
                                            <tr>
                                                <td colspan="4" class="text-right text-muted py-2">Other Charges:</td>
                                                <td class="text-right font-weight-600 py-2 pr-4 text-secondary">
                                                    + ₹ {{ number_format($data->other_charges, 2) }}
                                                </td>
                                            </tr>
                                        @endif
                                        @if((float)$data->round_off != 0)
                                            <tr>
                                                <td colspan="4" class="text-right text-muted py-2">Round Off:</td>
                                                <td class="text-right font-weight-600 py-2 pr-4 text-muted">
                                                    {{ (float)$data->round_off > 0 ? '+ ' : '' }}₹ {{ number_format($data->round_off, 2) }}
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="bg-light border-top" style="border-top: 2px solid #cbd5e1 !important;">
                                            <td colspan="4" class="text-right font-weight-bold py-3 text-dark h6 mb-0">Grand Total:</td>
                                            <td class="text-right font-weight-bold py-3 pr-4 text-success h5 mb-0">
                                                ₹ {{ number_format($data->total_amount, 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- REMARKS CARD --}}
                    @if($data->remarks)
                        <div class="card voucher-card mb-4 shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="font-weight-bold mb-0 text-muted small text-uppercase">
                                    <i class="fas fa-comment-alt mr-2 text-info"></i>Remarks / Narration
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0 text-dark" style="font-size: 14px; line-height: 1.6;">
                                    {{ $data->remarks }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- RIGHT COLUMN: SLIP & DETAILS --}}
                <div class="col-lg-4">
                    {{-- SLIP / DOCUMENT CARD --}}
                    <div class="card voucher-card mb-4 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="font-weight-bold mb-0 text-dark">
                                <i class="fas fa-receipt text-success mr-2"></i>Attached Slip / Bill
                            </h6>
                            @if($data->document)
                                <span class="badge badge-success px-2 py-1"><i class="fas fa-paperclip mr-1"></i>Attached</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1">No Slip</span>
                            @endif
                        </div>
                        <div class="card-body text-center p-3">
                            @if($data->document)
                                @php
                                    $docUrl = asset($data->document);
                                    $isPdf = Str::endsWith(strtolower($data->document), '.pdf');
                                @endphp

                                <div class="slip-card-preview p-2 mb-3">
                                    @if($isPdf)
                                        <div class="py-4">
                                            <i class="fas fa-file-pdf text-danger fa-4x mb-2"></i>
                                            <div class="font-weight-bold text-dark mt-2">PDF Document</div>
                                            <small class="text-muted d-block text-truncate px-3">{{ basename($data->document) }}</small>
                                        </div>
                                    @else
                                        <img src="{{ $docUrl }}" alt="Voucher Slip" class="slip-thumb-img shadow-sm"
                                            onclick="$('#slipModal').modal('show');" title="Click to view full image">
                                    @endif
                                </div>

                                <div class="d-flex justify-content-center flex-wrap gap-2">
                                    @if(!$isPdf)
                                        <button type="button" class="btn btn-sm btn-primary shadow-sm mr-2" data-toggle="modal" data-target="#slipModal">
                                            <i class="fas fa-search-plus mr-1"></i> View Full Slip
                                        </button>
                                    @endif
                                    <a href="{{ $docUrl }}" target="_blank" class="btn btn-sm btn-outline-primary shadow-sm mr-2">
                                        <i class="fas fa-external-link-alt mr-1"></i> Open New Tab
                                    </a>
                                    <a href="{{ $docUrl }}" download class="btn btn-sm btn-outline-success shadow-sm">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                </div>
                            @else
                                <div class="py-4 text-muted">
                                    <i class="fas fa-file-invoice text-muted fa-3x mb-2 opacity-50"></i>
                                    <p class="mb-0 small">No physical slip or document was uploaded for this voucher.</p>
                                    <div class="mt-3">
                                        <a href="{{ route('admin.payment.voucher.consumable.edit', ['id' => $data->id]) }}" class="btn btn-xs btn-outline-primary">
                                            <i class="fas fa-upload mr-1"></i> Upload Slip Now
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- VOUCHER INFO SUMMARY CARD --}}
                    <div class="card voucher-card mb-4 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="font-weight-bold mb-0 text-muted small text-uppercase">
                                <i class="fas fa-info-circle mr-2 text-primary"></i>Summary Information
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted pl-3 py-2 w-50">Voucher ID:</td>
                                        <td class="font-weight-bold text-dark py-2">#{{ $data->id }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-3 py-2">Voucher Number:</td>
                                        <td class="font-weight-bold text-dark py-2">{{ $data->voucher_number ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-3 py-2">Voucher Date:</td>
                                        <td class="font-weight-bold text-dark py-2">
                                            {{ $data->voucher_date ? \Carbon\Carbon::parse($data->voucher_date)->format('d M Y') : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-3 py-2">Party Name:</td>
                                        <td class="font-weight-bold text-primary py-2">
                                            {{ $data->consumableGood->name ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-3 py-2">Created At:</td>
                                        <td class="text-muted small py-2">
                                            {{ $data->created_at ? $data->created_at->format('d M Y, h:i A') : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-3 py-2">Last Updated:</td>
                                        <td class="text-muted small py-2">
                                            {{ $data->updated_at ? $data->updated_at->format('d M Y, h:i A') : '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white border-top text-center py-2">
                            <a href="{{ route('admin.payment.voucher.consumable.edit', ['id' => $data->id]) }}" class="btn btn-sm btn-outline-primary btn-block">
                                <i class="fas fa-edit mr-1"></i> Edit This Voucher
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- MODAL FOR IMAGE SLIP PREVIEW --}}
@if($data->document && !Str::endsWith(strtolower($data->document), '.pdf'))
<div class="modal fade" id="slipModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 900px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-receipt mr-2"></i>Slip - {{ $data->voucher_number ?: 'Voucher #' . $data->id }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-3" style="background: #f1f5f9;">
                <img id="fullSlipImage" src="{{ asset($data->document) }}" alt="Slip Full" class="img-fluid rounded shadow" style="max-height: 75vh; transition: transform 0.3s ease;">
            </div>
            <div class="modal-footer bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="rotateFullSlip()">
                    <i class="fas fa-undo-alt mr-1"></i> Rotate
                </button>
                <div>
                    <a href="{{ asset($data->document) }}" target="_blank" class="btn btn-sm btn-outline-primary mr-1">
                        <i class="fas fa-external-link-alt mr-1"></i> Open in New Tab
                    </a>
                    <a href="{{ asset($data->document) }}" download class="btn btn-sm btn-success mr-2">
                        <i class="fas fa-download mr-1"></i> Download
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
    let fullSlipRotation = 0;
    function rotateFullSlip() {
        fullSlipRotation = (fullSlipRotation + 90) % 360;
        document.getElementById('fullSlipImage').style.transform = 'rotate(' + fullSlipRotation + 'deg)';
    }
</script>
@endsection
