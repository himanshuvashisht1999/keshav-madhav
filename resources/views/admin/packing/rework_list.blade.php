@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper" style="background-color: #f1f5f9;">
    <!-- PAGE HEADER WITH EMBEDDED COMPACT STATS -->
    <section class="content-header pb-2 pt-3">
        <div class="container-fluid">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                <div>
                    <h1 class="m-0 font-weight-bold text-dark d-flex align-items-center" style="font-size: 1.35rem; letter-spacing: -0.3px;">
                        <span class="mr-2 text-danger"><i class="fas fa-tools"></i></span>
                        Defect & Rework Management
                    </h1>
                    <small class="text-muted">Items stored in rack awaiting admin rework assignment</small>
                </div>
                
                <!-- COMPACT INLINE STATS -->
                <div class="d-flex flex-wrap align-items-center mt-2 mt-md-0" style="gap: 10px;">
                    <div class="stat-pill bg-white px-3 py-1 rounded border shadow-sm d-flex align-items-center">
                        <span class="badge badge-warning p-2 mr-2 text-dark"><i class="fas fa-warehouse"></i></span>
                        <div style="line-height: 1.1;">
                            <span class="text-muted text-uppercase" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px;">Stored in Rack</span>
                            <div class="font-weight-bold text-dark" id="statStored" style="font-size: 0.95rem;">{{ $totalStoredPieces ?? 0 }} Pcs</div>
                        </div>
                    </div>

                    <div class="stat-pill bg-white px-3 py-1 rounded border shadow-sm d-flex align-items-center">
                        <span class="badge badge-success p-2 mr-2"><i class="fas fa-check-circle"></i></span>
                        <div style="line-height: 1.1;">
                            <span class="text-muted text-uppercase" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px;">Assigned</span>
                            <div class="font-weight-bold text-dark" id="statAssigned" style="font-size: 0.95rem;">{{ $totalAssignedPieces ?? 0 }} Pcs</div>
                        </div>
                    </div>

                    <div class="stat-pill bg-white px-3 py-1 rounded border shadow-sm d-flex align-items-center">
                        <span class="badge badge-primary p-2 mr-2"><i class="fas fa-cubes"></i></span>
                        <div style="line-height: 1.1;">
                            <span class="text-muted text-uppercase" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px;">Active Lots</span>
                            <div class="font-weight-bold text-dark" id="statLots" style="font-size: 0.95rem;">{{ $totalActiveLots ?? 0 }} Lots</div>
                        </div>
                    </div>

                    <a href="{{ route('admin.packing.index') }}" class="btn btn-sm btn-outline-secondary px-3 shadow-sm ml-2" style="border-radius: 6px; height: 36px; display: inline-flex; align-items: center;">
                        <i class="fas fa-box-open mr-1"></i> Packing Module
                    </a>
                    <button id="btnRefresh" class="btn btn-sm btn-light border px-3 shadow-sm" style="border-radius: 6px; height: 36px;" title="Refresh List">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- STREAMLINED FILTER BAR -->
            <div class="card shadow-sm border-0 mb-3" style="border-radius: 8px;">
                <div class="card-body bg-white p-2 px-3">
                    <div class="row align-items-center no-gutters">
                        <div class="col-lg-3 col-md-6 pr-2 mb-2 mb-lg-0">
                            <label class="small font-weight-bold text-muted mb-0 d-block" style="font-size: 0.72rem;">SEARCH</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted small"></i></span>
                                </div>
                                <input type="text" id="filterSearch" class="form-control form-control-sm border-left-0" placeholder="Order, Lot, Design, Customer...">
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-3 pr-2 mb-2 mb-lg-0">
                            <label class="small font-weight-bold text-muted mb-0 d-block" style="font-size: 0.72rem;">STATUS</label>
                            <select id="filterStatus" class="form-control form-control-sm custom-select">
                                <option value="">All Statuses</option>
                                <option value="stored" selected>In Rack (Pending)</option>
                                <option value="assigned">Assigned for Rework</option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-3 pr-2 mb-2 mb-lg-0">
                            <label class="small font-weight-bold text-muted mb-0 d-block" style="font-size: 0.72rem;">STOREROOM / RACK</label>
                            <select id="filterRack" class="form-control form-control-sm select2">
                                <option value="">All Storerooms / Racks</option>
                                @foreach($storerooms as $store)
                                    <optgroup label="{{ $store->name }}">
                                        @foreach($store->racks as $rack)
                                            <option value="{{ $rack->id }}">{{ $rack->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6 pr-2 mb-2 mb-lg-0">
                            <label class="small font-weight-bold text-muted mb-0 d-block" style="font-size: 0.72rem;">DATE FROM</label>
                            <input type="date" id="filterStartDate" class="form-control form-control-sm">
                        </div>

                        <div class="col-lg-2 col-md-6 mb-2 mb-lg-0 d-flex align-items-end">
                            <div class="w-100 pr-2">
                                <label class="small font-weight-bold text-muted mb-0 d-block" style="font-size: 0.72rem;">DATE TO</label>
                                <input type="date" id="filterEndDate" class="form-control form-control-sm">
                            </div>
                            <div class="d-flex" style="gap: 4px;">
                                <button id="btnFilterApply" class="btn btn-sm btn-primary shadow-sm" style="border-radius: 6px; padding: 4px 10px;" title="Apply Filter">
                                    <i class="fas fa-filter"></i>
                                </button>
                                <button id="btnResetFilters" class="btn btn-sm btn-light border shadow-sm" style="border-radius: 6px; padding: 4px 10px;" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FLOATING / STICKY BULK ACTION BAR -->
            <div id="bulkActionBar" class="card shadow-sm border-0 mb-3 bg-white border-left border-danger" style="border-radius: 8px; border-left-width: 4px !important; display: none;">
                <div class="card-body p-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-danger mr-2 px-2 py-1"><i class="fas fa-check-double mr-1"></i><span id="selectedCountText">0</span> Selected</span>
                        <span class="text-dark font-weight-bold small">Total: <span id="selectedQtyText" class="text-danger font-weight-bold">0</span> Pcs</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold px-3 mr-2 shadow-sm" id="btnBulkSlip" style="border-radius: 6px;" title="Print / Download Slips for selected items">
                            <i class="fas fa-print mr-1"></i> Download Slips
                        </button>
                        <button type="button" class="btn btn-sm btn-danger font-weight-bold px-3 mr-2 shadow-sm" id="btnOpenBulkAssign" style="border-radius: 6px;">
                            <i class="fas fa-paper-plane mr-1"></i> Assign Selected for Rework
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary px-2" id="btnBulkDelete" style="border-radius: 6px;" title="Delete Selected">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- DATA TABLE CARD -->
            <div class="card shadow-sm border-0" style="border-radius: 8px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="reworkTable" class="table table-hover table-striped align-middle mb-0 erp-rework-table">
                            <thead>
                                <tr>
                                    <th width="35" class="text-center py-2">
                                        <input type="checkbox" id="selectAllRework" style="cursor: pointer;">
                                    </th>
                                    <th width="105" class="py-2">Date / Slip</th>
                                    <th width="125" class="py-2">Order / Customer</th>
                                    <th width="85" class="text-center py-2">Lot No</th>
                                    <th width="130" class="py-2">Design & Color</th>
                                    <th width="105" class="text-center py-2">Size & Qty</th>
                                    <th width="150" class="py-2">Storeroom & Rack</th>
                                    <th width="145" class="py-2">Stage & Unit</th>
                                    <th width="105" class="text-center py-2">Status</th>
                                    <th width="100" class="py-2">Remarks</th>
                                    <th width="130" class="text-center py-2 pr-3">Action</th>
                                </tr>
                            </thead>
                            <tbody id="reworkTableBody">
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">
                                        <i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
                                        <div class="small">Loading rework records...</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- ASSIGNMENT MODAL -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-danger text-white px-4 py-3">
                <h6 class="modal-title font-weight-bold mb-0" id="assignModalTitle">
                    <i class="fas fa-tools mr-2"></i> Assign Items for Rework
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3 p-3" style="border-radius: 8px;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted font-weight-bold small">Items to Assign:</span>
                        <span class="badge badge-danger px-2 py-1" id="modalItemsCount">1 Record &bull; 0 Pcs</span>
                    </div>
                    <div class="small text-dark" id="modalItemsSummary" style="max-height: 110px; overflow-y: auto; line-height: 1.4;">
                        <!-- Item summaries populated dynamically -->
                    </div>
                </div>

                <form id="formAssignRework">
                    <input type="hidden" id="modalAssignIds">
                    
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">
                            Target Production Stage <span class="text-danger">*</span>
                        </label>
                        <select id="modalStage" class="form-control form-control-sm select2" required style="width: 100%;">
                            <option value="">-- Select Stage --</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Select the production stage where defect work will be done</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">
                            Target Unit Person <span class="text-danger">*</span>
                        </label>
                        <select id="modalUnit" class="form-control form-control-sm select2" required style="width: 100%;">
                            <option value="">-- Select Unit --</option>
                        </select>
                        <small class="text-muted d-block mt-1">Pre-filled with the unit from packing, or assign to any other unit person</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">Assignment Remarks</label>
                        <textarea id="modalRemarks" class="form-control form-control-sm" rows="2" placeholder="Optional notes for unit person..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end align-items-center mt-4">
                        <button type="button" class="btn btn-sm btn-light border px-3 mr-2" data-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                        <button type="submit" id="btnConfirmAssign" class="btn btn-sm btn-danger px-4 font-weight-bold shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-check mr-1"></i> Confirm & Assign for Rework
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CHANGE ASSIGNED UNIT MODAL -->
<div class="modal fade" id="reassignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-primary text-white px-4 py-3">
                <h6 class="modal-title font-weight-bold mb-0">
                    <i class="fas fa-exchange-alt mr-2"></i> Change Assigned Unit
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form id="formReassignUnit">
                    <input type="hidden" id="modalReassignId">
                    
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">Target Stage <span class="text-danger">*</span></label>
                        <select id="modalReassignStage" class="form-control form-control-sm select2" required style="width: 100%;">
                            <option value="">-- Select Stage --</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">Target Unit <span class="text-danger">*</span></label>
                        <select id="modalReassignUnit" class="form-control form-control-sm select2" required style="width: 100%;">
                            <option value="">-- Select Unit --</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">Remarks</label>
                        <textarea id="modalReassignRemarks" class="form-control form-control-sm" rows="2"></textarea>
                    </div>

                    <div class="d-flex justify-content-end align-items-center mt-4">
                        <button type="button" class="btn btn-sm btn-light border px-3 mr-2" data-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                        <button type="submit" id="btnConfirmReassign" class="btn btn-sm btn-primary px-4 font-weight-bold shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-save mr-1"></i> Update Unit Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .erp-rework-table {
        font-size: 0.8125rem;
    }
    .erp-rework-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #cbd5e1;
        border-top: none;
        vertical-align: middle;
        white-space: nowrap;
    }
    .erp-rework-table tbody td {
        vertical-align: middle;
        padding: 0.55rem 0.65rem;
        border-color: #f1f5f9;
        line-height: 1.25;
    }
    .select2-container .select2-selection--single {
        height: 31px !important;
        border-radius: 4px;
        border-color: #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        font-size: 0.8rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 28px !important;
    }
    .badge-in-rack {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-weight: 600;
        font-size: 0.73rem;
        padding: 0.3em 0.6em;
        border-radius: 4px;
        white-space: nowrap;
    }
    .badge-assigned {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
        font-weight: 600;
        font-size: 0.73rem;
        padding: 0.3em 0.6em;
        border-radius: 4px;
        white-space: nowrap;
    }
    .badge-size-qty {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        font-weight: 700;
        font-size: 0.78rem;
        padding: 0.35em 0.65em;
        border-radius: 4px;
        white-space: nowrap;
    }
</style>

<script>
    let allReworkItems = [];

    $(function() {
        if ($('.select2').length) {
            $('.select2').select2({ width: '100%' });
        }

        loadReworkData();

        $('#btnFilterApply').click(function() {
            loadReworkData();
        });

        $('#filterSearch').on('keyup', function(e) {
            if (e.key === 'Enter') {
                loadReworkData();
            }
        });

        $('#btnResetFilters').click(function() {
            $('#filterSearch').val('');
            $('#filterStatus').val('stored');
            $('#filterRack').val('').trigger('change');
            $('#filterStartDate').val('');
            $('#filterEndDate').val('');
            loadReworkData();
        });

        $('#btnRefresh').click(function() {
            loadReworkData();
        });

        $('#modalStage').change(function() {
            let stageId = $(this).val();
            let $unit = $('#modalUnit');
            $unit.html('<option value="">-- Select Unit --</option>');
            if (!stageId) return;

            $.get("{{ route('admin.packing.stageUnits', '') }}/" + stageId, function(res) {
                if (res.status === 'success' && res.units) {
                    res.units.forEach(u => {
                        $unit.append(`<option value="${u.id}">${u.name}</option>`);
                    });
                    $unit.trigger('change');
                }
            });
        });

        $('#modalReassignStage').change(function() {
            let stageId = $(this).val();
            let $unit = $('#modalReassignUnit');
            $unit.html('<option value="">-- Select Unit --</option>');
            if (!stageId) return;

            $.get("{{ route('admin.packing.stageUnits', '') }}/" + stageId, function(res) {
                if (res.status === 'success' && res.units) {
                    res.units.forEach(u => {
                        $unit.append(`<option value="${u.id}">${u.name}</option>`);
                    });
                    $unit.trigger('change');
                }
            });
        });

        $('#selectAllRework').change(function() {
            let isChecked = $(this).is(':checked');
            $('.rework-row-chk:visible').prop('checked', isChecked);
            updateBulkActionBar();
        });

        $(document).on('change', '.rework-row-chk', function() {
            updateBulkActionBar();
        });

        $(document).on('click', '.btn-assign-single', function() {
            let id = $(this).data('id');
            let item = allReworkItems.find(r => r.id == id);
            if (!item) return;

            openAssignmentModal([item]);
        });

        $('#btnOpenBulkAssign').click(function() {
            let selectedIds = getSelectedIds();
            if (selectedIds.length === 0) return;

            let selectedItems = allReworkItems.filter(r => selectedIds.includes(r.id));
            openAssignmentModal(selectedItems);
        });

        $('#formAssignRework').submit(function(e) {
            e.preventDefault();
            let ids = JSON.parse($('#modalAssignIds').val() || '[]');
            let toStageId = $('#modalStage').val();
            let toUnitId = $('#modalUnit').val();
            let remarks = $('#modalRemarks').val();

            if (!toStageId || !toUnitId || ids.length === 0) {
                alert('Please select both Target Stage and Unit.');
                return;
            }

            let $btn = $('#btnConfirmAssign');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Assigning...');

            $.ajax({
                url: "{{ route('admin.packing.assignReworkFromRack') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: ids,
                    to_stage_id: toStageId,
                    to_unit_id: toUnitId,
                    remarks: remarks
                },
                success: function(res) {
                    if (res.status === 'success') {
                        $('#assignModal').modal('hide');
                        if (ids.length === 1) {
                            toastr.success(res.message + ' <a href="/admin/packing/download-rework-slip/' + ids[0] + '" style="color: #fff; text-decoration: underline; font-weight: bold; margin-left: 8px;"><i class="fas fa-download"></i> Download Slip</a>');
                        } else {
                            toastr.success(res.message + ' <a href="{{ route("admin.packing.downloadBulkReworkSlip") }}?ids=' + ids.join(',') + '" style="color: #fff; text-decoration: underline; font-weight: bold; margin-left: 8px;"><i class="fas fa-download"></i> Download Slips</a>');
                        }
                        loadReworkData();
                    } else {
                        alert(res.message);
                    }
                    $btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Confirm & Assign for Rework');
                },
                error: function(xhr) {
                    let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error assigning rework';
                    alert(msg);
                    $btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Confirm & Assign for Rework');
                }
            });
        });

        $(document).on('click', '.btn-reassign-single', function() {
            let id = $(this).data('id');
            let item = allReworkItems.find(r => r.id == id);
            if (!item) return;

            $('#modalReassignId').val(item.id);
            $('#modalReassignStage').val(item.assigned_stage_id || item.responsible_stage_id || '').trigger('change');
            
            setTimeout(() => {
                $('#modalReassignUnit').val(item.assigned_unit_id || item.responsible_unit_id || '').trigger('change');
            }, 300);

            $('#modalReassignRemarks').val(item.remarks || '');
            $('#reassignModal').modal('show');
        });

        $('#formReassignUnit').submit(function(e) {
            e.preventDefault();
            let id = $('#modalReassignId').val();
            let toStageId = $('#modalReassignStage').val();
            let toUnitId = $('#modalReassignUnit').val();
            let remarks = $('#modalReassignRemarks').val();

            let $btn = $('#btnConfirmReassign');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');

            $.ajax({
                url: "{{ route('admin.packing.reassignReworkUnit') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    to_stage_id: toStageId,
                    to_unit_id: toUnitId,
                    remarks: remarks
                },
                success: function(res) {
                    if (res.status === 'success') {
                        $('#reassignModal').modal('hide');
                        toastr.success(res.message);
                        loadReworkData();
                    } else {
                        alert(res.message);
                    }
                    $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Unit Assignment');
                },
                error: function(xhr) {
                    let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error updating assignment';
                    alert(msg);
                    $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Unit Assignment');
                }
            });
        });

        $(document).on('click', '.btn-delete-rework', function() {
            let id = $(this).data('id');
            if (!confirm('Are you sure you want to delete this defect record? Pieces will be restored back to packing.')) return;

            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: "{{ route('admin.packing.deleteRework', '') }}/" + id,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        loadReworkData();
                    } else {
                        alert(res.message);
                        $btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                    }
                },
                error: function() {
                    alert('Error deleting record');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                }
            });
        });

        $('#btnBulkSlip').click(function() {
            let selectedAssignedIds = [];
            let unassignedCount = 0;
            $('.rework-row-chk:checked').each(function() {
                let id = parseInt($(this).val());
                let item = allReworkItems.find(r => r.id == id);
                if (item && item.status === 'assigned') {
                    selectedAssignedIds.push(id);
                } else {
                    unassignedCount++;
                }
            });

            if (selectedAssignedIds.length === 0) {
                alert('Slip can only be downloaded after items are assigned for rework. Please assign the selected items first.');
                return;
            }

            if (unassignedCount > 0) {
                if (!confirm(`${unassignedCount} unassigned item(s) will be excluded. Download slip for the ${selectedAssignedIds.length} assigned item(s)?`)) {
                    return;
                }
            }

            let url = "{{ route('admin.packing.downloadBulkReworkSlip') }}?ids=" + selectedAssignedIds.join(',');
            window.location.href = url;
        });

        $('#btnBulkDelete').click(function() {
            let selectedIds = getSelectedIds();
            if (selectedIds.length === 0) return;

            if (!confirm(`Are you sure you want to delete ${selectedIds.length} selected defect record(s)? Pieces will be restored back to packing.`)) return;

            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...');

            $.ajax({
                url: "{{ route('admin.packing.bulkDeleteRework') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: selectedIds
                },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        loadReworkData();
                    } else {
                        alert(res.message);
                    }
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete');
                },
                error: function() {
                    alert('Error deleting records');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete');
                }
            });
        });

    });

    function loadReworkData() {
        $('#reworkTableBody').html(`
            <tr>
                <td colspan="11" class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
                    <div class="small">Loading rework records...</div>
                </td>
            </tr>
        `);

        let params = {
            search_term: $('#filterSearch').val(),
            status: $('#filterStatus').val(),
            rack_id: $('#filterRack').val(),
            start_date: $('#filterStartDate').val(),
            end_date: $('#filterEndDate').val()
        };

        $.get("{{ route('admin.packing.reworkListData') }}", params, function(res) {
            if (res.status === 'success') {
                allReworkItems = res.data || [];
                renderTable(allReworkItems);
                updateStats(allReworkItems);
            } else {
                $('#reworkTableBody').html(`<tr><td colspan="11" class="text-center text-danger py-4 font-italic">Failed to load data.</td></tr>`);
            }
        }).fail(function() {
            $('#reworkTableBody').html(`<tr><td colspan="11" class="text-center text-danger py-4 font-italic">Error connecting to server.</td></tr>`);
        });
    }

    function renderTable(items) {
        let html = '';
        if (items.length === 0) {
            html = `<tr><td colspan="11" class="text-center py-5 text-muted font-italic"><i class="fas fa-inbox fa-2x mb-2 d-block text-secondary"></i>No defect or rework records found for the selected filters.</td></tr>`;
            $('#reworkTableBody').html(html);
            $('#selectAllRework').prop('checked', false);
            updateBulkActionBar();
            return;
        }

        items.forEach(item => {
            let slipUrl = item.slip_id ? `/admin/packing/process-new/${item.slip_id}/pack-lots` : '#';
            let createdDate = item.created_at ? new Date(item.created_at).toLocaleDateString('en-GB') : '-';
            let orderNo = item.order_main ? (item.order_main.order_no || `#${item.order_main.id}`) : '-';
            let customerName = item.order_main && item.order_main.customer ? item.order_main.customer.name : 'Direct Walk-in';
            let designNo = item.product ? (item.product.design_number || 'N/A') : '-';
            let colorName = item.color ? item.color.name : '-';
            let sizeName = item.size ? item.size.size : 'N/A';
            let storeroomName = item.rack && item.rack.storeroom ? item.rack.storeroom.name : '';
            let rackName = item.rack ? item.rack.name : 'Unassigned';

            let targetStageName = item.responsible_stage ? item.responsible_stage.name : '-';
            let targetUnitName = item.responsible_unit ? item.responsible_unit.name : '-';

            let isAssigned = item.status === 'assigned';
            let statusBadge = isAssigned 
                ? `<span class="badge-assigned"><i class="fas fa-check-circle mr-1"></i>Assigned</span>`
                : `<span class="badge-in-rack"><i class="fas fa-warehouse mr-1"></i>In Rack</span>`;

            let stageUnitDisplay = '';
            if (isAssigned) {
                let aStage = item.assigned_stage ? item.assigned_stage.name : targetStageName;
                let aUnit = item.assigned_unit ? item.assigned_unit.name : targetUnitName;
                stageUnitDisplay = `
                    <div class="font-weight-bold text-success text-truncate" style="max-width: 140px;" title="${aStage}">
                        <i class="fas fa-check-circle mr-1 text-success"></i>${aStage}
                    </div>
                    <div class="text-muted small text-truncate" style="max-width: 140px;" title="${aUnit}">
                        ${aUnit}
                    </div>
                `;
            } else {
                stageUnitDisplay = `
                    <div class="font-weight-bold text-dark text-truncate" style="max-width: 140px;" title="${targetStageName}">
                        <i class="fas fa-layer-group mr-1 text-muted"></i>${targetStageName}
                    </div>
                    <div class="text-muted small text-truncate" style="max-width: 140px;" title="${targetUnitName}">
                        ${targetUnitName}
                    </div>
                `;
            }

            let reworkSlipUrl = `/admin/packing/download-rework-slip/${item.id}`;
            let actionButtons = '';
            if (!isAssigned) {
                actionButtons = `
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-primary px-2 py-1 font-weight-bold btn-assign-single" data-id="${item.id}" title="Assign for Rework">
                            <i class="fas fa-paper-plane mr-1"></i>Assign
                        </button>
                        <button type="button" class="btn btn-outline-danger px-2 py-1 btn-delete-rework" data-id="${item.id}" title="Delete & Revert Stock">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                `;
            } else {
                actionButtons = `
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="${reworkSlipUrl}" class="btn btn-danger px-2 py-1 font-weight-bold" title="Direct Download Rework Slip">
                            <i class="fas fa-download mr-1"></i>Slip
                        </a>
                        <button type="button" class="btn btn-outline-secondary px-2 py-1 btn-reassign-single" data-id="${item.id}" title="Change Assigned Unit">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger px-2 py-1 btn-delete-rework" data-id="${item.id}" title="Delete & Revert Stock">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                `;
            }

            html += `
                <tr>
                    <td class="text-center">
                        <input type="checkbox" class="rework-row-chk" value="${item.id}" data-qty="${item.quantity}" style="cursor: pointer;">
                    </td>
                    <td>
                        <div class="font-weight-bold text-dark">${createdDate}</div>
                        <a href="${slipUrl}" class="text-primary font-weight-bold" style="font-size: 0.75rem;" target="_blank">
                            <i class="fas fa-receipt mr-1"></i>#${item.slip_id || '-'}
                        </a>
                    </td>
                    <td>
                        <div class="font-weight-bold text-dark">${orderNo}</div>
                        <div class="text-muted text-truncate" style="max-width: 125px; font-size: 0.75rem;" title="${customerName}">
                            ${customerName}
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-light border font-weight-bold text-dark px-2 py-1" style="font-family: monospace; font-size: 0.8rem;">
                            ${item.lot_no || '-'}
                        </span>
                    </td>
                    <td>
                        <div class="font-weight-bold text-dark text-truncate" style="max-width: 130px;" title="${designNo}">${designNo}</div>
                        <div class="text-muted small">${colorName}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge-size-qty">
                            ${sizeName}: ${item.quantity} Pcs
                        </span>
                    </td>
                    <td>
                        <div class="font-weight-bold text-dark text-truncate" style="max-width: 150px;" title="${storeroomName}">
                            <i class="fas fa-warehouse text-warning mr-1"></i>${storeroomName || 'Storage'}
                        </div>
                        <div class="text-muted small text-truncate" style="max-width: 150px;">
                            Rack: <strong class="text-dark">${rackName}</strong>
                        </div>
                    </td>
                    <td>
                        ${stageUnitDisplay}
                    </td>
                    <td class="text-center">
                        ${statusBadge}
                    </td>
                    <td>
                        <span class="text-muted small text-truncate d-inline-block" style="max-width: 100px;" title="${item.remarks || '-'}">
                            ${item.remarks || '-'}
                        </span>
                    </td>
                    <td class="text-center pr-2" style="white-space: nowrap;">
                        ${actionButtons}
                    </td>
                </tr>
            `;
        });

        $('#reworkTableBody').html(html);
        $('#selectAllRework').prop('checked', false);
        updateBulkActionBar();
    }

    function updateStats(items) {
        let stored = items.filter(r => r.status === 'stored').reduce((sum, r) => sum + parseInt(r.quantity || 0), 0);
        let assigned = items.filter(r => r.status === 'assigned').reduce((sum, r) => sum + parseInt(r.quantity || 0), 0);
        let lots = new Set(items.map(r => r.lot_no).filter(Boolean)).size;

        $('#statStored').text(`${stored} Pcs`);
        $('#statAssigned').text(`${assigned} Pcs`);
        $('#statLots').text(`${lots} Lots`);
    }

    function updateBulkActionBar() {
        let selected = $('.rework-row-chk:checked');
        let count = selected.length;
        if (count > 0) {
            let totalQty = 0;
            selected.each(function() {
                totalQty += parseInt($(this).data('qty')) || 0;
            });
            $('#selectedCountText').text(count);
            $('#selectedQtyText').text(totalQty);
            $('#bulkActionBar').slideDown(120);
        } else {
            $('#bulkActionBar').slideUp(120);
        }
    }

    function getSelectedIds() {
        let ids = [];
        $('.rework-row-chk:checked').each(function() {
            ids.push(parseInt($(this).val()));
        });
        return ids;
    }

    function openAssignmentModal(items) {
        if (!items || items.length === 0) return;

        let ids = items.map(i => i.id);
        let totalPcs = items.reduce((s, i) => s + (parseInt(i.quantity) || 0), 0);
        $('#modalAssignIds').val(JSON.stringify(ids));
        $('#modalItemsCount').text(`${items.length} Record(s) &bull; ${totalPcs} Pcs`);

        let summaryHtml = '<ul class="pl-3 mb-0" style="font-size: 0.82rem;">';
        items.forEach(i => {
            let rack = i.rack ? i.rack.name : 'Rack';
            summaryHtml += `<li>Lot: <strong>${i.lot_no}</strong> | Size: <strong>${i.size ? i.size.size : 'N/A'}</strong> | Qty: <strong>${i.quantity} Pcs</strong> | Loc: <em>${rack}</em></li>`;
        });
        summaryHtml += '</ul>';
        $('#modalItemsSummary').html(summaryHtml);

        let first = items[0];
        let defaultStage = first.responsible_stage_id || '';
        let defaultUnit = first.responsible_unit_id || '';

        $('#modalStage').val(defaultStage).trigger('change');
        if (defaultStage) {
            setTimeout(() => {
                if (defaultUnit) {
                    $('#modalUnit').val(defaultUnit).trigger('change');
                }
            }, 300);
        }

        $('#modalRemarks').val(first.remarks || '');
        $('#assignModal').modal('show');
    }
</script>
@endsection
