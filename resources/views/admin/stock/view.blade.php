@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="mb-0">Fabric Stock Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.stock.index') }}">Fabric Stock</a></li>
                        <li class="breadcrumb-item active">View Fabric Stock</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title"><i class="fas fa-boxes mr-2"></i> Fabric Stock Information</h3>
                </div>
                <div class="card-body">

                    <div class="row">
                        <!-- Left column -->
                        <div class="col-md-6">
                            <div class="info-box bg-light shadow-sm rounded">
                                <div class="info-box-content">
                                    <span class="info-box-text d-block text-muted mb-1"><strong>SKU :</strong></span>
                                    <span class="info-box-number">{{ $data->sku }}</span>
                                </div>
                            </div>

                            <div class="info-box bg-light shadow-sm rounded">
                                <div class="info-box-content">
                                    <span class="info-box-text d-block text-muted mb-1"><strong>Date :</strong></span>
                                    <span class="info-box-number">{{ getformatDate($data->date) }}</span>
                                </div>
                            </div>

                            
                            <div class="info-box bg-light shadow-sm rounded">
                                <div class="info-box-content">
                                    <span class="info-box-text d-block text-muted mb-1"><strong>Goods Entry No:</strong></span>
                                    <span class="info-box-number">{{ $data->goods_entry_number }}</span>
                                </div>
                            </div>
                            <div class="info-box bg-light shadow-sm rounded">
                                <div class="info-box-content">
                                    <span class="info-box-text d-block text-muted mb-1"><strong>Meter:</strong></span>
                                    <span class="info-box-number">{{ $data->meter }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right column -->
                        <div class="col-md-6">
                            <div class="info-box bg-light shadow-sm rounded">
                                <div class="info-box-content">
                                    <span class="info-box-text d-block text-muted mb-1"><strong>Purchase Order ID:</strong></span>
                                    <span class="info-box-number">{{ $data->purchase_order_id }}</span>
                                </div>
                            </div>
                            <div class="info-box bg-light shadow-sm rounded">
                                <div class="info-box-content">
                                    <span class="info-box-text d-block text-muted mb-1"><strong>Unique Number:</strong></span>
                                    <span class="info-box-number">{{ $data->unique_number }}</span>
                                </div>
                            </div>
                            <div class="info-box bg-light shadow-sm rounded">
                                <div class="info-box-content">
                                    <span class="info-box-text d-block text-muted mb-1"><strong>Batch No:</strong></span>
                                    <span class="info-box-number">{{ $data->batch_no }}</span>
                                </div>
                            </div>
                            <div class="info-box bg-light shadow-sm rounded">
                                <div class="info-box-content">
                                    <span class="info-box-text d-block text-muted mb-1"><strong>Roll:</strong></span>
                                    <span class="info-box-number">{{ $data->roll }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code -->
                        <div class="col-md-12 text-center mt-4">
                            <strong class="d-block mb-2">QR Code</strong>
                            <div class="border rounded p-3 d-inline-block bg-white shadow-sm">
                                <img src="{{ $data->qrcode }}" alt="QR Code" class="img-fluid" style="height:200px;width:200px;">
                            </div>
                        </div>
                    </div>

                </div>
            </div>


        </div>
    </section>
</div>
@endsection
