@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Edit Loan Account</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.payment.master.loan.index') }}">Manage Loan Master</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <form action="{{ route('admin.payment.master.loan.update') }}" method="post">
                        @csrf
                        <input type="hidden" name="id" value="{{ $data->id }}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Account Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" placeholder="Enter Account Name" value="{{ old('name', $data->name) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Current Balance</label>
                                        <div class="form-control" style="background-color: #e9ecef;">
                                            ₹ {{ number_format(abs($data->balance), 2) }}
                                            @if($data->balance >= 0)
                                                <span class="badge badge-success">Cr</span>
                                            @else
                                                <span class="badge badge-danger">Dr</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Opening Balance ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</label>
                                        <input type="number" step="0.01" name="balance" class="form-control" placeholder="Enter Opening Balance" value="{{ $data->currentOpeningBalance ? $data->currentOpeningBalance->amount : 0 }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Opening Balance Type</label>
                                        <select name="balance_type" class="form-control select2" style="width: 100%;">
                                            <option value="Credit" {{ ($data->currentOpeningBalance && $data->currentOpeningBalance->balance_type == 'Credit') ? 'selected' : ($data->balance >= 0 ? 'selected' : '') }}>Credit</option>
                                            <option value="Debit" {{ ($data->currentOpeningBalance && $data->currentOpeningBalance->balance_type == 'Debit') ? 'selected' : ($data->balance < 0 ? 'selected' : '') }}>Debit</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
