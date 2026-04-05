@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Adjustment Master</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <form action="{{ route('admin.payment.master.adjustment_master.update', $data->id) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $data->name }}" required>
                        </div>
                        <div class="form-group">
                            <label>Model Class <span class="text-danger">*</span></label>
                            <input type="text" name="model_name" class="form-control" value="{{ $data->model_name }}" required>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1" {{ $data->status == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $data->status == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">Update Master</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
