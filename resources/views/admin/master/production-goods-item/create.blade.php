@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Bill of Materials</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Bill of Materials</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Create Bill of Materials</h3>
                </div>
                <form action="{{route('admin.master.production-goods-item.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{request()->get('id')}}">
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Select Fabric Sku</label>
                                @if($selectecFabrics->isNotEmpty())
                                    <div id="fabric-container">
                                    @foreach($selectecFabrics as $index => $fabric)
                                        <input type="hidden" name="old_fabric_id[]" value="{{$fabric->id}}">
                                            <div class="fabric-row row mb-2">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <select name="old_fabric_sku[{{$fabric->id}}]" class="form-control select2" style="width: 100%;" required>
                                                            <option value="">Select Fabric</option>
                                                            @foreach($fabrics as $single_data)
                                                                <option value="{{$single_data->sku}}" {{ $fabric->fabric_sku == $single_data->sku ? 'selected' : '' }}>
                                                                    {{$single_data->sku}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <input type="number" name="old_fabric_meter[{{$fabric->id}}]" id=""class="form-control" placeholder="Meter" min="0.01" step="0.01" value="{{$fabric->meter}}" required>
                                                        
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    @if($index == 0)
                                                        <button type="button" class="btn btn-success add-fabric"><i class="fa fa-plus"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-danger remove-fabric"><i class="fa fa-minus"></i></button>
                                                    @endif
                                                    
                                                </div>
                                            </div>
                                    @endforeach
                                    </div>
                                @else
                                <div id="fabric-container">
                                    <div class="fabric-row row mb-2">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <select name="fabric_sku[]" class="form-control select2" style="width: 100%;" required>
                                                    <option value="">Select Fabric</option>
                                                    @foreach($fabrics as $single_data)
                                                        <option value="{{$single_data->sku}}" {{ old('fabric_sku') == $single_data->sku ? 'selected' : '' }}>
                                                            {{$single_data->sku}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="number" name="fabric_meter[]" class="form-control" placeholder="Enter meter" step="0.01" min="0.01" required>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-success add-fabric"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-12">
                                <label>Select Item Sku</label>
                                @if($productItems->isNotEmpty())
                                    <div id="items-container">
                                    @foreach($productItems as $index => $item)
                                        <input type="hidden" name="old_items_id[]" value="{{$item->id}}">
                                            <div class="items-row row mb-2">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <select name="old_items_sku[{{$item->id}}]" class="form-control select2 stage-select" style="width: 100%;" required>
                                                            <option value="">Select Item SKU</option>
                                                        
                                                            @foreach($itemCatogeriesValue as $itemCatogery)
                                                                <option value="{{ $itemCatogery->sku }}" {{($item->item_attribute_value_sku == $itemCatogery->sku) ? 'selected' : '' }}>{{ $itemCatogery->sku }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <input type="number" name="old_item_quantity[{{$item->id}}]" id=""class="form-control" placeholder="Quantity" step="1" min="1" value="{{$item->quantity}}" required>
                                                        
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    @if($index == 0)
                                                        <button type="button" class="btn btn-success add-item"><i class="fa fa-plus"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-danger remove-item"><i class="fa fa-minus"></i></button>
                                                    @endif
                                                    
                                                </div>
                                            </div>
                                    @endforeach
                                    </div>
                                @else
                                <div id="items-container">
                                    <div class="items-row row mb-2">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <select name="items_sku[]" class="form-control select2 stage-select" style="width: 100%;" required>
                                                    <option value="">Select Item SKU</option>
                                                   
                                                    @foreach($itemCatogeriesValue as $itemCatogery)
                                                        <option value="{{ $itemCatogery->sku }}">{{ $itemCatogery->sku }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <input type="number" name="item_quantity[]" id=""class="form-control" placeholder="Quantity" step="1" min="1" required>
                                                
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-success add-item"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            <div class="col-md-12">
                                <div class="mt-2" style="float:right">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<script>
    function generateSKU() {
        let type_of_garment = $("input[name='type_of_garment']").val().trim();
        let name_of_garment = $("input[name='name_of_garment']").val().trim();
        let garment_pattern = $("input[name='garment_pattern']").val().trim();
        let master_size_id = $("select[name='master_size_id'] option:selected").text().trim();
   

        // Remove special characters and uppercase
        type_of_garment = type_of_garment.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        name_of_garment = name_of_garment.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        garment_pattern = garment_pattern.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        master_size_id = master_size_id.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

        let sku = type_of_garment + '-' + name_of_garment + '-' + garment_pattern + '-' + master_size_id;

        let skuInput = $("#sku");
        if (!skuInput.data('edited') || skuInput.val() === "") {
            skuInput.val(sku);
        }
    }

    $(document).ready(function() {
        // Name input
        $("input[name='type_of_garment']").on("input", generateSKU);
        $("input[name='name_of_garment']").on("input", generateSKU);
        $("input[name='garment_pattern']").on("input", generateSKU);

        // All select fields
        $("select[name='master_size_id']")
            .on("change", generateSKU);

        // Mark SKU as manually edited
        $("#sku").on("input", function() {
            $(this).data('edited', true);
        });
    });

</script>
<script>
    $(document).ready(function () {

    // Add More Fabric
    $(document).on('click', '.add-item', function () {
        let newRow = `<div class="items-row row mb-2">
                        <div class="col-md-6">
                            <div class="form-group">
                                <select name="items_sku[]" class="form-control select2 stage-select" style="width: 100%;" required>
                                    <option value="">Select Item SKU</option>
                                    @foreach($itemCatogeriesValue as $itemCatogery)
                                        <option value="{{ $itemCatogery->sku }}">{{ $itemCatogery->sku }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <input type="number" name="item_quantity[]" id=""class="form-control" placeholder="Quantity" step="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger remove-item"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>`;
        $('#items-container').append(newRow);
        $('.select2').select2(); // reinitialize Select2 for new elements
    });

    // Remove items Row
    $(document).on('click', '.remove-item', function () {
        $(this).closest('.items-row').remove();
    });


    
    // Add More Fabric
    $(document).on('click', '.add-fabric', function () {
        let newRow = `
            <div class="fabric-row row mb-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <select name="fabric_sku[]" class="form-control select2" style="width: 100%;" required>
                            <option value="">Select Fabric</option>
                            @foreach($fabrics as $single_data)
                                <option value="{{$single_data->sku}}">{{$single_data->sku}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <input type="number" name="fabric_meter[]" class="form-control" placeholder="Enter meter" step="0.01" min="0.01" required>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-fabric"><i class="fa fa-minus"></i></button>
                </div>
            </div>
        `;
        $('#fabric-container').append(newRow);
        $('.select2').select2(); // reinitialize Select2 for new elements
    });

    // Remove Fabric Row
    $(document).on('click', '.remove-fabric', function () {
        $(this).closest('.fabric-row').remove();
    });
});

</script>
<script>
$(document).ready(function () {
    // Initialize Select2 for existing selects
    $('.select2').select2();

    // Add new stage row
   
    // Remove stage row
    $(document).on('click', '.remove-stage', function () {
        $(this).closest('.stage-row').remove();
    });
});
</script>

@endsection
