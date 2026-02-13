@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Inventory Pricing</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active">Inventory Pricing</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Pricing Records</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.master.inventory-price.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Price
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select id="filter-design" class="form-control select2">
                                    <option value="">Filter by Design...</option>
                                    @foreach($designs as $design)
                                        <option value="{{ $design->id }}">{{ $design->design_number }}
                                            ({{ $design->name_of_garment }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="filter-color" class="form-control select2">
                                    <option value="">Filter by Color...</option>
                                    @foreach($colors as $color)
                                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="filter-size-set" class="form-control select2">
                                    <option value="">Filter by Size Set...</option>
                                    @foreach($sizeSets as $set)
                                        <option value="{{ $set->id }}">{{ $set->set_size }} ({{ $set->size_group }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="name" id="name" class="form-control" placeholder="Search by Name">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="inventory-prices" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="60">ID</th>
                                        <th>Image</th>
                                        <th>Design</th>
                                        <th>Color</th>
                                        <th>Name</th>
                                        <th width="100">MRP</th>
                                        <th width="100">Selling Price</th>
                                        <th width="100">Status</th>
                                        <th width="100">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        $(function () {
            $('.select2').select2({ theme: 'bootstrap4' });

            var oTable = $('#inventory-prices').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                searching: false,
                ordering: false,
                ajax: {
                    url: '{!! route('admin.master.inventory-price.indexList') !!}',
                    data: function (d) {
                        d.design_id = $('#filter-design').val();
                        d.color_id = $('#filter-color').val();
                        d.size_set_id = $('#filter-size-set').val();
                        d.name = $('#name').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id' },
                    { data: 'image', name: 'image' },
                    { data: 'design', name: 'design' },
                    { data: 'color', name: 'color' },
                    { data: 'name', name: 'name' },
                    { data: 'mrp', name: 'mrp' },
                    { data: 'selling_price', name: 'selling_price' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', searchable: false }
                ],
                dom: 'lrtip',
            });

            $('#filter-design, #filter-color, #filter-size, #filter-size-set').on('change', function () {
                oTable.draw();
            });
            $('#name').on('keyup', function (e) {
                oTable.draw();
                e.preventDefault();
            });
        });
    </script>
@endsection