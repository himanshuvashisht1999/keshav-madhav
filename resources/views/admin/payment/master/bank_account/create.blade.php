@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Create Bank Account</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a
                                    href="{{route('admin.payment.master.bank_account.index')}}">Manage Bank
                                    Accounts</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <form action="{{route('admin.payment.master.bank_account.store')}}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control"
                                            placeholder="Enter Bank Name" value="{{old('bank_name')}}" required>
                                        @if ($errors->has('bank_name'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('bank_name') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Account Name</label>
                                        <input type="text" name="account_name" class="form-control"
                                            placeholder="Enter Account Name" value="{{old('account_name')}}" required>
                                        @if ($errors->has('account_name'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('account_name') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Account Number</label>
                                        <input type="text" name="account_number" class="form-control"
                                            placeholder="Enter Account Number (Optional)" value="{{old('account_number')}}">
                                        @if ($errors->has('account_number'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('account_number') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>IFSC Code</label>
                                        <input type="text" name="ifsc_code" class="form-control"
                                            placeholder="Enter IFSC Code (Optional)" value="{{old('ifsc_code')}}">
                                        @if ($errors->has('ifsc_code'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('ifsc_code') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Branch Name</label>
                                        <input type="text" name="branch_name" class="form-control"
                                            placeholder="Enter Branch Name (Optional)" value="{{old('branch_name')}}">
                                        @if ($errors->has('branch_name'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('branch_name') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Balance</label>
                                        <input type="number" step="0.01" name="balance" class="form-control"
                                            placeholder="Enter Balance" value="{{old('balance', 0)}}">
                                        @if ($errors->has('balance'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('balance') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Balance Type</label>
                                        <select name="balance_type" class="form-control select2" style="width: 100%;">
                                            <option value="Credit" {{old('balance_type') == 'Credit' ? 'selected' : ''}}>
                                                Credit</option>
                                            <option value="Debit" {{old('balance_type') == 'Debit' ? 'selected' : ''}}>Debit
                                            </option>
                                        </select>
                                        @if ($errors->has('balance_type'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('balance_type') }}</span>
                                        @endif
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
@endsection