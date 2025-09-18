@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>General Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Edit General Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="section-box">
                <div class="card card-default">
                    {{-- <div class="card-header">
                        <h3 class="card-title"> General Settings</h3>
                    </div> --}}
                    <form action="{{route('admin.settings.update')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{$data->id}}">
                        <div class="row" >
                            <div class=" col-11 card-header">
                                <h3 class="card-title">General Settings</h3>
                            </div>
                            <div class=" col-1 card-header">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>

                    
                       
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputFile">Logo</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="logo" class="custom-file-input" id="image-input" onchange="previewImage()" accept=".jpg,.jpeg,.png">
                                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                            </div>
                                            
                                            @if ($errors->has('logo'))
                                                <span class="invalid-feedback d-block">
                                                {{ $errors->first('logo') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputFile">Fav Icon</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="fav_icon" class="custom-file-input" id="icon-input" onchange="previewIcon()" accept=".jpg,.jpeg,.png">
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
                                <div class="col-md-6">
                                    <img class="" src="{{$data->logo}}" alt="Preview" id="image-preview" height="80px" width="80px">
                                </div>
                               
                                <div class="col-md-6">
                                    <img class="" src="{{$data->fav_icon}}" alt="Preview" id="icon-preview" height="80px" width="80px">                             
                                </div>
                                <div class="col-md-6 pt-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Phone </label>
                                        <input type="text" name="phone" class="form-control" placeholder="Enter  phone" value="{{$data->phone}}">
                                        @if ($errors->has('phone'))
                                            <span class="invalid-feedback d-block">
                                            {{ $errors->first('phone') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6  pt-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"> Email</label>
                                        <input type="text" name="email" class="form-control" placeholder="Enter email " value="{{$data->email}}">
                                        @if ($errors->has('email'))
                                            <span class="invalid-feedback d-block">
                                            {{ $errors->first('email') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                               
                                <div class="col-md-6  pt-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Website Name</label>
                                        <input type="text" name="website_name" class="form-control" placeholder="Enter website name" value="{{$data->website_name}}">
                                        @if ($errors->has('website_name'))
                                            <span class="invalid-feedback d-block">
                                            {{ $errors->first('website_name') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6  pt-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Copyright Text</label>
                                        <input type="text" name="copy_right" class="form-control" placeholder="Enter copy right " value="{{$data->copy_right}}">
                                        @if ($errors->has('copy_right'))
                                            <span class="invalid-feedback d-block">
                                            {{ $errors->first('copy_right') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                 <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Address</label>
                                        <textarea rows="3" type="text" name="address" class="form-control" placeholder="Enter address" value="">{{$data->address}}</textarea>
                                        @if ($errors->has('address'))
                                            <span class="invalid-feedback d-block">
                                            {{ $errors->first('address') }}
                                            </span>
                                        @endif
                                    </div>
                                </div> 
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Footer Sub Heading</label>
                                        <textarea rows="3" type="text" name="sub_heading" class="form-control" placeholder="Enter sub heading" value="">{{$data->sub_heading}}</textarea>
                                        @if ($errors->has('sub_heading'))
                                            <span class="invalid-feedback d-block">
                                            {{ $errors->first('sub_heading') }}
                                            </span>
                                        @endif
                                    </div>
                                </div> 
                                
                            </div>
                        </div>
                        </div>
                        <div class="card card-default">
                            <div class="card-header">
                                <h3 class="card-title">Seo Meta</h3>
                            </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"> Web Site Title</label>
                                                <input type="text" name="title" class="form-control" placeholder="Enter title" value="{{$data->title}}">
                                                @if ($errors->has('title'))
                                                    <span class="invalid-feedback d-block">
                                                    {{ $errors->first('title') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Meta Title</label>
                                                <input type="text" name="meta_title" class="form-control" placeholder="Enter Meta Title" value="{{$data->meta_title}}">
                                                @if ($errors->has('meta_title'))
                                                    <span class="invalid-feedback d-block">
                                                    {{ $errors->first('meta_title') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Meta Keywords</label>
                                                <input type="text" name="meta_keywords"class="form-control" placeholder="Enter Meta Keywords" value="{{$data->meta_keywords}}">
                                                @if ($errors->has('meta_keywords'))
                                                    <span class="invalid-feedback d-block">
                                                    {{ $errors->first('meta_keywords') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div> 
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Meta Description</label>
                                                <input type="text" name="meta_description" class="form-control" placeholder="Enter Meta Description" value="{{$data->meta_description}}">
                                                @if ($errors->has('meta_description'))
                                                    <span class="invalid-feedback d-block">
                                                    {{ $errors->first('meta_description') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div> 
                                        {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Header Script </label>
                                                <textarea rows="3" type="text" name="header_script" class="form-control" placeholder="Enter header script" value="">{{$data->header_script}}</textarea>
                                                @if ($errors->has('header_script'))
                                                    <span class="invalid-feedback d-block">
                                                    {{ $errors->first('header_script') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div> 
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Footer Script</label>
                                                <textarea rows="3" type="text" name="footer_script" class="form-control" placeholder="Enter footer script" value="">{{$data->footer_script}}</textarea>
                                                @if ($errors->has('footer_script'))
                                                    <span class="invalid-feedback d-block">
                                                    {{ $errors->first('footer_script') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>  --}}
                                    </div>       
                                </div>
                        </div>
                        <div class="card card-default">
                            <div class="card-header">
                                <h3 class="card-title">Social Media</h3>
                            </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Facebook</label>
                                                <input type="url" name="facebook" class="form-control" placeholder="Enter facebook url" value="{{$data->facebook}}">
                                                @if ($errors->has('facebook'))
                                                    <span class="invalid-feedback d-block">
                                                    {{ $errors->first('facebook') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Twitter (X) </label>
                                                <input type="url" name="twitter" class="form-control" placeholder="Enter twitter url" value="{{$data->twitter}}">
                                                @if ($errors->has('twitter'))
                                                    <span class="invalid-feedback d-block">
                                                    {{ $errors->first('twitter') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Instagram</label>
                                                <input type="text" name="instagram" class="form-control" placeholder="Enter instagram url" value="{{$data->instagram}}">
                                                @if ($errors->has('instagram'))
                                                    <span class="invalid-feedback d-block">
                                                    {{ $errors->first('instagram') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div> 
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Linkedin</label>
                                                <input type="text" name="linkedin" class="form-control" placeholder="Enter linkedin url" value="{{$data->instagram}}">
                                                @if ($errors->has('linkedin'))
                                                    <span class="invalid-feedback d-block">
                                                    {{ $errors->first('linkedin') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div> 
                                    </div>       
                                </div>
                        </div>
                        {{-- <div class="card card-default">
                            <div class="card-header">
                                <h3 class="card-title">App  Url</h3>
                            </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Android </label>
                                                <input type="url" name="android_app" class="form-control" placeholder="Enter android  url" value="{{$data->android_app}}">
                                                @if ($errors->has('android_app'))
                                                    <span class="invalid-feedback d-block">
                                                    {{ $errors->first('android_app') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Ios </label>
                                                <input type="url" name="ios_app" class="form-control" placeholder="Enter ios  url" value="{{$data->ios_app}}">
                                                @if ($errors->has('ios_app'))
                                                    <span class="invalid-feedback d-block">
                                                    {{ $errors->first('ios_app') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                    </div>       
                                </div>
                        </div> --}}
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="" style="float:right">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </div>
                        </div>
                    <form>
                </div>
            
        </div>
    </section>
</div>
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

    function previewIcon() {
        var iconInput = document.getElementById('icon-input');
        var iconPreview = document.getElementById('icon-preview');
        
        if (iconInput.files && iconInput.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                iconPreview.src = e.target.result;
            };
            
            reader.readAsDataURL(iconInput.files[0]);
        } else {
            // If no file is selected or supported, clear the preview
            iconPreview.src = "";
        }
    }
    
</script>

@endsection
