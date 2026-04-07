@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Brands</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Brands</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">
                <div class="card-body table-responsive">
                    <table id="brands-table" class="table table-bordered table-hover">
                        <thead>
                            <tr role="row" class="filter">
                                <td></td>
                                <td></td>
                                <td>
                                    <input type="text" class="form-control" name="name" id="search_name" placeholder="Search Name" autocomplete="off">
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>ID</th>
                                <th>Logo</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(function () {
        var oTable = $('#brands-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.master.brand.indexList') !!}',
                data: function (d) {
                    d.name = $('#search_name').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'logo', name: 'logo'},
                {data: 'name', name: 'name'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
                {
                    text: 'Add Brand',
                    className: 'btn btn-primary',
                    action: function (e, dt, node, config) {
                        window.location.href = "{{ route('admin.master.brand.create') }}";
                    }
                }
            ]
        });

        $('#search_name').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
    });

    function deleteItem(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "This brand will be marked as inactive!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.master.brand.delete') }}",
                    type: "POST",
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire("Deleted!", response.message, "success");
                            $('#brands-table').DataTable().draw();
                        }
                    }
                });
            }
        });
    }
</script>
@endsection
