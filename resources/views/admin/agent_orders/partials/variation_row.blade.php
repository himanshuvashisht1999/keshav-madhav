@php
    $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
@endphp
<tr class="variation-row {{ ($initialQty ?? 0) > 0 ? 'has-qty' : '' }}" data-key="{{ $vKey }}"
    data-product-id="{{ $variation->product_id }}"
    data-color-id="{{ $variation->color_id }}"
    data-size-set-id="{{ $variation->size_set_id }}"
    data-pcs="{{ $variation->pcs_per_box }}" data-price="{{ $variation->unit_price }}"
    data-available="{{ $variation->available_boxes }}">
    <td>
        @if($image)
            <img src="{{ asset('assets/products/' . $image) }}" alt="Product"
                class="rounded border shadow-xs"
                style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                onclick="window.open(this.src)">
        @else
            <div class="bg-light rounded border d-flex align-items-center justify-content-center"
                style="width: 50px; height: 50px;">
                <i class="fas fa-image text-muted opacity-50"></i>
            </div>
        @endif
    </td>
    <td>
        <div class="text-dark font-weight-bold">{{ $variation->design_number }}</div>
        <div class="text-primary small font-weight-500">{{ trim(($variation->series_name ?? '') . ' ' . $variation->name_of_garment) }}</div>
        <div class="text-muted small"><i class="fas fa-palette mr-1"></i>
            {{ $variation->color_name }}</div>
    </td>
    <td>
        <span class="badge badge-outline-secondary px-2 py-1">{{ $variation->size_set_name }}</span>
    </td>
    <td class="text-center font-weight-bold">{{ number_format($variation->pcs_per_box, 0) }}</td>
    <td class="text-center">
        <span class="badge badge-info px-2 py-1">{{ $variation->available_boxes }} Boxes</span>
    </td>
    <td class="text-right">
        <div class="text-dark font-weight-bold">₹{{ number_format($variation->unit_price, 2) }}</div>
        <small class="text-muted">MRP: ₹{{ number_format($variation->mrp, 2) }}</small>
    </td>
    <td class="text-center px-4">
        <div class="input-group input-group-sm quantity-control">
            <div class="input-group-prepend">
                <button class="btn btn-outline-secondary btn-minus" type="button">-</button>
            </div>
            <input type="number" class="form-control text-center box-qty-input" value="{{ $initialQty ?? 0 }}"
                min="0" max="{{ $variation->available_boxes + ($initialQty ?? 0) }}" data-key="{{ $vKey }}">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary btn-plus" type="button">+</button>
            </div>
        </div>
    </td>
</tr>
