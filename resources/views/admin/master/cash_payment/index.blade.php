@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Cash Payments</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active">Manage Cash Payments</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-body table-responsive">
                        <table id="cash_payments_table" class="table table-bordered table-hover">
                            <thead>
                                <tr role="row" class="filter">
                                    <td></td>
                                    <td><input type="text" class="form-control" name="name" id="name" placeholder="Name">
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th>ID</th>
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
            var oTable = $('#cash_payments_table').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                searching: true,
                ordering: false,
                lengthMenu: [[25, 100, -1], [25, 100, "All"]],
                "pageLength": 25,
                ajax: {
                    url: '{!! route('admin.master.cash_payment.indexList') !!}',
                    data: function (d) {
                        d.name = $('#name').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', searchable: false }
                ],
                dom: 'lBfrtip',
                buttons: [
                    {
                        text: 'Add Cash Payment',
                        className: 'btn-datatable',
                        action: function (e, dt, node, config) {
                            window.location.href = "{{ route('admin.master.cash_payment.create') }}";
                        }
                    }
                ]
            });

            $('#name').on('keyup', function (e) {
                oTable.draw();
                e.preventDefault();
            });
        });
    </script>
@endsection