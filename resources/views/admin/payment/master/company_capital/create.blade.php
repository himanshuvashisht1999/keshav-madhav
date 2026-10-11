@extends('admin.layouts.app')
@section('title', 'Add Company Capital')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-landmark text-primary mr-2"></i> Add Company Capital
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.payment.master.company_capital.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Capital
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-2 py-2 px-3" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-2 py-2 px-3" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-2 py-2 px-3" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Form Card -->
    <div class="erp-card p-3 mb-2">
        <form action="{{ route('admin.payment.master.company_capital.store') }}" method="post">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="amount"
                        class="form-control erp-input @error('amount') is-invalid @enderror"
                        placeholder="Enter Amount" value="{{ old('amount') }}" required>
                    @error('amount')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Transaction Date <span class="text-danger">*</span></label>
                    <input type="date" name="transaction_date"
                        class="form-control erp-input @error('transaction_date') is-invalid @enderror"
                        value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                    @error('transaction_date')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Payment Method Type <span class="text-danger">*</span></label>
                    <select name="payment_method_type" id="payment_method_type"
                        class="form-control select2 erp-input @error('payment_method_type') is-invalid @enderror"
                        required style="width: 100%;">
                        <option value="">Select Method Type</option>
                        <option value="Bank" {{ old('payment_method_type') == 'Bank' ? 'selected' : '' }}>Bank Account</option>
                        <option value="Cash" {{ old('payment_method_type') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    </select>
                    @error('payment_method_type')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <div id="bank_method_div" style="display: none;">
                        <label class="erp-label">Select Bank Account <span class="text-danger">*</span></label>
                        <select name="bank_id" class="form-control select2 erp-input payment_method_select" style="width: 100%;">
                            <option value="">Select Bank Account</option>
                            @foreach($methods['banks'] as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_number }} (Bal: ₹{{ number_format($bank->balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="cash_method_div" style="display: none;">
                        <label class="erp-label">Select Cash Payment <span class="text-danger">*</span></label>
                        <select name="cash_id" class="form-control select2 erp-input payment_method_select" style="width: 100%;">
                            <option value="">Select Cash Method</option>
                            @foreach($methods['cash'] as $cash)
                                <option value="{{ $cash->id }}">{{ $cash->name }} (Bal: ₹{{ number_format($cash->balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="payment_method_id" id="payment_method_id">
                    @error('payment_method_id')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="erp-label">Remarks</label>
                    <textarea name="remarks" class="form-control erp-input @error('remarks') is-invalid @enderror"
                        rows="2" placeholder="Enter Remarks (Optional)">{{ old('remarks') }}</textarea>
                    @error('remarks')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-12 text-right mt-2">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-save mr-1"></i> Submit Capital
                    </button>
                </div>
            </div>
        </form>
    </div>
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

        if ($('#payment_method_type').val()) {
            $('#payment_method_type').trigger('change');
        }
    });
</script>
@endsection
