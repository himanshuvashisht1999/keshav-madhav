@extends('admin.layouts.app')

@section('content')
<style>
    /* Snapkid Enterprise ERP Design System */
    :root {
        --erp-bg: #f8fafc;
        --erp-card-bg: #ffffff;
        --erp-border: #e2e8f0;
        --erp-primary: #05421c;
        --erp-primary-hover: #075e28;
        --erp-text-main: #1e293b;
        --erp-text-muted: #64748b;
        --erp-radius: 6px;
        --erp-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }

    .content-wrapper {
        background-color: var(--erp-bg);
    }

    /* 1. Header Bar */
    .erp-header-bar {
        background: linear-gradient(135deg, #05421c 0%, #0a5c28 100%) !important;
        border-radius: 6px;
        padding: 0.65rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #ffffff !important;
        border: none !important;
        box-shadow: 0 2px 6px rgba(5,66,28,0.2) !important;
        margin-bottom: 0.75rem;
    }
    .erp-header-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #ffffff !important;
        margin: 0;
        display: flex;
        align-items: center;
    }
    .erp-header-subtitle {
        color: rgba(255,255,255,0.85);
        font-size: 0.8rem;
        margin: 0;
    }

    /* 2. Filter Bar */
    .erp-filter-bar {
        background: #ffffff;
        border: 1px solid var(--erp-border);
        border-radius: var(--erp-radius);
        padding: 0.75rem 1rem;
        box-shadow: var(--erp-shadow);
        margin-bottom: 0.75rem;
    }
    .erp-filter-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
        display: block;
        letter-spacing: 0.4px;
    }
    .erp-input, .form-control-sm {
        height: 31px !important;
        font-size: 12.5px !important;
        border: 1px solid #ced4da;
        border-radius: var(--erp-radius);
    }
    .erp-input:focus, .form-control-sm:focus {
        border-color: #05421c;
        box-shadow: 0 0 0 0.15rem rgba(5,66,28,0.15);
    }

    /* 3. Cards */
    .erp-card {
        background: var(--erp-card-bg);
        border: 1px solid var(--erp-border);
        border-radius: var(--erp-radius);
        box-shadow: var(--erp-shadow);
        margin-bottom: 0.75rem;
    }

    /* 4. Buttons */
    .btn-erp {
        font-size: 0.82rem;
        padding: 0.35rem 0.75rem;
        border-radius: var(--erp-radius);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease-in-out;
        border: 1px solid transparent;
        cursor: pointer;
        height: 31px;
    }
    .btn-erp-primary {
        background: #fcee21 !important;
        color: #05421c !important;
        border-color: #fcee21 !important;
        font-weight: 700;
    }
    .btn-erp-primary:hover {
        background: #f5e51b !important;
        color: #032b12 !important;
    }
    .btn-erp-outline {
        background: transparent;
        border-color: rgba(255, 255, 255, 0.4);
        color: #ffffff;
    }
    .btn-erp-outline:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
    }
    .btn-erp-danger {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }
    .btn-erp-danger:hover {
        background: #dc2626;
        color: #ffffff;
    }
    .btn-erp-xs {
        height: 24px !important;
        padding: 0.15rem 0.5rem;
        font-size: 0.74rem;
        border-radius: 4px;
    }

    /* 5. Action Buttons */
    .erp-action-btn {
        width: 26px;
        height: 26px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-size: 11.5px;
        border: 1px solid transparent;
        transition: all 0.15s ease;
        background: transparent;
        cursor: pointer;
    }
    .erp-btn-delete {
        color: #ef4444;
        border-color: #fee2e2;
        background-color: #fef2f2;
    }
    .erp-btn-delete:hover {
        background-color: #ef4444;
        color: #ffffff;
    }

    /* 6. Tables */
    .erp-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .erp-table thead th {
        background-color: #edf7e4 !important;
        color: #05421c !important;
        font-size: 0.74rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        padding: 0.65rem 0.75rem !important;
        border-bottom: 2px solid #c3e6cb !important;
        letter-spacing: 0.4px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .erp-table tbody td {
        vertical-align: middle;
        padding: 0.55rem 0.75rem;
        border-color: #f1f5f9;
        font-size: 0.83rem;
        color: #1e293b;
    }

    /* 7. Select2 Fixes */
    .select2-container .select2-selection--single {
        height: 31px !important;
        border-radius: var(--erp-radius) !important;
        border-color: #ced4da !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 29px !important;
        font-size: 0.8rem;
        padding-left: 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px !important;
    }

    /* 8. Specific Badges */
    .badge-in-rack {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-weight: 600;
        font-size: 0.73rem;
        padding: 0.25em 0.55em;
        border-radius: 4px;
        white-space: nowrap;
    }
    .badge-assigned {
        background-color: #edf7e4;
        color: #05421c;
        border: 1px solid #c3e6cb;
        font-weight: 600;
        font-size: 0.73rem;
        padding: 0.25em 0.55em;
        border-radius: 4px;
        white-space: nowrap;
    }
    .badge-size-qty {
        background-color: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
        font-weight: 700;
        font-size: 0.76rem;
        padding: 0.25em 0.55em;
        border-radius: 4px;
        white-space: nowrap;
    }
</style>

<div class="content-wrapper erp-page p-2">
    <!-- 1. PAGE HEADER WITH EMBEDDED INLINE STATS -->
    <div class="erp-header-bar mb-3">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-tools mr-2 text-warning"></i> Defect & Rework Management
            </h1>
            <p class="erp-header-subtitle">Items stored in rack awaiting admin rework assignment</p>
        </div>
        
        <!-- COMPACT INLINE STATS & ACTIONS -->
        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <div class="px-3 py-1 rounded d-flex align-items-center" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);">
                <i class="fas fa-warehouse mr-2 text-warning" style="font-size: 1.1rem;"></i>
                <div style="line-height: 1.1;">
                    <span class="text-uppercase d-block" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; color: rgba(255,255,255,0.75);">In Rack</span>
                    <div class="font-weight-bold text-white" id="statStored" style="font-size: 0.95rem;">{{ $totalStoredPieces ?? 0 }} Pcs</div>
                </div>
            </div>

            <div class="px-3 py-1 rounded d-flex align-items-center" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);">
                <i class="fas fa-check-circle mr-2" style="font-size: 1.1rem; color: #4ade80 !important;"></i>
                <div style="line-height: 1.1;">
                    <span class="text-uppercase d-block" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; color: rgba(255,255,255,0.75);">Assigned</span>
                    <div class="font-weight-bold text-white" id="statAssigned" style="font-size: 0.95rem;">{{ $totalAssignedPieces ?? 0 }} Pcs</div>
                </div>
            </div>

            <div class="px-3 py-1 rounded d-flex align-items-center" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);">
                <i class="fas fa-cubes mr-2 text-warning" style="font-size: 1.1rem;"></i>
                <div style="line-height: 1.1;">
                    <span class="text-uppercase d-block" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; color: rgba(255,255,255,0.75);">Active Lots</span>
                    <div class="font-weight-bold text-white" id="statLots" style="font-size: 0.95rem;">{{ $totalActiveLots ?? 0 }} Lots</div>
                </div>
            </div>

            <a href="{{ route('admin.packing.index') }}" class="btn-erp btn-erp-outline" style="color: #fff; border-color: rgba(255,255,255,0.4);">
                <i class="fas fa-box-open mr-1"></i> Packing Module
            </a>
            <button id="btnRefresh" class="btn-erp btn-erp-outline" style="color: #fff; border-color: rgba(255,255,255,0.4);" title="Refresh List">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- 2. STREAMLINED FILTER BAR -->
    <div class="erp-filter-bar mb-3">
        <div class="row align-items-end" style="row-gap: 8px;">
            <div class="col-lg-3 col-md-6">
                <label class="erp-filter-label">Search</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted small"></i></span>
                    </div>
                    <input type="text" id="filterSearch" class="form-control form-control-sm border-left-0 erp-input" placeholder="Order, Lot, Design, Customer...">
                </div>
            </div>

            <div class="col-lg-2 col-md-3">
                <label class="erp-filter-label">Status</label>
                <select id="filterStatus" class="form-control form-control-sm custom-select erp-input">
                    <option value="">All Statuses</option>
                    <option value="stored" selected>In Rack (Pending)</option>
                    <option value="assigned">Assigned for Rework</option>
                </select>
            </div>

            <div class="col-lg-3 col-md-3">
                <label class="erp-filter-label">Storeroom / Rack</label>
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

            <div class="col-lg-2 col-md-4">
                <label class="erp-filter-label">Date From</label>
                <input type="date" id="filterStartDate" class="form-control form-control-sm erp-input">
            </div>

            <div class="col-lg-2 col-md-4">
                <label class="erp-filter-label">Date To</label>
                <input type="date" id="filterEndDate" class="form-control form-control-sm erp-input">
            </div>

            <div class="col-12 col-md-auto d-flex ml-auto align-items-end mt-2 mt-md-0" style="gap: 6px;">
                <button id="btnFilterApply" class="btn-erp btn-erp-primary font-weight-bold" title="Apply Filter">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <button id="btnResetFilters" class="btn-erp btn-erp-outline" style="border-color: #ced4da; color: #475569;" title="Reset Filters">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- 3. FLOATING / STICKY BULK ACTION BAR -->
    <div id="bulkActionBar" class="erp-card mb-3 bg-white" style="border-left: 4px solid #05421c !important; display: none;">
        <div class="p-2.5 px-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
            <div class="d-flex align-items-center" style="gap: 10px;">
                <span class="badge px-2.5 py-1 font-weight-bold text-white" style="background: #05421c; border-radius: 4px; font-size: 12px;">
                    <i class="fas fa-check-double mr-1 text-warning"></i><span id="selectedCountText">0</span> Selected
                </span>
                <span class="text-dark font-weight-bold small">Total Defect Qty: <strong id="selectedQtyText" style="color: #05421c; font-size: 13px;">0</strong> Pcs</span>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <button type="button" class="btn-erp btn-erp-outline btn-sm" id="btnBulkSlip" style="border-color: #cbd5e1; color: #05421c;" title="Print / Download Slips for selected items">
                    <i class="fas fa-print mr-1 text-success"></i> Download Slips
                </button>
                <button type="button" class="btn-erp btn-erp-primary btn-sm font-weight-bold" id="btnOpenBulkAssign">
                    <i class="fas fa-paper-plane mr-1"></i> Assign Selected for Rework
                </button>
                <button type="button" class="btn-erp btn-erp-danger btn-sm" id="btnBulkDelete" title="Delete Selected">
                    <i class="fas fa-trash-alt mr-1"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <!-- 4. DATA TABLE CARD -->
    <div class="erp-card mb-3">
        <div class="table-responsive">
            <table id="reworkTable" class="table table-hover table-bordered erp-table mb-0 erp-rework-table">
                <thead>
                    <tr>
                        <th width="35" class="text-center py-2">
                            <input type="checkbox" id="selectAllRework" style="cursor: pointer;">
                        </th>
                        <th width="105" class="py-2">Date / Slip</th>
                        <th width="130" class="py-2">Order / Customer</th>
                        <th width="85" class="text-center py-2">Lot No</th>
                        <th width="130" class="py-2">Design & Color</th>
                        <th width="110" class="text-center py-2">Size & Qty</th>
                        <th width="150" class="py-2">Storeroom & Rack</th>
                        <th width="145" class="py-2">Stage & Unit</th>
                        <th width="105" class="text-center py-2">Status</th>
                        <th width="100" class="py-2">Remarks</th>
                        <th width="115" class="text-center py-2 pr-3">Action</th>
                    </tr>
                </thead>
                <tbody id="reworkTableBody">
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2" style="color: #05421c;"></i>
                            <div class="small">Loading rework records...</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 5. ASSIGNMENT MODAL -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px; overflow: hidden;">
            <div class="modal-header text-white px-4 py-2.5" style="background: #05421c;">
                <h6 class="modal-title font-weight-bold mb-0" id="assignModalTitle" style="font-size: 15px;">
                    <i class="fas fa-tools mr-2 text-warning"></i> Assign Items for Rework
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="alert mb-3 p-3" style="background: #edf7e4; border: 1px solid #c3e6cb; border-radius: 6px;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="font-weight-bold small" style="color: #05421c;">Items to Assign:</span>
                        <span class="badge px-2 py-1 font-weight-bold" id="modalItemsCount" style="background: #05421c; color: #fff;">1 Record &bull; 0 Pcs</span>
                    </div>
                    <div class="small text-dark mt-2" id="modalItemsSummary" style="max-height: 110px; overflow-y: auto; line-height: 1.4;">
                        <!-- Item summaries populated dynamically -->
                    </div>
                </div>

                <form id="formAssignRework">
                    <input type="hidden" id="modalAssignIds">
                    
                    <div class="form-group mb-3">
                        <label class="erp-filter-label">
                            Target Production Stage <span class="text-danger">*</span>
                        </label>
                        <select id="modalStage" class="form-control form-control-sm select2" required style="width: 100%;">
                            <option value="">-- Select Stage --</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Select the production stage where defect rework will be processed</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="erp-filter-label">
                            Target Unit Person <span class="text-danger">*</span>
                        </label>
                        <select id="modalUnit" class="form-control form-control-sm select2" required style="width: 100%;">
                            <option value="">-- Select Unit --</option>
                        </select>
                        <small class="text-muted d-block mt-1">Select the unit responsible for rework</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="erp-filter-label">Assignment Remarks</label>
                        <textarea id="modalRemarks" class="form-control form-control-sm" rows="2" placeholder="Optional notes for unit person..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end align-items-center mt-3" style="gap: 8px;">
                        <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal" style="border-color: #ced4da; color: #475569;">Cancel</button>
                        <button type="submit" id="btnConfirmAssign" class="btn-erp btn-erp-primary font-weight-bold px-3">
                            <i class="fas fa-check mr-1"></i> Confirm & Assign for Rework
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 6. CHANGE ASSIGNED UNIT MODAL -->
<div class="modal fade" id="reassignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px; overflow: hidden;">
            <div class="modal-header text-white px-4 py-2.5" style="background: #05421c;">
                <h6 class="modal-title font-weight-bold mb-0" style="font-size: 15px;">
                    <i class="fas fa-exchange-alt mr-2 text-warning"></i> Change Assigned Unit
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <form id="formReassignUnit">
                    <input type="hidden" id="modalReassignId">
                    
                    <div class="form-group mb-3">
                        <label class="erp-filter-label">Target Stage <span class="text-danger">*</span></label>
                        <select id="modalReassignStage" class="form-control form-control-sm select2" required style="width: 100%;">
                            <option value="">-- Select Stage --</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="erp-filter-label">Target Unit <span class="text-danger">*</span></label>
                        <select id="modalReassignUnit" class="form-control form-control-sm select2" required style="width: 100%;">
                            <option value="">-- Select Unit --</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="erp-filter-label">Remarks</label>
                        <textarea id="modalReassignRemarks" class="form-control form-control-sm" rows="2"></textarea>
                    </div>

                    <div class="d-flex justify-content-end align-items-center mt-3" style="gap: 8px;">
                        <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal" style="border-color: #ced4da; color: #475569;">Cancel</button>
                        <button type="submit" id="btnConfirmReassign" class="btn-erp btn-erp-primary font-weight-bold px-3">
                            <i class="fas fa-save mr-1"></i> Update Unit Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
                    <i class="fas fa-spinner fa-spin fa-2x mb-2" style="color: #05421c;"></i>
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
            html = `<tr><td colspan="11" class="text-center py-5 text-muted font-italic"><i class="fas fa-inbox fa-2x mb-2 d-block" style="color: #cbd5e1;"></i>No defect or rework records found for the selected filters.</td></tr>`;
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
                    <div class="font-weight-bold text-truncate" style="max-width: 140px; color: #05421c;" title="${aStage}">
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
                    <div class="d-flex align-items-center justify-content-center" style="gap: 5px;">
                        <button type="button" class="btn-erp btn-erp-primary btn-xs btn-assign-single font-weight-bold" data-id="${item.id}" title="Assign for Rework">
                            <i class="fas fa-paper-plane mr-1"></i>Assign
                        </button>
                        <button type="button" class="erp-action-btn erp-btn-delete btn-delete-rework" data-id="${item.id}" title="Delete & Revert Stock">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                `;
            } else {
                actionButtons = `
                    <div class="d-flex align-items-center justify-content-center" style="gap: 5px;">
                        <a href="${reworkSlipUrl}" class="btn-erp btn-xs font-weight-bold" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca;" title="Download Rework Slip">
                            <i class="fas fa-download mr-1"></i>Slip
                        </a>
                        <button type="button" class="btn-erp btn-erp-outline btn-xs btn-reassign-single" style="border-color: #cbd5e1; color: #475569;" data-id="${item.id}" title="Change Assigned Unit">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                        <button type="button" class="erp-action-btn erp-btn-delete btn-delete-rework" data-id="${item.id}" title="Delete & Revert Stock">
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
                        <a href="${slipUrl}" class="font-weight-bold" style="color: #05421c; font-size: 0.76rem;" target="_blank">
                            <i class="fas fa-receipt mr-1 text-success"></i>#${item.slip_id || '-'}
                        </a>
                    </td>
                    <td>
                        <div class="font-weight-bold text-dark">${orderNo}</div>
                        <div class="text-muted text-truncate" style="max-width: 125px; font-size: 0.75rem;" title="${customerName}">
                            ${customerName}
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge px-2 py-1 font-weight-bold" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-family: monospace; font-size: 11.5px; border-radius: 4px;">
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
