@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">Add New Shop</h1>
                        <small class="text-muted">Registering a new shop for <strong>{{ $agent->name }}</strong></small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.master.sales-agent-shops.index', $agent->id) }}" class="btn btn-secondary shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-arrow-left mr-1"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" style="border-radius: 8px;">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title m-0 text-primary font-weight-bold"><i class="fas fa-store mr-2"></i> Shop Details</h5>
                    </div>
                    <form action="{{ route('admin.master.sales-agent-shops.store', $agent->id) }}" method="POST">
                        @csrf
                        <div class="card-body bg-light p-4">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold text-muted">Shop Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Enter shop or company name" required autocomplete="off">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold text-muted">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="E.g. 98XXXXXXXX" required autocomplete="off">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold text-muted">Email Address <small class="text-muted font-weight-normal">(Optional)</small></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="example@gmail.com" autocomplete="off">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold text-muted">Full Address <small class="text-muted font-weight-normal">(Optional)</small></label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Street, landmark, city...">{{ old('address') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top py-3 text-right">
                            <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm" style="border-radius: 6px;">
                                <i class="fas fa-save mr-1"></i> Register Shop
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
