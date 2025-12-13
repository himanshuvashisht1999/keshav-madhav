@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="text-center">Adjust POs With Fabric Shipments</h1>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <form id="filterForm" method="GET" action="{{ route('admin.purchase_order.adjustment') }}" class="form-inline">
                            <div class="form-group mr-2">
                                <label for="vendor_id" class="mr-2">Vendor</label>
                                <select name="vendor_id" id="vendor_id" class="form-control">
                                    <option value="">-- All Vendors --</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name ?? $vendor->company_name ?? 'Vendor #'.$vendor->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mr-2">
                                <label for="fabric_id" class="mr-2">Fabric</label>
                                <select name="fabric_id" id="fabric_id" class="form-control">
                                    <option value="">-- All Fabrics --</option>
                                    @foreach($fabrics as $fabric)
                                        <option value="{{ $fabric->id }}" {{ request('fabric_id') == $fabric->id ? 'selected' : '' }}>
                                            {{ $fabric->sku }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('admin.purchase_order.adjustment') }}" class="btn btn-outline-secondary ml-2">Reset</a>
                        </form>
                    </div>

                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="adjustmentTable" class="table table-striped table-bordered" data-ajax-url="{{ route('admin.purchase_order.adjustmentShipment') }}">
                            <thead>
                                <tr>
                                    <th>PO No.</th>
                                    <th>PO Date</th>
                                    <th>Exp. Delivery Date</th>
                                    <th>Vendor</th>
                                    <th>Fabric SKU</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                    @php
                                        $purchaseOrderSku = data_get($item, 'purchase_order_sku');
                                        $poDate = data_get($item, 'po_date');
                                        $expDelivery = data_get($item, 'expected_delivery_date');
                                        $fabricSku = data_get($item, 'fabric_sku');
                                        $meter = data_get($item, 'meter', 0);
                                        $purchaseOrderItemId = data_get($item, 'purchase_order_item_id');
                                        $vendorId = data_get($item, 'vendor_id');
                                        $fabricId = data_get($item, 'fabric_id');
                                        // vendor name lookup (you can optimize by passing vendors map from controller)
                                        $vendor_data = DB::table('vendors')->where('id',$vendorId)->first();
                                        $vendor_name = $vendor_data ? $vendor_data->name : '--';
                                    @endphp
                                    <tr class="adjustment-row" data-item-id="{{ $purchaseOrderItemId }}">
                                        <td>{{ $purchaseOrderSku }}</td>
                                        <td>
                                            @if($poDate)
                                                {{ \Carbon\Carbon::parse($poDate)->format('d M, Y H:i') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($expDelivery)
                                                {{ \Carbon\Carbon::parse($expDelivery)->format('d M, Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="vendor-cell" data-vendor-name="{{ $vendor_name }}">{{ $vendor_name }}</td>
                                        <td>{{ $fabricSku }}</td>
                                        <td class="text-right">{{ number_format($meter) }}</td>
                                        <td class="text-center">
                                            <input type="checkbox"
                                                   class="select-row"
                                                   onclick="selectCheckbox(this)"
                                                   data-fabric-id="{{ $fabricId }}"
                                                   data-fabric-sku="{{ $fabricSku }}"
                                                   data-vendor-id="{{ $vendorId }}"
                                                   data-po="{{ $purchaseOrderSku }}"
                                                   data-vendor-name="{{ $vendor_name }}">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Inline script: define selectCheckbox globally and behavior -->
<script>
/*
  Behavior:
  - Single selection: when a checkbox is checked, other rows hide and their checkboxes uncheck.
  - Calls AJAX GET to route('adjustmentShipment') with fabric_id & vendor_id.
  - Shows inline details row under selected row with Vendor, PO and Shipments (date, shipment number, meter).
  - When the checkbox is unchecked, restore original table.
*/

(function(){
    // helper: render shipments table html
    function renderDetailsHtml(vendorName, poNo, shipments,fabricSku) {
        var html = '<tr class="inline-details-row"><td colspan="7">';
        html += '<div class="p-2" style="background:#fafafa;border-radius:6px;">';
        html += '<div><strong>Vendor:</strong> ' + (vendorName || '--') 
      + ' &nbsp; | &nbsp; <strong>Fabric:</strong> ' + (fabricSku || '--') 
      + ' &nbsp; | &nbsp; <strong>PO:</strong> ' + (poNo || '--') 
      + '</div>';
        html += '<div class="mt-2"><strong>Shipments</strong></div>';
        html += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
        html += '<thead><tr><th>Shipment Number</th><th>Date</th><th>QTY</th></tr></thead><tbody>';

        if (!shipments || shipments.length === 0) {
            html += '<tr><td colspan="3" class="text-center">No shipments found.</td></tr>';
        } else {
            shipments.forEach(function(s){
                var dateObj = s.date_time ? new Date(s.date_time.replace(' ', 'T')) : null;

                var dateStr = dateObj
                    ? dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
                    : '-';
                html += '<tr>';
                html += '<td>' + (s.shipment_number || '-') + '</td>';
                html += '<td>' + dateStr + '</td>';
                html += '<td class="text-right">' + (s.meter !== undefined ? Number(s.meter).toLocaleString() : '-') + '</td>';
                html += '</tr>';
            });
        }

        html += '</tbody></table></div>'; // end shipments table
        html += '</div></td></tr>';
        return html;
    }

    // restore table state: show all rows, remove inline details rows, uncheck all checkboxes
    function restoreTable() {
        var tbody = document.querySelector('#adjustmentTable tbody');
        if (!tbody) return;
        // remove inline details rows
        var details = tbody.querySelectorAll('tr.inline-details-row');
        details.forEach(function(d){ d.parentNode && d.parentNode.removeChild(d); });

        // show all adjustment rows and uncheck checkboxes
        var rows = tbody.querySelectorAll('tr');
        rows.forEach(function(r){
            r.style.display = '';
            var chk = r.querySelector('input.select-row[type="checkbox"]');
            if (chk) chk.checked = false;
        });
    }

    // main function exposed globally for onclick to call
    window.selectCheckbox = function(el) {
        try {
            var fabricId = el.getAttribute('data-fabric-id');
            var fabricSku = el.getAttribute('data-fabric-sku');
            var vendorId = el.getAttribute('data-vendor-id');
            var po = el.getAttribute('data-po');
            var vendorName = el.getAttribute('data-vendor-name');

            // console debug
            console.log('selectCheckbox called — fabric_id:', fabricId, 'vendor_id:', vendorId, 'po:', po, 'vendor_name:', vendorName);

            var tbody = document.querySelector('#adjustmentTable tbody');
            if (!tbody) return;

            var isChecked = !!el.checked;

            if (!isChecked) {
                // unchecked: restore table
                restoreTable();
                return;
            }

            // Checked: single-selection behavior
            // Uncheck other checkboxes and hide other rows
            var allChecks = tbody.querySelectorAll('input.select-row[type="checkbox"]');
            allChecks.forEach(function(ch){
                if (ch !== el) ch.checked = false;
            });

            var allRows = tbody.querySelectorAll('tr');
            allRows.forEach(function(r){
                // hide all rows except the one containing current checkbox
                if (r.contains(el)) {
                    r.style.display = ''; // ensure selected row visible
                } else {
                    r.style.display = 'none';
                }
            });

            // remove any previous inline details rows
            var prevDetails = tbody.querySelectorAll('tr.inline-details-row');
            prevDetails.forEach(function(d){ d.parentNode && d.parentNode.removeChild(d); });

            // insert loading row just below the selected row
            var selRow = el.closest('tr');
            var loadingRow = document.createElement('tr');
            loadingRow.className = 'inline-details-row';
            loadingRow.innerHTML = '<td colspan="7">Loading shipments...</td>';
            selRow.parentNode.insertBefore(loadingRow, selRow.nextSibling);

            // AJAX request (uses jQuery if available, falls back to fetch)
            var ajaxUrl = document.querySelector('#adjustmentTable').getAttribute('data-ajax-url') || '{{ url('/adjustment-shipment') }}';

            // Use jQuery if present
            if (window.jQuery) {
                $.ajax({
                    url: ajaxUrl,
                    method: 'GET',
                    dataType: 'json',
                    data: { fabric_id: fabricId, vendor_id: vendorId },
                    success: function(res){
                        // remove loading row and append details
                        loadingRow.parentNode && loadingRow.parentNode.removeChild(loadingRow);
                        var shipments = Array.isArray(res) ? res : [];
                        var detailsHtml = renderDetailsHtml(vendorName, po, shipments,fabricSku);
                        selRow.insertAdjacentHTML('afterend', detailsHtml);
                    },
                    error: function(xhr, status, err){
                        console.error('AJAX error', status, err, xhr && xhr.responseText);
                        if (loadingRow.parentNode) {
                            loadingRow.parentNode.removeChild(loadingRow);
                            var errRow = document.createElement('tr');
                            errRow.className = 'inline-details-row';
                            errRow.innerHTML = '<td colspan="7" class="text-danger">Error fetching shipments. Check console for details.</td>';
                            selRow.parentNode.insertBefore(errRow, selRow.nextSibling);
                        }
                    }
                });
            } else {
                // fetch fallback
                var params = new URLSearchParams({ fabric_id: fabricId, vendor_id: vendorId });
                fetch(ajaxUrl + '?' + params.toString(), { method: 'GET', headers: { 'Accept': 'application/json' } })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    loadingRow.parentNode && loadingRow.parentNode.removeChild(loadingRow);
                    var shipments = Array.isArray(res) ? res : [];
                    var detailsHtml = renderDetailsHtml(vendorName, po, shipments,fabricSku);
                    selRow.insertAdjacentHTML('afterend', detailsHtml);
                })
                .catch(function(err){
                    console.error('Fetch error', err);
                    if (loadingRow.parentNode) {
                        loadingRow.parentNode.removeChild(loadingRow);
                        var errRow = document.createElement('tr');
                        errRow.className = 'inline-details-row';
                        errRow.innerHTML = '<td colspan="7" class="text-danger">Error fetching shipments. Check console for details.</td>';
                        selRow.parentNode.insertBefore(errRow, selRow.nextSibling);
                    }
                });
            }

        } catch (e) {
            console.error('selectCheckbox error', e);
        }
    };

})();
</script>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@endpush

@push('scripts')
<!-- DataTables (assumes jQuery is loaded globally by your layout) -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
$(function(){
    // initialize DataTable (if jQuery/DataTables available)
    if ($.fn.DataTable) {
        $('#adjustmentTable').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            order: [[1, 'desc']],
            lengthMenu: [10, 25, 50, 100],
            columnDefs: [
                { targets: 5, className: 'dt-body-right' },
                { targets: 6, orderable: false }
            ]
        });
    }
});
</script>
@endpush
