@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Fabric</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Create Fabric</li>
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
                    <form action="{{ route('admin.master.fabric.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                 
                                <div class="col-md-6">
                                    <div class="form-group"> 
                                        <label for="exampleInputEmail1">Supplier Name</label>
                                        <a href="{{route('admin.master.vendor.create')}}" target="_blank" style="float:right;">Create New +</a>
                                        
                                        <select name="vendor_id" class="form-control select2" style="width: 100%;">
                                            <option value="">Select Supplier</option>
                                            @foreach ($vender_data as $single_data)
                                                <option value="{{ $single_data->id }}"
                                                    {{ old('vendor_id') == $single_data->id ? 'selected' : '' }}>
                                                    {{ $single_data->name }}</option>
                                            @endforeach

                                        </select>
                                        @if ($errors->has('vendor_id'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('vendor_id') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Fabric Name</label>
                                        <input type="text" name="name" class="form-control"
                                            placeholder="Enter fabric name" value="{{ old('name') }}">
                                        @if ($errors->has('name'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('name') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fabric Dye</label>
                                        <select name="dye_id" class="form-control select2" style="width: 100%;">
                                            @foreach ($fab_dye_data as $single_data)
                                                <option value="{{ $single_data->id }}"
                                                    {{ old('dye_id') == $single_data->id ? 'selected' : '' }}>
                                                    {{ $single_data->sku }}</option>
                                            @endforeach

                                        </select>
                                        @if ($errors->has('dye_id'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('dye_id') }}
                                            </span>
                                        @endif
                                    </div>
                                </div> --}}

                                {{-- <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fabric Width</label>
                                        <select name="width_id" class="form-control select2" style="width: 100%;">
                                            @foreach ($fab_width_data as $single_data)
                                                <option value="{{ $single_data->id }}"
                                                    {{ old('width_id') == $single_data->id ? 'selected' : '' }}>
                                                    {{ $single_data->sku }}</option>
                                            @endforeach

                                        </select>
                                        @if ($errors->has('width_id'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('width_id') }}
                                            </span>
                                        @endif
                                    </div>
                                </div> --}}

                                {{-- <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fabric Weave Type</label>
                                        <select name="weave_type_id" class="form-control select2" style="width: 100%;">
                                            @foreach ($fab_weave_data as $single_data)
                                                <option value="{{ $single_data->id }}"
                                                    {{ old('weave_type_id') == $single_data->id ? 'selected' : '' }}>
                                                    {{ $single_data->sku }}</option>
                                            @endforeach

                                        </select>
                                        @if ($errors->has('weave_type_id'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('weave_type_id') }}
                                            </span>
                                        @endif
                                    </div>
                                </div> --}}

                                {{-- <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fabric GSM</label>
                                        <select name="gsm_id" class="form-control select2" style="width: 100%;">
                                            @foreach ($fab_gsm_data as $single_data)
                                                <option value="{{ $single_data->id }}"
                                                    {{ old('gsm_id') == $single_data->id ? 'selected' : '' }}>
                                                    {{ $single_data->sku }}</option>
                                            @endforeach

                                        </select>
                                        @if ($errors->has('gsm_id'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('gsm_id') }}
                                            </span>
                                        @endif
                                    </div>
                                </div> --}}

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fabric Composition</label>
                                        <select name="composition_id" class="form-control select2" style="width: 100%;">
                                            <option value="0">N/A</option>
                                            @foreach ($fab_composition_data as $single_data)
                                                <option value="{{ $single_data->id }}"
                                                    {{ old('composition_id') == $single_data->id ? 'selected' : '' }}>
                                                    {{ $single_data->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('composition_id'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('composition_id') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @if ($errors->has('status'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('status') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputFile">Main Image</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="image" class="custom-file-input" id="image-input" onchange="previewImage()"  accept=".jpg,.jpeg,.png">
                                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                            </div>
                                            
                                            @if ($errors->has('image'))
                                                <span class="invalid-feedback d-block">
                                                {{ $errors->first('image') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <img class="" src="{{asset('images/image-placeholder.png')}}" alt="Preview" id="image-preview" height="80px" width="80px">
                                </div>

                                <div class="col-md-12 mt-2">
                                    <div class="form-group">
                                        <label for="exampleInputFile">Other Images</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                
                                                <input type="file" name="other_images[]" id="other-images" class="custom-file-input" multiple accept=".jpg,.jpeg,.png">
                                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-2" id="preview-multiple-images">
                                    
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
        $(document).ready(function() {
            // Script logic if any
        });
    </script>

    <script>
    function previewImage() {
        var imageInput = document.getElementById('image-input');
        var imagePreview = document.getElementById('image-preview');
        
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
    document.getElementById('other-images').addEventListener('change', function() {
        let previewContainer = document.getElementById('preview-multiple-images');
        previewContainer.innerHTML = ""; // clear old previews

        if (this.files) {
            [...this.files].forEach(file => {
                let reader = new FileReader();
                reader.onload = function(e) {
                    let img = document.createElement("img");
                    img.src = e.target.result;
                    img.classList.add("m-1", "border", "rounded");
                    img.style.width = "80px";
                    img.style.height = "80px";
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
    });
</script>
@endsection
