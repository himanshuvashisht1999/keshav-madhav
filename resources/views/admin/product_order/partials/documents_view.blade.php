{{-- Read-only list of a sales order's documents (order view page).
     Usage: @include('admin.product_order.partials.documents_view', ['order' => $order_main, 'documents' => $documents]) --}}
@php
    $documents = $documents ?? collect();
    $canEdit = $order && method_exists($order, 'orderLots') && $order->orderLots()->count() == 0;
@endphp
<div class="erp-card mb-2 order-docs-strip">
    <span class="order-docs-title"><i class="fas fa-paperclip mr-1"></i> Documents</span>

    @if(($order && $order->corporate_order_file) || $documents->isNotEmpty())
        <ul class="order-docs-list">
            @if($order && $order->corporate_order_file)
                <li title="Order file: {{ $order->corporate_order_file }}">
                    <i class="fas fa-file-invoice text-success"></i>
                    <a href="{{ asset('assets/products/' . $order->corporate_order_file) }}" target="_blank" class="order-docs-name">Order file</a>
                </li>
            @endif
            @foreach($documents as $doc)
                <li title="{{ $doc->original_name }} · {{ $doc->readable_size }}{{ $doc->created_at ? ' · ' . $doc->created_at->format('d M Y') : '' }}{{ $doc->uploaded_by ? ' · ' . $doc->uploaded_by : '' }}">
                    <i class="fas {{ str_contains((string) $doc->mime_type, 'pdf') ? 'fa-file-pdf text-danger' : (str_starts_with((string) $doc->mime_type, 'image/') ? 'fa-file-image text-primary' : 'fa-file-alt text-secondary') }}"></i>
                    <a href="{{ $doc->url }}" target="_blank" class="order-docs-name">{{ $doc->original_name }}</a>
                    <span class="order-docs-size">{{ $doc->readable_size }}</span>
                </li>
            @endforeach
        </ul>
    @else
        <span class="text-muted small">No documents attached.</span>
    @endif

    @if($canEdit)
        <a href="{{ route('admin.product_order.editOrderMain', ['id' => $order->id]) }}" class="small font-weight-bold ml-auto">
            <i class="fas fa-edit mr-1"></i> Add / remove in Edit order
        </a>
    @endif
</div>
