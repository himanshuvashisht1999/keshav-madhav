@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Create Hulayati</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.payment.master.hulayati.index') }}">Manage
                                    Hulayati Master</a></li>
                            <li class="breadcrumb-item active">Create</li>
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
                                <h3 class="card-title"><i class="fas fa-plus mr-2"></i>Hulayati Master Details</h3>
                            </div>
                            <form action="{{ route('admin.payment.master.hulayati.store') }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="name"><i class="fas fa-tag mr-1 text-primary"></i> Hulayati Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control form-control-lg" id="name"
                                                    placeholder="Enter Hulayati Name" value="{{ old('name') }}" required>
                                                @error('name')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-money-bill-wave mr-1 text-success"></i> Balance</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">₹</span>
                                                    </div>
                                                    <input type="number" step="0.01" name="balance" class="form-control"
                                                        placeholder="0.00" value="{{ old('balance', 0) }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-exchange-alt mr-1 text-info"></i> Balance Type</label>
                                                <select name="balance_type" class="form-control select2" style="width: 100%;">
                                                    <option value="Credit" {{ old('balance_type') == 'Credit' ? 'selected' : '' }}>Credit (Cr)</option>
                                                    <option value="Debit" {{ old('balance_type') == 'Debit' ? 'selected' : '' }}>Debit (Dr)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white text-right">
                                    <a href="{{ route('admin.payment.master.hulayati.index') }}" class="btn btn-default mr-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4">Create Master</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection