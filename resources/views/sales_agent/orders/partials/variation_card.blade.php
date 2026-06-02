@php
    $see_price = Auth::guard('sales_agent')->user()->see_price;
@endphp
<div class="card variation-card shadow-sm border-0 h-100 animate__animated animate__fadeIn" 
     data-key="{{ $vKey }}"
     data-product-id="{{ $variation->product_id }}"
     data-color-id="{{ $variation->color_id }}"
     data-size-set-id="{{ $variation->size_set_id }}"
     data-pcs="{{ $variation->pcs_per_box }}"
     data-price="{{ $variation->unit_price }}">
    
    <div class="position-relative">
        @if($image)
            <img src="{{ asset('assets/products/' . $image) }}" class="card-img-top zoom-image" alt="Product" style="height: 180px; object-fit: cover; border-radius: 15px 15px 0 0; cursor: pointer;">
        @else
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px; border-radius: 15px 15px 0 0;">
                <i class="fas fa-image fa-3x text-muted opacity-25"></i>
            </div>
        @endif
    </div>
    
    <div class="card-body p-3">
        <h6 class="font-weight-bold text-dark mb-1 text-truncate">{{ $variation->series_name }} {{ $variation->name_of_garment }}</h6>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small text-muted"><i class="fas fa-palette mr-1"></i> {{ $variation->color_name }}</span>
            <span class="small text-muted"><i class="fas fa-ruler-combined mr-1"></i> {{ $variation->size_set_name }}</span>
        </div>
        
        <div class="bg-light rounded p-2 mb-3 d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted d-block uppercase tracking-wider font-weight-bold" style="font-size: 0.65rem;">Available</small>
                <span class="font-weight-bold text-dark">{{ $variation->available_boxes }} Boxes</span>
            </div>
            @if($see_price)
            <div class="text-right">
                <small class="text-muted d-block uppercase tracking-wider font-weight-bold" style="font-size: 0.65rem;">Price</small>
                <span class="text-primary font-weight-bold">₹{{ number_format($variation->unit_price, 2) }}</span>
            </div>
            @endif
        </div>

        <div class="quantity-control-app d-flex align-items-center justify-content-between p-1">
            <button class="btn btn-q btn-minus"><i class="fas fa-minus"></i></button>
            <input type="number" class="box-qty-input font-weight-bold" value="0" min="0" max="{{ $variation->available_boxes }}" readonly>
            <button class="btn btn-q btn-plus"><i class="fas fa-plus"></i></button>
        </div>
    </div>
</div>
