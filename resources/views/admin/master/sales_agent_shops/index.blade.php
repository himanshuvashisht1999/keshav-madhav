@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-store mr-2 text-primary"></i>Manage Shops</h1>
                        <small class="text-muted">Viewing shops for Sales Agent: <strong>{{ $agent->name }}</strong></small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.master.sales-agent.index') }}" class="btn btn-secondary shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Agents
                        </a>
                        <a href="{{ route('admin.master.sales-agent-shops.create', $agent->id) }}" class="btn btn-primary shadow-sm ml-2" style="border-radius: 6px;">
                            <i class="fas fa-plus mr-1"></i> Add Shop
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                
                <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title m-0 text-primary font-weight-bold"><i class="fas fa-list mr-2"></i> Shop List</h5>
                    </div>
                    <div class="card-body table-responsive">
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" id="global_search" class="form-control" placeholder="Search by name, email, or phone...">
                                </div>
                            </div>
                        </div>

                        <table id="agent-shops" class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%" class="text-center py-3">#</th>
                                    <th class="py-3">Shop Name</th>
                                    <th class="py-3">Email Address</th>
                                    <th class="py-3">Phone Number</th>
                                    <th width="10%" class="text-center py-3">Status</th>
                                    <th width="10%" class="text-center py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .table thead th { border-bottom: none; color: #444; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .table tbody td { vertical-align: middle; padding: 1rem 0.75rem; }
        .btn-icon { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
    </style>

    <script>
        $(function () {
            var oTable = $('#agent-shops').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                searching: false,
                ordering: false,
                lengthMenu: [[25, 100, -1], [25, 100, "All"]],
                "pageLength": 25,
                ajax: {
                    url: '{!! route('admin.master.sales-agent-shops.indexList', $agent->id) !!}',
                    data: function (d) {
                        d.search = d.search || {};
                        d.search.value = $('#global_search').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id', className: 'text-center text-muted' },
                    { data: 'name', name: 'name', className: 'font-weight-bold' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'action', name: 'action', searchable: false, className: 'text-center' }
                ],
                dom: 'lrtip',
                language: {
                    emptyTable: "No shops registered for this agent.",
                    processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
                }
            });

            $('#global_search').on('keyup change', function (e) {
                oTable.draw();
            });
        });
    </script>
@endsection
