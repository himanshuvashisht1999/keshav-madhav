@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Create Contractor</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admin.payment.master.contractor.index')}}">Manage
                                    Contractor</a></li>
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
                                <h3 class="card-title"><i class="fas fa-plus mr-2"></i>Contractor Details</h3>
                            </div>
                            <form action="{{route('admin.payment.master.contractor.store')}}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="name"><i class="fas fa-user mr-1 text-primary"></i> Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" id="name"
                                                    placeholder="Enter Contractor Name" required value="{{ old('name') }}">
                                                @error('name')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="phone"><i class="fas fa-phone mr-1 text-info"></i> Phone</label>
                                                <input type="text" name="phone" class="form-control" id="phone"
                                                    placeholder="Phone Number" value="{{ old('phone') }}">
                                                @error('phone')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="address"><i class="fas fa-map-marker-alt mr-1 text-danger"></i> Address</label>
                                                <textarea name="address" class="form-control" id="address" rows="2" placeholder="Enter Address">{{ old('address') }}</textarea>
                                                @error('address')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-money-bill-wave mr-1 text-success"></i> Opening Balance</label>
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
                                                <label><i class="fas fa-exchange-alt mr-1 text-secondary"></i> Balance Type</label>
                                                <select name="balance_type" class="form-control select2" style="width: 100%;">
                                                    <option value="Credit" {{ old('balance_type') == 'Credit' ? 'selected' : '' }}>Credit (Cr)</option>
                                                    <option value="Debit" {{ old('balance_type') == 'Debit' ? 'selected' : '' }}>Debit (Dr)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white text-right">
                                    <a href="{{ route('admin.payment.master.contractor.index') }}" class="btn btn-default mr-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4">Create Contractor</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
