@extends('admin.layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #eef2ff;
            --secondary-color: #94a3b8;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        .content-wrapper {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-main);
            padding-bottom: 300px;
        }

        .premium-page-header {
            padding: 2rem 0;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.025em;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
        }

        /* ITEM CARD STYLING */
        .inventory-item-card {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: visible;
        }

        .inventory-item-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .card-header-premium {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fcfdfe;
            border-radius: 16px 16px 0 0;
        }

        .item-number {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body-premium {
            padding: 1.5rem;
        }

        .input-group-premium {
            margin-bottom: 1.25rem;
        }

        .label-premium {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.025em;
            margin-bottom: 0.5rem;
        }

        .form-control-premium {
            height: 44px;
            width: 100%;
            font-size: 0.9375rem;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background-color: #fff;
            transition: all 0.2s;
            padding: 0 12px;
        }

        .form-control-premium:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        /* SELECT2 CUSTOMIZATION */
        .select2-container--bootstrap4 .select2-selection {
            height: 44px !important;
            border-radius: 10px !important;
            border: 1px solid var(--border-color) !important;
            display: flex;
            align-items: center;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 42px !important;
            font-size: 0.9375rem !important;
            padding-left: 12px !important;
        }

        .select2-dropdown {
            border-radius: 12px !important;
            box-shadow: var(--shadow-lg) !important;
            border-color: var(--border-color) !important;
            z-index: 10000 !important;
            /* Ensure it stays on top of everything including sticky footer */
            overflow: hidden;
        }

        .select2-results__options {
            max-height: 500px !important;
        }

        /* BUTTONS */
        .btn-add-item {
            background: #fff;
            color: var(--primary-color);
            border: 2px dashed var(--primary-color);
            border-radius: 12px;
            padding: 1rem;
            width: 100%;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-top: 1rem;
            cursor: pointer;
        }

        .btn-add-item:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
            border-style: solid;
        }

        .btn-remove-item {
            background: #fee2e2;
            color: var(--danger-color);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-remove-item:hover {
            background: var(--danger-color);
            color: #fff;
        }

        .sticky-actions {
            position: fixed;
            bottom: 0;
            left: 250px;
            right: 0;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            padding: 1.25rem 2.5rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 2rem;
            z-index: 1000;
            box-shadow: 0 -8px 20px -5px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 991.98px) {
            .sticky-actions {
                left: 0;
            }
        }

        .btn-confirm {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: #fff !important;
            border: none;
            border-radius: 12px;
            padding: 0.875rem 2.5rem;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -5px rgba(99, 102, 241, 0.4);
        }

        .btn-cancel {
            color: var(--text-muted);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .btn-cancel:hover {
            color: var(--text-main);
        }

        .animate-in {
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <header class="premium-page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">Inventory Stock Entry</h1>
                        <p class="page-subtitle">Add new production goods to your warehouse stock.</p>
                    </div>
                    <div class="header-actions">
                        <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary shadow-sm"><i class="fas fa-plus mr-1"></i> Create New</a>
                        <a href="javascript:window.location.reload();" class="btn btn-secondary shadow-sm ml-2"><i class="fas fa-sync-alt mr-1"></i> Refresh</a>
                    </div>
                </header>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <form action="{{ route('admin.inventory.store') }}" method="POST" id="addStockForm">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="label-premium">Source Type</label>
                            <select name="source_type" id="sourceType" class="form-control select2">
                                <option value="production">Self Production</option>
                                <option value="sample">Sample Production</option>
                                <option value="consume">Stock Consume</option>
                            </select>
                        </div>
                    </div>

                    <div id="itemsContainer">
                        <!-- Initial Item Card -->
                        <div class="inventory-item-card animate-in" data-index="0">
                            <div class="card-header-premium">
                                <span class="item-number">
                                    <i class="fas fa-barcode"></i> Stock Generate #1
                                </span>
                                <div class="header-actions">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-duplicate-item mr-2"
                                        data-card-index="0">
                                        <i class="fas fa-copy mr-1"></i> Duplicate
                                    </button>

                                </div>
                            </div>
                            <div class="card-body-premium">
                                <!-- Hidden Consume Selection Row -->
                                <div class="consume-selection-row mb-4 p-3 bg-light rounded"
                                    style="display: none; border: 1px dashed var(--primary-color);">
                                    <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-search mr-1"></i> Search
                                        Domestic Inventory</h6>
                                    <p class="small text-muted mb-3">Selecting from here will <b>subtract</b> from this
                                        source
                                        and <b>add</b> to your new entry below.</p>
                                    <input type="hidden" name="products[0][consume_source_id]" class="consume-source-id">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="label-premium text-primary">Design No</label>
                                            <select class="form-control select2 consume-design-select">
                                                <option value="">Select Design</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->design_number }} ({{ $product->series->name ?? '' }}
                                                        {{ $product->name_of_garment }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="label-premium text-primary">Pattern</label>
                                            <select class="form-control select2 consume-pattern-select">
                                                <option value="">Pattern</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="label-premium text-primary">Fitting</label>
                                            <select class="form-control select2 consume-fitting-select">
                                                <option value="">Fitting</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="label-premium text-primary">Warehouse</label>
                                            <select class="form-control select2 consume-warehouse-select">
                                                <option value="">Warehouse</option>
                                                @foreach($storerooms as $room)
                                                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="label-premium text-primary">Rack</label>
                                            <select class="form-control select2 consume-rack-select">
                                                <option value="">Rack</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <label class="label-premium text-primary">Size Set</label>
                                            <select class="form-control select2 consume-size-set-select">
                                                <option value="">Size Set</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="label-premium text-primary">Color</label>
                                            <select class="form-control select2 consume-color-select">
                                                <option value="">Color</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 d-flex align-items-end justify-content-end">
                                            <div class="mr-4 text-right">
                                                <label class="label-premium text-success d-block mb-0">Total Available
                                                    Stock</label>
                                                <div class="consume-available-display font-weight-bold text-success"
                                                    style="font-size: 1.5rem; line-height: 1;">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 1: Primary Details -->
                                <div class="row">
                                    <div class="col-md-3 input-group-premium">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="label-premium mb-0">Design No *</label>
    <div class="action-links">
        <a href="{{ route('admin.master.production-goods.create') }}" target="_blank" class="text-primary" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
        <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="design" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
    </div>
</div>
                                        <select name="products[0][product_id]" class="form-control select2 design-select"
                                            required>
                                            <option value="">Select Design</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-name="{{ $product->name_of_garment }}">
                                                    {{ $product->design_number }} ({{ $product->series->name ?? '' }}
                                                    {{ $product->name_of_garment }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 input-group-premium">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="label-premium mb-0">Pattern *</label>
    <div class="action-links">
        <a href="{{ route('admin.master.design-pattern.index') }}" target="_blank" class="text-primary" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
        <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="pattern" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
    </div>
</div>
                                        <select name="products[0][pattern_id]" class="form-control select2 pattern-select"
                                            required>
                                            <option value="">Pattern</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 input-group-premium">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="label-premium mb-0">Fitting *</label>
    <div class="action-links">
        <a href="{{ route('admin.master.fitting.create') }}" target="_blank" class="text-primary" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
        <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="fitting" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
    </div>
</div>
                                        <select name="products[0][fitting_id]" class="form-control select2 fitting-select"
                                            required>
                                            <option value="">Fitting</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 input-group-premium">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="label-premium mb-0">Warehouse *</label>
    <div class="action-links">
        <a href="{{ route('admin.master.storeroom.index') }}" target="_blank" class="text-primary" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
        <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="warehouse" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
    </div>
</div>
                                        <select name="products[0][warehouse_id]"
                                            class="form-control select2 warehouse-select" required>
                                            <option value="">Warehouse</option>
                                            @foreach($storerooms as $room)
                                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 input-group-premium">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="label-premium mb-0">Rack</label>
                                            <div class="action-links">
                                                <a href="javascript:void(0)" class="text-primary btn-add-new-rack" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
                                                <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="rack" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
                                            </div>
                                        </div>
                                        <select name="products[0][rack_id]" class="form-control select2 rack-select"
                                            required>
                                            <option value="">Rack</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Row 2: Secondary Details -->
                                <div class="row">
                                    <div class="col-md-3 input-group-premium">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="label-premium mb-0">Size Set *</label>
    <div class="action-links">
        <a href="{{ route('admin.master.size-measurement.create') }}" target="_blank" class="text-primary" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
        <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="size" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
    </div>
</div>
                                        <select name="products[0][size_set_id]" class="form-control select2 size-set-select"
                                            required>
                                            <option value="">Select Size Set</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 input-group-premium">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="label-premium mb-0">Color *</label>
    <div class="action-links">
        <a href="{{ route('admin.master.colors.create') }}" target="_blank" class="text-primary" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
        <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="color" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
    </div>
</div>
                                        <select name="products[0][color_id]" class="form-control select2 color-select"
                                            required>
                                            <option value="">Color</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 input-group-premium">
                                        <label class="label-premium">Total Boxes *</label>
                                        <input type="number" name="products[0][total_boxes]"
                                            class="form-control form-control-premium total-boxes-input" placeholder="Qty"
                                            min="1" required>
                                    </div>
                                    <div class="col-md-2 input-group-premium">
                                        <label class="label-premium">Pcs / Box</label>
                                        <input type="number" name="products[0][pieces_per_box]"
                                            class="form-control form-control-premium bg-light pcs-per-box-input"
                                            placeholder="Pcs/Box" readonly required>
                                    </div>
                                    <div class="col-md-2 input-group-premium">
                                        <label class="label-premium">MRP *</label>
                                        <input type="number" name="products[0][mrp]"
                                            class="form-control form-control-premium mrp-input bg-light" placeholder="Price"
                                            step="0.01" min="0" readonly required>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>



                    <button type="button" class="btn btn-add-item mt-3" id="addNewItem">
                        <i class="fas fa-plus-circle"></i> Add Stock Generate
                    </button>

                    <div class="sticky-actions">
                        <a href="{{ route('admin.inventory.index') }}" class="btn-cancel">Cancel and Exit</a>
                        <button type="submit" class="btn btn-confirm">
                            <i class="fas fa-check-double mr-2"></i> Confirm and Upload Stock
                        </button>
                    </div>
                </form>
            </div>
            </div>
        </section>
    </div>

    <!-- Quick Add Rack Modal -->
    <div class="modal fade" id="quickRackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Rack</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="quick_rack_warehouse_id">
                    <div class="mb-3">
                        <label>Rack Name *</label>
                        <input type="text" id="quick_rack_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Capacity (Optional)</label>
                        <input type="number" id="quick_rack_capacity" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitQuickRack">Save Rack</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            let itemCount = 0;

            function initSelect2(container) {
                container.find('.select2').each(function () {
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownAutoWidth: true,
                        dropdownParent: $('body')
                    });
                });
            }

            initSelect2($('.content-wrapper'));

            // Initial trigger for source type
            $('#sourceType').trigger('change');

            $('#sourceType').on('change', function () {
                let type = $(this).val();
                if (type === 'consume') {
                    $('.consume-selection-row').slideDown();
                } else {
                    $('.consume-selection-row').slideUp();
                    $('.consume-source-id').val('');
                }
            });

            const itemLabel = "Stock Generate";

            function addItem(values = null) {
                itemCount++;
                let idx = itemCount;
                let newItem = `
                                    <div class="inventory-item-card animate-in" data-index="${idx}">
                                        <div class="card-header-premium">
                                            <span class="item-number">
                                                <i class="fas fa-barcode"></i> ${itemLabel} #${idx + 1}
                                            </span>
                                            <div class="header-actions">
                                                <button type="button" class="btn btn-outline-primary btn-sm btn-duplicate-item mr-2" data-card-index="${idx}">
                                                    <i class="fas fa-copy mr-1"></i> Duplicate
                                                </button>

                                                <button type="button" class="btn btn-danger btn-sm btn-remove-item ml-2">
                                                    <i class="fas fa-trash-alt mr-1"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body-premium">
                                            <div class="consume-selection-row mb-4 p-3 bg-light rounded" style="display: none; border: 1px dashed var(--primary-color);">
                                                <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-search mr-1"></i> Search Domestic Inventory</h6>
                                                <p class="small text-muted mb-3">Selecting from here will <b>subtract</b> from this source and <b>add</b> to your new entry below.</p>
                                                <input type="hidden" name="products[${idx}][consume_source_id]" class="consume-source-id">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label class="label-premium text-primary">Design No</label>
                                                        <select class="form-control select2 consume-design-select">
                                                            <option value="">Select Design</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}">
                                                                    {{ $product->design_number }} ({{ $product->series->name ?? '' }} {{ $product->name_of_garment }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="label-premium text-primary">Pattern</label>
                                                        <select class="form-control select2 consume-pattern-select">
                                                            <option value="">Pattern</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="label-premium text-primary">Fitting</label>
                                                        <select class="form-control select2 consume-fitting-select">
                                                            <option value="">Fitting</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="label-premium text-primary">Warehouse</label>
                                                        <select class="form-control select2 consume-warehouse-select">
                                                            <option value="">Warehouse</option>
                                                            @foreach($storerooms as $room)
                                                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="label-premium text-primary">Rack</label>
                                                        <select class="form-control select2 consume-rack-select">
                                                            <option value="">Rack</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-md-3">
                                                        <label class="label-premium text-primary">Size Set</label>
                                                        <select class="form-control select2 consume-size-set-select">
                                                            <option value="">Size Set</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="label-premium text-primary">Color</label>
                                                        <select class="form-control select2 consume-color-select">
                                                            <option value="">Color</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 d-flex align-items-end justify-content-end">
                                                        <div class="mr-4 text-right">
                                                            <label class="label-premium text-success d-block mb-0">Total Available Stock</label>
                                                            <div class="consume-available-display font-weight-bold text-success" style="font-size: 1.5rem; line-height: 1;">-</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3 input-group-premium">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="label-premium mb-0">Design No *</label>
    <div class="action-links">
        <a href="{{ route('admin.master.production-goods.create') }}" target="_blank" class="text-primary" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
        <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="design" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
    </div>
</div>
                                                    <select name="products[${idx}][product_id]" class="form-control select2 design-select" required>
                                                        <option value="">Select Design</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" data-name="{{ $product->name_of_garment }}">{{ $product->design_number }} ({{ $product->series->name ?? '' }} {{ $product->name_of_garment }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2 input-group-premium">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="label-premium mb-0">Pattern *</label>
    <div class="action-links">
        <a href="{{ route('admin.master.design-pattern.index') }}" target="_blank" class="text-primary" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
        <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="pattern" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
    </div>
</div>
                                                    <select name="products[${idx}][pattern_id]" class="form-control select2 pattern-select" required>
                                                        <option value="">Pattern</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 input-group-premium">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="label-premium mb-0">Fitting *</label>
    <div class="action-links">
        <a href="{{ route('admin.master.fitting.create') }}" target="_blank" class="text-primary" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
        <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="fitting" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
    </div>
</div>
                                                    <select name="products[${idx}][fitting_id]" class="form-control select2 fitting-select" required>
                                                        <option value="">Fitting</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 input-group-premium">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="label-premium mb-0">Warehouse *</label>
    <div class="action-links">
        <a href="{{ route('admin.master.storeroom.index') }}" target="_blank" class="text-primary" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
        <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="warehouse" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
    </div>
</div>
                                                    <select name="products[${idx}][warehouse_id]" class="form-control select2 warehouse-select" required>
                                                        <option value="">Warehouse</option>
                                                        @foreach($storerooms as $room)
                                                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2 input-group-premium">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <label class="label-premium mb-0">Rack</label>
                                                        <div class="action-links">
                                                            <a href="javascript:void(0)" class="text-primary btn-add-new-rack" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
                                                            <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="rack" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
                                                        </div>
                                                    </div>
                                                    <select name="products[${idx}][rack_id]" class="form-control select2 rack-select" required>
                                                        <option value="">Rack</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3 input-group-premium">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="label-premium mb-0">Size Set *</label>
    <div class="action-links">
        <a href="{{ route('admin.master.size-measurement.create') }}" target="_blank" class="text-primary" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
        <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="size" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
    </div>
</div>
                                                    <select name="products[${idx}][size_set_id]" class="form-control select2 size-set-select" required>
                                                        <option value="">Select Size Set</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 input-group-premium">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
    <label class="label-premium mb-0">Color *</label>
    <div class="action-links">
        <a href="{{ route('admin.master.colors.create') }}" target="_blank" class="text-primary" title="Create New" style="font-size:12px;"><i class="fas fa-plus"></i></a>
        <a href="javascript:void(0)" class="text-info ml-1 btn-refresh-master" data-type="color" title="Refresh" style="font-size:12px;"><i class="fas fa-sync-alt"></i></a>
    </div>
</div>
                                                    <select name="products[${idx}][color_id]" class="form-control select2 color-select" required>
                                                        <option value="">Color</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 input-group-premium">
                                                    <label class="label-premium">Total Boxes *</label>
                                                    <input type="number" name="products[${idx}][total_boxes]" class="form-control form-control-premium total-boxes-input" placeholder="Qty" min="1" required>
                                                </div>
                                                <div class="col-md-2 input-group-premium">
                                                    <label class="label-premium">Pcs / Box</label>
                                                    <input type="number" name="products[${idx}][pieces_per_box]" class="form-control form-control-premium bg-light" placeholder="Pcs/Box" readonly required>
                                                </div>
                                                <div class="col-md-2 input-group-premium">
                                                    <label class="label-premium">MRP *</label>
                                                    <input type="number" name="products[${idx}][mrp]" class="form-control form-control-premium mrp-input bg-light" placeholder="Price" step="0.01" min="0" readonly required>
                                                </div>
                                            </div>

                                        </div>
                                    </div>`;

                $('#itemsContainer').append(newItem);
                let newCard = $('#itemsContainer').find(`.inventory-item-card[data-index="${idx}"]`);
                initSelect2(newCard);

                if (values) {
                    populateCard(newCard, values);
                } else {
                    // Auto-select first warehouse for new items
                    let warehouseSelect = newCard.find('.warehouse-select');
                    if (warehouseSelect.find('option').length > 1) {
                        warehouseSelect.val(warehouseSelect.find('option:eq(1)').val()).trigger('change');
                    }

                    // Also auto-select first warehouse for consume part if active
                    if ($('#sourceType').val() === 'consume') {
                        let consumeWarehouse = newCard.find('.consume-warehouse-select');
                        if (consumeWarehouse.find('option').length > 1) {
                            consumeWarehouse.val(consumeWarehouse.find('option:eq(1)').val()).trigger('change');
                        }
                    }
                }

                updateItemNumbers();

                // apply source type check
                $('#sourceType').trigger('change');
            }

            let isPopulating = false;

            function populateCard(card, values) {
                if (!values.product_id) return;

                isPopulating = true;

                // 0. Set Consume Fields if they exist
                if (values.consume_product_id) {
                    card.find('.consume-design-select').val(values.consume_product_id).trigger('change.select2');
                    if (values.consume_pattern_id) {
                        card.find('.consume-pattern-select').empty().append(`<option value="${values.consume_pattern_id}" selected>${values.consume_pattern_name}</option>`).trigger('change.select2');
                    }
                    if (values.consume_variants) {
                        card.data('consume_variants', values.consume_variants);
                        let cSizeSelect = card.find('.consume-size-set-select');
                        cSizeSelect.empty().append('<option value="">Select Size Set</option>');
                        let uniqueSizeSets = [];
                        values.consume_variants.forEach(function (v) {
                            if (!uniqueSizeSets.includes(v.size_set_id)) {
                                cSizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                                uniqueSizeSets.push(v.size_set_id);
                            }
                        });
                        cSizeSelect.val(values.consume_size_set_id).trigger('change.select2');

                        let cColorSelect = card.find('.consume-color-select');
                        cColorSelect.empty().append('<option value="">Select Color</option>');
                        let variant = values.consume_variants.find(v => v.size_set_id == values.consume_size_set_id);
                        if (variant) {
                            variant.colors.forEach(function (c) {
                                cColorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                            });
                        }
                        cColorSelect.val(values.consume_color_id).trigger('change.select2');
                    } else {
                        if (values.consume_size_set_id) {
                            card.find('.consume-size-set-select').empty().append(`<option value="${values.consume_size_set_id}" selected>${values.consume_size_set_name}</option>`).trigger('change.select2');
                        }
                        if (values.consume_color_id) {
                            card.find('.consume-color-select').empty().append(`<option value="${values.consume_color_id}" selected>${values.consume_color_name}</option>`).trigger('change.select2');
                        }
                    }
                    card.find('.consume-source-id').val(values.consume_source_id || '');
                    card.find('.consume-available-display').text(values.consume_available || '-');
                    if (values.consume_available) {
                        card.find('.total-boxes-input').attr('max', values.consume_available);
                    }
                }

                // 1. Set variants data immediately if available
                if (values.variants) {
                    card.data('variants', values.variants);
                }

                // 2. Set Product
                card.find('.design-select').val(values.product_id).trigger('change.select2');

                // 3. Set Pattern & Fitting
                if (values.pattern_id) {
                    card.find('.pattern-select').empty().append(`<option value="${values.pattern_id}" selected>${values.pattern_name || 'Pattern'}</option>`).trigger('change.select2');
                }
                if (values.fitting_id) {
                    card.find('.fitting-select').empty().append(`<option value="${values.fitting_id}" selected>${values.fitting_name || 'Fitting'}</option>`).trigger('change.select2');
                }

                // 4. Set Warehouse
                if (values.warehouse_id) {
                    card.find('.warehouse-select').val(values.warehouse_id).trigger('change.select2');
                }

                // 5. Populate dropdowns and set values
                if (values.variants) {
                    let sizeSelect = card.find('.size-set-select');
                    sizeSelect.empty().append('<option value="">Select Size Set</option>');
                    values.variants.forEach(function (v) {
                        sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                    });
                    sizeSelect.val(values.size_set_id).trigger('change.select2');

                    let colorSelect = card.find('.color-select');
                    colorSelect.empty().append('<option value="">Color</option>');
                    let variant = values.variants.find(v => v.size_set_id == values.size_set_id);
                    if (variant) {
                        variant.colors.forEach(function (c) {
                            colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                        });
                    }
                    colorSelect.val(values.color_id).trigger('change.select2');
                } else {
                    // Fallback: Always ensure the selected options exist in the DOM
                    if (values.size_set_id) {
                        let select = card.find('.size-set-select');
                        if (select.find(`option[value="${values.size_set_id}"]`).length === 0) {
                            select.append(`<option value="${values.size_set_id}" selected>${values.size_set_name || 'Size Set'}</option>`);
                        }
                        select.val(values.size_set_id).trigger('change.select2');
                    }
                    if (values.color_id) {
                        let select = card.find('.color-select');
                        if (select.find(`option[value="${values.color_id}"]`).length === 0) {
                            select.append(`<option value="${values.color_id}" selected>${values.color_name || 'Color'}</option>`);
                        }
                        select.val(values.color_id).trigger('change.select2');
                    }
                }

                // Set Rack
                if (values.rack_id) {
                    let select = card.find('.rack-select');
                    if (select.find(`option[value="${values.rack_id}"]`).length === 0) {
                        select.append(`<option value="${values.rack_id}" selected>${values.rack_name || 'Rack'}</option>`);
                    }
                    select.val(values.rack_id).trigger('change.select2');
                }

                // Set Numeric Inputs
                if (values.total_boxes) card.find('.total-boxes-input').val(values.total_boxes);
                if (values.pieces_per_box) card.find('input[name*="pieces_per_box"]').val(values.pieces_per_box);
                if (values.mrp) card.find('.mrp-input').val(values.mrp);

                isPopulating = false;
            }

            $('#addNewItem').on('click', function () {
                addItem();
            });

            $(document).on('click', '.btn-duplicate-item', function () {
                let sourceCard = $(this).closest('.inventory-item-card');
                let values = {
                    product_id: sourceCard.find('.design-select').val(),
                    pattern_id: sourceCard.find('.pattern-select').val(),
                    pattern_name: sourceCard.find('.pattern-select option:selected').text(),
                    fitting_id: sourceCard.find('.fitting-select').val(),
                    fitting_name: sourceCard.find('.fitting-select option:selected').text(),
                    warehouse_id: sourceCard.find('.warehouse-select').val(),
                    rack_id: sourceCard.find('.rack-select').val(),
                    rack_name: sourceCard.find('.rack-select option:selected').text(),
                    size_set_id: sourceCard.find('.size-set-select').val(),
                    size_set_name: sourceCard.find('.size-set-select option:selected').text(),
                    color_id: sourceCard.find('.color-select').val(),
                    color_name: sourceCard.find('.color-select option:selected').text(),
                    total_boxes: sourceCard.find('.total-boxes-input').val(),
                    pieces_per_box: sourceCard.find('input[name*="pieces_per_box"]').val(),
                    mrp: sourceCard.find('.mrp-input').val(),
                    variants: sourceCard.data('variants'),

                    // Consume fields
                    consume_product_id: sourceCard.find('.consume-design-select').val(),
                    consume_variants: sourceCard.data('consume_variants'),
                    consume_pattern_id: sourceCard.find('.consume-pattern-select').val(),
                    consume_pattern_name: sourceCard.find('.consume-pattern-select option:selected').text(),
                    consume_fitting_id: sourceCard.find('.consume-fitting-select').val(),
                    consume_fitting_name: sourceCard.find('.consume-fitting-select option:selected').text(),
                    consume_warehouse_id: sourceCard.find('.consume-warehouse-select').val(),
                    consume_rack_id: sourceCard.find('.consume-rack-select').val(),
                    consume_rack_name: sourceCard.find('.consume-rack-select option:selected').text(),
                    consume_size_set_id: sourceCard.find('.consume-size-set-select').val(),
                    consume_size_set_name: sourceCard.find('.consume-size-set-select option:selected').text(),
                    consume_color_id: sourceCard.find('.consume-color-select').val(),
                    consume_color_name: sourceCard.find('.consume-color-select option:selected').text(),
                    consume_source_id: sourceCard.find('.consume-source-id').val(),
                    consume_available: sourceCard.find('.consume-available-display').text()
                };
                addItem(values);
                toastr.info('Item duplicated. You can now modify specific fields.');

                // Validate duplication usage
                let lastCard = $('.inventory-item-card').last();
                validateStockAvailability(lastCard);
            });

            $(document).on('click', '.btn-remove-item', function () {
                $(this).closest('.inventory-item-card').fadeOut(300, function () {
                    $(this).remove();
                    updateItemNumbers();
                });
            });

            function updateItemNumbers() {
                $('.inventory-item-card').each(function (index) {
                    $(this).find('.item-number').html(`<i class="fas fa-barcode"></i> Stock Generate #${index + 1}`);
                });
            }

            $(document).on('change', '.design-select', function () {
                if (isPopulating) return;
                let productId = $(this).val();
                let card = $(this).closest('.inventory-item-card');
                let patternSelect = card.find('.pattern-select');
                let fittingSelect = card.find('.fitting-select');
                let sizeSelect = card.find('.size-set-select');
                let colorSelect = card.find('.color-select');

                // Clear subsequent dropdowns
                patternSelect.empty().append('<option value="">Pattern</option>').trigger('change.select2');
                fittingSelect.empty().append('<option value="">Fitting</option>').trigger('change.select2');
                sizeSelect.empty().append('<option value="">Select Size Set</option>').trigger('change.select2');
                colorSelect.empty().append('<option value="">Color</option>').trigger('change.select2');
                card.find('input[name*="pieces_per_box"]').val('');
                card.find('.mrp-input').val('');

                if (productId) {
                    $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function (data) {
                        if (data.success) {
                            if (data.pattern_id) {
                                patternSelect.empty().append(`<option value="${data.pattern_id}" selected>${data.pattern_name}</option>`).trigger('change.select2');
                            }

                            if (data.fitting_id) {
                                fittingSelect.empty().append(`<option value="${data.fitting_id}" selected>${data.fitting_name}</option>`).trigger('change.select2');
                            }

                            card.data('variants', data.variants);

                            sizeSelect.empty().append('<option value="">Select Size Set</option>');
                            data.variants.forEach(function (v) {
                                sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                            });
                            sizeSelect.trigger('change.select2');
                        }
                    });
                }
            });

            $(document).on('change', '.size-set-select', function () {
                if (isPopulating) return;
                let sizeSetId = $(this).val();
                let card = $(this).closest('.inventory-item-card');
                let variants = card.data('variants') || [];
                let colorSelect = card.find('.color-select');

                // Clear color when size changes
                colorSelect.empty().append('<option value="">Color</option>').trigger('change.select2');
                card.find('input[name*="pieces_per_box"]').val('');
                card.find('.mrp-input').val('');

                if (sizeSetId) {
                    $.get("{{ url('admin/inventory/get-size-set-info') }}/" + sizeSetId, function (data) {
                        card.find('input[name*="pieces_per_box"]').val(data.no_of_pcs);
                    });

                    let variant = variants.find(v => v.size_set_id == sizeSetId);
                    if (variant) {
                        card.find('.mrp-input').val(variant.mrp);
                        variant.colors.forEach(function (c) {
                            colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                        });
                        colorSelect.trigger('change.select2');
                    }
                }
            });

            $(document).on('change', '.warehouse-select', function () {
                if (isPopulating) return;
                let warehouseId = $(this).val();
                let card = $(this).closest('.inventory-item-card');
                let rackSelect = card.find('.rack-select');

                rackSelect.empty().append('<option value="">Rack</option>');
                if (warehouseId) {
                    $.get("{{ url('admin/inventory/warehouse-stock/racks') }}/" + warehouseId, function (data) {
                        data.forEach(function (rack) {
                            rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                        });
                        rackSelect.trigger('change.select2');

                        // Auto-select first rack if any
                        if (data.length > 0) {
                            rackSelect.val(data[0].id).trigger('change.select2');
                        }
                    });
                }
            });



            $(document).on('input', '.total-boxes-input', function () {
                validateStockAvailability($(this).closest('.inventory-item-card'));
            });

            function validateStockAvailability(currentCard) {
                if ($('#sourceType').val() !== 'consume') return;

                let sourceId = currentCard.find('.consume-source-id').val();
                if (!sourceId) return;

                let available = parseInt(currentCard.find('.consume-available-display').text()) || 0;
                let totalConsumed = 0;

                // Sum up all cards using this same source
                $('.inventory-item-card').each(function () {
                    if ($(this).find('.consume-source-id').val() === sourceId) {
                        totalConsumed += parseInt($(this).find('.total-boxes-input').val()) || 0;
                    }
                });

                if (totalConsumed > available) {
                    toastr.error(`Total consumption (${totalConsumed}) exceeds available stock (${available}) for this source.`);

                    // Calculate how much was already used by OTHER cards
                    let consumedByOthers = 0;
                    $('.inventory-item-card').not(currentCard).each(function () {
                        if ($(this).find('.consume-source-id').val() === sourceId) {
                            consumedByOthers += parseInt($(this).find('.total-boxes-input').val()) || 0;
                        }
                    });

                    let remaining = Math.max(0, available - consumedByOthers);
                    currentCard.find('.total-boxes-input').val(remaining);
                    calculateGlobalTotal();
                }
            }

            $(document).on('change', '.consume-warehouse-select', function () {
                if (isPopulating) return;
                let warehouseId = $(this).val();
                let card = $(this).closest('.inventory-item-card');
                let rackSelect = card.find('.consume-rack-select');
                rackSelect.empty().append('<option value="">Select Rack</option>');
                if (warehouseId) {
                    $.get("{{ url('admin/inventory/warehouse-stock/racks') }}/" + warehouseId, function (data) {
                        data.forEach(function (rack) {
                            rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                        });
                        rackSelect.trigger('change.select2');

                        // Auto-select first rack if any
                        if (data.length > 0) {
                            rackSelect.val(data[0].id).trigger('change.select2');
                        }
                    });
                }
            });

            $(document).on('change', '.consume-design-select', function () {
                if (isPopulating) return;
                let productId = $(this).val();
                let card = $(this).closest('.inventory-item-card');
                let sizeSelect = card.find('.consume-size-set-select');
                let patternSelect = card.find('.consume-pattern-select');
                let fittingSelect = card.find('.consume-fitting-select');

                sizeSelect.empty().append('<option value="">Select Size Set</option>').trigger('change.select2');
                card.find('.consume-color-select').empty().append('<option value="">Select Color</option>').trigger('change.select2');
                patternSelect.empty().append('<option value="">Pattern</option>').trigger('change.select2');
                fittingSelect.empty().append('<option value="">Fitting</option>').trigger('change.select2');

                if (productId) {
                    $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function (data) {
                        if (data.success) {
                            card.data('consume_variants', data.variants);
                            if (data.pattern_id) {
                                patternSelect.empty().append(`<option value="${data.pattern_id}" selected>${data.pattern_name}</option>`).trigger('change.select2');
                            }
                            if (data.fitting_id) {
                                fittingSelect.empty().append(`<option value="${data.fitting_id}" selected>${data.fitting_name}</option>`).trigger('change.select2');
                            }
                            if (data.variants) {
                                let uniqueSizeSets = [];
                                data.variants.forEach(function (v) {
                                    if (!uniqueSizeSets.includes(v.size_set_id)) {
                                        sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                                        uniqueSizeSets.push(v.size_set_id);
                                    }
                                });
                            }
                            sizeSelect.trigger('change.select2');
                        }
                    });
                }
            });

            $(document).on('change', '.consume-size-set-select', function () {
                if (isPopulating) return;
                let sizeSetId = $(this).val();
                let card = $(this).closest('.inventory-item-card');
                let productId = card.find('.consume-design-select').val();
                let colorSelect = card.find('.consume-color-select');
                colorSelect.empty().append('<option value="">Select Color</option>').trigger('change.select2');

                if (sizeSetId && productId) {
                    $.get("{{ url('admin/inventory/get-colors-by-product-size') }}/" + productId + "/" + sizeSetId, function (data) {
                        if (data.status === 'success') {
                            data.colors.forEach(function (c) {
                                colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                            });
                            colorSelect.trigger('change.select2');
                        }
                    });
                }
            });

            $(document).on('change', '.consume-design-select, .consume-warehouse-select, .consume-rack-select, .consume-size-set-select, .consume-color-select, .consume-pattern-select, .consume-fitting-select', function () {
                if (isPopulating) return;
                let card = $(this).closest('.inventory-item-card');
                let productId = card.find('.consume-design-select').val();
                let warehouseId = card.find('.consume-warehouse-select').val();
                let rackId = card.find('.consume-rack-select').val();
                let sizeSetId = card.find('.consume-size-set-select').val();
                let colorId = card.find('.consume-color-select').val();
                let patternId = card.find('.consume-pattern-select').val();
                let fittingId = card.find('.consume-fitting-select').val();

                if (productId && warehouseId && rackId && sizeSetId && colorId) {
                    $.get("{{ route('admin.inventory.get_domestic_inventory_for_consume') }}", {
                        product_id: productId,
                        warehouse_id: warehouseId,
                        rack_id: rackId,
                        size_set_id: sizeSetId,
                        color_id: colorId,
                        pattern_id: patternId,
                        fitting_id: fittingId
                    }, function (data) {
                        if (data.success) {
                            card.find('.consume-source-id').val(data.inventory_id);
                            card.find('.consume-available-display').text(data.total_boxes);
                            card.find('.total-boxes-input').attr('max', data.total_boxes);

                            // Auto-fill primary card fields with immediate option injection to avoid race conditions
                            card.find('.design-select').val(productId).trigger('change.select2');
                            card.find('.warehouse-select').val(warehouseId).trigger('change.select2');

                            // Manually inject options into primary selects to ensure they are selected immediately
                            let patternSelect = card.find('.pattern-select');
                            let fittingSelect = card.find('.fitting-select');
                            let rackSelect = card.find('.rack-select');
                            let sizeSelect = card.find('.size-set-select');
                            let colorSelect = card.find('.color-select');

                            let patternName = card.find('.consume-pattern-select option:selected').text();
                            let fittingName = card.find('.consume-fitting-select option:selected').text();
                            let rackName = card.find('.consume-rack-select option:selected').text();
                            let sizeName = card.find('.consume-size-set-select option:selected').text();
                            let colorName = card.find('.consume-color-select option:selected').text();

                            if (patternId && patternSelect.find(`option[value="${patternId}"]`).length === 0) {
                                patternSelect.append(`<option value="${patternId}" selected>${patternName}</option>`);
                            }
                            patternSelect.val(patternId || '').trigger('change.select2');

                            if (fittingId && fittingSelect.find(`option[value="${fittingId}"]`).length === 0) {
                                fittingSelect.append(`<option value="${fittingId}" selected>${fittingName}</option>`);
                            }
                            fittingSelect.val(fittingId || '').trigger('change.select2');

                            if (rackSelect.find(`option[value="${rackId}"]`).length === 0) {
                                rackSelect.append(`<option value="${rackId}" selected>${rackName}</option>`);
                            }
                            rackSelect.val(rackId).trigger('change.select2');

                            // Store variants data for the primary card
                            card.data('variants', data.variants);

                            // Populate all available size sets first if not already there
                            if (sizeSelect.find('option').length <= 1) {
                                sizeSelect.empty().append('<option value="">Select Size Set</option>');
                                data.variants.forEach(function (v) {
                                    sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                                });
                            }
                            sizeSelect.val(sizeSetId).trigger('change.select2');

                            // Populate ALL available colors for this size set, not just the one being consumed
                            colorSelect.empty().append('<option value="">Color</option>');
                            if (data.all_colors && data.all_colors.length > 0) {
                                data.all_colors.forEach(function (c) {
                                    colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                                });
                            } else {
                                // Fallback if no colors returned (shouldn't happen with updated backend)
                                colorSelect.append(`<option value="${colorId}" selected>${colorName}</option>`);
                            }
                            colorSelect.val(colorId).trigger('change.select2');

                            card.find('input[name*="total_boxes"]').val(data.total_boxes);
                            card.find('input[name*="pieces_per_box"]').val(data.pieces_per_box);
                            card.find('.mrp-input').val(data.mrp);

                            toastr.success('Existing stock details applied.');

                            // Trigger validation after auto-fill
                            validateStockAvailability(card);
                        } else {
                            toastr.warning(data.message);
                        }
                    });
                } else {
                    card.find('.consume-available-display').text('-');
                    card.find('.total-boxes-input').removeAttr('max');
                    card.find('.consume-source-id').val('');
                }
            });

            $(document).on('input', '.total-boxes-input, .purchase-rate-input, #global_gst_value, #global_other_amount, #global_discount', function () {
                calculateGlobalTotal();
            });

            $(document).on('change', '#global_gst_type', function () {
                calculateGlobalTotal();
            });

            function calculateGlobalTotal() {
                let subTotal = 0;

                $('.inventory-item-card').each(function () {
                    let card = $(this);
                    let totalBoxes = parseFloat(card.find('.total-boxes-input').val()) || 0;
                    let pcsPerBox = parseFloat(card.find('.pcs-per-box-input').val()) || 0;
                    let rate = parseFloat(card.find('.purchase-rate-input').val()) || 0;

                    subTotal += (totalBoxes * pcsPerBox * rate);
                });

                $('#global_sub_total').val(subTotal.toFixed(2));

                let gstValue = parseFloat($('#global_gst_value').val()) || 0;
                let gstType = $('#global_gst_type').val();
                let other = parseFloat($('#global_other_amount').val()) || 0;
                let discount = parseFloat($('#global_discount').val()) || 0;

                let gstAmount = 0;
                if (gstType === 'percentage') {
                    gstAmount = (subTotal * gstValue) / 100;
                } else {
                    gstAmount = gstValue;
                }

                $('#global_gst_amount').val(gstAmount.toFixed(2));
                let grandTotal = subTotal + gstAmount + other - discount;

                $('#global_total_amount').val(grandTotal.toFixed(2));
            }

            $('#addStockForm').on('submit', function (e) {
                e.preventDefault();
                let form = $(this);
                let btn = form.find('button[type="submit"]');
                let originalHtml = btn.html();

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Saving...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
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

                            response.ids.forEach(id => {
                                pdfForm.append($('<input>', {
                                    type: 'hidden',
                                    name: 'ids[]',
                                    value: id
                                }));
                            });

                            $('body').append(pdfForm);
                            pdfForm.submit();
                            pdfForm.remove();

                            setTimeout(() => {
                                window.location.href = "{{ route('admin.inventory.index') }}";
                            }, 1000);
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html(originalHtml);
                        let error = xhr.responseJSON ? xhr.responseJSON.message : 'Error adding stock';
                        toastr.error(error);
                    }
                });
            });

            // Initial auto-select for the first card on page load
            (function () {
                let firstWarehouse = $('.warehouse-select').first();
                if (firstWarehouse.length && firstWarehouse.find('option').length > 1 && !firstWarehouse.val()) {
                    firstWarehouse.val(firstWarehouse.find('option:eq(1)').val()).trigger('change');
                }
            })();

            // Handle Master Refresh
            $(document).on('click', '.btn-refresh-master', function(e) {
                e.preventDefault();
                var btn = $(this);
                var type = btn.data('type');
                var card = btn.closest('.inventory-item-card');
                if(!card.length) {
                    card = btn.closest('form'); // fallback
                }
                
                var icon = btn.find('i');
                icon.addClass('fa-spin');
                
                if (type === 'design' || type === 'warehouse') {
                    $.getJSON("{{ route('admin.inventory.master_data') }}", function(data) {
                        icon.removeClass('fa-spin');
                        if(data.success !== false) {
                            var allDesignSelects = $('.design-select');
                            allDesignSelects.each(function() {
                                var sel = $(this);
                                var currentVal = sel.val();
                                sel.empty().append('<option value="">Select Design</option>');
                                $.each(data.products, function(i, p) {
                                    var seriesName = p.series ? p.series.name : '';
                                    var txt = p.design_number + ' (' + seriesName + ' ' + p.name_of_garment + ')';
                                    sel.append($('<option>', {
                                        value: p.id,
                                        text: txt,
                                        'data-name': p.name_of_garment
                                    }));
                                });
                                sel.val(currentVal).trigger('change.select2');
                            });
                            
                            var allWarehouseSelects = $('.warehouse-select');
                            allWarehouseSelects.each(function() {
                                var sel = $(this);
                                var currentVal = sel.val();
                                sel.empty().append('<option value="">Warehouse</option>');
                                $.each(data.storerooms, function(i, s) {
                                    sel.append($('<option>', {
                                        value: s.id,
                                        text: s.name
                                    }));
                                });
                                sel.val(currentVal).trigger('change.select2');
                            });
                            toastr.success('Master data refreshed successfully');
                        }
                    }).fail(function() {
                        icon.removeClass('fa-spin');
                        toastr.error('Failed to refresh data');
                    });
                } else if (type === 'rack') {
                    var warehouseSelect = card.find('.warehouse-select');
                    if (warehouseSelect.length && warehouseSelect.val()) {
                        warehouseSelect.trigger('change');
                        setTimeout(function() {
                            icon.removeClass('fa-spin');
                            toastr.success('Racks refreshed for selected warehouse');
                        }, 500);
                    } else {
                        icon.removeClass('fa-spin');
                        toastr.info('Please select a warehouse first to load its racks');
                    }
                } else {
                    // pattern, fitting, size, color are dependent on design.
                    var designSelect = card.find('.design-select');
                    if (designSelect.length && designSelect.val()) {
                        designSelect.trigger('change');
                        setTimeout(function() {
                            icon.removeClass('fa-spin');
                            toastr.success('Refreshed based on selected design');
                        }, 800);
                    } else {
                        icon.removeClass('fa-spin');
                        toastr.info('Please select a design first to load its variants');
                    }
                }
            });

            // Quick Rack Add Handlers
            $(document).on('click', '.btn-add-new-rack', function(e) {
                e.preventDefault();
                let card = $(this).closest('.inventory-item-card');
                let warehouseId = card.find('.warehouse-select').val();
                if (!warehouseId) {
                    toastr.warning('Please select a warehouse first.');
                    return;
                }
                $('#quick_rack_warehouse_id').val(warehouseId);
                $('#quick_rack_name').val('');
                $('#quick_rack_capacity').val('');
                // Store reference to the card to refresh its rack dropdown after
                $('#quickRackModal').data('targetCard', card);
                $('#quickRackModal').modal('show');
            });

            $('#btnSubmitQuickRack').on('click', function() {
                let wId = $('#quick_rack_warehouse_id').val();
                let name = $('#quick_rack_name').val();
                let cap = $('#quick_rack_capacity').val();
                let btn = $(this);

                if (!name) { toastr.error('Name is required'); return; }

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.post("{{ route('admin.master.storeroom.rack.store') }}", {
                    _token: "{{ csrf_token() }}",
                    storeroom_id: wId,
                    name: name,
                    capacity: cap
                }, function(res) {
                    btn.prop('disabled', false).text('Save Rack');
                    if (res.status === 'success') {
                        toastr.success('Rack added successfully');
                        $('#quickRackModal').modal('hide');
                        let card = $('#quickRackModal').data('targetCard');
                        if (card) {
                            card.find('.warehouse-select').trigger('change');
                            setTimeout(() => {
                                card.find('.rack-select').val(res.rack.id).trigger('change.select2');
                            }, 500);
                        }
                    } else {
                        toastr.error(res.message || 'Error adding rack');
                    }
                }).fail(function(xhr) {
                    btn.prop('disabled', false).text('Save Rack');
                    let error = xhr.responseJSON ? (xhr.responseJSON.message || 'Failed to add rack') : 'Failed to add rack';
                    toastr.error(error);
                });
            });
        });
    </script>
@endsection