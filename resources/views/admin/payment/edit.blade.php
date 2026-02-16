@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Edit Payment</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.payment.history.index') }}">Payment
                                    History</a></li>
                            <li class="breadcrumb-item active">Edit Payment</li>
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
                            <div class="card-header bg-warning">
                                <h3 class="card-title"><i class="fas fa-edit mr-2"></i> Edit Payment ID: {{ $payment->id }}
                                </h3>
                            </div>
                            <form action="{{ route('admin.payment.history.update', $payment->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="card-body">

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        Payment Category:
                                        <strong>{{ ucfirst(str_replace('_', ' ', $payment->payment_category)) }}</strong>
                                        @if($payment->party)
                                            | Payee: <strong>{{ $payment->party->name }}</strong>
                                        @endif
                                    </div>

                                    @if($payment->payee_name && !$payment->party_id)
                                        <div class="form-group">
                                            <label for="payee_name">Payee Name <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="payee_name" id="payee_name"
                                                    value="{{ $payment->payee_name }}" required>
                                            </div>
                                            <small class="text-muted">You can edit the payee name for manual payments.</small>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="payment_date">Payment Date <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-calendar-alt"></i></span>
                                                    </div>
                                                    <input type="date" class="form-control" name="payment_date"
                                                        id="payment_date" value="{{ $payment->payment_date }}" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="amount">Amount <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-rupee-sign"></i></span>
                                                    </div>
                                                    <input type="number" step="0.01" class="form-control" name="amount"
                                                        id="amount" value="{{ $payment->amount }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="payment_mode">Payment Mode <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-credit-card"></i></span>
                                                    </div>
                                                    <select class="form-control" name="payment_mode" id="payment_mode"
                                                        required>
                                                        <option value="cash" {{ $payment->payment_mode == 'cash' ? 'selected' : '' }}>Cash</option>
                                                        <option value="bank_transfer" {{ $payment->payment_mode == 'bank_transfer' ? 'selected' : '' }}>
                                                            Bank Transfer</option>
                                                        <option value="cheque" {{ $payment->payment_mode == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                                        <option value="upi" {{ $payment->payment_mode == 'upi' ? 'selected' : '' }}>UPI</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="reference_id">Reference / Cheque No.</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                                                    </div>
                                                    <input type="text" class="form-control" name="reference_id"
                                                        id="reference_id" value="{{ $payment->reference_id }}"
                                                        placeholder="Optional">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="remarks">Remarks</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                            </div>
                                            <textarea class="form-control" name="remarks" id="remarks" rows="2"
                                                placeholder="Optional remarks">{{ $payment->remarks }}</textarea>
                                        </div>
                                    </div>

                                </div>
                                <div class="card-footer text-right">
                                    <a href="{{ route('admin.payment.history.index') }}"
                                        class="btn btn-secondary mr-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i>
                                        Update Payment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection