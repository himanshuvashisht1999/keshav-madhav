@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="text-center">Production Orders</h1>
                </div>
                {{-- <div class="col-sm-12">
                    <h4 class="text-center">Order ID - ({{$order_main->sku}})</h4>
                </div> --}}
                {{-- <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Production Order</li>
                    </ol>
                </div> --}}
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
                        <h3 class="card-title">Manage Production Order</h3>
                    </div>
                    <div class="col-3 card-header">
                        <a href="{{route('admin.sales_order.create')}}" class="btn btn-primary" style =" float: right;  width: max-content;">Add Sales Order</a>
                    </div>
                </div> -->
                
                <div class="card-body table-responsive">
                <table id="customers" class="table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        <td>
                            {{-- <input type="hidden" class="form-control" name="id" id="id" value="{{$order_main->id}}" autocomplete="off"> --}}
                        </td>
                        <td>
                            <input type="text" class="form-control" name="sku" id="sku" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="product_sku" id="product_sku" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="quantity" id="quantity" autocomplete="off">
                        </td>
                                            
                        <td>
                            <select id="status" class="form-control form-control-sm">
                                <option value="">All</option>
                                <option value="1">Not Issued</option>
                                <option value="2">In Progress</option>
                                <option value="3">Completed</option>
                            </select>
                        </td>

                        <td></td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Product ID</th>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Quantity</th>
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

<!-- Modal -->
<div class="modal fade" id="tableModal" tabindex="-1" role="dialog" aria-labelledby="tableModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title" id="tableModalLabel"></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      
      <div class="modal-body" id="tableContainer">
        <!-- Table will be injected here -->
      </div>

    </div>
  </div>
</div>

<style >
    #hoverBox {
        position: absolute;
        display: none;
        background: #fff;
        border-radius: 10px;
        padding: 10px 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 220px;
        z-index: 9999;
        transition: all 0.2s ease-in-out;
    }

    #hoverBox h6 {
        margin: 0 0 5px;
        font-weight: 600;
        color: #007bff;
    }

    #hoverBox p {
        margin: 0;
        font-size: 14px;
        color: #444;
    }

    #productTable tbody tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }

</style>
<div id="hoverBox"></div>
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
                url: '{!! route('admin.product_order.indexList',['order_main_id' => $order_main_id]) !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.product_sku = $('#product_sku').val();
                    d.quantity = $('#quantity').val();
                    d.status = $('#status').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'product_sku', name: 'product_sku'},                
                {data: 'quantity', name: 'quantity'},                
                {data: 'status', name: 'status'},                
                {data: 'action', name: 'action', searchable: false}
            ],
           
        });

        $('#email-queue-search-form').on('submit', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#sku').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#product_sku').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#quantity').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#status').on('change', function (e) {
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
                window.location.href = "{{ route('admin.product_order.delete', ['id' => '']) }}" + id;
            }
        });
    }

    
    $(document).ready(function() {
        const STAGE_ROUTE = "{{ route('admin.order-stages.index') }}";
        $(document).on('click', '.statusLink', function() {
            let product_order_id = $(this).data('id');   // Get ID from data-id
            let order_sku = $(this).data('order_sku');   // Get ID from data-id    
                // small delay to avoid too many calls
                ajaxTimeout = setTimeout(() => {
                $.ajax({
                    url: '/admin/production-order/status-hover-data', // Laravel route
                    type: 'GET',
                    data: { id: product_order_id },
                    success: function (response) {
                    const stages = response.data.original;
                    // console.log("Product Order ID:", order_sku);
                    // console.log(stages);
                    $('#tableModalLabel').text('Order ID: ' + order_sku);
                    let tableHtml = `
                    
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="bg-light">
                        <tr>
                            <th>Stage</th>
                            <th>Total</th>
                            <th>Completed</th>
                            <th>Pending</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                    `;

                    Object.values(stages).forEach(row => {
                    let statusBadge = "";
                    if (row.status === 0) {
                        statusBadge = '<span class="badge badge-primary">Pending</span>';
                    } else if (row.status === 1) {
                        statusBadge = '<span class="badge badge-warning">In Progress</span>';
                    } else if (row.status === 2) {
                        statusBadge = '<span class="badge badge-success">Completed</span>';
                    }

                    tableHtml += `
                        <tr>
                        <td><a target="_blank" href="${STAGE_ROUTE}?stage_id=${row.stage_id}">${row.name}</a></td>
                        <td>${row.total_qty}</td>
                        <td>${row.completed_qty}</td>
                        <td>${row.pending_qty}</td>
                        <td>${statusBadge}</td>
                        </tr>
                    `;
                    });

                    tableHtml += '</tbody></table>';

                    // Inject table into modal body
                    $('#tableContainer').html(tableHtml);

                    // Show modal
                    $('#tableModal').modal('show');
                    
                    
                    },
                    error: function () {
                    hoverBox.html("<strong>Error loading data</strong>").fadeIn(150);
                    }
                });
                }, 200);
            });
    });
</script>

@endsection
