{{-- Price of one piece: price, MRP crossed out and the discount; just "MRP" when the price is the MRP.
     Same look as skPriceHtml() in the layout (used by the screens drawn in JavaScript). --}}
@php
    $skMoney = fn($v) => '₹' . number_format((float) $v, fmod((float) $v, 1) ? 2 : 0);
    $skPrice = (float) ($price ?? 0);
    $skMrp = (float) ($mrp ?? 0);
    $skOff = ($skMrp > $skPrice && $skMrp > 0) ? (int) round(($skMrp - $skPrice) / $skMrp * 100) : 0;
@endphp
<span class="sk-price-now">{{ $skMoney($skPrice) }}</span>
@if($skMrp > $skPrice)
    <span class="sk-price-mrp">{{ $skMoney($skMrp) }}</span>
    @if($skOff > 0)<span class="sk-price-off">{{ $skOff }}% OFF</span>@endif
@elseif($skMrp > 0)
    <span class="sk-price-tag">MRP</span>
@endif
