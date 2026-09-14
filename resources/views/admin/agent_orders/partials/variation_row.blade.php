@php
    $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
    $dispQty = (int) ($dispatchedQty ?? ($dispatched_quantities[$vKey] ?? 0));
    $availBoxes = (int) ($variation->available_boxes ?? 0);
    $physBoxes = (int) ($variation->physical_boxes ?? $availBoxes);
    $maxBoxes = (!isset($variation->is_advance_sample) || !$variation->is_advance_sample) ? max($physBoxes, $availBoxes, $dispQty, (int)($initialQty ?? 0)) : null;
@endphp
<tr class="variation-row {{ ($initialQty ?? 0) > 0 ? 'has-qty' : '' }}" data-key="{{ $vKey }}"
    data-product-id="{{ $variation->product_id }}"
    data-color-id="{{ $variation->color_id }}"
    data-size-set-id="{{ $variation->size_set_id }}"
    data-pcs="{{ $variation->pcs_per_box }}" data-price="{{ $variation->unit_price }}"
    data-available="{{ $variation->available_boxes }}"
    data-physical="{{ $physBoxes }}"
    data-dispatched="{{ $dispQty }}">
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
        <div class="d-flex align-items-center">
            <span class="text-dark font-weight-bold">{{ $variation->design_number }}</span>
            @if($dispQty > 0)
                <span class="badge badge-warning text-dark font-weight-bold ml-2 px-2 py-1 shadow-xs" title="Already Dispatched Boxes (Locked)">
                    <i class="fas fa-truck mr-1"></i>Disp: {{ $dispQty }}
                </span>
            @endif
        </div>
        <div class="text-primary small font-weight-500">{{ trim(($variation->series_name ?? '') . ' ' . $variation->name_of_garment) }}</div>
        <div class="text-muted small"><i class="fas fa-palette mr-1"></i>
            {{ str_replace(' ('.$variation->color_id.')', '', $variation->color_name) }} ({{ $variation->color_id }})</div>
    </td>
    <td>
        <span class="badge badge-outline-secondary px-2 py-1">{{ $variation->size_set_name }}</span>
    </td>
    <td class="text-center font-weight-bold">{{ number_format($variation->pcs_per_box, 0) }}</td>
    <td class="text-center">
        @if(isset($variation->is_advance_sample) && $variation->is_advance_sample)
            <span class="badge badge-success px-2 py-1">Advance Sample</span>
        @else
            @if($availBoxes > 0)
                <span class="badge badge-info px-2 py-1" title="{{ $physBoxes }} physical in warehouse, {{ (int)($variation->allocated_boxes ?? 0) }} allocated to other orders">{{ $availBoxes }} Available</span>
                @if(($variation->allocated_boxes ?? 0) > 0)
                    <small class="text-muted d-block" style="font-size: 10px;">({{ $physBoxes }} in stock)</small>
                @endif
            @else
                <span class="badge badge-warning text-dark px-2 py-1" title="Physical stock: {{ $physBoxes }} | Allocated to other orders: {{ (int)($variation->allocated_boxes ?? 0) }}">
                    {{ $physBoxes }} in Stock (0 Free)
                </span>
            @endif
        @endif
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
                min="{{ $dispQty }}" data-min="{{ $dispQty }}" @if($maxBoxes !== null) max="{{ $maxBoxes }}" @endif data-key="{{ $vKey }}">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary btn-plus" type="button">+</button>
            </div>
        </div>
        @if($dispQty > 0)
            <small class="text-muted d-block mt-1 font-italic" style="font-size: 10px;">Min {{ $dispQty }} (Dispatched)</small>
        @endif
    </td>
</tr>
