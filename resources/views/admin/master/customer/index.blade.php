@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-4">
                    <h1>Manage Customer</h1>
                </div>
                <div class="col-sm-8">
                    <div class="d-flex justify-content-end">
                        <div class="info-box bg-light border-0 shadow-none m-0 mr-2" style="min-height: 50px; padding: 5px;">
                            <span class="info-box-icon bg-info" style="width: 40px; font-size: 1rem;"><i class="fas fa-users"></i></span>
                            <div class="info-box-content" style="padding: 0 10px;">
                                <span class="info-box-text text-muted small">Opening ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</span>
                                <span class="info-box-number" style="font-size: 0.9rem;">₹ {{ number_format(abs($total_opening_balance), 2) }} {{ $total_opening_balance >= 0 ? 'Cr' : 'Dr' }}</span>
                            </div>
                        </div>
                        <div class="info-box bg-light border-0 shadow-none m-0" style="min-height: 50px; padding: 5px;">
                            <span class="info-box-icon bg-success" style="width: 40px; font-size: 1rem;"><i class="fas fa-wallet"></i></span>
                            <div class="info-box-content" style="padding: 0 10px;">
                                <span class="info-box-text text-muted small">Current Balance</span>
                                <span class="info-box-number" style="font-size: 0.9rem;">₹ {{ number_format(abs($total_current_balance), 2) }} {{ $total_current_balance >= 0 ? 'Cr' : 'Dr' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default ">
                 <!-- <div class="row" >
                    <div class="col-9 card-header">
                        <h3 class="card-title">Manage Customers</h3>
                    </div>
                    <div class="col-3 card-header">
                        <a href="{{route('admin.master.customer.create')}}" class="btn btn-primary" style =" float: right;  width: max-content;">Add Customer</a>
                    </div>
                </div> -->
                
                <div class="card-body table-responsive">
                <table id="customers" class="table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        <td>
                            <!-- <input type="text" class="form-control" name="id" id="id" autocomplete="off"> -->
                        </td>
                        <td>
                            <input type="text" class="form-control" name="name" id="name" autocomplete="off">
                        </td>

                        <td>
                            <input type="text" class="form-control" name="phone" id="phone" autocomplete="off">
                        </td>
                        <td>
                            <select class="form-control select2bs4" name="agent_ids[]" id="agent_ids" multiple="multiple" data-placeholder="Select Agents" style="width: 100%;">
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </td>
                       
                        
                        <td>
                            <select class="form-control" name="type" id="type" autocomplete="off">
                                <option value="">ALL</option>
                                <option value="corporate">Corporate</option>
                                <option value="domestic">Domestic</option>
                                <option value="direct">Direct</option>
                            </select>
                        </td>
                        <td>
                            <input type="date" class="form-control" name="start_date" id="start_date" autocomplete="off">
                        </td>
                        <td>
                            <input type="date" class="form-control" name="end_date" id="end_date" autocomplete="off">
                        </td>
                        <td>
                            <select class="form-control" name="status" id="status" autocomplete="off">
                                <option value="">ALL</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </td>
                        <td></td>
                    </tr>
                   <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Agent Name</th>
                    <th>Type</th>
                    <th>Opening Balance</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                  <!-- <tr>
                    <td>1</td>
                    <td>wefds</td>
                    <td>Win 95+</td>
                    <td> 4</td>
                    <td>X</td>
                  </tr> -->
                  
                  </tbody>
                  
                </table>
              </div>
            </div>
        </div>
    </section>
</div>
<script>
    $(function () {
        var i = 1;
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering:false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            "pageLength":25,
            ajax: {
                url: '{!! route('admin.master.customer.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.name = $('#name').val();
                    d.phone = $('#phone').val();
                    d.agent_ids = $('#agent_ids').val();
                    d.type = $('#type').val();
					d.status = $('#status').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                },
                orderable: false
            },
            drawCallback: function(settings) {
                var json = settings.json;
                if (json) {
                    let opBal = json.total_opening_balance || 0;
                    let currBal = json.total_current_balance || 0;
                    
                    let opType = opBal >= 0 ? 'Cr' : 'Dr';
                    let currType = currBal >= 0 ? 'Cr' : 'Dr';
                    
                    $('.info-box-text:contains("Opening")').next('.info-box-number').text('₹ ' + Math.abs(opBal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + opType);
                    $('.info-box-text:contains("Current Balance")').next('.info-box-number').text('₹ ' + Math.abs(currBal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + currType);
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'phone', name: 'phone'},
                {data: 'agent_name', name: 'agent_name'},
                {data: 'type', name: 'type'},
                {data: 'opening_balance', name: 'opening_balance'},
                {data: 'balance', name: 'balance'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
                {
                    text: 'Add Customer',
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        window.location.href = "{{ route('admin.master.customer.create') }}";
                    }
                },
                {
                    text: 'Download PDF',
                    className: 'btn-datatable bg-danger',
                    action: function (e, dt, node, config) {
                        var name = $('#name').val() || '';
                        var phone = $('#phone').val() || '';
                        var agent_ids = $('#agent_ids').val() || [];
                        var type = $('#type').val() || '';
                        var status = $('#status').val() || '';
                        var start_date = $('#start_date').val() || '';
                        var end_date = $('#end_date').val() || '';
                        
                        var agentIdsParams = agent_ids.map(id => 'agent_ids[]=' + encodeURIComponent(id)).join('&');
                        var url = "{{ route('admin.master.customer.downloadPdf') }}?" + 
                            "name=" + encodeURIComponent(name) + 
                            "&phone=" + encodeURIComponent(phone) + 
                            (agentIdsParams ? "&" + agentIdsParams : "") +
                            "&type=" + encodeURIComponent(type) + 
                            "&status=" + encodeURIComponent(status) +
                            "&start_date=" + encodeURIComponent(start_date) +
                            "&end_date=" + encodeURIComponent(end_date);
                            
                        window.location.href = url;
                    }
                }
            ]
        });

        $('#email-queue-search-form').on('submit', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#name').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#phone').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#agent_ids').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
     

        $('#status').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#type').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#start_date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#end_date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });


    });

    $(document).ready(function () {
        
    });

    function deleteData(id){
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirms, trigger the delete route
                window.location.href = "{{ route('admin.master.customer.delete') }}?id=" + id;
            }
        });
    }
</script>

@endsection
