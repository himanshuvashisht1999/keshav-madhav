@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Add/Update Pricing</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admin.master.inventory-price.index')}}">Inventory
                                    Pricing</a></li>
                            <li class="breadcrumb-item active">Add Pricing</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Define Prices per Design</h3>
                    </div>
                    <form action="{{route('admin.master.inventory-price.store')}}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select Design <span class="text-danger">*</span></label>
                                        <select name="design_id" class="form-control select2" required>
                                            <option value="">-- Select Design --</option>
                                            @foreach($designs as $design)
                                                <option value="{{ $design->id }}" {{ old('design_id') == $design->id ? 'selected' : '' }}>{{ $design->design_number }} ({{ $design->name_of_garment }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('design_id')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" placeholder="Enter Product Name" value="{{ old('name') }}" required>
                                        @error('name')<span
                                        class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select Colors <span class="text-danger">*</span></label>
                                        <select name="color_ids[]" class="form-control select2" multiple required
                                            data-placeholder="Choose multiple colors">
                                            @foreach($colors as $color)
                                                <option value="{{ $color->id }}">{{ $color->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('color_ids')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select Size Set <span class="text-danger">*</span></label>
                                        <select name="size_set_id" class="form-control select2" required>
                                            <option value="">-- Choose Size Set --</option>
                                            @foreach($sizeSets as $set)
                                                <option value="{{ $set->id }}" {{ old('size_set_id') == $set->id ? 'selected' : '' }}>
                                                    {{ $set->set_size }} ({{ $set->size_group }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('size_set_id')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>MRP <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">₹</span>
                                            </div>
                                            <input type="number" step="0.01" name="mrp" class="form-control"
                                                placeholder="0.00" value="{{ old('mrp') }}" required>
                                        </div>
                                        @error('mrp')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Selling Price <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">₹</span>
                                            </div>
                                            <input type="number" step="0.01" name="selling_price" class="form-control"
                                                placeholder="0.00" value="{{ old('selling_price') }}" required>
                                        </div>
                                        @error('selling_price')<span
                                        class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Images</label>
                                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                                        <small class="text-muted">You can select multiple images.</small>
                                        @error('images')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3" style="float:right">
                                <button type="submit" class="btn btn-primary">Save Pricing</button>
                            </div>
                        </div>
                    </form>
                    <div class="card card-default mt-4">
                        <div class="card-header bg-light">
                            <h3 class="card-title text-primary"><i class="fas fa-exclamation-triangle mr-1"></i> Inventory
                                Items Needing Price</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="bg-gray-light">
                                        <tr>
                                            <th class="pl-3">Design No</th>
                                            <th>Product</th>
                                            <th>Color</th>
                                            <th>Size Set</th>
                                            <th width="100">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendingItems as $pending)
                                            <tr>
                                                <td class="pl-3 font-weight-bold">{{ $pending->design_number }}</td>
                                                <td>{{ $pending->product_name }}</td>
                                                <td>{{ $pending->color_name }}</td>
                                                <td>{{ $pending->size_set_name }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-xs btn-outline-primary handle-pending"
                                                        data-design="{{ $pending->product_id }}"
                                                        data-color="{{ $pending->color_id }}"
                                                        data-size-set="{{ $pending->size_set_id }}">
                                                        Select
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-3 text-muted">No pending pricing items
                                                    found in inventory.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        </section>
    </div>

    <script>
        $(function () {
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            $('.handle-pending').on('click', function () {
                let designId = $(this).data('design');
                let colorId = $(this).data('color');
                let sizeSetId = $(this).data('size-set');

                $('select[name="design_id"]').val(designId).trigger('change');
                $('select[name="color_ids[]"]').val([colorId]).trigger('change');
                $('select[name="size_set_id"]').val(sizeSetId).trigger('change');

                // Scroll to form
                $([document.documentElement, document.body]).animate({
                    scrollTop: $(".card-default").first().offset().top - 20
                }, 500);
            });
        });
    </script>
@endsection