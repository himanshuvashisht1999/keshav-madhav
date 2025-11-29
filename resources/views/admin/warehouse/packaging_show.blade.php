@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    {{-- PAGE HEADER --}}
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-1">Package Details</h1>
                <small class="text-muted">Package #{{ $package->id }}</small>
            </div>

            <div>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary mr-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </section>

    {{-- MAIN CONTENT --}}
    <section class="content">
        <div class="container-fluid">

            @php
                $totalBoxes = $package->package_boxes->count();
                $totalItems = $package->package_boxes->sum(function($box) {
                    return $box->package_boxes_items->count();
                });
            @endphp

            {{-- TOP SUMMARY STRIP --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-receipt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Order ID</span>
                            <span class="info-box-number">{{ $package->order_main_id }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-box-open"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Boxes</span>
                            <span class="info-box-number">{{ $totalBoxes }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-cubes"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Items</span>
                            <span class="info-box-number">{{ $totalItems }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PACKAGE INFORMATION (UPPER STYLE VERSION) --}}
            <div class="row mb-3">

                <div class="col-md-4">
                    <div class="info-box bg-light shadow-sm">
                        <span class="info-box-icon bg-primary"><i class="fas fa-hashtag"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Product Type SKU</span>
                            <span class="info-box-number">{{ $package->product_type_sku }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box bg-light shadow-sm">
                        <span class="info-box-icon bg-warning"><i class="fas fa-box"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Quantity per Box</span>
                            <span class="info-box-number">{{ $package->quantity }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box bg-light shadow-sm">
                        <span class="info-box-icon bg-info"><i class="fas fa-clipboard-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Boxes Generated</span>
                            <span class="info-box-number">{{ $totalBoxes }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-2">
                    <div class="card shadow-sm border">
                        <div class="card-header bg-light">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-align-left mr-1"></i> Description
                            </h3>
                        </div>
                        <div class="card-body">
                            {{ $package->description ?: '-' }}
                        </div>
                    </div>
                </div>

            </div>

            {{-- BOXES + ITEMS TABLE --}}
            <div class="card mt-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-box mr-1"></i>
                        Boxes &amp; Items
                    </h3>
                    <span class="badge badge-secondary">
                        {{ $totalBoxes }} boxes • {{ $totalItems }} items
                    </span>
                </div>

                <div class="card-body table-responsive p-0">
                    @if($package->package_boxes->count())
                        <table class="table table-striped table-hover table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Box ID</th>
                                    <th>Quantity in Box</th>
                                    <th>Barcode</th>
                                    <th>Items (Product SKU)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($package->package_boxes as $index => $box)
                                    @php
                                        // Value encoded in barcode
                                        $barcodeValue = sprintf('%08d', $box->id); // e.g. 00000012
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>

                                        <td>
                                            <span class="badge badge-info">
                                                #{{ $box->id }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge badge-primary">
                                                {{ $box->quantity }} items
                                            </span>
                                        </td>

                                        {{-- BARCODE COLUMN --}}
                                        <td class="text-center">

                                            {{-- HTML barcode with numeric text under it --}}
                                            {!! DNS1D::getBarcodeHTML($barcodeValue, 'C128', 2, 60, 'black', true) !!}

                                            {{-- (Optional) If you want extra text below --}}
                                            {{-- <div class="mt-1" style="font-size: 0.75rem;">
                                                {{ $barcodeValue }}
                                            </div> --}}

                                            <div class="mt-2">
                                                <a href="{{ route('admin.warehouse.barcodeDownload', ['box_id' => $box->id]) }}"
                                                class="btn btn-sm btn-success">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </div>
                                        </td>

                                        {{-- ITEMS COLUMN --}}
                                        <td>
                                            @if($box->package_boxes_items->count())
                                                @foreach($box->package_boxes_items as $item)
                                                    <span class="badge badge-secondary mb-1">
                                                        {{ $item->product_sku }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">No items in this box.</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="p-3 mb-0">No boxes created for this package yet.</p>
                    @endif
                </div>
            </div>

            <div class="mt-3 mb-4">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

        </div>
    </section>
</div>
@endsection
