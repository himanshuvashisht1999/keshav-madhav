@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Edit Order #{{ $order->id }}</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admin.agent-orders.index')}}">Agent Orders</a>
                            </li>
                            <li class="breadcrumb-item active">Edit Order</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Update Order Details</h3>
                    </div>
                    <form action="{{ route('admin.agent-orders.update', $order->id) }}" method="post">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Order ID</label>
                                        <input type="text" class="form-control" value="{{ $order->id }}" disabled>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Total Amount (Before Discount/Tax)</label>
                                        <input type="text" class="form-control"
                                            value="{{ number_format($order->total_amount, 2) }}" disabled>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Current Status</label>
                                        <input type="text" class="form-control" value="{{ ucfirst($order->status) }}"
                                            disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Discount Percentage (%)</label>
                                        <input type="number" step="0.01" name="discount_percentage" class="form-control"
                                            value="{{ $order->discount_percentage }}" required>
                                        @error('discount_percentage')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>GST Percentage (%)</label>
                                        <input type="number" step="0.01" name="gst_percentage" class="form-control"
                                            value="{{ $order->gst_percentage }}" required>
                                        @error('gst_percentage')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-info"></i> Calculation Review</h5>
                                <p>Changing these values will recalculate the Grand Total based on the base Total Amount of
                                    <strong>₹{{ number_format($order->total_amount, 2) }}</strong>.</p>
                            </div>

                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Update Order</button>
                            <a href="{{ route('admin.agent-orders.index') }}" class="btn btn-default float-right">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection