@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

        {{-- HEADER BAR --}}
        <div class="erp-header-bar mb-3">
            <div>
                <h1 class="erp-header-title">Outflows & Adjustments</h1>
            </div>
            <div class="erp-report-meta">
                Date : {{ now()->format('d M Y h:i A') }}
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- FILTERS CARD --}}
        <div class="erp-card mb-3">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('admin.reports.outflows') }}">
                    <div class="row align-items-end">
                        <!-- Type -->
                        <div class="col-md-3 mb-2">
                            <label class="erp-form-label">Adjustment Type</label>
                            <select name="type" class="form-control select2">
                                <option value="">All Outflow Types</option>
                                <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
                                <option value="sampling" {{ request('type') === 'sampling' ? 'selected' : '' }}>Sampling</option>
                                <option value="dead" {{ request('type') === 'dead' ? 'selected' : '' }}>Dead / Damage</option>
                            </select>
                        </div>
                        <!-- Lot No -->
                        <div class="col-md-2 mb-2">
                            <label class="erp-form-label">Lot Number</label>
                            <input type="text" name="lot_no" value="{{ request('lot_no') }}" class="form-control" placeholder="Search Lot Number">
                        </div>
                        <!-- Design No -->
                        <div class="col-md-2 mb-2">
                            <label class="erp-form-label">Design Number</label>
                            <select name="design_no" class="form-control select2">
                                <option value="">All Designs</option>
                                @foreach($designs as $design)
                                    <option value="{{ $design }}" {{ request('design_no') == $design ? 'selected' : '' }}>{{ $design }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Slip ID -->
                        <div class="col-md-2 mb-2">
                            <label class="erp-form-label">Slip Reference ID</label>
                            <input type="number" name="slip_id" value="{{ request('slip_id') }}" class="form-control" placeholder="Search Slip ID">
                        </div>
                        <!-- Responsible Unit -->
                        <div class="col-md-3 mb-2">
                            <label class="erp-form-label">Responsible Unit</label>
                            <select name="unit_id" class="form-control select2">
                                <option value="">All Units</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Date Range -->
                        <div class="col-md-3 mb-2">
                            <label class="erp-form-label">Start Date</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="erp-form-label">End Date</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-6 mb-2 d-flex justify-content-end align-items-center" style="gap: 8px;">
                            <button type="submit" class="btn-erp btn-erp-primary">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                            <a href="{{ route('admin.reports.outflows') }}" class="btn-erp btn-erp-secondary">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                            <a href="{{ route('admin.reports.outflows.export', request()->all()) }}" class="btn-erp btn-erp-success" title="Export Excel">
                                <i class="fas fa-file-excel mr-1"></i> Export Excel
                            </a>
                            <a href="{{ route('admin.reports.outflows.pdf', request()->all()) }}" class="btn-erp btn-erp-danger" title="Export PDF">
                                <i class="fas fa-file-pdf mr-1"></i> Export PDF
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <button type="button" class="btn-erp btn-erp-warning" id="btnDisposeSelected">
                <i class="fas fa-trash-alt mr-1"></i> Dispose Selected
            </button>
            <a href="{{ route('admin.reports.outflows.history') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-history mr-1"></i> View Dispose History
            </a>
        </div>

        {{-- RESULTS CARD --}}
        <div class="erp-card">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                    <span class="text-muted small">Report Date: <strong>{{ now()->format('d M Y') }}</strong></span>
                    <span class="erp-stat-pill">Grand Total Quantity: <strong>{{ number_format($totalQuantity) }} pcs</strong></span>
                </div>
                <div class="table-responsive">
                    <table class="erp-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px;" class="text-center">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th style="width: 60px;" class="text-center">Sr. No.</th>
                                <th>Log Date</th>
                                <th>Slip ID</th>
                                <th>Lot Number</th>
                                <th>Design Number</th>
                                <th>Type</th>
                                <th>Size & Color</th>
                                <th class="text-right">Outflow Quantity</th>
                                <th>Responsible Unit</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outflows as $out)
                                @php
                                    $badgeClass = 'erp-badge-status-info';
                                    if ($out->type === 'debit') $badgeClass = 'erp-badge-status-danger';
                                    if ($out->type === 'sampling') $badgeClass = 'erp-badge-status-open';
                                    if ($out->type === 'dead') $badgeClass = 'erp-badge-status-neutral';
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="row-checkbox" value="{{ $out->id }}" data-type="{{ $out->type }}" data-amount="{{ $out->total_amount ?? 0 }}">
                                    </td>
                                    <td class="text-center text-muted font-weight-bold">
                                        {{ $loop->iteration + ($outflows->currentPage() - 1) * $outflows->perPage() }}
                                    </td>
                                    <td>
                                        {{ date('d-m-Y', strtotime($out->created_at)) }}
                                    </td>
                                    <td class="font-weight-bold">
                                        <a href="{{ route('admin.packing.view', $out->slip->packingMain->id ?? '#') }}" target="_blank">
                                            #{{ $out->slip_id }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border">LOT-{{ str_pad($out->lot_no, 4, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $out->product->design_number ?? 'N/A' }}</strong>
                                        <span class="d-block text-muted small">{{ $out->product->name ?? 'Garment' }}</span>
                                    </td>
                                    <td>
                                        <span class="erp-badge-status {{ $badgeClass }} text-uppercase">{{ str_replace('_', ' ', $out->type) }}</span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">Size: {{ $out->size->size ?? 'N/A' }}</div>
                                        <div class="text-muted small">Color: {{ $out->color->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        {{ $out->quantity }} <span class="text-muted small font-weight-normal">PCS</span>
                                    </td>
                                    <td>
                                        {{ $out->responsibleUnit->name ?? 'N/A' }}
                                    </td>
                                    <td class="text-muted small" style="max-width: 200px; white-space: normal; word-break: break-all;">
                                        {{ $out->remarks ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-muted py-5 text-center">
                                        <h5>No Outflow Adjustments Found</h5>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- PAGINATION --}}
                @if($outflows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                    <div class="card-footer bg-white border-top py-2 d-flex justify-content-end">
                        {{ $outflows->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="disposeModal" tabindex="-1" role="dialog" aria-labelledby="disposeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="disposeForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="disposeModalLabel">Dispose Outflows</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning" id="disposeWarning" style="display: none;"></div>
                        
                        <div id="debitFields" style="display: none;">
                            <div class="form-group">
                                <label for="masterType">Master Type</label>
                                <select class="form-control" id="masterType" name="master_type">
                                    <option value="">-- Select Master Type --</option>
                                    @foreach($masters as $master)
                                        <option value="{{ $master->id }}">{{ $master->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="subItem">Select Item</label>
                                <select class="form-control" id="subItem" name="sub_item">
                                    <option value="">-- Select Item --</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="debitAmount">Amount</label>
                                <input type="number" class="form-control" id="debitAmount" name="amount" step="0.01">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="disposeReason">Reason / Remarks</label>
                            <textarea class="form-control" id="disposeReason" name="reason" rows="3" required placeholder="Enter reason here..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitDispose">Confirm Dispose</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            function updateCheckboxState() {
                let hasDebitChecked = $('.row-checkbox:checked[data-type="debit"]').length > 0;
                let hasNonDebitChecked = $('.row-checkbox:checked[data-type!="debit"]').length > 0;

                if (hasDebitChecked) {
                    $('.row-checkbox[data-type!="debit"]').prop('disabled', true);
                    $('#selectAll').prop('disabled', true); // Disable select all to prevent mixing
                } else if (hasNonDebitChecked) {
                    $('.row-checkbox[data-type="debit"]').prop('disabled', true);
                    $('#selectAll').prop('disabled', true);
                } else {
                    $('.row-checkbox').prop('disabled', false);
                    $('#selectAll').prop('disabled', false);
                }
            }

            $('#selectAll').change(function() {
                if (!$(this).prop('disabled')) {
                    $('.row-checkbox:not([disabled])').prop('checked', $(this).prop('checked'));
                    updateCheckboxState();
                }
            });

            $('.row-checkbox').change(function() {
                if ($('.row-checkbox:checked').length === $('.row-checkbox:not([disabled])').length && $('.row-checkbox:checked').length > 0) {
                    $('#selectAll').prop('checked', true);
                } else {
                    $('#selectAll').prop('checked', false);
                }
                updateCheckboxState();
            });

            $('#masterType').change(function() {
                let masterId = $(this).val();
                $('#subItem').html('<option value="">-- Loading... --</option>');
                if (masterId) {
                    $.ajax({
                        url: "{{ route('admin.payment.adjustment.getSubMastersAll') }}",
                        type: "GET",
                        success: function(res) {
                            let options = '<option value="">-- Select Item --</option>';
                            let filtered = res.filter(item => item.master_id == masterId);
                            filtered.forEach(function(item) {
                                options += `<option value="${item.id}">${item.name}</option>`;
                            });
                            $('#subItem').html(options);
                        }
                    });
                } else {
                    $('#subItem').html('<option value="">-- Select Item --</option>');
                }
            });

            $('#btnDisposeSelected').click(function() {
                let selectedIds = [];
                let hasDebit = false;
                let totalAmount = 0;
                
                $('.row-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                    if ($(this).data('type') === 'debit') {
                        hasDebit = true;
                        totalAmount += parseFloat($(this).data('amount')) || 0;
                    }
                });

                if (selectedIds.length === 0) {
                    Swal.fire('Warning', 'Please select at least one record to dispose.', 'warning');
                    return;
                }

                if (hasDebit) {
                    $('#debitFields').show();
                    $('#masterType').prop('required', true);
                    $('#subItem').prop('required', true);
                    $('#debitAmount').prop('required', true).val(totalAmount.toFixed(2));
                } else {
                    $('#debitFields').hide();
                    $('#masterType').prop('required', false);
                    $('#subItem').prop('required', false);
                    $('#debitAmount').prop('required', false);
                }

                $('#disposeReason').val('');
                $('#disposeModal').modal('show');
            });

            $('#disposeForm').submit(function(e) {
                e.preventDefault();
                let selectedIds = [];
                let hasDebit = false;
                $('.row-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                    if ($(this).data('type') === 'debit') {
                        hasDebit = true;
                    }
                });

                let data = {
                    _token: "{{ csrf_token() }}",
                    outflow_ids: selectedIds,
                    reason: $('#disposeReason').val(),
                    is_debit: hasDebit ? 1 : 0
                };

                if (hasDebit) {
                    data.adjustment_master_id = $('#masterType').val();
                    data.ref_id = $('#subItem').val();
                    data.amount = $('#debitAmount').val();
                }

                $.ajax({
                    url: "{{ route('admin.reports.outflows.dispose') }}",
                    type: "POST",
                    data: data,
                    success: function(res) {
                        if (res.success) {
                            $('#disposeModal').modal('hide');
                            Swal.fire('Success', res.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function(err) {
                        Swal.fire('Error', 'An error occurred while disposing.', 'error');
                    }
                });
            });
        });
    </script>
@endsection
