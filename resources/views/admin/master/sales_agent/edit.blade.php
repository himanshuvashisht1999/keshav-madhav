@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Sales Agents</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active">Edit Sales Agent</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Edit Sales Agent</h3>
                    </div>
                    <form action="{{route('admin.master.sales-agent.update')}}" method="post">
                        @csrf
                        <input type="hidden" name="id" value="{{$data->id}}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control" placeholder="Enter name"
                                            value="{{$data->name}}">
                                        @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" placeholder="Enter email"
                                            value="{{$data->email}}">
                                        @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="phone" class="form-control" placeholder="Enter phone"
                                            value="{{$data->phone}}">
                                        @error('phone')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password (Leave blank to keep current)</label>
                                        <input type="password" name="password" class="form-control"
                                            placeholder="Enter new password">
                                        @error('password')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="1" {{$data->status == 1 ? 'selected' : ''}}>Active</option>
                                            <option value="0" {{$data->status == 0 ? 'selected' : ''}}>Inactive</option>
                                        </select>
                                        @error('status')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control"
                                            placeholder="Enter address">{{$data->address}}</textarea>
                                        @error('address')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2" style="float:right">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection