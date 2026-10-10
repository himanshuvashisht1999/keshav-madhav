@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-boxes text-primary"></i> Create Bill of Materials
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.production-goods.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="erp-card">
        <div class="px-3 py-2 border-bottom bg-light font-weight-bold" style="font-size: 13px; color: var(--erp-text-main);">
            <i class="fas fa-edit mr-1 text-primary"></i> Bill of Materials Information
        </div>
        <form action="{{ route('admin.master.production-goods-item.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="product_id" value="{{ request()->get('id') }}">
            
            <div class="erp-card-body p-3">
                <div class="row">
                    <!-- Fabric Section -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label font-weight-bold d-block" style="font-size: 13px;">Select Fabric SKU</label>
                        @if($selectecFabrics->isNotEmpty())
                            <div id="fabric-container">
                            @foreach($selectecFabrics as $index => $fabric)
                                <input type="hidden" name="old_fabric_id[]" value="{{ $fabric->id }}">
                                <div class="fabric-row row mb-2 align-items-center">
                                    <div class="col-md-6">
                                        <select name="old_fabric_sku[{{ $fabric->id }}]" class="form-control select2 erp-filter-select" required>
                                            <option value="">Select Fabric</option>
                                            @foreach($fabrics as $single_data)
                                                <option value="{{ $single_data->sku }}" {{ $fabric->fabric_sku == $single_data->sku ? 'selected' : '' }}>
                                                    {{ $single_data->sku }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="number" name="old_fabric_meter[{{ $fabric->id }}]" class="form-control erp-filter-input" placeholder="Meter" min="0.01" step="0.01" value="{{ $fabric->meter }}" required>
                                    </div>
                                    <div class="col-md-1">
                                        @if($index == 0)
                                            <button type="button" class="btn-erp btn-erp-primary add-fabric"><i class="fa fa-plus"></i></button>
                                        @else
                                            <button type="button" class="btn-erp btn-erp-danger remove-fabric"><i class="fa fa-minus"></i></button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        @else
                        <div id="fabric-container">
                            <div class="fabric-row row mb-2 align-items-center">
                                <div class="col-md-6">
                                    <select name="fabric_sku[]" class="form-control select2 erp-filter-select" required>
                                        <option value="">Select Fabric</option>
                                        @foreach($fabrics as $single_data)
                                            <option value="{{ $single_data->sku }}" {{ old('fabric_sku') == $single_data->sku ? 'selected' : '' }}>
                                                {{ $single_data->sku }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="number" name="fabric_meter[]" class="form-control erp-filter-input" placeholder="Enter meter" step="0.01" min="0.01" required>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn-erp btn-erp-primary add-fabric"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Item Section -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label font-weight-bold d-block" style="font-size: 13px;">Select Item SKU</label>
                        @if($productItems->isNotEmpty())
                            <div id="items-container">
                            @foreach($productItems as $index => $item)
                                <input type="hidden" name="old_items_id[]" value="{{ $item->id }}">
                                <div class="items-row row mb-2 align-items-center">
                                    <div class="col-md-6">
                                        <select name="old_items_sku[{{ $item->id }}]" class="form-control select2 stage-select erp-filter-select" required>
                                            <option value="">Select Item SKU</option>
                                            @foreach($itemCatogeriesValue as $itemCatogery)
                                                <option value="{{ $itemCatogery->sku }}" {{ ($item->item_attribute_value_sku == $itemCatogery->sku) ? 'selected' : '' }}>{{ $itemCatogery->sku }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="number" name="old_item_quantity[{{ $item->id }}]" class="form-control erp-filter-input" placeholder="Quantity" step="1" min="1" value="{{ $item->quantity }}" required>
                                    </div>
                                    <div class="col-md-1">
                                        @if($index == 0)
                                            <button type="button" class="btn-erp btn-erp-primary add-item"><i class="fa fa-plus"></i></button>
                                        @else
                                            <button type="button" class="btn-erp btn-erp-danger remove-item"><i class="fa fa-minus"></i></button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        @else
                        <div id="items-container">
                            <div class="items-row row mb-2 align-items-center">
                                <div class="col-md-6">
                                    <select name="items_sku[]" class="form-control select2 stage-select erp-filter-select" required>
                                        <option value="">Select Item SKU</option>
                                        @foreach($itemCatogeriesValue as $itemCatogery)
                                            <option value="{{ $itemCatogery->sku }}">{{ $itemCatogery->sku }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="number" name="item_quantity[]" class="form-control erp-filter-input" placeholder="Quantity" step="1" min="1" required>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn-erp btn-erp-primary add-item"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 border-top bg-white d-flex justify-content-end" style="gap: 8px;">
                <a href="{{ route('admin.master.production-goods.index') }}" class="btn-erp btn-erp-outline">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Add More Item
        $(document).on('click', '.add-item', function () {
            let newRow = `<div class="items-row row mb-2 align-items-center">
                            <div class="col-md-6">
                                <select name="items_sku[]" class="form-control select2 stage-select erp-filter-select" required>
                                    <option value="">Select Item SKU</option>
                                    @foreach($itemCatogeriesValue as $itemCatogery)
                                        <option value="{{ $itemCatogery->sku }}">{{ $itemCatogery->sku }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <input type="number" name="item_quantity[]" class="form-control erp-filter-input" placeholder="Quantity" step="1" min="1" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn-erp btn-erp-danger remove-item"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>`;
            $('#items-container').append(newRow);
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
        });

        // Remove item row
        $(document).on('click', '.remove-item', function () {
            $(this).closest('.items-row').remove();
        });

        // Add More Fabric
        $(document).on('click', '.add-fabric', function () {
            let newRow = `
                <div class="fabric-row row mb-2 align-items-center">
                    <div class="col-md-6">
                        <select name="fabric_sku[]" class="form-control select2 erp-filter-select" required>
                            <option value="">Select Fabric</option>
                            @foreach($fabrics as $single_data)
                                <option value="{{$single_data->sku}}">{{$single_data->sku}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="number" name="fabric_meter[]" class="form-control erp-filter-input" placeholder="Enter meter" step="0.01" min="0.01" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn-erp btn-erp-danger remove-fabric"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
            `;
            $('#fabric-container').append(newRow);
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
        });

        // Remove Fabric Row
        $(document).on('click', '.remove-fabric', function () {
            $(this).closest('.fabric-row').remove();
        });
    });
</script>
@endsection
