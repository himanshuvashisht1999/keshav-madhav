@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Storeroom: {{ $storeroom->name }}</h1>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="{{ route('admin.master.storeroom.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <!-- Storeroom Details -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Storeroom Details</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.master.storeroom.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $storeroom->id }}">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $storeroom->name }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="1" {{ $storeroom->status == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $storeroom->status == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Order Taken</label>
                                <select name="order_taken" id="order_taken_edit" class="form-control">
                                    <option value="No" {{ $storeroom->order_taken == 'No' ? 'selected' : '' }}>No</option>
                                    <option value="Yes" {{ $storeroom->order_taken == 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3" id="order_priority_div_edit" style="display: {{ $storeroom->order_taken == 'Yes' ? 'block' : 'none' }};">
                                <label>Order Priority</label>
                                <input type="number" name="order_priority" class="form-control" value="{{ $storeroom->order_priority }}" placeholder="Enter Priority Number (e.g., 1, 2, 3)" min="1">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Description</label>
                                <textarea name="description" class="form-control">{{ $storeroom->description }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Details</button>
                    </form>
                </div>
            </div>

@endsection

@push('scripts')
<script>
    // Rack management logic has been moved to its own page.
    $(document).ready(function() {
        $('#order_taken_edit').change(function() {
            if ($(this).val() == 'Yes') {
                $('#order_priority_div_edit').show();
            } else {
                $('#order_priority_div_edit').hide();
            }
        });
    });
</script>
@endpush
