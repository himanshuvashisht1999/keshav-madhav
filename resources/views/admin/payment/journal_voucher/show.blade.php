@extends('admin.layouts.app')

@section('content')
<style>
    .voucher-card {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: none;
    }
    .voucher-header {
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 25px;
    }
    .voucher-no {
        font-size: 24px;
        font-weight: 800;
        letter-spacing: 1px;
    }
    .label-muted {
        color: rgba(255,255,255,0.7);
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 600;
    }
    .table thead th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #64748b;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .total-row {
        background: #f1f5f9;
        font-weight: 700;
    }
    .narration-box {
        background: #f8fafc;
        border-left: 4px solid #3b82f6;
        padding: 15px;
        border-radius: 4px;
        font-style: italic;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Journal Voucher Details</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.payment.journal-voucher.download', $voucher->id) }}" class="btn btn-danger shadow-sm mr-2" style="border-radius: 20px;">
                        <i class="fas fa-file-pdf mr-2"></i>Download PDF
                    </a>
                    <a href="{{ route('admin.payment.journal-voucher.index') }}" class="btn btn-light shadow-sm" style="border-radius: 20px;">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card voucher-card">
                <div class="voucher-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="label-muted">Voucher Number</span>
                            <div class="voucher-no">{{ $voucher->voucher_no }}</div>
                        </div>
                        <div class="col-md-3">
                            <span class="label-muted">Voucher Date</span>
                            <div class="h5 mb-0 font-weight-bold">{{ date('d M Y', strtotime($voucher->date)) }}</div>
                        </div>
                        <div class="col-md-3 text-md-right">
                            <span class="label-muted">Total Amount</span>
                            <div class="h3 mb-0 font-weight-bold">₹ {{ number_format($voucher->total_debit, 2) }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">#</th>
                                    <th>Master Type</th>
                                    <th>Party / Account Name</th>
                                    <th>Narration</th>
                                    <th class="text-right">Debit (DR)</th>
                                    <th class="text-right pr-4">Credit (CR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($voucher->items as $index => $item)
                                <tr>
                                    <td class="pl-4 text-muted">{{ $index + 1 }}</td>
                                    <td><span class="badge badge-light border">{{ $item->master_name }}</span></td>
                                    <td class="font-weight-bold text-dark">{{ $item->party_name }}</td>
                                    <td class="text-muted small">{{ $item->narration ?? '-' }}</td>
                                    <td class="text-right text-success font-weight-bold">
                                        {{ $item->type == 'debit' ? '₹ ' . number_format($item->amount, 2) : '-' }}
                                    </td>
                                    <td class="text-right text-danger font-weight-bold pr-4">
                                        {{ $item->type == 'credit' ? '₹ ' . number_format($item->amount, 2) : '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td colspan="4" class="text-right pl-4">TOTALS</td>
                                    <td class="text-right text-success">₹ {{ number_format($voucher->total_debit, 2) }}</td>
                                    <td class="text-right text-danger pr-4">₹ {{ number_format($voucher->total_credit, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                @if($voucher->narration)
                <div class="card-body border-top">
                    <h6 class="font-weight-bold text-muted small text-uppercase mb-2">Overall Narration</h6>
                    <div class="narration-box text-dark">
                        {{ $voucher->narration }}
                    </div>
                </div>
                @endif
                
                <div class="card-footer bg-white border-0 py-3">
                    <div class="row align-items-center">
                        <div class="col-sm-6 text-muted small">
                            <i class="fas fa-clock mr-1"></i> Created on {{ $voucher->created_at->format('d M Y, h:i A') }}
                        </div>
                        <div class="col-sm-6 text-right">
                            <a href="{{ route('admin.payment.journal-voucher.edit', $voucher->id) }}" class="btn btn-info px-4 shadow-sm" style="border-radius: 20px;">
                                <i class="fas fa-edit mr-2"></i>Edit Voucher
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
