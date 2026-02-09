@extends('sales_agent.layouts.app', ['title' => 'Browse Inventory'])

@section('content')
    <div class="container pb-5 mb-5 mt-3">
        <div class="mb-4">
            <h2 class="font-weight-bold h4 mb-1 text-dark">Browse Inventory</h2>
            <p class="text-muted small">Grouped by Design, Color and Size Set</p>
        </div>

        <!-- Filters Section -->
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('agent.inventory.index') }}" id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-md-3 col-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Design No</label>
                            <select name="design_number" class="form-control form-control-sm select2">
                                <option value="">All Designs</option>
                                @foreach($designs as $design)
                                    <option value="{{ $design }}" {{ request('design_number') == $design ? 'selected' : '' }}>
                                        {{ $design }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Color</label>
                            <select name="color_name" class="form-control form-control-sm select2">
                                <option value="">All Colors</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color }}" {{ request('color_name') == $color ? 'selected' : '' }}>
                                        {{ $color }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-12 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Size Set</label>
                            <select name="size_set_name" class="form-control form-control-sm select2">
                                <option value="">All Sets</option>
                                @foreach($size_sets as $set)
                                    <option value="{{ $set }}" {{ request('size_set_name') == $set ? 'selected' : '' }}>
                                        {{ $set }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-12 mb-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="{{ route('agent.inventory.index') }}" class="btn btn-secondary btn-sm px-3">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inventory List -->
        <div class="row">
            @forelse($inventories as $variation)
                @php
                    $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
                    $image = $boxImages[$vKey] ?? null;
                    // Find a random packing_box_id from this group to use for the Detail route
                    // Since the controller grouped them, we need at least one ID.
                    // Let's pass the variation group context or just use a proxy ID.
                    $proxy_box_id = DB::table('domestic_inventories')
                        ->where('product_id', $variation->product_id)
                        ->where('color_id', $variation->color_id)
                        ->where('size_set_id', $variation->size_set_id)
                        ->value('packing_box_id');
                @endphp
                <div class="col-12 col-md-6 mb-3">
                    <div class="app-card shadow-sm border-0 d-flex gap-3 p-3 bg-white" style="border-radius: 15px;">
                        <div class="variation-img">
                            @if($image)
                                <img src="{{ asset('uploads/inventory_prices/' . $image) }}" alt="Product" class="rounded border"
                                    style="width: 80px; height: 80px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded border d-flex align-items-center justify-content-center"
                                    style="width: 80px; height: 80px;">
                                    <i class="fas fa-image text-muted opacity-50 fa-2x"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <h6 class="font-weight-bold mb-0 text-dark">{{ $variation->design_number }}</h6>
                                    <small class="text-muted"><i class="fas fa-palette mr-1"></i>
                                        {{ $variation->color_name }}</small>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-primary px-2 py-1 rounded-pill">
                                        {{ $variation->available_boxes }} Boxes
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                <span class="small text-secondary">
                                    <span class="badge badge-light border">{{ $variation->size_set_name }}</span>
                                    <span class="ml-2">{{ number_format($variation->pcs_per_box, 0) }} pcs/box</span>
                                </span>
                                <div class="text-right">
                                    <span
                                        class="h6 font-weight-bold text-primary mb-0">₹{{ number_format($variation->unit_price, 2) }}</span>
                                    <div class="small text-muted" style="font-size: 10px;">per pc</div>
                                </div>
                            </div>

                            <a href="{{ route('agent.inventory.show', $proxy_box_id) }}"
                                class="btn btn-light btn-sm btn-block mt-3 rounded-pill py-2 font-weight-bold">
                                <i class="fas fa-search-plus mr-1 text-primary"></i> View All {{ $variation->available_boxes }}
                                Boxes
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3 opacity-25"></i>
                    <p class="text-muted mb-0">No matching inventory found.</p>
                    <small class="text-muted">Try adjusting your filters</small>
                </div>
            @endforelse
        </div>
    </div>
@endsection