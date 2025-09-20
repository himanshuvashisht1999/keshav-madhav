@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Vendor</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Vendor</li>
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
                    <h3 class="card-title">Create Vendor</h3>
                </div>
                <form action="{{route('admin.master.vendor.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter name" value="{{old('name')}}">
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('name') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Phone</label>
                                    <input type="number" name="phone" class="form-control" placeholder="Enter phone" value="{{old('name')}}">
                                    @if ($errors->has('phone'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('phone') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Email</label>
                                    <input type="text" name="email" class="form-control" placeholder="Enter email" value="{{old('name')}}">
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('email') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Address</label>
                                    <input type="text" name="address" class="form-control" placeholder="Enter address" value="{{old('name')}}">
                                    @if ($errors->has('address'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('address') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Items</label>
                                    <select name="items[]" class="form-control select2" style="width: 100%;" multiple required>
                                        @foreach($items as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('items'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('items') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            

                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputFile">Upload Image (Recommended size: 500 × 300 px)</label>
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
                            </div> -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
                                        <option value="1" {{old('status') == '1' ? 'selected' : ''}}>Active</option>
                                        <option value="0" {{old('status') == '0' ? 'selected' : ''}}>Inactive</option>
                                    </select>
                                    @if ($errors->has('status'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('status') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" name="sku" id="sku" class="form-control" placeholder="Auto-generated SKU">
                                    @if ($errors->has('sku'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('sku') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <!-- <div class="col-md-6">
                                <img class="" src="{{asset('images/image-placeholder.png')}}" alt="Preview" id="image-preview" height="80px" width="80px">
                            </div> -->

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Description</label>
                                    
                                    <textarea name="description" class="form-control" placeholder="Enter description">{{old('description')}}</textarea>
                                    @if ($errors->has('description'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('description') }}
                                        </span>
                                    @endif
                                </div>
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
        // function previewImage() {
        //     var imageInput = document.getElementById('image-input');
        //     var imagePreview = document.getElementById('image-preview');
            
        //     if (imageInput.files && imageInput.files[0]) {
        //         var reader = new FileReader();
        //         reader.onload = function(e) {
        //             imagePreview.src = e.target.result;
        //         };
                
        //         reader.readAsDataURL(imageInput.files[0]);
        //     } else {
        //         // If no file is selected or supported, clear the preview
        //         imagePreview.src = "";
        //     }
        // }

</script>
<script>
    function generateSKU() {
        let name = document.querySelector("input[name='name']").value.trim();
        let phone = document.querySelector("input[name='phone']").value.trim();
        let address = document.querySelector("input[name='address']").value.trim();
        
        // Get first 4 letters of name (fresh every time)
        let part1 = name.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

        // Get last 3 digits of phone
        let part2 = phone.replace(/[^0-9]/g, '');
        part2 = part2.length >= 3 ? part2.slice(-3) : part2;

        // Combine
        // let sku = 'VENDOR-' + part1 + "-" + part2;
        let sku = part1;

        let skuInput = document.getElementById("sku");

        // Only overwrite if user has not manually typed in SKU
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

    document.querySelector("input[name='phone']").addEventListener("input", function() {
        let skuInput = document.getElementById("sku");
        if (!skuInput.dataset.edited) {
            generateSKU();
        }
    });

    document.querySelector("input[name='address']").addEventListener("input", function() {
        let skuInput = document.getElementById("sku");
        if (!skuInput.dataset.edited) {
            generateSKU();
        }
    });

    // Mark as manually edited when user types in SKU
    document.getElementById("sku").addEventListener("input", function() {
        this.dataset.edited = true;
    });
</script>



@endsection
