@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Sales Agents</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active">Manage Sales Agents</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Sales Agent List</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.master.sales-agent.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Sales Agent
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="sales-agents" class="table table-bordered table-striped">
                            <thead>
                                <tr role="row" class="filter">
                                    <td></td>
                                    <td><input type="text" class="form-control form-control-sm" name="name" id="name"
                                            placeholder="Search Name..." autocomplete="off"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="email" id="email"
                                            placeholder="Search Email..." autocomplete="off"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th width="60">ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th width="100">Status</th>
                                    <th width="100">Action</th>
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
            var oTable = $('#sales-agents').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                searching: false,
                ordering: false,
                lengthMenu: [[25, 100, -1], [25, 100, "All"]],
                "pageLength": 25,
                ajax: {
                    url: '{!! route('admin.master.sales-agent.indexList') !!}',
                    data: function (d) {
                        d.name = $('#name').val();
                        d.email = $('#email').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', searchable: false }
                ],
                dom: 'lrtip',
            });

            $('#name, #email').on('keyup', function (e) {
                oTable.draw();
                e.preventDefault();
            });
        });
    </script>
@endsection