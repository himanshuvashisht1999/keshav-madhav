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
                            <li class="breadcrumb-item active">Create Sales Agent</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Create Sales Agent</h3>
                    </div>
                    <form action="{{route('admin.master.sales-agent.store')}}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control" placeholder="Enter name"
                                            value="{{old('name')}}">
                                        @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" placeholder="Enter email"
                                            value="{{old('email')}}">
                                        @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="phone" class="form-control" placeholder="Enter phone"
                                            value="{{old('phone')}}">
                                        @error('phone')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" name="password" class="form-control"
                                            placeholder="Enter password">
                                        @error('password')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control"
                                            placeholder="Enter address">{{old('address')}}</textarea>
                                        @error('address')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Opening Balance ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</label>
                                        <input type="number" step="0.01" name="balance" class="form-control" placeholder="Enter opening balance" value="{{ old('balance', 0) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                         <label>Opening Balance Type</label>
                                         <select name="balance_type" class="form-control select2" style="width: 100%;">
                                             <option value="Credit" {{ old('balance_type') == 'Credit' ? 'selected' : 'selected' }}>Credit</option>
                                             <option value="Debit" {{ old('balance_type') == 'Debit' ? 'selected' : '' }}>Debit</option>
                                         </select>
                                     </div>
                                 </div>
                                <div class="col-md-12 mt-4">
                                    <h5>Per Brand Discounts</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Brand Name</th>
                                                    <th>Discount Percentage (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($brands as $brand)
                                                    <tr>
                                                        <td>{{ $brand->name }}</td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" max="100" 
                                                                name="brand_discounts[{{ $brand->id }}]" 
                                                                class="form-control" 
                                                                placeholder="Enter discount for {{ $brand->name }}"
                                                                value="{{ old('brand_discounts.' . $brand->id) }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group custom-control custom-checkbox mt-2">
                                        <input class="custom-control-input" type="checkbox" id="see_price" name="see_price" value="1" {{ old('see_price', 1) ? 'checked' : '' }}>
                                        <label for="see_price" class="custom-control-label">Show Pricing Info (Prices, GST, Totals)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2" style="float:right">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection