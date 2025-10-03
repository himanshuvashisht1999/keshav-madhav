@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Fabric Receipt Detail</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Fabric Receipt Detail</li>
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
                        <h3 class="card-title">Fabric Receipt Detail</h3>
                    </div>
                    <form action="{{ route('admin.fabric_receipt.storeDetail') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $data->id }}">
                        <div class="card-body">
                            <div class="row">



                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fabric SKU</label>
                                        <select name="fabric_sku" id="fabric_sku" class="form-control select2"
                                            style="width: 100%;">
                                            @foreach ($fabrics as $single_data)
                                                <option value="{{ $single_data->id }}">{{ $single_data->sku }}</option>
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
                                        <label for="roll">Rolls / Boxes</label>
                                        <input type="number" name="roll" id="roll" class="form-control"
                                            placeholder="Enter rolls">
                                        @if ($errors->has('roll'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('roll') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="meter">Meter (per roll)</label>
                                        <input type="number" name="meter" id="meter" class="form-control"
                                            placeholder="Enter meters">
                                        @if ($errors->has('meter'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('meter') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Purchase Order</label>
                                        <select name="purchase_order_id" id="purchase_order_id" class="form-control select2"
                                            style="width: 100%;">
                                            <option value="0">NIL</option>
                                            @foreach ($purchase_orders as $single_data)
                                                <option value="{{ $single_data->id }}">{{ $single_data->sku }}</option>
                                            @endforeach

                                        </select>
                                        @if ($errors->has('purchase_order_id'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('purchase_order_id') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @for ($i = 1; $i <= $data->roll; $i++)
                                    <div class="col-md-12 mb-3">
                                        <label class="fw-semibold">Roll {{ $i }}</label>

                                        <div class="row g-2 align-items-center fabric-roll-row">
                                            {{-- Hidden roll number --}}
                                            <input type="hidden" name="rolls[{{ $i }}][roll_number]"
                                                value="{{ $i }}" required>

                                            <div class="col-md-2">
                                                <input type="number" name="rolls[{{ $i }}][meter]"
                                                    class="form-control" placeholder="Meters" required>
                                            </div>

                                            <div class="col-md-2">
                                                <input type="text" name="rolls[{{ $i }}][batch]"
                                                    class="form-control" placeholder="Batch" required>
                                            </div>
                                        </div>
                                    </div>
                                @endfor


                                <div class="col-md-12">
                                    <div class="mt-2" style="float:right">
                                        <button type="submit" class="btn btn-success">
                                            Save & Add New
                                        </button>
                                        <a href="{{ route('admin.fabric_receipt.index') }}" class="btn btn-danger">
                                            Exit Without Save
                                        </a>
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
