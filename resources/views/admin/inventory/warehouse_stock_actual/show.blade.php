@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- PAGE HEADER -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Warehouse Stock Actual Detail</h1>
                <p class="erp-page-subtitle">Viewing physical inventory breakdown lying in this location</p>
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.inventory.warehouse_stock_actual') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Actual Stock
                </a>
            </div>
        </div>

        <!-- MAIN DETAILS CARD -->
        <div class="erp-card mb-2">
            <div class="erp-card-body p-2">
                <div class="row align-items-center">
                    <div class="col-md-3 border-right mb-2 mb-md-0 d-flex align-items-center">
                        @if(isset($items[0]) && $items[0]->variant_id)
                            @php
                                $imgSrc = $items[0]->product_image ? asset('assets/products/' . $items[0]->product_image) : asset('images/image-placeholder.png');
                            @endphp
                            <img src="{{ $imgSrc }}" alt="Product Image" style="width: 45px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;" onerror="this.src='{{ asset('images/image-placeholder.png') }}'" class="mr-2">
                        @endif
                        <div>
                            <span class="erp-label mb-0"><i class="fas fa-box text-success mr-1"></i> Product name</span>
                            <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $product->series->name ?? '' }} {{ $product->name_of_garment ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-2 border-right mb-2 mb-md-0">
                        <span class="erp-label mb-0">Design number</span>
                        <span class="font-weight-bold text-success" style="font-size: 13px;">{{ $product->design_number ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-2 border-right mb-2 mb-md-0">
                        <span class="erp-label mb-0">Size set</span>
                        <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $sizeSet->name ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-3 border-right mb-2 mb-md-0">
                        <span class="erp-label mb-0"><i class="fas fa-warehouse text-danger mr-1"></i> Warehouse</span>
                        <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $rack->storeroom->name ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-2">
                        <span class="erp-label mb-0"><i class="fas fa-th text-info mr-1"></i> Rack / Bin</span>
                        <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $rack->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLOR BREAKDOWN CARD -->
        <div class="erp-card mb-2">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fas fa-palette mr-1"></i> Color breakdown (Actual physical stock)</h3>
                <span class="badge badge-success px-2 py-1" style="font-size: 12px;">
                    Total boxes: {{ $items->sum('total_boxes') }} | Total pcs: {{ $items->sum(fn($i) => $i->total_boxes * $i->quantity) }}
                </span>
            </div>
            <div class="erp-card-body p-0">
                <div class="table-responsive">
                    <table class="erp-table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Color</th>
                                <th class="text-center">Barcode</th>
                                <th class="text-center">Pieces per box</th>
                                <th class="text-center">Actual boxes</th>
                                <th class="text-center">Actual pieces</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $row)
                            <tr>
                                <td class="font-weight-bold">{{ $row->color->name ?? 'N/A' }}</td>
                                <td class="text-center"><span class="badge badge-light border font-monospace">{{ $row->barcode }}</span></td>
                                <td class="text-center">{{ $row->quantity }}</td>
                                <td class="text-center font-weight-bold text-success">{{ $row->total_boxes }}</td>
                                <td class="text-center font-weight-bold">{{ $row->total_boxes * $row->quantity }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No items found for this location.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- INDIVIDUAL PHYSICAL BOXES CARD -->
        @if(isset($boxes) && count($boxes) > 0)
        <div class="erp-card mb-2">
            <div class="erp-card-header">
                <h3 class="erp-card-title"><i class="fas fa-box-open mr-1"></i> Physical box items in rack ({{ count($boxes) }})</h3>
            </div>
            <div class="erp-card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="erp-table table-bordered table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 45px;" class="text-center">#</th>
                                <th>Box no.</th>
                                <th>Carton no.</th>
                                <th>Color</th>
                                <th class="text-center">Barcode</th>
                                <th class="text-center">Quantity (pcs)</th>
                                <th class="text-center">Packed date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($boxes as $idx => $b)
                            <tr>
                                <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                <td><strong>{{ $b->box_no ?? 'N/A' }}</strong></td>
                                <td>{{ $b->carton_no ? 'Carton #' . $b->carton_no : 'N/A' }}</td>
                                <td>{{ $b->color->name ?? 'N/A' }}</td>
                                <td class="text-center"><span class="badge badge-light border">{{ $b->barcode }}</span></td>
                                <td class="text-center font-weight-bold">{{ $b->quantity }}</td>
                                <td class="text-center text-muted small">{{ $b->created_at ? date('d M Y, h:i A', strtotime($b->created_at)) : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
