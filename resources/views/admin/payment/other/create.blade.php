@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Other Payment</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Other Payment</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title"><i class="fas fa-money-check-alt mr-2"></i> Record Other Payment
                                </h3>
                            </div>
                            <form action="{{ route('admin.payment.other.store') }}" method="POST">
                                @csrf
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
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="payment_date">Payment Date <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-calendar-alt"></i></span>
                                                    </div>
                                                    <input type="date"
                                                        class="form-control @error('payment_date') is-invalid @enderror"
                                                        name="payment_date" id="payment_date"
                                                        value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="payment_type">Transaction Type <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-exchange-alt"></i></span>
                                                    </div>
                                                    <select class="form-control" name="payment_type" id="payment_type"
                                                        required>
                                                        <option value="paid">Paid (Outgoing)</option>
                                                        <option value="received">Received (Incoming)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="payment_mode">Payment Mode <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-credit-card"></i></span>
                                                    </div>
                                                    <select name="payment_mode" id="payment_mode"
                                                        class="form-control @error('payment_mode') is-invalid @enderror"
                                                        required>
                                                        <option value="">Select Mode</option>
                                                        <option value="Bank" {{ old('payment_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
                                                        <option value="Cash" {{ old('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3" id="bank_account_div" style="display:none;">
                                            <div class="form-group">
                                                <label for="bank_account_id">Bank Account <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <select name="payment_method_id" id="bank_account_id"
                                                        class="form-control select2">
                                                        <option value="">Select Bank Account</option>
                                                        @foreach($bank_accounts as $bank)
                                                            <option value="{{ $bank->id }}">
                                                                {{ $bank->bank_name }} ({{ $bank->account_number }}) - Bal:
                                                                ₹{{ number_format($bank->balance, 2) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3" id="cash_account_div" style="display:none;">
                                            <div class="form-group">
                                                <label for="cash_account_id">Cash Account <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <select name="payment_method_id" id="cash_account_id"
                                                        class="form-control select2">
                                                        <option value="">Select Cash Account</option>
                                                        @foreach($cash_accounts as $cash)
                                                            <option value="{{ $cash->id }}">{{ $cash->name }} - Bal:
                                                                ₹{{ number_format($cash->balance, 2) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="reference_id">Reference / Cheque No.</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                                                    </div>
                                                    <input type="text" class="form-control" name="reference_id"
                                                        id="reference_id" placeholder="Optional">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="remarks">Global Remarks</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                                    </div>
                                                    <input type="text" class="form-control" name="remarks" id="remarks" placeholder="Applied to all payments">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <h5 class="text-primary font-weight-bold">Payment Details</h5>
                                            <table class="table table-bordered table-striped" id="payment_table">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th style="width: 25%">Employee <small class="text-muted">(Optional)</small></th>
                                                        <th style="width: 20%">Payee Name <small class="text-muted">(Manual)</small></th>
                                                        <th style="width: 25%">Payment Type <span class="text-danger">*</span></th>
                                                        <th style="width: 20%">Amount <span class="text-danger">*</span></th>
                                                        <th style="width: 10%">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <select class="form-control select2 employee-select" name="payments[0][employee_id]">
                                                                <option value="">-- Employee --</option>
                                                                @foreach($employees as $employee)
                                                                    <option value="{{ $employee->id }}">
                                                                        {{ $employee->name }} ({{ $employee->phone }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control payee-input" name="payments[0][payee_name]" placeholder="Enter Name">
                                                        </td>
                                                        <td>
                                                            <select class="form-control select2" name="payments[0][payment_type_id]" required>
                                                                <option value="">-- Type --</option>
                                                                @foreach($payment_types as $type)
                                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" class="form-control" name="payments[0][amount]" placeholder="0.00" required>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-danger btn-sm remove-row" disabled><i class="fas fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <button type="button" class="btn btn-success mt-2" id="add_more"><i class="fas fa-plus mr-1"></i> Add More Payment</button>
                                        </div>
                                    </div>

                                </div>
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i> Save
                                        Payments</button>
                                </div>
                            </form>
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
            let rowCount = 1;

            function initSelect2(element) {
                element.find('.select2').select2({
                    placeholder: "Select Option",
                    allowClear: true,
                    width: '100%'
                });
            }

            // Initialize existing
            initSelect2($('body'));

            $('#payment_mode').on('change', function () {
                var mode = $(this).val();
                if (mode == 'Bank') {
                    $('#bank_account_div').show();
                    $('#bank_account_id').attr('required', true).prop('disabled', false);
                    $('#cash_account_div').hide();
                    $('#cash_account_id').attr('required', false).prop('disabled', true);
                } else if (mode == 'Cash') {
                    $('#cash_account_div').show();
                    $('#cash_account_id').attr('required', true).prop('disabled', false);
                    $('#bank_account_div').hide();
                    $('#bank_account_id').attr('required', false).prop('disabled', true);
                } else {
                    $('#bank_account_div').hide();
                    $('#cash_account_div').hide();
                    $('#bank_account_id').attr('required', false).prop('disabled', true);
                    $('#cash_account_id').attr('required', false).prop('disabled', true);
                }
            });

            $('#payment_mode').trigger('change');

            $('#add_more').on('click', function() {
                let newRow = `
                    <tr>
                        <td>
                            <select class="form-control select2 employee-select" name="payments[${rowCount}][employee_id]">
                                <option value="">-- Employee --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->name }} ({{ $employee->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control payee-input" name="payments[${rowCount}][payee_name]" placeholder="Enter Name">
                        </td>
                        <td>
                            <select class="form-control select2" name="payments[${rowCount}][payment_type_id]" required>
                                <option value="">-- Type --</option>
                                @foreach($payment_types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control" name="payments[${rowCount}][amount]" placeholder="0.00" required>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                let $newRow = $(newRow);
                $('#payment_table tbody').append($newRow);
                initSelect2($newRow);
                rowCount++;
                updateRemoveButtons();
            });

            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                updateRemoveButtons();
            });

            function updateRemoveButtons() {
                let rows = $('#payment_table tbody tr');
                if (rows.length <= 1) {
                    rows.find('.remove-row').prop('disabled', true);
                } else {
                    rows.find('.remove-row').prop('disabled', false);
                }
            }

            $(document).on('change', '.employee-select', function() {
                let row = $(this).closest('tr');
                if ($(this).val()) {
                    row.find('.payee-input').prop('disabled', true).val('');
                } else {
                    row.find('.payee-input').prop('disabled', false);
                }
            });

            // Initial trigger
            $('.employee-select').trigger('change');
        });
    </script>
@endsection