@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- PAGE HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">Warehouse Stock Actual Detail</h1>
                    <small class="text-muted">Viewing physical inventory breakdown lying in this location</small>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.inventory.warehouse_stock_actual') }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Actual Stock
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="content">
        <div class="container-fluid">
            <!-- MAIN DETAILS CARD -->
            <div class="card shadow-sm border-0 mb-4 bg-light" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="row align-items-center text-center text-md-left">
                        <div class="col-md-3 border-right mb-2 mb-md-0 d-flex align-items-center">
                            @if(isset($items[0]) && $items[0]->variant_id)
                                @php
                                    $imgSrc = $items[0]->product_image ? asset('assets/products/' . $items[0]->product_image) : asset('images/image-placeholder.png');
                                @endphp
                                <img src="{{ $imgSrc }}" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;" onerror="this.src='{{ asset('images/image-placeholder.png') }}'" class="mr-3">
                            @endif
                            <div>
                                <label class="small text-muted mb-0 d-block uppercase font-weight-bold"><i class="fas fa-box text-primary mr-1"></i> Product Name</label>
                                <span class="h6 font-weight-bold text-dark mb-0">{{ $product->series->name ?? '' }} {{ $product->name_of_garment ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-2 border-right mb-2 mb-md-0">
                            <label class="small text-muted mb-0 d-block uppercase font-weight-bold">Design Number</label>
                            <span class="h6 font-weight-bold text-dark mb-0">{{ $product->design_number ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-2 border-right mb-2 mb-md-0">
                            <label class="small text-muted mb-0 d-block uppercase font-weight-bold">Size Set</label>
                            <span class="h6 font-weight-bold text-dark mb-0">{{ $sizeSet->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-3 border-right mb-2 mb-md-0">
                            <label class="small text-muted mb-0 d-block uppercase font-weight-bold"><i class="fas fa-warehouse text-danger mr-1"></i> Warehouse</label>
                            <span class="h6 font-weight-bold text-dark mb-0">{{ $rack->storeroom->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted mb-0 d-block uppercase font-weight-bold"><i class="fas fa-th text-info mr-1"></i> Rack / Bin</label>
                            <span class="h6 font-weight-bold text-dark mb-0">{{ $rack->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLOR BREAKDOWN CARD -->
            <div class="card shadow border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold mb-0"><i class="fas fa-palette text-info mr-2"></i> Color Breakdown (Actual Physical Stock)</h5>
                    <span class="badge badge-success px-3 py-2" style="font-size: 13px;">
                        Total Boxes: {{ $items->sum('total_boxes') }} | Total Pcs: {{ $items->sum(fn($i) => $i->total_boxes * $i->quantity) }}
                    </span>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light contrast-text">
                                <tr>
                                    <th class="py-3 px-4">Color</th>
                                    <th class="py-3 text-center">Barcode</th>
                                    <th class="py-3 text-center">Pieces Per Box</th>
                                    <th class="py-3 text-center">Actual Boxes</th>
                                    <th class="py-3 text-center">Actual Pieces</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $row)
                                <tr>
                                    <td class="px-4 font-weight-bold">{{ $row->color->name ?? 'N/A' }}</td>
                                    <td class="text-center"><span class="badge badge-light border font-monospace">{{ $row->barcode }}</span></td>
                                    <td class="text-center">{{ $row->quantity }}</td>
                                    <td class="text-center"><span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 13px;">{{ $row->total_boxes }}</span></td>
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
            <div class="card shadow border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-title font-weight-bold mb-0"><i class="fas fa-box-open text-primary mr-2"></i> Physical Box Items in Rack ({{ count($boxes) }})</h5>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-2 px-3">#</th>
                                    <th class="py-2">Box No</th>
                                    <th class="py-2">Carton No</th>
                                    <th class="py-2">Color</th>
                                    <th class="py-2 text-center">Barcode</th>
                                    <th class="py-2 text-center">Quantity (Pcs)</th>
                                    <th class="py-2 text-center">Packed Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($boxes as $idx => $b)
                                <tr>
                                    <td class="px-3 text-muted">{{ $idx + 1 }}</td>
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
    </section>
</div>
@endsection
