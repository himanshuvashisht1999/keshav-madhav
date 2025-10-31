@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Production Order</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Production Order</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default ">
                 <div class="row" >
                    <div class="col-9 card-header">
                        <h3 class="card-title">Manage Production Order</h3>
                    </div>
                    <div class="col-3 card-header">
                        <!-- <a href="{{route('admin.sales_order.create')}}" class="btn btn-primary" style =" float: right;  width: max-content;">Add Sales Order</a> -->
                    </div>
                </div>
                
                <div class="card-body table-responsive">
                <table id="customers" class="table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        <td>
                            <!-- <input type="text" class="form-control" name="id" id="id" autocomplete="off"> -->
                        </td>
                        <td>
                            <input type="text" class="form-control" name="sku" id="sku" autocomplete="off">
                        </td>
                        <td>
                            <select name="master_customer_id" id="master_customer_id" class="form-control">
                                <option value="">All</option>
                                @foreach($customers as $customer)
                                <option value="{{$customer->id}}">{{$customer->name}}</option>
                                @endforeach
                            </select>
                            
                        </td>
                       
                        <td>
                            <input type="date" class="form-control" name="created_at" id="created_at" autocomplete="off">
                        </td>
                        <td>
                            <input type="date" class="form-control" name="expected_delivery_date" id="expected_delivery_date" autocomplete="off">
                        </td>

                        <td></td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Created Date</th>
                        <th>Expected Delivery Date</th>
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
                url: '{!! route('admin.product_order.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.master_customer_id = $('#master_customer_id').val();
                    d.created_at = $('#created_at').val();
                    d.expected_delivery_date = $('#expected_delivery_date').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'master_customer_id', name: 'master_customer_id'},                
                {data: 'created_at', name: 'created_at'},                
                {data: 'expected_delivery_date', name: 'expected_delivery_date'},                
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: ['excel', 'csv', 'pdf', 'copy']
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
        $('#master_customer_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#expected_delivery_date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#created_at').on('change', function (e) {
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
        let table = $('#customers').DataTable();
        const hoverBox = $('#hoverBox');
        let ajaxTimeout;
        let currentCell = null; // 👈 track current hovered cell
        // Find the index of the "Product Name" column dynamically
        const colIndex = $('#customers thead th').filter(function() {
            return $(this).text().trim() === 'Order ID';
        }).index() + 1;

        // Hover only on that column
        $('#customers tbody').on('mouseenter', `tr td:nth-child(${colIndex})`, function(e) {
            const cell = this; // current cell reference
            currentCell = cell; // mark current active cell
            // $('#customers tbody').on('mouseenter', `tr td:nth-child(${colIndex})`, function(e) {
            const productName = $(this).text().trim();
            console.log(productName);
            let product_order_id = productName.substring(productName.lastIndexOf('/') + 1);
            // clear any old ajax delay
                clearTimeout(ajaxTimeout);

                // small delay to avoid too many calls
                ajaxTimeout = setTimeout(() => {
                $.ajax({
                    url: '/admin/production-order/status-hover-data', // Laravel route
                    type: 'GET',
                    data: { id: product_order_id },
                    success: function (response) {
                    const stages = response.data.original;
                    // console.log(stages);
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
                            statusBadge = 'Pending';
                        } else if (row.status === 1) {
                            statusBadge = 'In Progress';
                        } else if (row.status === 2) {
                            statusBadge = 'Completed';
                        }
                        tableHtml += `
                        <tr>
                            <td>${row.name}</td>
                            <td>${row.total_qty}</td>
                            <td>${row.completed_qty}</td>
                            <td>${row.pending_qty}</td>
                            <td><span >${statusBadge}</span></td>
                        </tr>
                        `;
                    });

                    tableHtml += '</tbody></table>';
                
                    hoverBox.html(`
                        <strong>Order ID: ${productName} </strong>
                        <hr class="my-2">
                        ${tableHtml}
                    `).css({
                        top: e.pageY + 15 + 'px',
                        left: e.pageX + 15 + 'px'
                    }).fadeIn(200);
                    },
                    error: function () {
                    hoverBox.html("<strong>Error loading data</strong>").fadeIn(150);
                    }
                });
                }, 200);
            });

        // Move box with cursor
       $('#customers tbody').on('mousemove', `tr td:nth-child(${colIndex})`, function(e) {
            hoverBox.css({
                top: e.pageY + 10 + 'px',
                left: e.pageX + 10 + 'px'
            });
        });

        // Hide box when mouse leaves the cell
        $('#customers tbody').on('mouseleave', `tr td:nth-child(${colIndex})`, function() {
            currentCell = null; // 👈 reset current cell
            clearTimeout(ajaxTimeout);
            hoverBox.hide();
        });
    });
</script>

@endsection
