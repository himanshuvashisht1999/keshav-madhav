@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper erp-page p-2">
        <!-- PAGE HEADER -->
        <div class="erp-header-bar">
            <div>
                <div class="erp-header-title"><i class="fas fa-tags text-primary"></i> Inventory Pricing</div>
                <div class="erp-page-subtitle">Manage the Base Prices / MRP for your Domestic Inventory</div>
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.inventory-prices.create') }}" class="btn-erp btn-erp-primary"><i class="fas fa-plus"></i> Add Pricing Profile</a>
            </div>
        </div>

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

                <div class="erp-filter-bar">
                    <form method="GET" action="{{ route('admin.inventory-prices.index') }}">
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-1">
                                <label class="erp-filter-label"><i class="fas fa-tshirt mr-1"></i> Product</label>
                                <select name="product_id" class="form-control select2">
                                    <option value="">All Products</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                                            {{ $prod->series ? $prod->series->name : '' }} {{ $prod->name_of_garment }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-1">
                                <label class="erp-filter-label"><i class="fas fa-palette mr-1"></i> Color</label>
                                <select name="color_id" class="form-control select2">
                                    <option value="">All Colors</option>
                                    @foreach($colors as $color)
                                        <option value="{{ $color->id }}" {{ request('color_id') == $color->id ? 'selected' : '' }}>{{ $color->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto mb-1 ml-auto">
                                <div class="erp-filter-actions">
                                    <button type="submit" class="btn-erp btn-erp-primary"><i class="fas fa-filter"></i> Filter</button>
                                    <a href="{{ route('admin.inventory-prices.index') }}" class="btn-erp btn-erp-outline"><i class="fas fa-undo"></i> Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TABLE CARD -->
                <div class="erp-card">
                    <div class="erp-card-body p-2">
                        <div class="table-responsive">
                            <table class="erp-table table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th width="10%" class="text-center">Image</th>
                                        <th class="py-3">Product Name</th>
                                        <th class="py-3">Color</th>
                                        <th class="py-3">Size Set</th>
                                        <th class="py-3">Fitting</th>
                                        <th class="py-3">Pattern</th>
                                        <th class="py-3">MRP (₹)</th>
                                        <th class="text-center" width="10%">Action</th>
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
                                                <button type="button" class="erp-action-btn erp-btn-edit"
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

                @if($prices->hasPages())
                <div class="d-flex justify-content-between align-items-center flex-wrap mt-1">
                    <small class="text-muted">Showing {{ $prices->firstItem() }} to {{ $prices->lastItem() }} of {{ $prices->total() }} entries</small>
                    {{ $prices->appends(request()->query())->links() }}
                </div>
                @endif

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
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning px-4 font-weight-bold" id="btnUpdatePrice">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .erp-table tbody td { vertical-align: middle !important; }
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
