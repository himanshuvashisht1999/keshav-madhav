@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Edit Pricing</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admin.master.inventory-price.index')}}">Inventory
                                    Pricing</a></li>
                            <li class="breadcrumb-item active">Edit Pricing</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Update Pricing Record</h3>
                    </div>
                    <form action="{{route('admin.master.inventory-price.update')}}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $data->id }}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Design: </label>
                                        <p class="form-control-static"><strong>{{ $data->design->design_number }}
                                                ({{ $data->design->name_of_garment }})</strong></p>
                                        <input type="hidden" name="design_id" value="{{ $data->design_id }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Color: </label>
                                        <p class="form-control-static"><strong>{{ $data->color->name ?? 'N/A' }}</strong>
                                        </p>
                                        <input type="hidden" name="color_id" value="{{ $data->color_id }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Size Set: </label>
                                        <p class="form-control-static">
                                            <strong>{{ $data->sizeSet->set_size ?? 'N/A' }}
                                                ({{ $data->sizeSet->size_group ?? 'N/A' }})</strong>
                                        </p>
                                        <input type="hidden" name="size_set_id" value="{{ $data->size_set_id }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" placeholder="Enter Product Name" value="{{ $data->name }}" required>
                                        @error('name')<span
                                        class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label>MRP <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">₹</span>
                                            </div>
                                            <input type="number" step="0.01" name="mrp" class="form-control"
                                                placeholder="0.00" value="{{ $data->mrp }}" required>
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
                                                placeholder="0.00" value="{{ $data->selling_price }}" required>
                                        </div>
                                        @error('selling_price')<span
                                        class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Add More Images</label>
                                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                                        @error('images')<span class="text-danger small">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="1" {{ $data->status == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $data->status == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Image Gallery</h5>
                                    <div class="row">
                                        @foreach($data->images as $img)
                                            <div class="col-md-2 mb-3 text-center image-container-{{ $img->id }}">
                                                <img src="{{ $img->image_url }}" class="img-thumbnail"
                                                    style="height: 100px; width: 100px; object-fit: cover;">
                                                <button type="button"
                                                    class="btn btn-danger btn-xs btn-block mt-1 delete-pricing-image"
                                                    data-id="{{ $img->id }}">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3" style="float:right">
                                <button type="submit" class="btn btn-primary">Update Pricing</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <script>
        $(function () {
            $('.delete-pricing-image').on('click', function () {
                if (confirm('Are you sure you want to delete this image?')) {
                    let imageId = $(this).data('id');
                    let btn = $(this);
                    $.post('{{ route('admin.master.inventory-price.image-delete') }}', {
                        _token: '{{ csrf_token() }}',
                        id: imageId
                    }, function (response) {
                        if (response.success) {
                            $('.image-container-' + imageId).remove();
                        } else {
                            alert('Error deleting image.');
                        }
                    });
                }
            });
        });
    </script>
@endsection