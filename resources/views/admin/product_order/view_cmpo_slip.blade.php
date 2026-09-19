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

                @media print {
                    .no-print {
                        display: none !important;
                    }
                }
            </style>

           
            <!-- ================= HEADER DETAILS ================= -->
            <table class="meta-table">
                <tr>
                    <td class="meta-label">ID:</td>
                    <td>ID-{{ $header['cmpo_id'] }}</td>

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
                    <td>
                        <div class="d-flex align-items-center justify-content-between">
                            <span id="display-fabric-names">{{ $header['fabric'] }}</span>
                            <button type="button" class="btn btn-xs btn-outline-primary ml-2 no-print" id="btnOpenEditFabrics" title="Update Assigned Fabrics">
                                <i class="fas fa-edit"></i> Edit Fabrics
                            </button>
                        </div>
                    </td>

                    <td class="meta-label">Fitting:</td>
                    <td>{{ $header['fitting'] }}</td>
                </tr>

                <tr>
                    <td class="meta-label">Pattern:</td>
                    <td>{{ $header['pattern'] }}</td>

                    <td class="meta-label">Printing Unit:</td>
                    <td>{{ $header['printing_unit_name'] }}</td>
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
                        <th>Ratio</th>
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
                            <td>{{ $row['ratio'] ?? '-' }}</td>
                            <td>{{ number_format($row['pcs'], 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" style="text-align:right;">Total</th>
                        <th>{{ number_format(array_sum(array_column($sizeData, 'ratio')), 0) }}</th>
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
                            <th>CMPO</th>
                            <th>Cutting Master</th>
                            <th>Assigned QTY</th>
                            <th>Assigned Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $assignment->id }}</td>
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

<!-- ================= EDIT ASSIGNED FABRICS MODAL ================= -->
<div class="modal fade" id="editFabricsModal" tabindex="-1" role="dialog" aria-labelledby="editFabricsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="editFabricsForm">
                @csrf
                <input type="hidden" name="id" value="{{ $header['cmpo_id'] }}">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold" id="editFabricsModalLabel">
                        <i class="fas fa-layer-group text-primary mr-1"></i> Update Assigned Fabrics (CMPO: ID-{{ $header['cmpo_id'] }})
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="fabrics-modal-loader" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">Loading fabric details...</p>
                    </div>

                    <div id="fabrics-modal-content" style="display:none;">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Policy:</strong> Fabrics marked as 
                            <span class="badge badge-warning text-dark"><i class="fas fa-lock"></i> Used in Lot (Locked)</span> 
                            cannot be removed because production lots have already used them. 
                            Unused fabrics can be removed, and additional fabrics can be added below.
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold mb-2">Currently Assigned Fabrics:</label>
                            <div id="assigned-fabrics-container" class="border rounded p-3 bg-light">
                                <!-- Populated dynamically via AJAX -->
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label class="font-weight-bold mb-1">Add More Fabrics:</label>
                            <select id="select_additional_fabrics" class="form-control select2" multiple="multiple" style="width: 100%;">
                                <!-- Populated dynamically via AJAX -->
                            </select>
                            <small class="form-text text-muted">Select new fabrics to add to this cutting order.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveFabrics">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Open Edit Fabrics Modal
    $('#btnOpenEditFabrics').on('click', function() {
        $('#editFabricsModal').modal('show');
        $('#fabrics-modal-loader').show();
        $('#fabrics-modal-content').hide();
        $('#assigned-fabrics-container').empty();
        $('#select_additional_fabrics').empty();

        $.ajax({
            url: "{{ route('admin.product_order.getCuttingSlipFabrics') }}",
            type: "GET",
            data: { id: "{{ $header['cmpo_id'] }}" },
            success: function(res) {
                if (res.status) {
                    let assignedHtml = '';
                    let assignedIds = [];

                    if (res.assigned_fabrics && res.assigned_fabrics.length > 0) {
                        res.assigned_fabrics.forEach(function(fab) {
                            assignedIds.push(parseInt(fab.id));
                            if (fab.is_used) {
                                assignedHtml += `
                                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="fab_chk_${fab.id}" checked disabled>
                                            <input type="hidden" name="fabric_ids[]" value="${fab.id}">
                                            <label class="custom-control-label font-weight-bold text-dark" for="fab_chk_${fab.id}">${fab.name}</label>
                                        </div>
                                        <div>
                                            <span class="badge badge-warning text-dark px-2 py-1" title="Used in lots">
                                                <i class="fas fa-lock mr-1"></i> Used in Lot: ${fab.lot_nos || 'Assigned'}
                                            </span>
                                        </div>
                                    </div>
                                `;
                            } else {
                                assignedHtml += `
                                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input assigned-fab-chk" name="fabric_ids[]" id="fab_chk_${fab.id}" value="${fab.id}" checked>
                                            <label class="custom-control-label" for="fab_chk_${fab.id}">${fab.name}</label>
                                        </div>
                                        <div>
                                            <span class="badge badge-success px-2 py-1">
                                                <i class="fas fa-check mr-1"></i> Unused (Can Remove)
                                            </span>
                                        </div>
                                    </div>
                                `;
                            }
                        });
                    } else {
                        assignedHtml = '<p class="text-muted mb-0">No fabrics assigned currently.</p>';
                    }
                    $('#assigned-fabrics-container').html(assignedHtml);

                    // Populate additional fabrics dropdown (exclude already assigned)
                    let availableHtml = '';
                    if (res.available_fabrics && res.available_fabrics.length > 0) {
                        res.available_fabrics.forEach(function(avail) {
                            if (!assignedIds.includes(parseInt(avail.id))) {
                                availableHtml += `<option value="${avail.id}">${avail.name} (${avail.remaining} m)</option>`;
                            }
                        });
                    }
                    $('#select_additional_fabrics').html(availableHtml);
                    $('#select_additional_fabrics').select2({
                        dropdownParent: $('#editFabricsModal'),
                        placeholder: "Choose additional fabrics..."
                    });

                    $('#fabrics-modal-loader').hide();
                    $('#fabrics-modal-content').show();
                } else {
                    alert(res.message || 'Failed to fetch fabrics');
                    $('#editFabricsModal').modal('hide');
                }
            },
            error: function() {
                alert('Error loading fabric data.');
                $('#editFabricsModal').modal('hide');
            }
        });
    });

    // Handle Form Submission
    $('#editFabricsForm').on('submit', function(e) {
        e.preventDefault();

        let selectedIds = [];

        // 1. Gather all checked or locked fabric IDs from assigned container
        $('#assigned-fabrics-container input[name="fabric_ids[]"]:checked, #assigned-fabrics-container input[type="hidden"][name="fabric_ids[]"]').each(function() {
            selectedIds.push($(this).val());
        });

        // 2. Gather all newly selected additional fabric IDs
        let additionalSelected = $('#select_additional_fabrics').val();
        if (additionalSelected && additionalSelected.length > 0) {
            additionalSelected.forEach(function(id) {
                if (!selectedIds.includes(id)) {
                    selectedIds.push(id);
                }
            });
        }

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Fabrics Selected',
                text: 'Please ensure at least one fabric is assigned.'
            });
            return;
        }

        const btn = $('#btnSaveFabrics');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: "{{ route('admin.product_order.updateAssignedFabrics') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: "{{ $header['cmpo_id'] }}",
                fabric_ids: selectedIds
            },
            success: function(res) {
                if (res.status) {
                    toastr.success(res.message);
                    $('#display-fabric-names').text(res.fabric_names || '-');
                    $('#editFabricsModal').modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Cannot Update Fabrics',
                        text: res.message
                    });
                }
            },
            error: function(xhr) {
                let msg = 'Failed to update fabrics. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Changes');
            }
        });
    });
});
</script>
@endsection
