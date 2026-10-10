@extends('admin.layouts.app')

@section('content')

<div class="content-wrapper erp-page p-2">

    {{-- ================= HEADER ================= --}}
    <div class="erp-header-bar mb-3">
        <div class="d-flex align-items-center">
            <h1 class="erp-page-title mb-0">Sample catalog details</h1>
            <div class="erp-report-meta ml-3">
                <span class="meta-item">Batch: <span class="barcode-display ml-1">{{ $batch->batch_no }}</span></span>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.inventory.sample-product.index') }}" class="btn-erp btn-erp-outline mr-2">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <a href="{{ route('admin.inventory.sample-product.edit', $batch->id) }}" class="btn-erp btn-erp-warning">
                <i class="fas fa-edit mr-1"></i> Edit catalog
            </a>
        </div>
    </div>

    {{-- ================= BATCH METRICS ================= --}}
    <div class="card erp-card mb-3">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap" style="gap: 16px;">
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
                            <th class="text-right">MRP</th>
                            <th class="text-right">Discount</th>
                            <th class="text-right">Final price</th>
                            <th class="text-center" style="width: 150px;">Barcode</th>
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
                            <td class="align-middle text-right">
                                ₹{{ number_format($sample->discount_percent < 100 ? $sample->final_price / (1 - ($sample->discount_percent / 100)) : $sample->final_price, 2) }}
                            </td>
                            <td class="align-middle text-right">
                                <span class="text-danger font-weight-bold">{{ number_format($sample->discount_percent, 2) }}%</span>
                            </td>
                            <td class="align-middle text-right font-weight-bold" style="color: var(--brand-dark-green, #05421c);">
                                ₹{{ number_format($sample->final_price, 2) }}
                            </td>
                            <td class="align-middle text-center">
                                <span class="barcode-display">{{ $sample->barcode }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 text-secondary d-block"></i>
                                <p class="mb-0">No products found in this sample catalog.</p>
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
