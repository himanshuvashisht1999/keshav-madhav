@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- PAGE HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">Inventory Detail</h1>
                    <small class="text-muted">Viewing inventory details</small>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.inventory.warehouse_stock') }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Listing
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- MAIN DETAILS CARD -->
                <div class="col-md-8">
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-white border-0 pt-4 px-4">
                            <h5 class="card-title font-weight-bold"><i class="fas fa-box text-primary mr-2"></i> Item Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded shadow-xs mb-3">
                                        <label class="small text-muted mb-1 d-block uppercase font-weight-bold">Product Name</label>
                                        <span class="h6 font-weight-bold text-dark">{{ $data->product_name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded shadow-xs mb-3">
                                        <label class="small text-muted mb-1 d-block uppercase font-weight-bold">Design Number</label>
                                        <span class="h6 font-weight-bold text-dark">{{ $data->design_number }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded shadow-xs mb-3">
                                        <label class="small text-muted mb-1 d-block uppercase font-weight-bold">Color</label>
                                        <span class="h6 font-weight-bold text-dark">{{ $data->color_name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded shadow-xs mb-3">
                                        <label class="small text-muted mb-1 d-block uppercase font-weight-bold">Size Set</label>
                                        <span class="h6 font-weight-bold text-dark">{{ $data->size_set_name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded shadow-xs mb-3">
                                        <label class="small text-muted mb-1 d-block uppercase font-weight-bold">Quantity</label>
                                        <span class="h6 font-weight-bold text-primary">{{ $data->quantity }}</span>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4" style="border-top: 1px dashed #ddd;">

                            <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-map-marker-alt text-danger mr-2"></i> Current Location</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded mb-3">
                                        <label class="small text-muted mb-1 d-block">Warehouse</label>
                                        <span class="font-weight-bold">{{ $data->rack->storeroom->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded mb-3">
                                        <label class="small text-muted mb-1 d-block">Rack / Bin</label>
                                        <span class="font-weight-bold">{{ $data->rack->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECONDARY DETAILS SIDEBAR -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-white border-0 pt-4 px-4 text-center">
                            <h5 class="card-title font-weight-bold">Barcode</h5>
                        </div>
                        <div class="card-body p-4 text-center">
                            @if($data->barcode)
                                <div class="bg-white p-3 border rounded d-inline-block shadow-xs mb-3">
                                    {!! DNS1D::getBarcodeHTML($data->barcode, 'C128', 2, 50) !!}
                                    <div class="small mt-2 font-weight-bold text-muted">{{ $data->barcode }}</div>
                                </div>
                            @else
                                <div class="text-muted small py-4">No barcode available</div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .bg-light { background-color: #f8f9fa !important; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.035) !important; }
    .uppercase { text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.75rem; }
</style>
@endsection
