@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Salary Payment</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Salary Payment</li>
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
                                <h3 class="card-title"><i class="fas fa-money-bill-wave mr-2"></i> Record Salary Payment
                                </h3>
                            </div>
                            <form action="{{ route('admin.payment.salary.store') }}" method="POST">
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
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="employee_id">Select Employee <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                    </div>
                                                    <select
                                                        class="form-control select2 @error('employee_id') is-invalid @enderror"
                                                        name="employee_id" id="employee_id" required>
                                                        <option value="">Select Employee</option>
                                                        @foreach($employees as $employee)
                                                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                                {{ $employee->name }}
                                                                ({{ $employee->phone }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('employee_id')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
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
                                                @error('payment_date')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="amount">Amount <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-rupee-sign"></i></span>
                                                    </div>
                                                    <input type="number" step="0.01"
                                                        class="form-control @error('amount') is-invalid @enderror"
                                                        name="amount" id="amount" value="{{ old('amount') }}"
                                                        placeholder="Enter Amount" required>
                                                </div>
                                                @error('amount')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
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
                                                @error('payment_mode')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4" id="bank_account_div" style="display:none;">
                                            <div class="form-group">
                                                <label for="bank_account_id">Bank Account <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-university"></i></span>
                                                    </div>
                                                    <select name="payment_method_id" id="bank_account_id"
                                                        class="form-control select2 @error('payment_method_id') is-invalid @enderror"
                                                        style="width: 80%;">
                                                        <option value="">Select Bank Account</option>
                                                        @foreach($bank_accounts as $bank)
                                                            <option value="{{ $bank->id }}" {{ old('payment_method_id') == $bank->id ? 'selected' : '' }}>
                                                                {{ $bank->bank_name }}
                                                                ({{ $bank->account_number }}) - Bal:
                                                                ₹{{ number_format($bank->balance, 2) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('payment_method_id')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4" id="cash_account_div" style="display:none;">
                                            <div class="form-group">
                                                <label for="cash_account_id">Cash Account <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-wallet"></i></span>
                                                    </div>
                                                    <select name="payment_method_id" id="cash_account_id"
                                                        class="form-control select2 @error('payment_method_id') is-invalid @enderror"
                                                        style="width: 80%;">
                                                        <option value="">Select Cash Account</option>
                                                        @foreach($cash_accounts as $cash)
                                                            <option value="{{ $cash->id }}" {{ old('payment_method_id') == $cash->id ? 'selected' : '' }}>
                                                                {{ $cash->name }} - Bal:
                                                                ₹{{ number_format($cash->balance, 2) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('payment_method_id')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="reference_id_div">
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
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="remarks">Remarks</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                                    </div>
                                                    <textarea class="form-control" name="remarks" id="remarks" rows="1"
                                                        placeholder="Optional remarks"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i> Save
                                        Payment</button>
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
            $('.select2').select2();
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