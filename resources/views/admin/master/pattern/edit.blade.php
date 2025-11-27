@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Pattern</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Edit Pattern</li>
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
                    <h3 class="card-title">Edit Pattern</h3>
                </div>
                <form action="{{route('admin.master.pattern.update')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$data->id}}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter name" value="{{$data->name}}">
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('name') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" name="sku" id="sku" class="form-control" placeholder="Auto-generated SKU" value="{{$data->sku}}" readonly>
                                    @if ($errors->has('sku'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('sku') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputFile">Pattern Photo</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="pattern_img" class="custom-file-input" id="image-input2" onchange="previewImage2()"  accept=".jpg,.jpeg,.png">
                                            <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                        </div>
                                        
                                        @if ($errors->has('pattern_img'))
                                            <span class="invalid-feedback d-block">
                                            {{ $errors->first('pattern_img') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <img class="" src="{{ asset('assets/pattern-img/' . $data->pattern_img) }}" alt="Preview" id="image-preview-2" height="80px" width="80px">
                            </div>
                        </div>
                        <br/>
                        <div id="items-container">
                            <label>Pattern Parts :</label>

                            @if ($parts_data->isNotEmpty()) 
                                @foreach($parts_data as $index => $part)
                                    <input type="hidden" name="old_parts_id[]" value="{{ $part->id }}">

                                    <div class="items-row row mb-2 item-row align-items-center">

                                        <div class="col-12 col-md-1 mb-2">
                                            <input type="text" name="old_part_no[{{ $part->id }}]" 
                                                class="form-control part_no" readonly>
                                        </div>

                                        <div class="col-12 col-md-4 mb-2">
                                            <input type="text" name="old_part_name[{{ $part->id }}]" 
                                                class="form-control" value="{{ $part->name }}" required>
                                        </div>

                                        <div class="col-6 col-md-2 mb-2">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input"
                                                    name="old_is_printing[{{ $part->id }}]"
                                                    {{ $part->is_printing ? 'checked' : '' }}>
                                                <label class="form-check-label">Printing</label>
                                            </div>
                                        </div>

                                        {{-- <div class="col-6 col-md-2 mb-2">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input"
                                                    name="old_is_embroidery[{{ $part->id }}]"
                                                    {{ $part->is_embroidery ? 'checked' : '' }}>
                                                <label class="form-check-label">Embroidery</label>
                                                <a href="{{ asset('assets/pattern-img/' . $part->parts_img) }}" target="_blank" >View</a>
                                            </div>
                                        </div> --}}
                                        <div class="col-6 col-md-2 mb-2">
                                            <div class="form-check d-flex align-items-center justify-content-between">
                                                
                                                <div>
                                                    <input type="checkbox" class="form-check-input"
                                                        name="old_is_embroidery[{{ $part->id }}]"
                                                        {{ $part->is_embroidery ? 'checked' : '' }}>
                                                    <label class="form-check-label">Embroidery</label>
                                                </div>

                                                <a href="{{ asset('assets/pattern-img/' . $part->parts_img) }}" target="_blank">View</a>

                                            </div>
                                        </div>
                                        <div class="col-12 col-md-2 mb-2">
                                            <input type="file" name="old_part_img[{{ $part->id }}]" 
                                                class="form-control"
                                                {{ $part->parts_img == '' ? 'required' : '' }}>
                                        </div>

                                        <div class="col-12 col-md-1 mb-2 d-flex justify-content-center">
                                            @if($index == 0)
                                                <button type="button" class="btn btn-success add-item">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-danger remove-item">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                            @endif
                                        </div>

                                    </div>

                                @endforeach

                            @else
                                <div class="items-row row mb-2 item-row align-items-center">

                                    <div class="col-12 col-md-1 mb-2">
                                        <input type="text" name="part_no[]" class="form-control part_no" readonly>
                                    </div>

                                    <div class="col-12 col-md-4 mb-2">
                                        <input type="text" name="part_name[]" class="form-control" required>
                                    </div>

                                    <div class="col-6 col-md-2 mb-2">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="is_printing[]">
                                            <label class="form-check-label">Printing</label>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-2 mb-2">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="is_embroidery[]">
                                            <label class="form-check-label">Embroidery</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-2 mb-2">
                                        <input type="file" name="part_img[]" class="form-control" required>
                                    </div>

                                    <div class="col-12 col-md-1 mb-2 d-flex justify-content-center">
                                        <button type="button" class="btn btn-success add-item">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>

                                </div>
                            @endif
                        </div>

                        <br/><br/>
                        <div class="row">
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
    function previewImage2() {
        var imageInput = document.getElementById('image-input2');
        var imagePreview = document.getElementById('image-preview-2');
        
        if (imageInput.files && imageInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
            };
            
            reader.readAsDataURL(imageInput.files[0]);
        } else {
            // If no file is selected or supported, clear the preview
            imagePreview.src = "";
        }
    }

     function generateSKU() {
        let name = document.querySelector("input[name='name']").value.trim();
        let part1 = name.trim().replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_]/g, '').toUpperCase();
        let part2 = Math.floor(1000 + Math.random() * 9000);
        // let sku = 'pattern-' + part1 + "-" + part2;
        let rows = $("#items-container .item-row");
        let sku = part1 + "-" + rows.length ;
        let skuInput = document.getElementById("sku");
        if (!skuInput.dataset.edited || skuInput.value === "") {
            skuInput.value = sku;
        }
    }

    // Attach auto-generate on typing (name, phone, address)
    document.querySelector("input[name='name']").addEventListener("input", function() {
        let skuInput = document.getElementById("sku");
        if (!skuInput.dataset.edited) {
            generateSKU();
        }
    });

    // // Mark as manually edited when user types in SKU
    // document.getElementById("sku").addEventListener("input", function() {
    //     this.dataset.edited = true;
    // });

     // Add More Fabric
    let rowCounter = 1;  // global counter

    $(document).on('click', '.add-item', function () {
        
        rowCounter++; // increase count
    console.log(rowCounter);
        let uniquePartName = "partname_" + rowCounter;
        let uniquePrintId  = "print_" + rowCounter;
        let uniqueEmbId    = "embro_" + rowCounter;

        let newRow = `
            <div class="items-row row mb-2 item-row align-items-center">

                <div class="col-12 col-md-1 mb-2">
                    <input type="text" name="part_no[]" class="form-control part_no" readonly>
                </div>

                <div class="col-12 col-md-4 mb-2">
                    <input type="text" name="part_name[]" class="form-control" required>
                </div>

                <div class="col-6 col-md-2 mb-2">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_printing[]">
                        <label class="form-check-label">Printing</label>
                    </div>
                </div>

                <div class="col-6 col-md-2 mb-2">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_embroidery[]">
                        <label class="form-check-label">Embroidery</label>
                    </div>
                </div>

                <div class="col-12 col-md-2 mb-2">
                    <input type="file" name="part_img[]" class="form-control" required>
                </div>

                <div class="col-12 col-md-1 mb-2 d-flex justify-content-center">
                    <button type="button" class="btn btn-danger remove-item">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>

            </div>`;

        $('#items-container').append(newRow);
        $('.select2').select2(); 
        generateSKU();
        updatePartNumbers();
        

    });

    // Remove items Row
    $(document).on('click', '.remove-item', function () {
        $(this).closest('.items-row').remove();
        updatePartNumbers();
        generateSKU();
    });

    function updatePartNumbers() {
        let rows = $("#items-container .item-row");
        let total = rows.length;

        rows.each(function (index) {
            $(this).find('input.part_no').val((index + 1) + "/" + total);
        });
    }

    // Run once on load
    updatePartNumbers();

    document.querySelector("form").addEventListener("submit", function (e) {
        document.querySelectorAll("input[type='checkbox']").forEach(function (checkbox) {
            if (checkbox.checked) {
                // Set checked value
                checkbox.value = 1;
            } else {
                // Create hidden input to submit 0 (or 2) if not checked
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = checkbox.name;
                hiddenInput.value = 0; // value for unchecked
                checkbox.parentNode.appendChild(hiddenInput);
                
                // Disable the original checkbox to prevent double submission
                checkbox.readonly = true;
            }
        });
    });
</script>

@endsection
