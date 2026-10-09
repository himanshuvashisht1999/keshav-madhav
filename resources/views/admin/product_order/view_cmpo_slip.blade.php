@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-file-invoice text-primary"></i> Cutting Master Production Order (CMPO: ID-{{ $header['cmpo_id'] }})
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.product_order.indexOrderSetDownload', ['id' => $header['cmpo_id']]) }}" class="btn-erp btn-erp-primary" title="Download Cutting Slip PDF">
                <i class="fas fa-download"></i> Download Slip
            </a>
            <button type="button" class="btn-erp btn-erp-yellow no-print" id="btnOpenEditFabrics" title="Update Assigned Fabrics">
                <i class="fas fa-edit"></i> Edit Fabrics
            </button>
            <button type="button" class="btn-erp btn-erp-outline no-print" onclick="window.print()" title="Print Slip">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="javascript:history.back()" class="btn-erp btn-erp-outline no-print">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- ERP Voucher Sheet -->
    <div class="erp-voucher-sheet">
        <!-- Header Section -->
        <div class="erp-voucher-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="erp-voucher-title mb-1">Cutting Master Production Order</h4>
                <div class="erp-company-meta text-muted">
                    <b>Sales Order:</b> {{ $header['order_no'] }} &nbsp;|&nbsp; <b>Customer:</b> {{ $header['customer'] }}
                </div>
            </div>
            <div class="text-right">
                <span class="badge erp-badge-yellow px-2 py-1 font-weight-bold" style="font-size: 13px;">
                    ID-{{ $header['cmpo_id'] }}
                </span>
                <div class="text-muted small mt-1 font-weight-bold">
                    Date: {{ $header['date'] }}
                </div>
            </div>
        </div>

        <!-- Voucher Info Details -->
        <div class="row mb-3">
            <div class="col-md-6">
                <table class="erp-info-table">
                    <tr>
                        <td class="label-col">CMPO ID:</td>
                        <td class="val-col font-weight-bold">ID-{{ $header['cmpo_id'] }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Sales Order:</td>
                        <td class="val-col">{{ $header['order_no'] }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Customer:</td>
                        <td class="val-col font-weight-bold">{{ $header['customer'] }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Fabric:</td>
                        <td class="val-col">
                            <span id="display-fabric-names" class="font-weight-bold text-dark">{{ $header['fabric'] }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Fitting:</td>
                        <td class="val-col">{{ $header['fitting'] }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="erp-info-table">
                    <tr>
                        <td class="label-col">Warehouse:</td>
                        <td class="val-col">{{ $header['warehouse_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Cutting Master:</td>
                        <td class="val-col font-weight-bold">{{ $header['cuttingMaster'] }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Pattern:</td>
                        <td class="val-col">{{ $header['pattern'] }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Season:</td>
                        <td class="val-col">{{ $header['season'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Printing Unit:</td>
                        <td class="val-col">{{ $header['printing_unit_name'] }}</td>
                    </tr>
                </table>
            </div>
            @if(!empty($header['belt']) || !empty($header['remark']))
                <div class="col-12 mt-2 pt-2 border-top">
                    <div class="row">
                        @if(!empty($header['belt']))
                            <div class="col-md-6">
                                <span class="text-muted font-weight-bold small text-uppercase">Belt:</span>
                                <span class="font-weight-bold text-dark ml-1">{{ $header['belt'] }}</span>
                            </div>
                        @endif
                        @if(!empty($header['remark']))
                            <div class="col-md-6">
                                <span class="text-muted font-weight-bold small text-uppercase">Remark:</span>
                                <span class="font-weight-bold text-dark ml-1">{{ $header['remark'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Product Table -->
        <h6 class="font-weight-bold text-uppercase border-bottom pb-1 mb-2" style="font-size: var(--erp-font-sm); color: var(--erp-text-heading);">
            <i class="fas fa-boxes mr-1 text-primary"></i> Product & Quantity Details
        </h6>

        <div class="table-responsive mb-4">
            <table class="erp-table table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Design No</th>
                        <th style="width: 140px;">Color</th>
                        <th style="width: 120px;">Size</th>
                        <th style="width: 100px;" class="text-center">Ratio</th>
                        <th style="width: 140px;" class="text-right">QTY (Per Size)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalHeaderPcs = 0; @endphp
                    @foreach ($sizeData as $row)
                        @php $totalHeaderPcs += $row['pcs']; @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="font-weight-bold">{{ $row['design_no'] }}</td>
                            <td>{{ $row['color'] }}</td>
                            <td>{{ $row['size'] }}</td>
                            <td class="text-center">{{ $row['ratio'] ?? '-' }}</td>
                            <td class="text-right font-weight-bold text-primary">{{ number_format($row['pcs'], 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-light font-weight-bold">
                        <th colspan="4" class="text-right">Total:</th>
                        <th class="text-center">{{ number_format(array_sum(array_column($sizeData, 'ratio')), 0) }}</th>
                        <th class="text-right text-primary font-weight-bold">{{ number_format($totalHeaderPcs, 0) }} pcs</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Assignments History -->
        @if(count($assignments) > 0)
            <h6 class="font-weight-bold text-uppercase border-bottom pb-1 mb-2" style="font-size: var(--erp-font-sm); color: var(--erp-text-heading);">
                <i class="fas fa-history mr-1 text-primary"></i> Assignments History
            </h6>

            <div class="table-responsive mb-4">
                <table class="erp-table table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 45px;" class="text-center">#</th>
                            <th style="width: 120px;">CMPO ID</th>
                            <th>Cutting Master</th>
                            <th style="width: 130px;" class="text-right">Assigned QTY</th>
                            <th style="width: 140px;" class="text-center">Assigned Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="font-weight-bold">ID-{{ $assignment->id }}</td>
                                <td>{{ $assignment->cutting_master->name ?? '-' }} ({{ $assignment->cutting_master->masterFabricWarehouse->cutting_master_name ?? '-' }})</td>
                                <td class="text-right font-weight-bold text-primary">{{ $assignment->quantity }}</td>
                                <td class="text-center">{{ $assignment->created_at->format('d-m-Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light font-weight-bold">
                            <th colspan="3" class="text-right">Total Assigned:</th>
                            <th class="text-right text-primary font-weight-bold">{{ $assignments->sum('quantity') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- Signatures -->
        <div class="row pt-4 mt-3 border-top">
            <div class="col-6 text-center">
                <div class="pt-4" style="border-top: 1px dashed #aaa; width: 60%; margin: 0 auto;">
                    <strong>Prepared By</strong>
                </div>
            </div>
            <div class="col-6 text-center">
                <div class="pt-4" style="border-top: 1px dashed #aaa; width: 60%; margin: 0 auto;">
                    <strong>Authorized Signatory</strong>
                </div>
            </div>
        </div>

        <div class="text-center text-muted small mt-3">
            This is a system generated cutting slip from SNAPKID ERP.
        </div>
    </div>
</div>

<!-- EDIT ASSIGNED FABRICS MODAL -->
<div class="modal fade" id="editFabricsModal" tabindex="-1" role="dialog" aria-labelledby="editFabricsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 6px; overflow: hidden; border: 1px solid var(--erp-border);">
            <form id="editFabricsForm">
                @csrf
                <input type="hidden" name="id" value="{{ $header['cmpo_id'] }}">
                <div class="modal-header py-2 px-3" style="background: var(--erp-green-primary); color: #fff;">
                    <h5 class="modal-title font-weight-bold" style="font-size: var(--erp-font-md);" id="editFabricsModalLabel">
                        <i class="fas fa-layer-group mr-1"></i> Update Assigned Fabrics (CMPO: ID-{{ $header['cmpo_id'] }})
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <div id="fabrics-modal-loader" class="text-center py-4">
                        <div class="spinner-border text-success" role="status">
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
                            Unused fabrics can be unchecked to remove, and additional fabrics can be added below.
                        </div>

                        <div class="erp-form-group mb-3">
                            <label class="erp-filter-label mb-2">Currently Assigned Fabrics:</label>
                            <div id="assigned-fabrics-container" class="border rounded p-3 bg-light">
                                <!-- Populated dynamically via AJAX -->
                            </div>
                        </div>

                        <div class="erp-form-group mb-2">
                            <label class="erp-filter-label mb-1">Add More Fabrics:</label>
                            <select id="select_additional_fabrics" class="form-control select2 erp-input" multiple="multiple" style="width: 100%;">
                                <!-- Populated dynamically via AJAX -->
                            </select>
                            <small class="form-text text-muted">Select new fabrics to add to this cutting order.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn-erp btn-erp-primary" id="btnSaveFabrics">
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
                                            <label class="custom-control-label font-weight-bold text-dark" for="fab_chk_${fab.id}">${fab.name}</label>
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
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to fetch fabrics' });
                    $('#editFabricsModal').modal('hide');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error loading fabric data.' });
                $('#editFabricsModal').modal('hide');
            }
        });
    });

    $('#editFabricsForm').on('submit', function(e) {
        e.preventDefault();

        let selectedIds = [];
        $('#assigned-fabrics-container input[name="fabric_ids[]"]:checked, #assigned-fabrics-container input[type="hidden"][name="fabric_ids[]"]').each(function() {
            selectedIds.push($(this).val());
        });

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
