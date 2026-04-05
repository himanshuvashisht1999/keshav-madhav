@extends('admin.layouts.app')
@section('content')

<div class="content-wrapper">

    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Product Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.production-goods.index')}}">Available Products</a></li>
                        <li class="breadcrumb-item active">View Product</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">

                <div class="card-header">
                    <h3 class="card-title">Product Specification</h3>

                    <div class="card-tools">
                        <a href="{{ route('admin.master.production-goods.edit', ['id' => $data->id]) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>

                <div class="card-body">

                    <div class="row">

                        {{-- Company --}}
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold d-block">Company</label>

                            <p class="mb-0">
                                @if($data->company_id == 2)
                                    Snapkid
                                @else
                                    General
                                @endif
                            </p>
                        </div>

                        {{-- DESIGN NUMBER (Shown for both companies) --}}
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold d-block">Design Number</label>
                            <p class="mb-0">{{ $data->design_number ?? '-' }}</p>
                        </div>

                        {{-- BRAND --}}
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold d-block">Brand</label>
                            <p class="mb-0">{{ $data->brand->name ?? '-' }}</p>
                        </div>

                        {{-- PRODUCT NAME --}}
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold d-block">Product Name</label>
                            <p class="mb-0">{{ $data->name_of_garment ?? '-' }}</p>
                        </div>

                        {{-- FITTING --}}
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold d-block">Fitting</label>
                            <p class="mb-0">{{ $data->fitting->name ?? '-' }}</p>
                        </div>

                        {{-- PATTERN --}}
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold d-block">Pattern</label>
                            <p class="mb-0">{{ $data->pattern->name ?? '-' }}</p>
                        </div>

                        {{-- ============================= --}}
                        {{-- FIELDS ONLY FOR GENERAL COMPANY --}}
                        {{-- ============================= --}}
                        @if($data->company_id == 1)

                            {{-- Product Type --}}
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold d-block">Product Type</label>
                                @php
                                    $type = $product_types->firstWhere('sku', $data->type_of_garment);
                                @endphp
                                <p class="mb-0">{{ $type ? $type->name : '-' }}</p>
                            </div>

                            {{-- Product Size --}}
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold d-block">Product Size</label>
                                @php
                                    $size = $sizes->firstWhere('id', $data->master_size_id);
                                @endphp
                                <p class="mb-0">{{ $size ? $size->sku : '-' }}</p>
                            </div>

                            {{-- Product Color --}}
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold d-block">Color</label>
                                @php
                                    $color = $colors->firstWhere('id', $data->master_color_id);
                                @endphp
                                <p class="mb-0">{{ $color ? $color->name : '-' }}</p>
                            </div>

                            {{-- Product Pattern --}}
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold d-block">Product Pattern</label>
                                <p class="mb-0">{{ $data->garment_pattern ?? '-' }}</p>
                            </div>

                        @endif
                        {{-- END GENERAL FIELDS --}}

                        {{-- STATUS --}}
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold d-block">Status</label>
                            <p class="mb-0">
                                @if($data->status == 1)
                                    <span class="badge badge-success">Published</span>
                                @else
                                    <span class="badge badge-primary">Pending BOM</span>
                                @endif
                            </p>
                        </div>

                    </div>

                    <hr>

                    {{-- ============================= --}}
                    {{-- PRODUCT VARIANTS (SIZE SETS) --}}
                    {{-- ============================= --}}
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="font-weight-bold mb-3">Size Sets & Pricing</h5>
                            @if($data->variants && $data->variants->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 20%">Size Set</th>
                                                <th style="width: 50%">Colors</th>
                                                <th style="width: 30%">MRP (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data->variants as $variant)
                                                <tr>
                                                    <td>
                                                        @if($variant->sizeSet)
                                                            <strong>{{ $variant->sizeSet->name }}</strong> ({{ $variant->sizeSet->set_size }})
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($variant->colors && $variant->colors->count() > 0)
                                                            <div class="d-flex flex-wrap" style="gap: 5px;">
                                                                @foreach($variant->colors as $clr)
                                                                    <span class="badge badge-pill border shadow-sm" style="background-color: #f8f9fa; color: #333; font-size: 0.9em; padding: 5px 10px;">
                                                                        {{ $clr->name }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="text-muted">No colors specified</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="font-weight-bold text-success">{{ number_format($variant->mrp, 2) }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted border p-3 rounded bg-light">No size sets defined for this product.</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    {{-- ============================= --}}
                    {{-- IMAGES SECTION --}}
                    {{-- ============================= --}}

                    <div class="row">

                        {{-- MAIN IMAGE --}}
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold d-block">Main Image</label>

                            @php
                                $mainImg = optional($data->images->where('is_main', 1)->first());
                            @endphp

                            <img src="{{ $mainImg ? $mainImg->image : asset('assets/products/default-image.png') }}"
                                 class="img-thumbnail"
                                 style="max-height:240px;object-fit:cover;">
                        </div>

                        {{-- OTHER IMAGES --}}
                        <div class="col-md-8 mb-3 d-none">
                            <label class="font-weight-bold d-block">Other Images</label>

                            <div class="d-flex flex-wrap" style="gap:12px;">
                                @forelse($data->images->where('is_main', 0) as $img)
                                    <img src="{{ $img->image }}" class="img-thumbnail"
                                         style="max-width:140px; max-height:140px; object-fit:cover;">
                                @empty
                                    <span class="text-muted">No additional images added.</span>
                                @endforelse
                            </div>
                        </div>

                    </div>

                    <hr>

                    {{-- ============================= --}}
                    {{-- PRODUCTION STAGES (GENERAL ONLY) --}}
                    {{-- ============================= --}}
                    @if($data->company_id == 1)

                        <div class="row d-none">
                            <div class="col-md-12">

                                <label class="font-weight-bold d-block">Production Stages (in order)</label>

                                @php
                                    $printing = $data->printing_stage_after;
                                    $emb = $data->embroidery_stage_after;

                                    $stages = $data->product_stages->whereNotIn('master_stage_id',[1,2])->values();
                                @endphp

                                @if($stages->isEmpty())
                                    <p class="text-muted">No stages defined.</p>
                                @else
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Stage</th>
                                                <th>Printing / Embroidery</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($stages as $i => $st)
                                                @php
                                                    $stageMaster = $product_stages->firstWhere('id', $st->master_stage_id);
                                                @endphp

                                                <tr>
                                                    <td>{{ $i+1 }}</td>
                                                    <td>{{ $stageMaster->name ?? 'Stage '.$st->master_stage_id }}</td>
                                                    <td>
                                                        @if($printing == $st->master_stage_id)
                                                            <span class="badge badge-info">Printing after this</span>
                                                        @endif
                                                        @if($emb == $st->master_stage_id)
                                                            <span class="badge badge-warning ml-1">Embroidery after this</span>
                                                        @endif

                                                        @if($printing != $st->master_stage_id && $emb != $st->master_stage_id)
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>

                                            @endforeach
                                        </tbody>

                                    </table>
                                @endif

                            </div>
                        </div>

                        <hr>

                    @endif
                    {{-- END GENERAL STAGES --}}

                    {{-- ============================= --}}
                    {{-- FABRIC DETAILS --}}
                    {{-- Visible for BOTH companies (you decide) --}}
                    {{-- ============================= --}}
                    <div class="row d-none">
                        <div class="col-md-12">

                            <label class="font-weight-bold d-block">Fabric Details</label>

                            @if($data->bill_of_materials->isEmpty())
                                <p class="text-muted">No fabric added.</p>
                            @else
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Fabric SKU</th>
                                            <th>Meter</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($data->bill_of_materials as $i => $bom)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ $bom->fabric_sku }}</td>
                                            <td>{{ $bom->meter }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            @endif

                        </div>
                    </div>

                </div> <!-- card-body -->

            </div>
        </div>
    </section>

</div>

@endsection
