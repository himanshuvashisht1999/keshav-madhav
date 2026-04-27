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
                        <li class="breadcrumb-item active">Edit Vendor</li>
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
                    <h3 class="card-title">Edit Vendor</h3>
                </div> -->
                <form action="{{route('admin.master.vendor.update')}}" method="post" enctype="multipart/form-data">
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

                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Price</label>
                                    <input type="number" name="price" class="form-control" placeholder="Enter price" value="{{$data->price}}">
                                    @if ($errors->has('price'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('price') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputFile">Upload Image (Recommended size: 500 × 300 px)</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="image" class="custom-file-input" id="image-input" onchange="previewImage()" accept=".jpg,.jpeg,.png">
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
                                    <label for="exampleInputEmail1">Phone</label>
                                    <input type="number" name="phone" class="form-control" placeholder="Enter phone" value="{{$data->phone}}">
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
                                    <input type="text" name="email" class="form-control" placeholder="Enter email" value="{{$data->email}}">
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
                                    <input type="text" name="address" class="form-control" placeholder="Enter address" value="{{$data->address}}">
                                    @if ($errors->has('address'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('address') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <?php
                                $selectedItems = unserialize(optional($data)->items);
                            ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Current Balance</label>
                                    <div class="form-control" style="background-color: #e9ecef;">
                                        ₹ {{ number_format(abs($data->balance), 2) }}
                                        @if($data->balance >= 0)
                                            <span class="badge badge-success">Cr</span>
                                        @else
                                            <span class="badge badge-danger">Dr</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Opening Balance ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</label>
                                    <input type="number" step="0.01" name="balance" class="form-control" placeholder="Enter opening balance" value="{{ $data->currentOpeningBalance ? $data->currentOpeningBalance->amount : 0 }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                     <label>Opening Balance Type</label>
                                     <select name="type" class="form-control select2" style="width: 100%;">
                                         <option value="Credit" {{ ($data->currentOpeningBalance && $data->currentOpeningBalance->balance_type == 'Credit') ? 'selected' : ($data->balance >= 0 ? 'selected' : '') }}>Credit</option>
                                         <option value="Debit" {{ ($data->currentOpeningBalance && $data->currentOpeningBalance->balance_type == 'Debit') ? 'selected' : ($data->balance < 0 ? 'selected' : '') }}>Debit</option>
                                     </select>
                                 </div>
                             </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Purchase Agent</label>
                                    <select name="purchase_agent_id" class="form-control select2" style="width: 100%;">
                                        <option value="">Select Purchase Agent</option>
                                        @foreach($purchase_agents as $agent)
                                        <option value="{{$agent->id}}" {{old('purchase_agent_id', $data->purchase_agent_id) == $agent->id ? 'selected' : ''}}>{{$agent->name}}</option>
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
                                    <label>Items</label>
                                    <select name="items[]" class="form-control select2" style="width: 100%;" multiple>
                                        <option value="0" @if(in_array(0, $selectedItems ?? [])) selected @endif>Fabric</option>
                                        @foreach($items as $item)
                                        <option value="{{$item->id}}" @if(in_array($item->id, $selectedItems ?? [])) selected @endif >{{$item->name}}</option>
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
                                    <label>Status</label>
                                    <select name="status" class="form-control select2" style="width: 100%;">
                                        <option value="1" {{optional($data)->status == 1 ? 'selected' : ''}}>Active</option>
                                        <option value="0" {{optional($data)->status == 0 ? 'selected' : ''}}>Inactive</option>
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
                                    
                                    <textarea name="description" class="form-control" placeholder="Enter description">{{$data->description}}</textarea>
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
