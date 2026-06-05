@extends('admin.layouts.app')

@section('content')
<style>
    :root {
        --primary: #6366f1;
        --bg-main: #f8fafc;
        --border: #e2e8f0;
    }
    .content-wrapper {
        background-color: var(--bg-main);
    }
    .premium-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }
    .table thead th {
        background: #fcfdfe;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #64748b;
        border-bottom: 1px solid var(--border);
    }
    .barcode-display {
        font-family: monospace;
        background: #f1f5f9;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
    }
    .product-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid var(--border);
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 font-weight-bold text-dark mb-0">Sample Catalog Details</h1>
                <p class="text-muted">Viewing details for Batch <span class="barcode-display">{{ $batch->batch_no }}</span></p>
            </div>
            <div>
                <a href="{{ route('admin.inventory.sample-product.index') }}" class="btn btn-outline-secondary px-4 mr-2">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
                <a href="{{ route('admin.inventory.sample-product.edit', $batch->id) }}" class="btn btn-warning px-4">
                    <i class="fas fa-edit mr-2"></i> Edit Catalog
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="premium-card p-3">
                    <h6 class="text-muted text-uppercase small font-weight-bold mb-3">Batch Information</h6>
                    <table class="table table-sm table-borderless mb-0" style="max-width: 400px;">
                        <tr>
                            <td class="text-muted w-50">Batch Number:</td>
                            <td class="font-weight-bold"><span class="barcode-display">{{ $batch->batch_no }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created Date:</td>
                            <td class="font-weight-bold">{{ $batch->created_at->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Products:</td>
                            <td class="font-weight-bold">{{ $batch->products->count() }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="premium-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="py-3">Image</th>
                            <th class="py-3">Design No</th>
                            <th class="py-3">Product Description</th>
                            <th class="py-3">Size Set</th>
                            <th class="py-3 text-right">MRP</th>
                            <th class="py-3 text-right">Discount</th>
                            <th class="py-3 text-right">Final Price</th>
                            <th class="py-3 text-center px-4">Barcode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batch->products as $index => $sample)
                        <tr>
                            <td class="px-4 align-middle text-muted">{{ $index + 1 }}</td>
                            <td class="align-middle">
                                @if($sample->product->display_image)
                                    <img src="{{ asset('assets/products/' . $sample->product->display_image) }}" class="product-img" alt="Product Image">
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
                            <td class="align-middle text-right font-weight-bold text-success">
                                ₹{{ number_format($sample->final_price, 2) }}
                            </td>
                            <td class="align-middle text-center px-4">
                                <span class="barcode-display">{{ $sample->barcode }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                <p>No products found in this sample catalog.</p>
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
