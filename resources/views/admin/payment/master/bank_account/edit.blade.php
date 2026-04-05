@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Edit Bank Account</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admin.payment.master.bank_account.index')}}">Manage Bank
                                    Accounts</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <form action="{{route('admin.payment.master.bank_account.update')}}" method="post">
                        @csrf
                        <input type="hidden" name="id" value="{{$data->id}}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control"
                                            placeholder="Enter Bank Name" value="{{$data->bank_name}}" required>
                                        @if ($errors->has('bank_name'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('bank_name') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Account Name</label>
                                        <input type="text" name="account_name" class="form-control"
                                            placeholder="Enter Account Name" value="{{$data->account_name}}" required>
                                        @if ($errors->has('account_name'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('account_name') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Account Number</label>
                                        <input type="text" name="account_number" class="form-control"
                                            placeholder="Enter Account Number" value="{{$data->account_number}}" required>
                                        @if ($errors->has('account_number'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('account_number') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>IFSC Code</label>
                                        <input type="text" name="ifsc_code" class="form-control"
                                            placeholder="Enter IFSC Code" value="{{$data->ifsc_code}}" required>
                                        @if ($errors->has('ifsc_code'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('ifsc_code') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Branch Name</label>
                                        <input type="text" name="branch_name" class="form-control"
                                            placeholder="Enter Branch Name (Optional)" value="{{$data->branch_name}}">
                                        @if ($errors->has('branch_name'))
                                            <span class="invalid-feedback d-block">{{ $errors->first('branch_name') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mt-2" style="float:right">
                                        <button type="submit" class="btn btn-primary">Update</button>
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
