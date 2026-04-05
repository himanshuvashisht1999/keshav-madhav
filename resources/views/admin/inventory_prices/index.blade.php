@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center mb-3">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">Inventory Pricing</h1>
                        <small class="text-muted">Manage the Base Prices / MRP for your Domestic Inventory</small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.inventory-prices.create') }}" class="btn btn-primary font-weight-bold shadow-sm" style="border-radius: 8px;">
                            <i class="fas fa-plus mr-1"></i> Add Pricing Profile
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body bg-light rounded p-4">
                        <form method="GET" action="{{ route('admin.inventory-prices.index') }}">
                            <div class="row align-items-end">
                                <div class="col-md-4 form-group mb-0">
                                    <label class="small font-weight-bold text-muted mb-1">Product</label>
                                    <select name="product_id" class="form-control select2">
                                        <option value="">All Products</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                                                {{ $prod->series ? $prod->series->name : '' }} {{ $prod->name_of_garment }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-0">
                                    <label class="small font-weight-bold text-muted mb-1">Color</label>
                                    <select name="color_id" class="form-control select2">
                                        <option value="">All Colors</option>
                                        @foreach($colors as $color)
                                            <option value="{{ $color->id }}" {{ request('color_id') == $color->id ? 'selected' : '' }}>
                                                {{ $color->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 form-group mb-0">
                                    <button type="submit" class="btn btn-primary shadow-sm btn-block">
                                        <i class="fas fa-search mr-1"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-2 form-group mb-0">
                                    <a href="{{ route('admin.inventory-prices.index') }}" class="btn btn-secondary shadow-sm btn-block">
                                        <i class="fas fa-undo mr-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABLE CARD -->
                <div class="card shadow border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light contrast-text">
                                    <tr>
                                        <th width="5%" class="text-center py-3">#</th>
                                        <th width="10%" class="text-center py-3">Image</th>
                                        <th class="py-3">Product Name</th>
                                        <th class="py-3">Color</th>
                                        <th class="py-3">Size Set</th>
                                        <th class="py-3">Fitting</th>
                                        <th class="py-3">Pattern</th>
                                        <th class="py-3">MRP (₹)</th>
                                        <th class="text-center py-3" width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($prices as $price)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration + $prices->firstItem() - 1 }}</td>
                                            <td class="text-center">
                                                @if($price->image_path)
                                                    <img src="{{ $price->image_url }}" alt="Product Image" class="img-thumbnail" style="height: 50px; width: 50px; object-fit: cover; border-radius: 8px;">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center text-muted border" style="height: 50px; width: 50px; border-radius: 8px; font-size: 10px; margin: 0 auto;">No IMG</div>
                                                @endif
                                            </td>
                                            <td class="font-weight-bold">{{ $price->product_name }}</td>
                                            <td>{{ $price->color ? $price->color->name : '-' }}</td>
                                            <td>{{ $price->sizeSet ? $price->sizeSet->name : '-' }}</td>
                                            <td>{{ $price->fitting ? $price->fitting->name : '-' }}</td>
                                            <td>{{ $price->pattern ? $price->pattern->name : '-' }}</td>
                                            <td class="font-weight-bold text-success">₹{{ number_format($price->mrp, 2) }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-warning btn-sm btn-icon shadow-sm"
                                                        data-toggle="modal" 
                                                        data-target="#editPriceModal"
                                                        data-id="{{ $price->id }}"
                                                        data-mrp="{{ $price->mrp }}"
                                                        data-name="{{ $price->product_name }}"
                                                        title="Update MRP">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-info-circle mr-2"></i> No Inventory pricing records found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    {{ $prices->links() }}
                </div>

            </div>
        </section>
    </div>

    <!-- Edit Price Modal -->
    <div class="modal fade" id="editPriceModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none;">
                <div class="modal-header bg-warning border-0 text-dark">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-tag mr-2"></i> Update MRP</h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="alert alert-info text-sm">
                        <i class="fas fa-info-circle mr-2"></i> Updating this will change the Base MRP for all unassigned inventory matching these attributes.
                    </div>
                    <form id="editPriceForm">
                        @csrf
                        <input type="hidden" name="id" id="edit_price_id">
                        
                        <div class="form-group">
                            <label class="small font-weight-bold text-muted">Product Reference</label>
                            <input type="text" class="form-control font-weight-bold" id="edit_product_name" readonly style="background-color: #e9ecef;">
                        </div>

                        <div class="form-group">
                            <label class="small font-weight-bold text-muted">New MRP (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₹</span>
                                </div>
                                <input type="number" step="0.01" class="form-control font-weight-bold text-success" name="mrp" id="edit_mrp" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-white border-0 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning px-4 font-weight-bold" id="btnUpdatePrice">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .contrast-text th { color: #444; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
        .table tbody td { vertical-align: middle; padding: 1rem 0.75rem; }
        .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
    </style>

    <script>
        $(function () {
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

            $('#editPriceModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                $('#edit_price_id').val(button.data('id'));
                $('#edit_mrp').val(button.data('mrp'));
                $('#edit_product_name').val(button.data('name'));
            });

            $('#btnUpdatePrice').on('click', function() {
                var form = $('#editPriceForm');
                if(!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }

                var btn = $(this);
                var originalText = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.inventory-prices.update-price') }}",
                    type: "POST",
                    data: form.serialize(),
                    success: function(res) {
                        if(res.success) {
                            window.location.reload();
                        } else {
                            btn.html(originalText).prop('disabled', false);
                            toastr.error(res.message);
                        }
                    },
                    error: function(xhr) {
                        btn.html(originalText).prop('disabled', false);
                        toastr.error(xhr.responseJSON?.message || "An error occurred.");
                    }
                });
            });
        });
    </script>
@endsection
