@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Brand</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.brand.index')}}">Manage Brands</a></li>
                        <li class="breadcrumb-item active">Edit Brand</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">
                <form action="{{ route('admin.master.brand.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Brand Name</label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $data->name) }}" placeholder="Enter Brand Name">
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="logo">Brand Logo</label>
                                    <input type="file" name="logo" id="logo" class="form-control-file @error('logo') is-invalid @enderror" accept="image/*">
                                    @if($data->logo)
                                        <div class="mt-2">
                                            <img src="{{ asset('assets/brands/' . $data->logo) }}" alt="Current Logo" class="img-thumbnail" style="max-height: 80px;">
                                            <p class="small text-muted mb-0">Current Logo</p>
                                        </div>
                                    @endif
                                    @error('logo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="active" {{ old('status', $data->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $data->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('admin.master.brand.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Brand</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
