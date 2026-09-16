@extends('admin.layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        :root {
            --erp-primary: #4f46e5;
            --erp-primary-hover: #4338ca;
            --erp-primary-light: #eef2ff;
            --erp-success: #059669;
            --erp-success-light: #ecfdf5;
            --erp-danger: #dc2626;
            --erp-danger-light: #fef2f2;
            --erp-warning: #d97706;
            --erp-border: #cbd5e1;
            --erp-bg-header: #f1f5f9;
            --erp-text: #0f172a;
            --erp-muted: #64748b;
        }

        .content-wrapper {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            padding-bottom: 90px;
        }

        /* STICKY ERP TOP HUD BAR */
        .sticky-erp-hud {
            position: sticky;
            top: 0;
            z-index: 1020;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border-bottom: 2px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 8px 16px;
            margin: -0.5rem -0.5rem 12px -0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: nowrap;
        }

        .hud-stat-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 12px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }

        .hud-source-box {
            border-left: 4px solid var(--erp-primary);
            background: #fafbff;
            min-width: 220px;
        }

        .hud-target-box {
            border-left: 4px solid var(--erp-success);
            background: #fbfdfb;
            min-width: 320px;
        }

        .hud-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }

        .hud-numbers {
            font-size: 1.1rem;
            font-weight: 800;
            line-height: 1.2;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .hud-pills-row {
            display: flex;
            flex-wrap: wrap;
            gap: 3px;
            max-height: 42px;
            overflow-y: auto;
            max-width: 320px;
        }

        /* ERP CARDS & TABLES */
        .erp-card {
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            margin-bottom: 12px;
            overflow: hidden;
        }

        .erp-card-header {
            padding: 7px 12px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .erp-card-title {
            font-size: 12.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .erp-table {
            width: 100%;
            margin-bottom: 0;
            font-size: 11.5px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .erp-table thead th {
            background: #f1f5f9;
            color: #334155;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 6px;
            border-bottom: 2px solid #cbd5e1;
            border-top: none;
            vertical-align: middle;
            white-space: nowrap;
        }

        .erp-table tbody td {
            padding: 4px 5px;
            vertical-align: middle;
            border-top: 1px solid #edf2f7;
            border-bottom: none;
        }

        .erp-table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* COMPACT CONTROLS IN ERP TABLE */
        .erp-input {
            height: 29px !important;
            padding: 2px 6px !important;
            font-size: 11.5px !important;
            font-weight: 600;
            border-radius: 5px !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff;
            line-height: 1.2;
        }

        .erp-input:focus {
            border-color: var(--erp-primary) !important;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15) !important;
            outline: none;
        }

        .erp-input[readonly] {
            background-color: #f1f5f9 !important;
            color: #475569;
        }

        /* SELECT2 IN ERP TABLE */
        .erp-table .select2-container--bootstrap4 .select2-selection {
            height: 29px !important;
            min-height: 29px !important;
            border-radius: 5px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 0 !important;
            display: flex;
            align-items: center;
        }

        .erp-table .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 27px !important;
            font-size: 11.5px !important;
            padding-left: 6px !important;
            padding-right: 18px !important;
            color: var(--erp-text);
        }

        .erp-table .select2-container--bootstrap4 .select2-selection__arrow {
            height: 27px !important;
            right: 3px !important;
            width: 14px !important;
        }

        .select2-dropdown {
            border-radius: 8px !important;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12) !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 12px !important;
            z-index: 10050 !important;
        }

        /* PILLS & BADGES */
        .erp-pill-gen {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.3;
            white-space: nowrap;
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .erp-badge-info {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 5px;
            border-radius: 4px;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ACTION BUTTONS */
        .btn-erp-add {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 6px;
            line-height: 1.4;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
        }

        .btn-erp-icon {
            width: 26px;
            height: 26px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            font-size: 11px;
            border: none;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .btn-erp-del {
            background: #fee2e2;
            color: #ef4444;
        }
        .btn-erp-del:hover {
            background: #ef4444;
            color: #fff;
        }

        .btn-erp-copy {
            background: #ecfdf5;
            color: #059669;
        }
        .btn-erp-copy:hover {
            background: #059669;
            color: #fff;
        }

        /* SUBMIT BUTTON */
        .btn-hud-confirm {
            background: linear-gradient(135deg, var(--erp-primary) 0%, var(--erp-primary-hover) 100%);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 12.5px;
            border-radius: 7px;
            padding: 7px 18px;
            border: none;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
            white-space: nowrap;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-hud-confirm:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.4);
        }

        .btn-hud-confirm:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* STICKY BOTTOM ACTIONS BAR */
        .sticky-bottom-bar {
            position: fixed;
            bottom: 0;
            left: 250px;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 8px 24px;
            border-top: 1px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.04);
        }

        @media (max-width: 991.98px) {
            .sticky-bottom-bar {
                left: 0;
            }
            .sticky-erp-hud {
                flex-wrap: wrap;
            }
        }
    </style>

    <div class="content-wrapper">
        <section class="content pt-2">
            <div class="container-fluid px-2">
                <form action="{{ route('admin.inventory.store') }}" method="POST" id="addStockForm">
                    @csrf

                    <!-- ========================================================= -->
                    <!-- 1. STICKY ERP HUD HEADER BAR (ALWAYS VISIBLE WITH ZERO SCROLL) -->
                    <!-- ========================================================= -->
                    <div class="sticky-erp-hud">
                        <!-- Source Type Selector -->
                        <div class="hud-stat-box hud-source-box">
                            <div>
                                <span class="hud-label text-primary"><i class="fas fa-layer-group mr-1"></i> SOURCE TYPE</span>
                                <select name="source_type" id="sourceType" class="form-control erp-input font-weight-bold text-primary" style="height: 28px !important; min-width: 170px;">
                                    <option value="production">Self Production</option>
                                    <option value="sample">Sample Production</option>
                                </select>
                            </div>
                        </div>

                        <!-- Live Stock Totals Box -->
                        <div class="hud-stat-box hud-target-box">
                            <div>
                                <span class="hud-label text-success"><i class="fas fa-cubes mr-1"></i> TOTAL STOCK TO UPLOAD</span>
                                <div class="hud-numbers">
                                    <span id="hudTotalBoxes" class="text-dark">0</span> <small class="text-muted" style="font-size:10px;">Bx</small>
                                    <span class="text-muted mx-1" style="font-size:12px;">|</span>
                                    <span id="hudTotalPieces" class="text-success font-weight-bold">0</span> <small class="text-muted" style="font-size:10px;">Pcs</small>
                                </div>
                            </div>
                            <div class="border-left pl-2 ml-auto">
                                <span class="text-muted d-block" style="font-size:9.5px; font-weight:700;">SIZES BREAKDOWN:</span>
                                <div id="hudSizePills" class="hud-pills-row">
                                    <span class="text-muted small font-italic" style="font-size:10px;">None</span>
                                </div>
                            </div>
                        </div>

                        <!-- Instant Action Button in HUD -->
                        <div>
                            <button type="submit" class="btn-hud-confirm" id="btnHudSubmit" disabled>
                                <i class="fas fa-check-double mr-1"></i> Confirm & Upload Stock
                            </button>
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- 2. PRODUCTION STOCK TABLE (COMPACT ERP TABLE) -->
                    <!-- ========================================================= -->
                    <div class="erp-card">
                        <div class="erp-card-header">
                            <div class="erp-card-title text-success">
                                <i class="fas fa-barcode"></i> Self & Sample Production Stock Entry
                                <span class="badge badge-light border text-muted ml-2 font-weight-normal" style="font-size:11px;">Add goods directly to warehouse inventory</span>
                            </div>
                            <button type="button" class="btn btn-outline-success btn-erp-add" id="btnAddItem">
                                <i class="fas fa-plus"></i> Add Stock Row
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="erp-table table table-sm table-hover" id="productionTable">
                                <thead>
                                    <tr>
                                        <th style="width: 3%; text-align: center;">#</th>
                                        <th style="width: 20%;">
                                            Design No *
                                            <a href="{{ route('admin.master.production-goods.create') }}" target="_blank" class="text-primary ml-1" title="Create New Design" style="font-size:10px;"><i class="fas fa-plus"></i></a>
                                            <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="design" title="Refresh Designs" style="font-size:10px;"><i class="fas fa-sync-alt"></i></a>
                                        </th>
                                        <th style="width: 11%;">Pattern & Fit</th>
                                        <th style="width: 11%;">
                                            Warehouse *
                                            <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="warehouse" title="Refresh Warehouses" style="font-size:10px;"><i class="fas fa-sync-alt"></i></a>
                                        </th>
                                        <th style="width: 11%;">
                                            Rack *
                                            <a href="javascript:void(0)" class="text-primary ml-1 btn-add-new-rack" title="Quick Add Rack" style="font-size:10px;"><i class="fas fa-plus"></i></a>
                                            <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="rack" title="Refresh Racks" style="font-size:10px;"><i class="fas fa-sync-alt"></i></a>
                                        </th>
                                        <th style="width: 12%;">
                                            Size Set *
                                            <a href="{{ route('admin.master.size-measurement.index') }}" target="_blank" class="text-primary ml-1" title="Size Master" style="font-size:10px;"><i class="fas fa-plus"></i></a>
                                        </th>
                                        <th style="width: 10%;">
                                            Color *
                                            <a href="{{ route('admin.master.colors.index') }}" target="_blank" class="text-primary ml-1" title="Color Master" style="font-size:10px;"><i class="fas fa-plus"></i></a>
                                        </th>
                                        <th style="width: 5%; text-align: center;">Boxes *</th>
                                        <th style="width: 4%; text-align: center;">Pcs/Bx</th>
                                        <th style="width: 4%; text-align: right;">Total Pcs</th>
                                        <th style="width: 5%; text-align: right;">MRP</th>
                                        <th style="width: 4%; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="productionTableBody">
                                    <!-- Dynamic rows inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- 3. COLLAPSIBLE DETAILED SIZE BREAKDOWN ACCORDION -->
                    <!-- ========================================================= -->
                    <div class="erp-card mb-4">
                        <div class="erp-card-header" style="cursor: pointer;" data-toggle="collapse" data-target="#auditCollapse">
                            <div class="erp-card-title text-dark font-weight-bold" style="font-size:11.5px;">
                                <i class="fas fa-list-check text-info"></i> Detailed Per-Size Breakdown Summary
                                <span class="text-muted font-weight-normal ml-2" style="font-size:10.5px;">(Click to view full breakdown by individual size)</span>
                            </div>
                            <span class="text-muted"><i class="fas fa-chevron-down"></i></span>
                        </div>
                        <div class="collapse" id="auditCollapse">
                            <div class="p-3 bg-white border-top">
                                <ul class="list-unstyled mb-0 d-flex flex-wrap gap-3" id="detailedSizesList" style="font-size:12px;">
                                    <li class="text-muted font-italic">No stock rows added yet</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- 4. STICKY BOTTOM ACTIONS BAR -->
                    <!-- ========================================================= -->
                    <div class="sticky-bottom-bar">
                        <div>
                            <a href="{{ route('admin.inventory.index') }}" class="text-secondary font-weight-bold mr-3" style="font-size:12px;">
                                <i class="fas fa-arrow-left mr-1"></i> Cancel and Exit
                            </a>
                            <span class="text-muted small">All stock items are verified before uploading to warehouse inventory.</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn-hud-confirm" id="btnSubmitStock" disabled>
                                <i class="fas fa-check-double mr-1"></i> Confirm and Upload Stock
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <!-- Quick Add Rack Modal -->
    <div class="modal fade" id="quickRackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 10px;">
                <div class="modal-header py-2">
                    <h6 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-1 text-primary"></i> Quick Add Rack</h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body py-3">
                    <form id="quickRackForm">
                        @csrf
                        <input type="hidden" id="quickRackWarehouseId" name="storeroom_id">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Warehouse</label>
                            <input type="text" class="form-control form-control-sm" id="quickRackWarehouseName" readonly>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Rack Name / Number *</label>
                            <input type="text" class="form-control form-control-sm" id="quickRackName" name="name" required placeholder="e.g. R-101">
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Capacity (Boxes)</label>
                            <input type="number" class="form-control form-control-sm" id="quickRackCapacity" name="capacity" placeholder="Optional capacity">
                        </div>
                    </form>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold" id="btnSaveQuickRack">Save Rack</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const productsMaster = @json($products);
        const storeroomsMaster = @json($storerooms);
        const sizeSetsMaster = @json($size_sets);
        const colorsMaster = @json($colors);
        const fittingsMaster = @json($fittings);
        const patternsMaster = @json($patterns);

        // Pre-build map of size set details: { id => { sizes: ['22','24',...], pcs: 1, name: '...' } }
        const sizeSetsMap = {};
        sizeSetsMaster.forEach(function (s) {
            if (s.size_group) {
                let sizes = s.size_group.split(',').map(item => item.trim()).filter(Boolean);
                let pcs = (sizes.length > 0 && s.no_of_pcs) ? (s.no_of_pcs / sizes.length) : 1;
                sizeSetsMap[s.id] = { sizes: sizes, pcs: pcs, name: s.name, total_pcs: s.no_of_pcs };
            }
        });

        let itemCounter = 0;

        function initSelect2(container) {
            container.find('.select2').each(function () {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
                $(this).select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownParent: $('body')
                });
            });
        }

        // Pre-rendered master options
        let designOptionsHtml = '<option value="">Select Design</option>';
        productsMaster.forEach(p => {
            let sName = p.series ? p.series.name : '';
            designOptionsHtml += `<option value="${p.id}" data-name="${p.name_of_garment}">${p.design_number} (${sName} ${p.name_of_garment})</option>`;
        });

        let warehouseOptionsHtml = '<option value="">Warehouse</option>';
        storeroomsMaster.forEach(w => {
            warehouseOptionsHtml += `<option value="${w.id}">${w.name}</option>`;
        });

        // =========================================================================
        // ADD PRODUCTION STOCK ROW
        // =========================================================================
        function addItem(values = null) {
            itemCounter++;
            let idx = itemCounter;

            let rowHtml = `
                <tr class="stock-table-row" data-idx="${idx}">
                    <td class="text-center font-weight-bold text-muted">
                        <span class="row-badge badge badge-success">${idx}</span>
                        <input type="hidden" name="products[${idx}][pattern_id]" class="pattern-val">
                        <input type="hidden" name="products[${idx}][fitting_id]" class="fitting-val">
                    </td>
                    <td>
                        <select name="products[${idx}][product_id]" class="form-control select2 design-select" required>
                            ${designOptionsHtml}
                        </select>
                    </td>
                    <td>
                        <div class="pattern-fit-badges text-truncate">
                            <span class="text-muted small font-italic">-</span>
                        </div>
                    </td>
                    <td>
                        <select name="products[${idx}][warehouse_id]" class="form-control select2 warehouse-select" required>
                            ${warehouseOptionsHtml}
                        </select>
                    </td>
                    <td>
                        <select name="products[${idx}][rack_id]" class="form-control select2 rack-select" required>
                            <option value="">Select Rack</option>
                        </select>
                    </td>
                    <td>
                        <select name="products[${idx}][size_set_id]" class="form-control select2 size-set-select" required>
                            <option value="">Select Size Set</option>
                        </select>
                    </td>
                    <td>
                        <select name="products[${idx}][color_id]" class="form-control select2 color-select" required>
                            <option value="">Select Color</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <input type="number" name="products[${idx}][total_boxes]"
                            class="form-control erp-input boxes-input font-weight-bold text-center"
                            style="min-width: 65px;" min="1" placeholder="Qty" required>
                    </td>
                    <td class="text-center">
                        <input type="number" name="products[${idx}][pieces_per_box]"
                            class="form-control erp-input pcs-input text-center"
                            style="min-width: 55px;" readonly required>
                    </td>
                    <td class="text-right font-weight-bold text-success">
                        <span class="total-pcs-display">-</span>
                    </td>
                    <td class="text-right">
                        <input type="number" name="products[${idx}][mrp]"
                            class="form-control erp-input mrp-input text-right"
                            style="min-width: 70px;" step="0.01" min="0" readonly required>
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <button type="button" class="btn-erp-icon btn-erp-copy btn-duplicate mr-1" title="Duplicate row">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button type="button" class="btn-erp-icon btn-erp-del btn-remove" title="Remove row">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;

            $('#productionTableBody').append(rowHtml);
            let newRow = $('#productionTableBody').find(`.stock-table-row[data-idx="${idx}"]`);
            initSelect2(newRow);

            if (values) {
                populateRow(newRow, values);
            } else {
                let wSelect = newRow.find('.warehouse-select');
                if (wSelect.find('option').length > 1) {
                    wSelect.val(wSelect.find('option:eq(1)').val()).trigger('change');
                }
            }

            updateRowCounters();
            recalculateAll();
        }

        function updateRowCounters() {
            let rows = $('.stock-table-row');
            rows.each(function (i) {
                $(this).find('.row-badge').text(i + 1);
            });
            if (rows.length <= 1) {
                $('.btn-remove').hide();
            } else {
                $('.btn-remove').show();
            }
        }

        function populateRow(row, values) {
            if (!values.product_id) return;
            row.find('.design-select').val(values.product_id).trigger('change');

            if (values.pattern_id) {
                row.find('.pattern-val').val(values.pattern_id);
            }
            if (values.fitting_id) {
                row.find('.fitting-val').val(values.fitting_id);
            }
            if (values.pattern_name || values.fitting_name) {
                row.find('.pattern-fit-badges').html(`
                    <span class="erp-badge-info font-weight-bold text-dark" title="Pattern: ${values.pattern_name || ''}">${values.pattern_name || ''}</span>
                    <span class="erp-badge-info text-muted" title="Fitting: ${values.fitting_name || ''}">${values.fitting_name || ''}</span>
                `);
            }

            if (values.variants) {
                row.data('variants', values.variants);
                let sizeSelect = row.find('.size-set-select');
                sizeSelect.empty().append('<option value="">Select Size Set</option>');
                let uniqueSizeSets = [];
                values.variants.forEach(function (v) {
                    if (!uniqueSizeSets.includes(v.size_set_id)) {
                        sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                        uniqueSizeSets.push(v.size_set_id);
                    }
                });
                sizeSelect.val(values.size_set_id).trigger('change');

                let colorSelect = row.find('.color-select');
                colorSelect.empty().append('<option value="">Select Color</option>');
                let variant = values.variants.find(v => v.size_set_id == values.size_set_id);
                if (variant) {
                    variant.colors.forEach(function (c) {
                        colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                    });
                }
                colorSelect.val(values.color_id).trigger('change');
            }

            if (values.warehouse_id) {
                row.find('.warehouse-select').val(values.warehouse_id).trigger('change');
                let rackSelect = row.find('.rack-select');
                rackSelect.empty().append('<option value="">Select Rack</option>');
                if (values.racks) {
                    values.racks.forEach(function (r) {
                        rackSelect.append(`<option value="${r.id}" ${r.id == values.rack_id ? 'selected' : ''}>${r.name}</option>`);
                    });
                    rackSelect.trigger('change');
                }
            }

            row.find('.boxes-input').val(values.total_boxes || '');
            row.find('.pcs-input').val(values.pieces_per_box || '');
            let total = (parseInt(values.total_boxes) || 0) * (parseInt(values.pieces_per_box) || 0);
            row.find('.total-pcs-display').text(total > 0 ? total : '-');
            row.find('.mrp-input').val(values.mrp || '');
        }

        // =========================================================================
        // RECALCULATE TOTALS & SIZE BREAKDOWN
        // =========================================================================
        function recalculateAll() {
            let totalBoxes = 0;
            let totalPieces = 0;
            let sizeTally = {};

            $('.stock-table-row').each(function () {
                let sizeSetId = $(this).find('.size-set-select').val();
                let boxes = parseInt($(this).find('.boxes-input').val()) || 0;
                let pcsPerBox = parseInt($(this).find('.pcs-input').val()) || 0;

                if (boxes > 0 && pcsPerBox > 0) {
                    totalBoxes += boxes;
                    totalPieces += (boxes * pcsPerBox);

                    if (sizeSetId && sizeSetsMap[sizeSetId]) {
                        let sInfo = sizeSetsMap[sizeSetId];
                        sInfo.sizes.forEach(size => {
                            sizeTally[size] = (sizeTally[size] || 0) + (boxes * sInfo.pcs);
                        });
                    }
                }
            });

            $('#hudTotalBoxes').text(totalBoxes);
            $('#hudTotalPieces').text(totalPieces);

            // Render Size Pills in HUD
            let pillsHtml = '';
            let detailedListHtml = '';
            let hasSizes = Object.keys(sizeTally).length > 0;

            if (hasSizes) {
                for (let size in sizeTally) {
                    pillsHtml += `<span class="erp-pill-gen">${size}: ${sizeTally[size]}</span>`;
                    detailedListHtml += `<li class="mr-4"><strong>Size ${size}:</strong> ${sizeTally[size]} pcs</li>`;
                }
            } else {
                pillsHtml = '<span class="text-muted small font-italic" style="font-size:10px;">None</span>';
                detailedListHtml = '<li class="text-muted font-italic">No stock rows added yet</li>';
            }

            $('#hudSizePills').html(pillsHtml);
            $('#detailedSizesList').html(detailedListHtml);

            let canSubmit = totalBoxes > 0 && totalPieces > 0;
            $('#btnHudSubmit').prop('disabled', !canSubmit);
            $('#btnSubmitStock').prop('disabled', !canSubmit);
        }

        // =========================================================================
        // DOM EVENTS
        // =========================================================================
        $(document).on('click', '#btnAddItem', function () {
            addItem();
        });

        $(document).on('click', '.btn-remove', function () {
            $(this).closest('.stock-table-row').remove();
            updateRowCounters();
            recalculateAll();
        });

        $(document).on('click', '.btn-duplicate', function () {
            let row = $(this).closest('.stock-table-row');
            let values = {
                product_id: row.find('.design-select').val(),
                pattern_id: row.find('.pattern-val').val(),
                fitting_id: row.find('.fitting-val').val(),
                pattern_name: row.find('.pattern-fit-badges .font-weight-bold').text(),
                fitting_name: row.find('.pattern-fit-badges .text-muted').text(),
                size_set_id: row.find('.size-set-select').val(),
                color_id: row.find('.color-select').val(),
                warehouse_id: row.find('.warehouse-select').val(),
                rack_id: row.find('.rack-select').val(),
                total_boxes: row.find('.boxes-input').val(),
                pieces_per_box: row.find('.pcs-input').val(),
                mrp: row.find('.mrp-input').val(),
                variants: row.data('variants')
            };

            let racks = [];
            row.find('.rack-select option').each(function () {
                if ($(this).val()) racks.push({ id: $(this).val(), name: $(this).text() });
            });
            values.racks = racks;

            addItem(values);
        });

        $(document).on('change', '.warehouse-select', function () {
            let warehouseId = $(this).val();
            let row = $(this).closest('.stock-table-row');
            let rackSelect = row.find('.rack-select');
            rackSelect.empty().append('<option value="">Select Rack</option>');

            if (warehouseId) {
                $.get("{{ url('admin/inventory/warehouse-stock/racks') }}/" + warehouseId, function (data) {
                    data.forEach(function (rack) {
                        rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                    });
                    rackSelect.trigger('change');
                    if (data.length > 0) {
                        rackSelect.val(data[0].id).trigger('change');
                    }
                });
            }
        });

        $(document).on('change', '.design-select', function () {
            let productId = $(this).val();
            let row = $(this).closest('.stock-table-row');

            let sizeSelect = row.find('.size-set-select');
            let colorSelect = row.find('.color-select');
            let patternFitContainer = row.find('.pattern-fit-badges');

            sizeSelect.empty().append('<option value="">Select Size Set</option>').trigger('change');
            colorSelect.empty().append('<option value="">Select Color</option>').trigger('change');
            row.find('.pcs-input').val('');
            row.find('.mrp-input').val('');
            row.find('.total-pcs-display').text('-');
            patternFitContainer.html('<span class="text-muted small font-italic">-</span>');

            if (productId) {
                $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function (data) {
                    let pName = data.pattern_name || '';
                    let fName = data.fitting_name || '';
                    row.find('.pattern-val').val(data.pattern_id || '');
                    row.find('.fitting-val').val(data.fitting_id || '');

                    patternFitContainer.html(`
                        <span class="erp-badge-info font-weight-bold text-dark" title="Pattern: ${pName}">${pName}</span>
                        <span class="erp-badge-info text-muted" title="Fitting: ${fName}">${fName}</span>
                    `);

                    row.data('variants', data.variants);
                    let uniqueSizeSets = [];
                    data.variants.forEach(function (v) {
                        if (!uniqueSizeSets.includes(v.size_set_id)) {
                            sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                            uniqueSizeSets.push(v.size_set_id);
                        }
                    });
                    sizeSelect.trigger('change');
                    if (uniqueSizeSets.length === 1) {
                        sizeSelect.val(uniqueSizeSets[0]).trigger('change');
                    }
                });
            }
        });

        $(document).on('change', '.size-set-select', function () {
            let row = $(this).closest('.stock-table-row');
            let sizeSetId = $(this).val();
            let colorSelect = row.find('.color-select');
            colorSelect.empty().append('<option value="">Select Color</option>');

            let variants = row.data('variants') || [];
            let variant = variants.find(v => v.size_set_id == sizeSetId);
            if (variant) {
                variant.colors.forEach(function (c) {
                    colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                });
                row.find('.mrp-input').val(variant.mrp);
            }
            colorSelect.trigger('change');
            if (variant && variant.colors.length === 1) {
                colorSelect.val(variant.colors[0].id).trigger('change');
            }

            if (sizeSetId) {
                $.get("{{ url('admin/inventory/get-size-set-info') }}/" + sizeSetId, function (data) {
                    if (data && data.no_of_pcs) {
                        row.find('.pcs-input').val(data.no_of_pcs);
                        let boxes = parseInt(row.find('.boxes-input').val()) || 0;
                        let total = boxes * data.no_of_pcs;
                        row.find('.total-pcs-display').text(total > 0 ? total : '-');
                        recalculateAll();
                    }
                });
            } else {
                row.find('.pcs-input').val('');
                row.find('.total-pcs-display').text('-');
                recalculateAll();
            }
        });

        $(document).on('input change', '.boxes-input', function () {
            let row = $(this).closest('.stock-table-row');
            let boxes = parseInt($(this).val()) || 0;
            let pcs = parseInt(row.find('.pcs-input').val()) || 0;
            let total = boxes * pcs;
            row.find('.total-pcs-display').text(total > 0 ? total : '-');
            recalculateAll();
        });

        // Master Refresh Buttons
        $(document).on('click', '.btn-refresh-master', function (e) {
            e.preventDefault();
            let btn = $(this);
            let type = btn.data('type');
            let row = btn.closest('.stock-table-row');
            let icon = btn.find('i');
            icon.addClass('fa-spin');

            if (type === 'design') {
                $.getJSON("{{ route('admin.inventory.master_data') }}", function (data) {
                    icon.removeClass('fa-spin');
                    if (data && data.products) {
                        let html = '<option value="">Select Design</option>';
                        data.products.forEach(function (p) {
                            let sName = p.series ? p.series.name : '';
                            html += `<option value="${p.id}" data-name="${p.name_of_garment}">${p.design_number} (${sName} ${p.name_of_garment})</option>`;
                        });
                        designOptionsHtml = html;
                        $('.design-select').each(function() {
                            let cur = $(this).val();
                            $(this).html(html).val(cur).trigger('change.select2');
                        });
                        toastr.success('Designs refreshed');
                    }
                }).fail(() => icon.removeClass('fa-spin'));
            } else if (type === 'warehouse') {
                $.getJSON("{{ route('admin.inventory.master_data') }}", function (data) {
                    icon.removeClass('fa-spin');
                    if (data && data.storerooms) {
                        let html = '<option value="">Warehouse</option>';
                        data.storerooms.forEach(function (s) {
                            html += `<option value="${s.id}">${s.name}</option>`;
                        });
                        warehouseOptionsHtml = html;
                        $('.warehouse-select').each(function() {
                            let cur = $(this).val();
                            $(this).html(html).val(cur).trigger('change.select2');
                        });
                        toastr.success('Warehouses refreshed');
                    }
                }).fail(() => icon.removeClass('fa-spin'));
            } else if (type === 'rack') {
                let wId = row.find('.warehouse-select').val();
                if (wId) {
                    $.get("{{ url('admin/inventory/warehouse-stock/racks') }}/" + wId, function (data) {
                        icon.removeClass('fa-spin');
                        let select = row.find('.rack-select');
                        let currentVal = select.val();
                        let html = '<option value="">Select Rack</option>';
                        data.forEach(function (r) {
                            html += `<option value="${r.id}">${r.name}</option>`;
                        });
                        select.html(html).val(currentVal).trigger('change');
                        toastr.success('Racks refreshed');
                    }).fail(() => icon.removeClass('fa-spin'));
                } else {
                    icon.removeClass('fa-spin');
                }
            } else {
                icon.removeClass('fa-spin');
            }
        });

        // Quick Add Rack Modal
        let currentTargetRackSelect = null;
        $(document).on('click', '.btn-add-new-rack', function () {
            let row = $(this).closest('.stock-table-row');
            let warehouseSelect = row.find('.warehouse-select');
            let warehouseId = warehouseSelect.val();
            let warehouseName = warehouseSelect.find('option:selected').text();

            if (!warehouseId) {
                toastr.warning('Please select a Warehouse first.');
                return;
            }

            currentTargetRackSelect = row.find('.rack-select');
            $('#quickRackWarehouseId').val(warehouseId);
            $('#quickRackWarehouseName').val(warehouseName);
            $('#quickRackName').val('');
            $('#quickRackCapacity').val('');
            $('#quickRackModal').modal('show');
        });

        $('#btnSaveQuickRack').on('click', function () {
            let form = $('#quickRackForm');
            let name = $('#quickRackName').val();
            if (!name) {
                toastr.warning('Please enter a rack name.');
                return;
            }

            let btn = $(this);
            btn.prop('disabled', true).text('Saving...');

            $.post("{{ route('admin.master.storeroom.rack.store') }}", form.serialize(), function (response) {
                btn.prop('disabled', false).text('Save Rack');
                $('#quickRackModal').modal('hide');
                toastr.success('Rack created successfully.');

                if (currentTargetRackSelect) {
                    let newOption = new Option(response.name || name, response.id || response.rack?.id, true, true);
                    currentTargetRackSelect.append(newOption).trigger('change');
                }
            }).fail(function (xhr) {
                btn.prop('disabled', false).text('Save Rack');
                toastr.error(xhr.responseJSON?.message || 'Error creating rack.');
            });
        });

        // =========================================================================
        // FORM SUBMISSION & BULK BARCODE PRINTING
        // =========================================================================
        $('#addStockForm').on('submit', function (e) {
            e.preventDefault();

            let totalBoxes = parseInt($('#hudTotalBoxes').text()) || 0;
            let totalPieces = parseInt($('#hudTotalPieces').text()) || 0;

            if (totalBoxes <= 0 || totalPieces <= 0) {
                toastr.error('Please enter at least one valid stock row.');
                return;
            }

            let form = $(this);
            let btnHud = $('#btnHudSubmit');
            let btnBottom = $('#btnSubmitStock');
            let origHudText = btnHud.html();
            let origBottomText = btnBottom.html();

            btnHud.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Uploading...');
            btnBottom.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Uploading...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (response) {
                    if (response.success) {
                        toastr.success(response.message);

                        let pdfForm = $('<form>', {
                            action: "{{ route('admin.inventory.barcode-generator.generate-bulk-tspl') }}",
                            method: 'POST',
                            target: '_blank'
                        }).append($('<input>', {
                            type: 'hidden',
                            name: '_token',
                            value: "{{ csrf_token() }}"
                        }));

                        if (response.print_data) {
                            response.print_data.forEach(data => {
                                pdfForm.append($('<input>', {
                                    type: 'hidden',
                                    name: 'print_data[' + data.id + ']',
                                    value: data.qty
                                }));
                            });
                        } else if (response.ids) {
                            response.ids.forEach(id => {
                                pdfForm.append($('<input>', {
                                    type: 'hidden',
                                    name: 'ids[]',
                                    value: id
                                }));
                            });
                        }

                        $('body').append(pdfForm);
                        pdfForm.submit();
                        pdfForm.remove();

                        setTimeout(() => {
                            window.location.href = "{{ route('admin.inventory.create') }}";
                        }, 1200);
                    }
                },
                error: function (xhr) {
                    btnHud.prop('disabled', false).html(origHudText);
                    btnBottom.prop('disabled', false).html(origBottomText);
                    let error = xhr.responseJSON ? xhr.responseJSON.message : 'Error adding stock.';
                    toastr.error(error);
                }
            });
        });

        // Initialize with 1 Stock Row
        $(document).ready(function () {
            addItem();
        });
    </script>
@endpush