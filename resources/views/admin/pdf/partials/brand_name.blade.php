@php
    /*
     * Brand name for PDF headers.
     * "SNAPKID" is printed as the real wordmark (same lettering as the logo / sidebar);
     * any other name (other brand / company) is printed as plain text.
     * Usage: @include('admin.pdf.partials.brand_name', ['name' => $brandTitle, 'height' => 26])
     */
    static $__wordmarkUri = null;
    $__name = trim((string) ($name ?? 'SNAPKID'));
    $__height = (int) ($height ?? 26);
    if (strtoupper($__name) === 'SNAPKID') {
        if ($__wordmarkUri === null) {
            $__file = public_path('admin_assets/img/snapkid-wordmark.png');
            $__wordmarkUri = file_exists($__file) ? 'data:image/png;base64,' . base64_encode(file_get_contents($__file)) : '';
        }
    }
@endphp
@if(strtoupper($__name) === 'SNAPKID' && $__wordmarkUri)<img src="{{ $__wordmarkUri }}" alt="snapkid" style="height: {{ $__height }}px; vertical-align: middle;">@else{{ $__name }}@endif
