@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-tshirt text-primary"></i> Edit Product
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
            <i class="fas fa-edit mr-1 text-primary"></i> Product Information
        </div>
        <form action="{{ route('admin.master.production-goods.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Product Type</label>
                        <input list="garment_types" name="type_of_garment" id="type_of_garment"
                            class="form-control erp-filter-input" placeholder="Select or type garment type"
                            value="{{ $data->type_of_garment }}">
                        <datalist id="garment_types">
                            @foreach($garment_types as $garment)
                                <option value="{{ $garment->type_of_garment }}">
                            @endforeach
                        </datalist>
                        @if ($errors->has('type_of_garment'))
                            <span class="invalid-feedback d-block">{{ $errors->first('type_of_garment') }}</span>
                        @endif
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Product Name</label>
                        <input type="text" name="name_of_garment" class="form-control erp-filter-input" placeholder="Enter name of garment" value="{{ $data->name_of_garment }}">
                        @if ($errors->has('name_of_garment'))
                            <span class="invalid-feedback d-block">{{ $errors->first('name_of_garment') }}</span>
                        @endif
                    </div>
                     
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Product Size</label>
                        <select name="master_size_id" class="form-control select2 erp-filter-select">
                            @foreach($sizes as $single_data)
                                <option value="{{ $single_data->id }}" {{ $data->master_size_id == $single_data->id ? 'selected' : '' }}>{{ $single_data->sku }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('master_size_id'))
                            <span class="invalid-feedback d-block">{{ $errors->first('master_size_id') }}</span>
                        @endif
                    </div> 

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Color</label>
                        <select name="master_color_id" class="form-control select2 erp-filter-select">
                            @foreach($colors as $single_data)
                                <option value="{{ $single_data->id }}" {{ $data->master_color_id == $single_data->id ? 'selected' : '' }}>{{ $single_data->name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('master_color_id'))
                            <span class="invalid-feedback d-block">{{ $errors->first('master_color_id') }}</span>
                        @endif
                    </div> 

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Is Printing</label>
                        <select name="is_printing" class="form-control select2 erp-filter-select">
                            <option value="0" {{ $data->is_printing == 0 ? 'selected' : '' }}>No</option>
                            <option value="1" {{ $data->is_printing == 1 ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Is Embroidery</label>
                        <select name="is_embroidery" class="form-control select2 erp-filter-select">
                            <option value="0" {{ $data->is_embroidery == 0 ? 'selected' : '' }}>No</option>
                            <option value="1" {{ $data->is_embroidery == 1 ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Garment Pattern</label>
                        <input list="garment_patterns" name="garment_pattern" id="garment_pattern"
                            class="form-control erp-filter-input" placeholder="Select or type garment type"
                            value="{{ $data->garment_pattern }}">
                        <datalist id="garment_patterns">
                            @foreach($garment_patterns as $single)
                                <option value="{{ $single->garment_pattern }}">
                            @endforeach
                        </datalist>
                        @if ($errors->has('garment_pattern'))
                            <span class="invalid-feedback d-block">{{ $errors->first('garment_pattern') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">SKU</label>
                        <input type="text" name="sku" id="sku" class="form-control erp-filter-input" placeholder="Auto-generated SKU" value="{{ $data->sku }}" readonly>
                        @if ($errors->has('sku'))
                            <span class="invalid-feedback d-block">{{ $errors->first('sku') }}</span>
                        @endif
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label font-weight-bold d-block" style="font-size: 13px;">Production Stages (in order)</label>
                        <div id="stages-container">
                            @foreach($data->product_stages as $key=>$single_stage)
                            <div class="stage-row row mb-2 align-items-center">
                                <div class="col-md-10">
                                    <select name="product_stage_id[]" class="form-control select2 stage-select erp-filter-select" required>
                                        <option value="">Select Stage</option>
                                        @foreach($product_stages as $stage)
                                            <option value="{{ $stage->id }}" {{ $stage->id == $single_stage->master_stage_id ? 'selected' : '' }}>{{ $stage->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    @if($key==0)
                                        <button type="button" class="btn-erp btn-erp-primary add-stage"><i class="fa fa-plus"></i></button>
                                    @else
                                        <button type="button" class="btn-erp btn-erp-danger remove-stage"><i class="fa fa-minus"></i></button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label font-weight-bold d-block" style="font-size: 13px;">Fabric Details</label>
                        <div id="fabric-container">
                            @foreach($data->bill_of_materials as $key=>$single_bom)
                            <div class="fabric-row row mb-2 align-items-center">
                                <div class="col-md-5">
                                    <select name="fabric_sku[]" class="form-control select2 erp-filter-select" required>
                                        <option value="">Select Fabric</option>
                                        @foreach($fabrics as $single_data)
                                            <option value="{{ $single_data->sku }}" {{ $single_bom->fabric_sku == $single_data->sku ? 'selected' : '' }}>
                                                {{ $single_data->sku }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="number" name="fabric_meter[]" class="form-control erp-filter-input" placeholder="Enter meter" step="0.01" min="0" value="{{ $single_bom->meter }}" required>
                                </div>
                                <div class="col-md-2">
                                    @if($key==0)
                                        <button type="button" class="btn-erp btn-erp-primary add-fabric"><i class="fa fa-plus"></i></button>
                                    @else
                                        <button type="button" class="btn-erp btn-erp-danger remove-fabric"><i class="fa fa-minus"></i></button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
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
        // Add More Fabric
        $(document).on('click', '.add-fabric', function () {
            let newRow = `
                <div class="fabric-row row mb-2 align-items-center">
                    <div class="col-md-5">
                        <select name="fabric_sku[]" class="form-control select2 erp-filter-select" required>
                            <option value="">Select Fabric</option>
                            @foreach($fabrics as $single_data)
                                <option value="{{$single_data->sku}}">{{$single_data->sku}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="number" name="fabric_meter[]" class="form-control erp-filter-input" placeholder="Enter meter" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-2">
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

        // Add new stage row
        $(document).on('click', '.add-stage', function () {
            let newRow = `
                <div class="stage-row row mb-2 align-items-center">
                    <div class="col-md-10">
                        <select name="product_stage_id[]" class="form-control select2 stage-select erp-filter-select" required>
                            <option value="">Select Stage</option>
                            @foreach($product_stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn-erp btn-erp-danger remove-stage"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
            `;
            $('#stages-container').append(newRow);
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
        });

        // Remove stage row
        $(document).on('click', '.remove-stage', function () {
            $(this).closest('.stage-row').remove();
        });
    });
</script>
@endsection
