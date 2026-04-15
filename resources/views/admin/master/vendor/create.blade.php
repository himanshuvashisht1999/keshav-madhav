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
                <!-- <div class="card-header">
                    <h3 class="card-title">Create Vendor</h3>
                </div> -->
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
                                    <input type="number" name="phone" class="form-control" placeholder="Enter phone" value="{{old('phone')}}">
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
                                    <input type="text" name="email" class="form-control" placeholder="Enter email" value="{{old('email')}}">
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
                                    <label>Items</label>
                                    <select name="items[]" class="form-control select2" style="width: 100%;" multiple required>
                                        <option value="0" selected>Fabric</option>
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

                            

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Balance</label>
                                    <input type="number" step="0.01" name="balance" class="form-control" placeholder="Enter balance" value="{{old('balance', 0)}}">
                                    @if ($errors->has('balance'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('balance') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Purchase Agent</label>
                                    <select name="purchase_agent_id" class="form-control select2" style="width: 100%;">
                                        <option value="">Select Purchase Agent</option>
                                        @foreach($purchase_agents as $agent)
                                        <option value="{{$agent->id}}" {{old('purchase_agent_id') == $agent->id ? 'selected' : ''}}>{{$agent->name}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('purchase_agent_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('purchase_agent_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                     <label>Type</label>
                                     <select name="type" class="form-control select2" style="width: 100%;">
                                         <option value="Credit" {{old('type') == 'Credit' ? 'selected' : ''}}>Credit</option>
                                         <option value="Debit" {{old('type') == 'Debit' ? 'selected' : ''}}>Debit</option>
                                     </select>
                                     @if ($errors->has('type'))
                                         <span class="invalid-feedback d-block">
                                         {{ $errors->first('type') }}
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
</script>



@endsection
