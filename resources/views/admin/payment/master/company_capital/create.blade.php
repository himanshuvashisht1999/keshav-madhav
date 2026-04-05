@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Add Company Capital</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admin.payment.master.company_capital.index')}}">Company
                                    Capital</a></li>
                            <li class="breadcrumb-item active">Add Capital</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <form action="{{route('admin.payment.master.company_capital.store')}}" method="post">
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
                                        <label>Amount</label>
                                        <input type="number" step="0.01" name="amount"
                                            class="form-control @error('amount') is-invalid @enderror"
                                            placeholder="Enter Amount" value="{{old('amount')}}" required>
                                        @error('amount')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Transaction Date</label>
                                        <input type="date" name="transaction_date"
                                            class="form-control @error('transaction_date') is-invalid @enderror"
                                            value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                        @error('transaction_date')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment Method Type</label>
                                        <select name="payment_method_type" id="payment_method_type"
                                            class="form-control @error('payment_method_type') is-invalid @enderror"
                                            required>
                                            <option value="">Select Method Type</option>
                                            <option value="Bank" {{ old('payment_method_type') == 'Bank' ? 'selected' : '' }}>
                                                Bank Account</option>
                                            <option value="Cash" {{ old('payment_method_type') == 'Cash' ? 'selected' : '' }}>
                                                Cash</option>
                                        </select>
                                        @error('payment_method_type')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="bank_method_div" style="display: none;">
                                        <label>Select Bank Account</label>
                                        <select name="bank_id" class="form-control payment_method_select">
                                            <option value="">Select Bank Account</option>
                                            @foreach($methods['banks'] as $bank)
                                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} -
                                                    {{ $bank->account_number }} (Bal: ₹{{ number_format($bank->balance, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group" id="cash_method_div" style="display: none;">
                                        <label>Select Cash Payment</label>
                                        <select name="cash_id" class="form-control payment_method_select">
                                            <option value="">Select Cash Method</option>
                                            @foreach($methods['cash'] as $cash)
                                                <option value="{{ $cash->id }}">{{ $cash->name }} (Bal:
                                                    ₹{{ number_format($cash->balance, 2) }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" name="payment_method_id" id="payment_method_id">
                                    @error('payment_method_id')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror"
                                            placeholder="Enter Remarks (Optional)">{{old('remarks')}}</textarea>
                                        @error('remarks')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mt-2" style="float:right">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <script>
        $(document).ready(function () {
            $('#payment_method_type').change(function () {
                var type = $(this).val();
                $('.payment_method_select').val('');
                $('#payment_method_id').val('');

                if (type === 'Bank') {
                    $('#bank_method_div').show();
                    $('#cash_method_div').hide();
                } else if (type === 'Cash') {
                    $('#cash_method_div').show();
                    $('#bank_method_div').hide();
                } else {
                    $('#bank_method_div').hide();
                    $('#cash_method_div').hide();
                }
            });

            $('.payment_method_select').change(function () {
                $('#payment_method_id').val($(this).val());
            });

            // Trigger change if old value exists
            if ($('#payment_method_type').val()) {
                $('#payment_method_type').trigger('change');
            }
        });
    </script>
@endsection
