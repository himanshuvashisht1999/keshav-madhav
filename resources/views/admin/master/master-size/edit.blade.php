@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Master Size</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Edit Master Size</li>
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
                    <h3 class="card-title">Edit Master Size</h3>
                </div>
                <form action="{{route('admin.master.size.update')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$data->id}}">
                    <div class="card-body">
                        <div class="row">
                         
                            <div class="col-md-6" >
                                <div class="form-group">
                                    <label id="label1" for="label1">To</label>
                                    <input type="text" name="size" class="form-control" placeholder="Enter start size" value="{{$data->size}}">
                                    @if ($errors->has('size'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('size') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
                                        <option value="1" {{$data->status == '1' ? 'selected' : ''}}>Active</option>
                                        <option value="0" {{$data->status == '0' ? 'selected' : ''}}>Inactive</option>
                                    </select>
                                    @if ($errors->has('status'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('status') }}
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


@endsection
