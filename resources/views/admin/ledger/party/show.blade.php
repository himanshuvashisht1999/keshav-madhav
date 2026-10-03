@extends('admin.layouts.app')

@section('content')
    <style>
        .ledger-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card-detail {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .table-ledger thead th {
            background: #f8f9fa;
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #555;
            border-top: none;
        }

        .text-debit {
            color: #d32f2f;
            font-weight: 600;
        }

        .text-credit {
            color: #2e7d32;
            font-weight: 600;
        }

        .text-balance {
            font-weight: 700;
            color: #1e3c72;
        }

        .date-badge {
            background: #f1f3f5;
            color: #495057;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .particulars-text {
            font-size: 13px;
            line-height: 1.4;
        }

        .type-label {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
        }

        .btn-slip {
            background: #10b981;
            border-color: #10b981;
            color: #fff;
            font-weight: 600;
            border-radius: 6px;
        }

        .btn-slip:hover {
            background: #059669;
            border-color: #059669;
            color: #fff;
        }

        .view-slip-trigger {
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }

        .view-slip-trigger:hover {
            transform: scale(1.05);
        }
    </style>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="ledger-header">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h5 class="text-uppercase mb-1 opacity-75">{{ $type }} Ledger</h5>
                            <h2 class="font-weight-bold mb-0">{{ $party->name }}</h2>
                            <p class="mb-0 opacity-75 mt-2">
                                <i class="fas fa-map-marker-alt mr-2"></i>{{ $party->address ?? 'No Address Provided' }} |
                                <i class="fas fa-phone mr-2"></i>{{ $party->phone ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-7 text-right">
                            <div class="d-flex justify-content-end align-items-center">
                                <div class="bg-white text-dark p-3 px-4 rounded shadow-sm mr-3 text-center border-left border-secondary"
                                    style="min-width: 180px; border-left-width: 4px !important;">
                                    <p class="small text-uppercase font-weight-bold mb-1 text-muted">Opening Balance</p>
                                    <h4 class="mb-0 font-weight-bold">
                                        ₹ {{ number_format(abs($openingBalAmount), 2) }}
                                        <span
                                            class="badge {{ $openingBalAmount >= 0 ? 'badge-success' : 'badge-danger' }} ml-1"
                                            style="font-size: 11px;">{{ $openingBalAmount >= 0 ? 'CR' : 'DR' }}</span>
                                    </h4>
                                </div>
                                <div class="bg-white text-dark p-3 px-4 rounded shadow-sm text-center border-left border-primary"
                                    style="min-width: 180px; border-left-width: 4px !important;">
                                    <p class="small text-uppercase font-weight-bold mb-1 text-muted">Current Balance</p>
                                    <h4 class="mb-0 font-weight-bold text-primary">
                                        ₹ {{ number_format(abs($party->balance), 2) }}
                                        <span
                                            class="badge {{ $party->balance >= 0 ? 'badge-success' : 'badge-danger' }} ml-1"
                                            style="font-size: 11px;">
                                            {{ $party->balance >= 0 ? 'CR' : 'DR' }}
                                        </span>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FILTERS --}}
                <div class="card card-detail mb-4 shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('admin.ledger.party.show', ['type' => $type, 'id' => $party->id]) }}" method="GET">
                            <div class="row">
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <label class="small font-weight-bold text-muted">Start Date</label>
                                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                                        class="form-control" style="border-radius: 8px;">
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <label class="small font-weight-bold text-muted">End Date</label>
                                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                                        class="form-control" style="border-radius: 8px;">
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <label class="small font-weight-bold text-muted">Type</label>
                                    <select name="transaction_type" class="form-control" style="border-radius: 8px;">
                                        <option value="">All Types</option>
                                        @if(isset($availableTypes) && count($availableTypes) > 0)
                                            @foreach($availableTypes as $t)
                                                <option value="{{ $t }}" {{ strcasecmp(request('transaction_type'), $t) === 0 ? 'selected' : '' }}>
                                                    {{ $t }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="Sale" {{ strcasecmp(request('transaction_type'), 'Sale') === 0 ? 'selected' : '' }}>Sale</option>
                                            <option value="Order Dispatch" {{ strcasecmp(request('transaction_type'), 'Order Dispatch') === 0 ? 'selected' : '' }}>Order Dispatch</option>
                                            <option value="Payment" {{ strcasecmp(request('transaction_type'), 'Payment') === 0 ? 'selected' : '' }}>Payment</option>
                                            <option value="Receipt" {{ strcasecmp(request('transaction_type'), 'Receipt') === 0 ? 'selected' : '' }}>Receipt</option>
                                            <option value="Sale Return" {{ strcasecmp(request('transaction_type'), 'Sale Return') === 0 ? 'selected' : '' }}>Sale Return</option>
                                            <option value="Adjustment" {{ strcasecmp(request('transaction_type'), 'Adjustment') === 0 ? 'selected' : '' }}>Adjustment</option>
                                            <option value="Journal Voucher" {{ strcasecmp(request('transaction_type'), 'Journal Voucher') === 0 ? 'selected' : '' }}>Journal Voucher</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <label class="small font-weight-bold text-muted">Adjustment</label>
                                    <select name="adjustment_type" class="form-control" style="border-radius: 8px;">
                                        <option value="">All Adjustments</option>
                                        <option value="payment" {{ request('adjustment_type') === 'payment' ? 'selected' : '' }}>1. Payment (Debit / Paid)</option>
                                        <option value="receipt" {{ request('adjustment_type') === 'receipt' ? 'selected' : '' }}>2. Receipt (Credit / Received)</option>
                                    </select>
                                </div>

                                <div class="col-md-3 col-sm-6 mb-3">
                                    <label class="small font-weight-bold text-muted">2. Ref No.</label>
                                    <input type="text" name="ref_no" value="{{ request('ref_no') }}" placeholder="Search Ref / Voucher / Batch..."
                                        class="form-control" style="border-radius: 8px;">
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <label class="small font-weight-bold text-muted">Debit Value (₹)</label>
                                    <input type="text" name="debit_value" value="{{ request('debit_value') }}" placeholder="Enter Debit Value"
                                        class="form-control" style="border-radius: 8px;">
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <label class="small font-weight-bold text-muted">Credit Value (₹)</label>
                                    <input type="text" name="credit_value" value="{{ request('credit_value') }}" placeholder="Enter Credit Value"
                                        class="form-control" style="border-radius: 8px;">
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <label class="small font-weight-bold text-muted">Slip Attachment</label>
                                    <select name="has_slip" class="form-control" style="border-radius: 8px;">
                                        <option value="">All (With &amp; Without Slip)</option>
                                        <option value="yes" {{ request('has_slip') === 'yes' ? 'selected' : '' }}>With Slip Attached</option>
                                        <option value="no" {{ request('has_slip') === 'no' ? 'selected' : '' }}>Without Slip</option>
                                    </select>
                                </div>

                                @if($type === 'sales_agent')
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <label class="small font-weight-bold text-muted">Filter by Customer</label>
                                        <select name="customer_id" class="form-control" style="border-radius: 8px;" onchange="this.form.submit()">
                                            <option value="">All Customers (Mix Parties)</option>
                                            @foreach($shops as $shop)
                                                <option value="{{ $shop->id }}" {{ request('customer_id') == $shop->id ? 'selected' : '' }}>
                                                    {{ $shop->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if(!request('customer_id'))
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <label class="small font-weight-bold text-muted">View Mode</label>
                                        <select name="view_mode" class="form-control" style="border-radius: 8px;" onchange="this.form.submit()">
                                            <option value="mix" {{ request('view_mode', 'mix') === 'mix' ? 'selected' : '' }}>Mix (Consolidated)</option>
                                            <option value="party_wise" {{ request('view_mode') === 'party_wise' ? 'selected' : '' }}>Party-wise (Grouped)</option>
                                        </select>
                                    </div>
                                    @endif
                                @endif

                                <div class="col-md-3 col-sm-12 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary px-3 mr-1" style="border-radius: 8px;">
                                        <i class="fas fa-filter mr-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.ledger.party.show', ['type' => $type, 'id' => $party->id]) }}"
                                        class="btn btn-outline-secondary mr-1" style="border-radius: 8px;">Reset</a>
                                    <a href="{{ route('admin.ledger.party.download', array_merge(request()->query(), ['type' => $type, 'id' => $party->id])) }}"
                                        class="btn btn-danger px-3 mr-1" style="border-radius: 8px;" title="Download PDF">
                                        <i class="fas fa-file-pdf mr-1"></i> PDF
                                    </a>
                                    <a href="{{ route('admin.ledger.party.export-excel', array_merge(request()->query(), ['type' => $type, 'id' => $party->id])) }}"
                                        class="btn btn-success px-3" style="border-radius: 8px;" title="Export Excel">
                                        <i class="fas fa-file-excel mr-1"></i> Excel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                                {{-- LEDGER TABLE --}}
                @if(isset($viewMode) && $viewMode === 'party_wise' && isset($groupedLedgers))
                    @forelse($groupedLedgers as $ledger)
                        <div class="card card-detail mb-4">
                            <div class="card-header bg-light">
                                <div class="row align-items-center">
                                    <div class="col-sm-6">
                                        <h5 class="font-weight-bold mb-0 text-primary">{{ $ledger->shop->name }}</h5>
                                        <small class="text-muted"><i class="fas fa-phone mr-1"></i>{{ $ledger->shop->phone ?? '-' }} | <i class="fas fa-map-marker-alt mr-1"></i>{{ $ledger->shop->address ?? 'No Address' }}</small>
                                    </div>
                                    <div class="col-sm-6 text-md-right mt-2 mt-md-0">
                                        <span class="mr-3">Opening: <strong>₹ {{ number_format(abs($ledger->opening_balance), 2) }} <span class="badge {{ $ledger->opening_balance >= 0 ? 'badge-success' : 'badge-danger' }}">{{ $ledger->opening_balance >= 0 ? 'CR' : 'DR' }}</span></strong></span>
                                        <span>Closing: <strong>₹ {{ number_format(abs($ledger->closing_balance), 2) }} <span class="badge {{ $ledger->closing_balance >= 0 ? 'badge-success' : 'badge-danger' }}">{{ $ledger->closing_balance >= 0 ? 'CR' : 'DR' }}</span></strong></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-ledger mb-0">
                                        <thead>
                                            <tr>
                                                <th width="12%" class="pl-4">Date</th>
                                                <th width="10%">Type</th>
                                                <th width="12%">Reference</th>
                                                <th>Particulars</th>
                                                <th width="12%" class="text-right">Debit (DR)</th>
                                                <th width="12%" class="text-right">Credit (CR)</th>
                                                <th width="12%" class="text-right">Balance</th>
                                                <th width="12%" class="text-center pr-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $currentBalance = $ledger->opening_balance; @endphp
                                            @forelse($ledger->transactions as $tx)
                                                @php $currentBalance = $tx->running_balance; @endphp
                                                <tr>
                                                    <td class="pl-4 align-middle">
                                                        <span class="date-badge">
                                                             {{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="type-label bg-light border">
                                                            {{ $tx->type }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-muted small font-weight-bold">
                                                        {{ $tx->ref }}
                                                        @if(!empty($tx->slip_url))
                                                            <a href="javascript:void(0)" class="text-success ml-1 view-slip-trigger"
                                                                data-slip-url="{{ $tx->slip_url }}"
                                                                data-ref="{{ $tx->ref ?? '-' }}"
                                                                data-date="{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}"
                                                                data-type="{{ $tx->type ?? '-' }}"
                                                                data-desc="{{ $tx->description ?? '-' }}"
                                                                data-amount="₹ {{ number_format($tx->credit > 0 ? $tx->credit : $tx->debit, 2) }}"
                                                                title="Slip Attached (Click to preview)">
                                                                <i class="fas fa-paperclip"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="particulars-text">{{ $tx->description }}</div>
                                                    </td>
                                                    <td class="align-middle text-right text-debit">
                                                        {{ $tx->debit > 0 ? '₹ ' . number_format($tx->debit, 2) : '-' }}
                                                    </td>
                                                    <td class="align-middle text-right text-credit">
                                                        {{ $tx->credit > 0 ? '₹ ' . number_format($tx->credit, 2) : '-' }}
                                                    </td>
                                                    <td class="align-middle text-right text-balance">
                                                        ₹ {{ number_format(abs($currentBalance), 2) }}
                                                        <small class="text-muted ml-1">{{ $currentBalance >= 0 ? 'CR' : 'DR' }}</small>
                                                    </td>
                                                    <td class="align-middle text-center pr-4 text-nowrap">
                                                        @if(!empty($tx->slip_url))
                                                            <button type="button" class="btn btn-xs btn-success font-weight-bold view-slip-trigger mr-1 shadow-sm px-2"
                                                                data-slip-url="{{ $tx->slip_url }}"
                                                                data-ref="{{ $tx->ref ?? '-' }}"
                                                                data-date="{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}"
                                                                data-type="{{ $tx->type ?? '-' }}"
                                                                data-desc="{{ $tx->description ?? '-' }}"
                                                                data-amount="₹ {{ number_format($tx->credit > 0 ? $tx->credit : $tx->debit, 2) }}"
                                                                title="View Slip Document">
                                                                <i class="fas fa-file-invoice mr-1"></i> Slip
                                                            </button>
                                                        @endif
                                                        @if(isset($tx->view_url) && $tx->view_url !== '#')
                                                            <a href="{{ $tx->view_url }}" class="btn btn-xs btn-outline-primary" title="View Transaction Details" target="_blank">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        @endif
                                                        @if(empty($tx->slip_url) && (!isset($tx->view_url) || $tx->view_url === '#'))
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4 text-muted">
                                                        No transactions recorded for this customer in the selected period.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if(!$ledger->transactions->isEmpty())
                                            <tfoot class="bg-light">
                                                <tr class="font-weight-bold">
                                                    <td colspan="4" class="text-right py-3 pr-4">Totals:</td>
                                                    <td class="text-right py-3 text-debit">₹ {{ number_format($ledger->transactions->sum('debit'), 2) }}</td>
                                                    <td class="text-right py-3 text-credit">₹ {{ number_format($ledger->transactions->sum('credit'), 2) }}</td>
                                                    <td class="text-right py-3 text-primary">
                                                        ₹ {{ number_format(abs($currentBalance), 2) }}
                                                        <small>{{ $currentBalance >= 0 ? 'CR' : 'DR' }}</small>
                                                    </td>
                                                    <td class="bg-light"></td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="card card-detail">
                            <div class="card-body text-center py-5 text-muted">
                                No customers found for this agent.
                            </div>
                        </div>
                    @endforelse
                @else
                <div class="card card-detail">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-ledger mb-0">
                                <thead>
                                    <tr>
                                        <th width="12%" class="pl-4">Date</th>
                                        <th width="10%">Type</th>
                                        <th width="12%">Reference</th>
                                        <th>Particulars</th>
                                        <th width="12%" class="text-right">Debit (DR)</th>
                                        <th width="12%" class="text-right">Credit (CR)</th>
                                        <th width="12%" class="text-right">Balance</th>
                                        <th width="12%" class="text-center pr-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $currentBalance = 0; @endphp
                                    @forelse($transactions as $tx)
                                        @php $currentBalance = $tx->running_balance; @endphp
                                        <tr>
                                            <td class="pl-4 align-middle">
                                                <span class="date-badge">
                                                    {{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <span class="type-label bg-light border">
                                                    {{ $tx->type }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-muted small font-weight-bold">
                                                {{ $tx->ref }}
                                                @if(!empty($tx->slip_url))
                                                    <a href="javascript:void(0)" class="text-success ml-1 view-slip-trigger"
                                                        data-slip-url="{{ $tx->slip_url }}"
                                                        data-ref="{{ $tx->ref ?? '-' }}"
                                                        data-date="{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}"
                                                        data-type="{{ $tx->type ?? '-' }}"
                                                        data-desc="{{ $tx->description ?? '-' }}"
                                                        data-amount="₹ {{ number_format($tx->credit > 0 ? $tx->credit : $tx->debit, 2) }}"
                                                        title="Slip Attached (Click to preview)">
                                                        <i class="fas fa-paperclip"></i>
                                                    </a>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                <div class="particulars-text">{{ $tx->description }}</div>
                                            </td>
                                            <td class="align-middle text-right text-debit">
                                                {{ $tx->debit > 0 ? '₹ ' . number_format($tx->debit, 2) : '-' }}
                                            </td>
                                            <td class="align-middle text-right text-credit">
                                                {{ $tx->credit > 0 ? '₹ ' . number_format($tx->credit, 2) : '-' }}
                                            </td>
                                            <td class="align-middle text-right text-balance">
                                                ₹ {{ number_format(abs($currentBalance), 2) }}
                                                <small class="text-muted ml-1">{{ $currentBalance >= 0 ? 'CR' : 'DR' }}</small>
                                            </td>
                                            <td class="align-middle text-center pr-4 text-nowrap">
                                                @if(!empty($tx->slip_url))
                                                    <button type="button" class="btn btn-xs btn-success font-weight-bold view-slip-trigger mr-1 shadow-sm px-2"
                                                        data-slip-url="{{ $tx->slip_url }}"
                                                        data-ref="{{ $tx->ref ?? '-' }}"
                                                        data-date="{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}"
                                                        data-type="{{ $tx->type ?? '-' }}"
                                                        data-desc="{{ $tx->description ?? '-' }}"
                                                        data-amount="₹ {{ number_format($tx->credit > 0 ? $tx->credit : $tx->debit, 2) }}"
                                                        title="View Slip Document">
                                                        <i class="fas fa-file-invoice mr-1"></i> Slip
                                                    </button>
                                                @endif
                                                @if(isset($tx->view_url) && $tx->view_url !== '#')
                                                    <a href="{{ $tx->view_url }}" class="btn btn-xs btn-outline-primary" title="View Transaction Details" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                                @if(empty($tx->slip_url) && (!isset($tx->view_url) || $tx->view_url === '#'))
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                No transactions recorded for the selected period.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(!$transactions->isEmpty())
                                    <tfoot class="bg-light">
                                        <tr class="font-weight-bold">
                                            <td colspan="4" class="text-right py-3 pr-4">Totals:</td>
                                            <td class="text-right py-3 text-debit">₹
                                                {{ number_format($transactions->sum('debit'), 2) }}</td>
                                            <td class="text-right py-3 text-credit">₹
                                                {{ number_format($transactions->sum('credit'), 2) }}</td>
                                            <td class="text-right py-3 text-primary">
                                                ₹ {{ number_format(abs($currentBalance), 2) }}
                                                <small>{{ $currentBalance >= 0 ? 'CR' : 'DR' }}</small>
                                            </td>
                                            <td class="bg-light"></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </section>

        {{-- SLIP PREVIEW MODAL --}}
        <div class="modal fade" id="slipPreviewModal" tabindex="-1" role="dialog" aria-labelledby="slipPreviewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 900px;">
                <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                        <div>
                            <h5 class="modal-title font-weight-bold mb-1" id="slipPreviewModalLabel">
                                <i class="fas fa-file-invoice mr-2"></i>Slip Document Preview
                            </h5>
                            <div class="small opacity-75">
                                <span id="modalSlipRef" class="badge badge-light text-primary font-weight-bold mr-2"></span>
                                <span id="modalSlipType" class="badge badge-warning text-dark font-weight-bold mr-2"></span>
                                <span id="modalSlipDate" class="text-white mr-2"></span>
                                <span id="modalSlipAmount" class="badge badge-success font-weight-bold"></span>
                            </div>
                        </div>
                        <button type="button" class="close text-white opacity-75" data-dismiss="modal" aria-label="Close" style="outline: none;">
                            <span aria-hidden="true" style="font-size: 28px;">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-3 bg-light text-center" style="min-height: 400px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                        <div id="modalSlipDescriptionBox" class="w-100 text-left mb-2 px-2 py-1 bg-white rounded border small text-muted font-italic">
                            <i class="fas fa-info-circle mr-1 text-primary"></i> <span id="modalSlipDesc"></span>
                        </div>

                        <div id="slipMediaContainer" class="w-100 position-relative" style="background: #f1f5f9; border-radius: 12px; border: 1px dashed #cbd5e1; padding: 15px; min-height: 450px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <img id="slipImagePreview" src="" alt="Slip Document" class="img-fluid rounded shadow-sm" style="max-height: 65vh; max-width: 100%; object-fit: contain; transition: transform 0.3s ease; display: none;">
                            <iframe id="slipPdfPreview" src="" style="width: 100%; height: 65vh; border: none; border-radius: 8px; display: none;"></iframe>
                            <div id="slipLoadingIndicator" class="text-muted text-center py-5">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <div>Loading Slip Document...</div>
                            </div>
                            <div id="slipErrorFallback" class="text-center py-4 px-3" style="display: none;">
                                <i class="fas fa-file-invoice text-muted fa-4x mb-3"></i>
                                <h6 class="font-weight-bold text-dark">Document Preview</h6>
                                <p class="text-muted small mb-3">Direct inline preview is not supported for this file type or the file is hosted externally.</p>
                                <a id="slipFallbackDirectBtn" href="#" target="_blank" class="btn btn-primary btn-sm px-3 shadow-sm mr-2">
                                    <i class="fas fa-external-link-alt mr-1"></i> Open Document Directly
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white d-flex justify-content-between py-2 px-3">
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="slipRotateBtn" title="Rotate Image 90°">
                                <i class="fas fa-undo-alt mr-1"></i> Rotate
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary ml-1" id="slipZoomBtn" title="Reset / Toggle Zoom">
                                <i class="fas fa-search-plus mr-1"></i> Zoom
                            </button>
                        </div>
                        <div>
                            <a id="slipOpenTabBtn" href="#" target="_blank" class="btn btn-sm btn-outline-primary mr-1">
                                <i class="fas fa-external-link-alt mr-1"></i> Open Full View
                            </a>
                            <a id="slipDownloadBtn" href="#" download class="btn btn-sm btn-success mr-2">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let currentRotation = 0;
        let isZoomed = false;

        $(document).on('click', '.view-slip-trigger', function(e) {
            e.preventDefault();
            const btn = $(this);
            const slipUrl = btn.data('slip-url');
            const ref = btn.data('ref') || '-';
            const date = btn.data('date') || '';
            const type = btn.data('type') || '';
            const desc = btn.data('desc') || '';
            const amount = btn.data('amount') || '';

            $('#modalSlipRef').text(ref);
            $('#modalSlipDate').text(date ? 'Date: ' + date : '');
            $('#modalSlipType').text(type);
            $('#modalSlipAmount').text(amount);
            $('#modalSlipDesc').text(desc);

            $('#slipOpenTabBtn').attr('href', slipUrl);
            $('#slipDownloadBtn').attr('href', slipUrl);
            $('#slipFallbackDirectBtn').attr('href', slipUrl);

            // Reset transform & state
            currentRotation = 0;
            isZoomed = false;
            const img = $('#slipImagePreview');
            const pdf = $('#slipPdfPreview');
            const loader = $('#slipLoadingIndicator');
            const fallback = $('#slipErrorFallback');

            img.css('transform', 'none').hide();
            pdf.hide().attr('src', '');
            fallback.hide();
            loader.show();

            const isPdf = slipUrl.toLowerCase().endsWith('.pdf') || slipUrl.toLowerCase().includes('.pdf?');

            if (isPdf) {
                $('#slipRotateBtn, #slipZoomBtn').hide();
                loader.hide();
                pdf.attr('src', slipUrl).show();
            } else {
                $('#slipRotateBtn, #slipZoomBtn').show();
                img.off('load error').on('load', function() {
                    loader.hide();
                    img.show();
                }).on('error', function() {
                    loader.hide();
                    img.hide();
                    fallback.show();
                });
                img.attr('src', slipUrl);
            }

            $('#slipPreviewModal').modal('show');
        });

        $('#slipRotateBtn').on('click', function() {
            currentRotation = (currentRotation + 90) % 360;
            applyTransform();
        });

        $('#slipZoomBtn').on('click', function() {
            isZoomed = !isZoomed;
            applyTransform();
        });

        function applyTransform() {
            const scale = isZoomed ? 'scale(1.5)' : 'scale(1)';
            const rotate = 'rotate(' + currentRotation + 'deg)';
            $('#slipImagePreview').css('transform', rotate + ' ' + scale);
        }
    });
</script>
@endsection