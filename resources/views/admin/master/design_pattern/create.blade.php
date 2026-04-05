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
                            <li class="breadcrumb-item active">Create Pattern</li>
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
                    <!-- <div class="card-header">
                        <h3 class="card-title">Create Pattern</h3>
                    </div> -->
                    <form action="{{route('admin.master.design-pattern.store')}}" method="post"
                        enctype="multipart/form-data" autocomplete="off">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Name</label>
                                        <input type="text" name="name" class="form-control" placeholder="Enter name"
                                            value="{{old('name')}}">
                                        @if ($errors->has('name'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('name') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control select2" style="width: 100%;">
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
                                        <label for="exampleInputFile">Pattern Photo</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="pattern_img" class="custom-file-input"
                                                    id="image-input2" onchange="previewImage2()" accept=".jpg,.jpeg,.png">
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
                                    <img class="" src="{{asset('images/image-placeholder.png')}}" alt="Preview"
                                        id="image-preview-2" height="80px" width="80px">
                                </div>
                            </div>
                            <br />

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
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                };

                reader.readAsDataURL(imageInput.files[0]);
            } else {
                // If no file is selected or supported, clear the preview
                imagePreview.src = "";
            }
        }

    </script>

@endsection