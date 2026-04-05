@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Fabric Shipment Payment</h1>
                        <small class="text-muted">Record payments for fabric shipments</small>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Fabric Shipment Payment</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title">Create Payment</h3>
                            </div>
                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle mr-2"></i> <strong>Please fix the
                                            following errors:</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                                <form action="{{ route('admin.payment.fabric-shipment.store') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <label for="vendor_id" class="form-label font-weight-bold">Select Vendor</label>
                                            <select class="form-control select2" name="vendor_id" id="vendor_id" required>
                                                <option value="">Select Vendor</option>
                                                @foreach($vendors as $vendor)
                                                    <option value="{{ $vendor->id }}" {{ (isset($selectedVendorId) && $selectedVendorId == $vendor->id) ? 'selected' : '' }}>
                                                        {{ $vendor->name }} ({{ $vendor->phone }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div id="shipment_section" style="display: none;">
                                        <h5 class="mb-3 border-bottom pb-2">Unpaid Shipments</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="5%">Select</th>
                                                        <th>Date</th>
                                                        <th>Shipment No</th>
                                                        <th>Total Amount</th>
                                                        <th>Paid</th>
                                                        <th>Balance</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="shipments_table_body">
                                                    <!-- Populated via AJAX -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div id="payment_section" class="mt-4" style="display: none;">
                                        <h5 class="mb-3 border-bottom pb-2">Payment Details</h5>

                                        <div class="card bg-light mb-4 border-left-primary">
                                            <div class="card-body" id="selected_shipment_details">
                                                <!-- Details of selected shipment -->
                                            </div>
                                        </div>

                                        <div class="row">
                                            <input type="hidden" name="fabric_receipt_id" id="fabric_receipt_id">

                                            <div class="col-md-4 mb-3">
                                                <label for="amount" class="form-label">Payment Amount <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">₹</span>
                                                    </div>
                                                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" name="amount"
                                                        id="amount" value="{{ old('amount') }}" required>
                                                </div>
                                                @error('amount')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label for="payment_date" class="form-label">Payment Date <span
                                                        class="text-danger">*</span></label>
                                                <input type="date" class="form-control @error('payment_date') is-invalid @enderror" name="payment_date"
                                                    id="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                                @error('payment_date')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label for="payment_mode" class="form-label">Payment Mode <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control @error('payment_mode') is-invalid @enderror" name="payment_mode" id="payment_mode" required>
                                                    <option value="">Select Mode</option>
                                                    <option value="Bank" {{ old('payment_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
                                                    <option value="Cash" {{ old('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                                </select>
                                                @error('payment_mode')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mb-3" id="bank_account_div" style="display:none;">
                                                <label for="bank_account_id" class="form-label">Bank Account <span
                                                        class="text-danger">*</span></label>
                                                <select name="payment_method_id" id="bank_account_id"
                                                    class="form-control select2 @error('payment_method_id') is-invalid @enderror" style="width: 100%;">
                                                    <option value="">Select Bank Account</option>
                                                    @foreach($bank_accounts as $bank)
                                                        <option value="{{ $bank->id }}" {{ old('payment_method_id') == $bank->id ? 'selected' : '' }}>{{ $bank->bank_name }}
                                                            ({{ $bank->account_number }}) - Bal:
                                                            ₹{{ number_format($bank->balance, 2) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('payment_method_id')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mb-3" id="cash_account_div" style="display:none;">
                                                <label for="cash_account_id" class="form-label">Cash Account <span
                                                        class="text-danger">*</span></label>
                                                <select name="payment_method_id" id="cash_account_id"
                                                    class="form-control select2 @error('payment_method_id') is-invalid @enderror" style="width: 100%;">
                                                    <option value="">Select Cash Account</option>
                                                    @foreach($cash_accounts as $cash)
                                                        <option value="{{ $cash->id }}" {{ old('payment_method_id') == $cash->id ? 'selected' : '' }}>{{ $cash->name }} - Bal:
                                                            ₹{{ number_format($cash->balance, 2) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('payment_method_id')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mb-3" id="reference_id_div">
                                                <label for="reference_id" class="form-label">Reference ID / Cheque
                                                    No.</label>
                                                <input type="text" class="form-control" name="reference_id"
                                                    id="reference_id" placeholder="e.g. UPI Ref, Cheque Number">
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label for="image" class="form-label">Payment Proof (Image)</label>
                                                <input type="file" class="form-control" name="image" id="image">
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <label for="remarks" class="form-label">Remarks</label>
                                                <textarea class="form-control" name="remarks" id="remarks" rows="2"
                                                    placeholder="Optional remarks..."></textarea>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <button type="submit" class="btn btn-success px-4"><i class="fas fa-save"></i>
                                                Record Payment</button>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#vendor_id').change(function () {
                var vendorId = $(this).val();
                if (vendorId) {
                    $.ajax({
                        url: "{{ route('admin.payment.fabric-shipment.get-shipments') }}",
                        type: "GET",
                        data: { vendor_id: vendorId },
                        success: function (response) {
                            $('#shipments_table_body').empty();
                            $('#payment_section').hide();

                            if (response.shipments.length > 0) {
                                $('#shipment_section').show();
                                var selectedReceiptId = "{{ $selectedReceiptId ?? '' }}";
                                $.each(response.shipments, function (key, shipment) {
                                    var isChecked = (selectedReceiptId == shipment.id) ? 'checked' : '';
                                    var row = `
                                                                                <tr>
                                                                                    <td>
                                                                                        <input type="radio" name="selected_shipment" class="shipment_radio" 
                                                                                            data-id="${shipment.id}"
                                                                                            data-balance="${shipment.balance_amount}"
                                                                                            data-total="${shipment.total_amount}"
                                                                                            data-paid="${shipment.paid_amount}"
                                                                                            data-challan="${shipment.shipment_id || 'N/A'}"
                                                                                            data-date="${shipment.created_at}"
                                                                                            data-shipment-photo="${shipment.shipment_photo}"
                                                                                            data-challan-photo="${shipment.challan_photo}"
                                                                                            ${isChecked}
                                                                                        >
                                                                                    </td>
                                                                                    <td>${new Date(shipment.created_at).toLocaleDateString()}</td>
                                                                                    <td>${shipment.shipment_id || '-'}</td>
                                                                                    <td>${parseFloat(shipment.total_amount).toFixed(2)}</td>
                                                                                    <td>${parseFloat(shipment.paid_amount || 0).toFixed(2)}</td>
                                                                                    <td>${parseFloat(shipment.balance_amount || 0).toFixed(2)}</td>
                                                                                    <td>
                                                                                        <a href="{{ url('admin/fabric-receipt/view') }}?id=${shipment.id}" target="_blank" class="btn btn-sm btn-info" title="View Details">
                                                                                            <i class="fas fa-eye"></i>
                                                                                        </a>
                                                                                    </td>
                                                                                </tr>
                                                                            `;
                                    $('#shipments_table_body').append(row);
                                });
                            } else {
                                $('#shipment_section').hide();
                                alert('No unpaid shipments found for this vendor.');
                            }
                        }
                    });
                } else {
                    $('#shipment_section').hide();
                    $('#payment_section').hide();
                }
            });

            $(document).on('change', '.shipment_radio', function () {
                var id = $(this).data('id');
                var balance = $(this).data('balance');
                var total = $(this).data('total');
                var paid = $(this).data('paid');
                var challan = $(this).data('challan');
                var date = $(this).data('date');

                // Populate Payment Section
                $('#fabric_receipt_id').val(id);
                $('#amount').val(balance); // Auto-fill balance
                $('#amount').attr('max', balance); // Set max validation

                // Show Details
                var detailsHtml = `
                                                            <div class="row">
                                                                <div class="col-md-3"><strong>Date:</strong> ${new Date(date).toLocaleDateString()}</div>
                                                                <div class="col-md-3"><strong>Shipment No:</strong> ${challan}</div>
                                                                <div class="col-md-3"><strong>Total Amount:</strong> ${parseFloat(total).toFixed(2)}</div>
                                                                <div class="col-md-3"><strong>Balance:</strong> <span class="text-danger">${parseFloat(balance).toFixed(2)}</span></div>
                                                            </div>
                                                        `;
                $('#selected_shipment_details').html(detailsHtml);

                $('#payment_section').slideDown();
            });

            // Trigger change if vendor is pre-selected
            if ($('#vendor_id').val()) {
                $('#vendor_id').trigger('change');

                // Use an interval to check for the radio button existence to avoid race conditions
                var checkExist = setInterval(function () {
                    if ($('.shipment_radio:checked').length > 0) {
                        $('.shipment_radio:checked').trigger('change');
                        clearInterval(checkExist);
                    }
                }, 100);

                // Safety timeout to clear interval after 3 seconds
                setTimeout(function () {
                    clearInterval(checkExist);
                }, 3000);
            }
            $('#payment_mode').on('change', function () {
                var mode = $(this).val();
                if (mode == 'Bank') {
                    $('#bank_account_div').show();
                    $('#bank_account_id').attr('required', true).prop('disabled', false);
                    $('#cash_account_div').hide();
                    $('#cash_account_id').attr('required', false).prop('disabled', true);
                    $('#cash_account_id').val('').trigger('change');
                } else if (mode == 'Cash') {
                    $('#cash_account_div').show();
                    $('#cash_account_id').attr('required', true).prop('disabled', false);
                    $('#bank_account_div').hide();
                    $('#bank_account_id').attr('required', false).prop('disabled', true);
                    $('#bank_account_id').val('').trigger('change');
                } else {
                    $('#bank_account_div').hide();
                    $('#cash_account_div').hide();
                    $('#bank_account_id').attr('required', false).prop('disabled', true);
                    $('#cash_account_id').attr('required', false).prop('disabled', true);
                }
            });

            // Trigger on load for old values
            $('#payment_mode').trigger('change');
        });
    </script>
@endsection