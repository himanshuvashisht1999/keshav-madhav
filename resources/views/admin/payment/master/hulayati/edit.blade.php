@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Edit Hulayati</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.payment.master.hulayati.index') }}">Manage
                                    Hulayati Master</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Hulayati Master Details</h3>
                            </div>
                            <form action="{{ route('admin.payment.master.hulayati.update') }}" method="post">
                                @csrf
                                <input type="hidden" name="id" value="{{ $data->id }}">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="form-group">
                                                <label for="name"><i class="fas fa-tag mr-1 text-primary"></i> Hulayati Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control form-control-lg" id="name"
                                                    placeholder="Enter Hulayati Name" value="{{ old('name', $data->name) }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label><i class="fas fa-wallet mr-1 text-success"></i> Current Balance</label>
                                                <div class="form-control-plaintext border px-3 rounded bg-light" style="font-size: 1.1rem; font-weight: 600;">
                                                    ₹ {{ number_format(abs($data->balance), 2) }}
                                                    @if($data->balance >= 0)
                                                        <span class="badge badge-success ml-1">Cr</span>
                                                    @else
                                                        <span class="badge badge-danger ml-1">Dr</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <h6 class="text-muted mb-3"><i class="fas fa-history mr-1"></i> Opening Balance Settings</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-calendar-alt mr-1 text-info"></i> Amount ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">₹</span>
                                                    </div>
                                                    <input type="number" step="0.01" name="balance" class="form-control"
                                                        placeholder="0.00" value="{{ $data->currentOpeningBalance ? $data->currentOpeningBalance->amount : 0 }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-exchange-alt mr-1 text-secondary"></i> Balance Type</label>
                                                <select name="balance_type" class="form-control select2" style="width: 100%;">
                                                    <option value="Credit" {{ ($data->currentOpeningBalance && $data->currentOpeningBalance->balance_type == 'Credit') ? 'selected' : ($data->balance >= 0 ? 'selected' : '') }}>Credit (Cr)</option>
                                                    <option value="Debit" {{ ($data->currentOpeningBalance && $data->currentOpeningBalance->balance_type == 'Debit') ? 'selected' : ($data->balance < 0 ? 'selected' : '') }}>Debit (Dr)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white text-right">
                                    <a href="{{ route('admin.payment.master.hulayati.index') }}" class="btn btn-default mr-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4">Update Details</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection