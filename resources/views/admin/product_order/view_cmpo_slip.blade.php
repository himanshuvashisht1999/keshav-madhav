@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <h1 class="text-center">Cutting Master Production Order</h1>
        </div>
        <!-- Download Button -->
            <div class="col-sm-12 text-right">
                <a href="{{ route('admin.product_order.indexOrderSetDownload', ['id' => $header['cmpo_id']]) }}"
                   class="btn btn-primary">
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <style>
                .cmpo-title {
                    text-align: center;
                    font-size: 22px;
                    font-weight: bold;
                    margin-bottom: 20px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .meta-table td {
                    padding: 6px;
                    vertical-align: top;
                }

                .meta-label {
                    font-weight: bold;
                    width: 160px;
                }

                .section-title {
                    text-align: center;
                    font-weight: bold;
                    font-size: 16px;
                    margin: 25px 0 10px;
                    text-transform: uppercase;
                }

                .data-table th,
                .data-table td {
                    border: 1px solid #000;
                    padding: 6px;
                    text-align: center;
                }

                .data-table th {
                    background: #f2f2f2;
                }

                .signature-table td {
                    padding-top: 40px;
                    text-align: center;
                }

                .footer-note {
                    margin-top: 15px;
                    text-align: center;
                    font-size: 12px;
                    color: #555;
                }
            </style>

           
            <!-- ================= HEADER DETAILS ================= -->
            <table class="meta-table">
                <tr>
                    <td class="meta-label">CMPO No:</td>
                    <td>CMPO-{{ $header['cmpo_id'] }}</td>

                    <td class="meta-label">Date:</td>
                    <td>{{ $header['date'] }}</td>
                </tr>

                <tr>
                    <td class="meta-label">Sales Order No:</td>
                    <td>{{ $header['order_no'] }}</td>

                    <td class="meta-label">Customer:</td>
                    <td>{{ $header['customer'] }}</td>
                </tr>

                <tr>
                    <td class="meta-label">Fabric:</td>
                    <td>{{ $header['fabric'] }}</td>

                    <td class="meta-label">Fitting:</td>
                    <td>{{ $header['fitting'] }}</td>
                </tr>

                <tr>
                    <td class="meta-label">Pattern:</td>
                    <td>{{ $header['pattern'] }}</td>
                </tr>

                <tr>
                    <td class="meta-label">Warehouse:</td>
                    <td>{{ $header['warehouse_name'] }}</td>

                    <td class="meta-label">Cutting Master:</td>
                    <td>{{ $header['cuttingMaster'] }}</td>
                </tr>

                {{-- <tr>
                    <td class="meta-label">Address:</td>
                    <td colspan="3">{{ $header['cuttingMasterAddress'] }}</td>
                </tr> --}}

                <tr>
                    <td class="meta-label">Remark:</td>
                    <td colspan="3">{{ $header['remark'] }}</td>
                </tr>
            </table>

            <!-- ================= PRODUCT TABLE ================= -->
            <div class="section-title">
                Product & Quantity Details
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Design No</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th>QTY (Per Size)</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalHeaderPcs = 0; 
                    @endphp
                    @foreach ($sizeData as $row)
                        @php $totalHeaderPcs += $row['pcs']; @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row['design_no'] }}</td>
                            <td>{{ $row['color'] }}</td>
                            <td>{{ $row['size'] }}</td>
                            <td>{{ number_format($row['pcs'], 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" style="text-align:right;">Total</th>
                        <th>{{ number_format($totalHeaderPcs, 0) }}</th>
                    </tr>
                </tfoot>
            </table>

            <!-- ================= ASSIGNMENTS HISTORY ================= -->
            @if(count($assignments) > 0)
                <div class="section-title">
                    Assignments History
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>SKU</th>
                            <th>Cutting Master</th>
                            <th>Assigned QTY</th>
                            <th>Assigned Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $assignment->sku }}</td>
                                <td>{{ $assignment->cutting_master->name ?? '-' }} ({{ $assignment->cutting_master->masterFabricWarehouse->cutting_master_name ?? '-' }})</td>
                                <td>{{ $assignment->quantity }}</td>
                                <td>{{ $assignment->created_at->format('d-m-Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" style="text-align:right;">Total Assigned</th>
                            <th>{{ $assignments->sum('quantity') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            @endif

            <!-- ================= SIGNATURE ================= -->
            <table class="signature-table" width="100%">
                <tr>
                    <td>
                        _______________________<br>
                        <strong>Prepared By</strong>
                    </td>
                    <td>
                        _______________________<br>
                        <strong>Authorized Sign</strong>
                    </td>
                </tr>
            </table>

            <div class="footer-note">
                This is a system generated sales order slip.
            </div>

        </div>
    </section>
</div>
@endsection
