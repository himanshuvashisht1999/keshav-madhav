@extends('admin.layouts.app')

@section('content')

<div class="content-wrapper erp-page p-2">

    {{-- ================= HEADER ================= --}}
    <div class="erp-header-bar mb-3">
        <div class="d-flex align-items-center">
            <h1 class="erp-page-title mb-0">Sample set details</h1>
            <div class="erp-report-meta ml-3">
                <span class="meta-item">Batch: <span class="barcode-display ml-1">{{ $batch->batch_no }}</span></span>
            </div>
        </div>
        <div class="d-flex align-items-center" style="gap: 6px;">
            <a href="{{ route('admin.inventory.fair-product.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <a href="{{ route('admin.inventory.fair-product.generate-pdf-batch', $batch->id) }}?show_wsp=yes" class="btn-erp btn-erp-outline" title="PDF with WSP" target="_blank">
                <i class="fas fa-file-invoice-dollar text-success mr-1"></i> WSP
            </a>
            <a href="{{ route('admin.inventory.fair-product.generate-pdf-batch', $batch->id) }}?show_wsp=no" class="btn-erp btn-erp-outline" title="PDF without price" target="_blank">
                <i class="fas fa-file-pdf text-danger mr-1"></i> No WSP
            </a>
            <a href="{{ route('admin.inventory.fair-product.download-prn') }}?batch_id={{ $batch->id }}" class="btn-erp btn-erp-outline" title="Download PRN (Printer)">
                <i class="fas fa-print mr-1"></i> PRN
            </a>
            <a href="{{ route('admin.inventory.fair-product.download-prn') }}?batch_id={{ $batch->id }}&type=qrcode" class="btn-erp btn-erp-outline" title="Download PRN (QR code)">
                <i class="fas fa-qrcode mr-1"></i> PRN (QR)
            </a>
            <a href="{{ route('admin.inventory.fair-product.edit', $batch->id) }}" class="btn-erp btn-erp-warning">
                <i class="fas fa-edit mr-1"></i> Edit batch
            </a>
        </div>
    </div>

    {{-- ================= METRICS & AGENTS ================= --}}
    <div class="row g-2 mb-3">
        <div class="col-md-5">
            <div class="card erp-card p-3 h-100">
                <div class="d-flex flex-wrap" style="gap: 14px;">
                    <div class="erp-stat-pill">
                        Batch number: <strong class="ml-1">{{ $batch->batch_no }}</strong>
                    </div>
                    <div class="erp-stat-pill">
                        Created date: <strong class="ml-1">{{ $batch->created_at->format('d M Y') }}</strong>
                    </div>
                    <div class="erp-stat-pill">
                        Total products: <strong class="ml-1">{{ $batch->products->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card erp-card p-3 h-100">
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <span class="erp-label mb-0 mr-2">Sales agents:</span>
                    @php
                        $salesAgents = \App\Models\SalesAgent::where('status', 1)->get();
                        $agentNames = [];
                        if(is_array($batch->sales_agent_ids)) {
                            foreach($batch->sales_agent_ids as $id) {
                                $ag = $salesAgents->where('id', $id)->first();
                                if($ag) {
                                    $agentNames[] = $ag->name;
                                }
                            }
                        }
                    @endphp
                    @if(count($agentNames) > 0)
                        @foreach($agentNames as $name)
                            <span class="badge badge-light border px-2 py-1">{{ $name }}</span>
                        @endforeach
                    @else
                        <span class="text-muted small">No specific sales agents assigned</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ================= PRODUCTS TABLE ================= --}}
    <div class="card erp-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table erp-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th style="width: 60px;">Image</th>
                            <th>Design no</th>
                            <th>Product description</th>
                            <th>Size set</th>
                            <th>Colors</th>
                            <th class="text-right">MRP</th>
                            <th class="text-right">Discount</th>
                            <th class="text-right">Final price</th>
                            <th class="text-center" style="width: 160px;">Barcode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batch->products as $index => $sample)
                        <tr>
                            <td class="text-center text-muted align-middle">{{ $index + 1 }}</td>
                            <td class="align-middle">
                                @if($sample->product->display_image)
                                    <img src="{{ asset('assets/products/' . $sample->product->display_image) }}" class="product-img" alt="Product image">
                                @else
                                    <div class="product-img d-flex align-items-center justify-content-center bg-light text-muted">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="align-middle font-weight-bold">
                                {{ $sample->product->design_number }}
                            </td>
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">{{ $sample->product->series->name ?? '' }} {{ $sample->product->name_of_garment }}</div>
                                <small class="text-muted">{{ $sample->product->fitting->name ?? '' }} | {{ $sample->product->pattern->name ?? '' }}</small>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-light border">{{ $sample->sizeSet->name }}</span>
                            </td>
                            <td class="align-middle">
                                @if(!empty($sample->color_names))
                                    <div class="d-flex flex-wrap" style="gap: 4px;">
                                    @foreach($sample->color_names as $colorName)
                                        <span class="badge" style="background-color: #eaf7ec; color: var(--brand-dark-green, #05421c); border: 1px solid #c2e2c8; font-weight: 600;">{{ $colorName }}</span>
                                    @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="align-middle text-right">
                                ₹{{ number_format($sample->final_price / (1 - ($sample->discount_percent / 100)), 2) }}
                            </td>
                            <td class="align-middle text-right">
                                <span class="text-danger font-weight-bold">{{ number_format($sample->discount_percent, 2) }}%</span>
                            </td>
                            <td class="align-middle text-right font-weight-bold" style="color: var(--brand-dark-green, #05421c);">
                                ₹{{ number_format($sample->final_price, 2) }}
                            </td>
                            <td class="align-middle text-center">
                                <span class="barcode-display">{{ $sample->barcode }}</span>
                                <i class="far fa-copy ml-1 text-muted copy-barcode" data-barcode="{{ $sample->barcode }}" title="Copy barcode" style="cursor: pointer;"></i>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 text-secondary d-block"></i>
                                <p class="mb-0">No products found in this sample set.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $(document).on('click', '.copy-barcode', function() {
        const barcode = $(this).data('barcode');
        navigator.clipboard.writeText(barcode).then(() => {
            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'success',
                    title: 'Barcode copied to clipboard!'
                });
            } else if (typeof toastr !== 'undefined') {
                toastr.success('Barcode copied to clipboard!');
            } else {
                alert('Barcode copied: ' + barcode);
            }
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    });
});
</script>
@endpush
