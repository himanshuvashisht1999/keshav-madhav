@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Doctor</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Doctor</li>
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
                    <h3 class="card-title">Create Doctor</h3>
                </div>
                <form action="{{route('admin.doctor.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">First Name</label>
                                    <input type="text" name="first_name" class="form-control" placeholder="Enter first name" value="{{old('first_name')}}">
                                    @if ($errors->has('first_name'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('first_name') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" placeholder="Enter last name" value="{{old('last_name')}}">
                                    @if ($errors->has('last_name'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('last_name') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Enter last name" value="{{old('title')}}">
                                    @if ($errors->has('title'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('title') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Branch Name</label>
                                    <select name="branch_id" class="form-control select2" style="width: 100%;">
                                        @foreach($branches as $branch)
                                        <option value="{{$branch->id}}" {{old('branch_id') == $branch->id ? 'selected' : ''}}>{{$branch->name}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('branch_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('branch_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Email</label>
                                    <input type="text" name="email" class="form-control" placeholder="Enter Email" value="{{old('email')}}">
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('email') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Phone</label>
                                    <input type="text" name="phone" class="form-control" placeholder="Enter phone" value="{{old('phone')}}">
                                    @if ($errors->has('phone'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('phone') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Facebook URL</label>
                                    <input type="text" name="facebook" class="form-control" placeholder="Enter facebook url" value="{{old('facebook')}}">
                                    @if ($errors->has('facebook'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('facebook') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Instagram URL</label>
                                    <input type="text" name="instagram" class="form-control" placeholder="Enter instagram url" value="{{old('instagram')}}">
                                    @if ($errors->has('instagram'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('instagram') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Linkdin URL</label>
                                    <input type="text" name="linkdin" class="form-control" placeholder="Enter linkdin url" value="{{old('linkdin')}}">
                                    @if ($errors->has('linkdin'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('linkdin') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Twitter URL</label>
                                    <input type="text" name="twitter" class="form-control" placeholder="Enter twitter url" value="{{old('twitter')}}">
                                    @if ($errors->has('twitter'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('twitter') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Gender</label>
                                    <select name="gender" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
                                        <option value="Male" {{old('gender') == 'Male' ? 'selected' : ''}}>Male</option>
                                        <option value="Female" {{old('gender') == 'Female' ? 'selected' : ''}}>Female</option>
                                        <option value="Other" {{old('gender') == 'Other' ? 'selected' : ''}}>Other</option>
                                    </select>
                                    @if ($errors->has('gender'))`
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('gender') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Address</label>
                                    <input type="text" name="address" class="form-control" placeholder="Enter address" value="{{old('address')}}">
                                    @if ($errors->has('address'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('address') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="Enter password" value="{{old('password')}}">
                                    @if ($errors->has('password'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('password') }}
                                        </span> 
                                    @endif
                                </div>
                            </div> <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="Enter Confirm Password" value="{{old('comfirm_password')}}">
                                    @if ($errors->has('confirm_password'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('confirm_password') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" name="role_id" value="2">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputFile">Upload Image (Recommended size: 500 × 450 px)</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="image" class="custom-file-input" id="image-input" onchange="previewImage()" accept=".jpg,.jpeg,.png">
                                            <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                        </div>
                                        {{-- <div class="input-group-append">
                                            <span class="input-group-text">Upload</span>
                                        </div> --}}
                                        
                                        @if ($errors->has('image'))
                                            <span class="invalid-feedback d-block">
                                            {{ $errors->first('image') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
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
                                <img class="" src="{{asset('images/image-placeholder.png')}}" alt="Preview" id="image-preview" height="80px" width="80px">
                            </div>
                            <div class="col-md-12">
                                <div class="" style="float:right">
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
</script>

@endsection
