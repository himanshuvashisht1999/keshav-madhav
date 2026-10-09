<div class="erp-card mb-3">
    <div class="erp-card-header d-flex justify-content-between align-items-center">
        <span class="font-weight-bold" style="color: #05421c; font-size: 13px;">
            <i class="fas fa-random mr-1 text-success"></i> {{ $domesticTitle }}
        </span>
    </div>
    <div class="erp-card-body p-3">
        <div class="row align-items-end" style="row-gap: 8px;">
            <div class="col-md-3">
                <label class="erp-filter-label">Design</label>
                <select id="domesticDesign" class="form-control form-control-sm select2">
                    @if(count($designs_with_ids) > 1)
                    <option value="">Select Design</option>
                    @endif
                    @foreach($designs_with_ids as $d)
                    <option value="{{ $d->design_number }}" data-product-id="{{ $d->id }}" {{ count($designs_with_ids) === 1 ? 'selected' : '' }}>{{ $d->design_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="erp-filter-label">Size Set</label>
                <select id="domesticSizeSet" class="form-control form-control-sm select2" disabled>
                    <option value="">Select Size Set</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="erp-filter-label">Color</label>
                <select id="domesticColor" class="form-control form-control-sm select2" disabled>
                    <option value="">Select Color</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="erp-filter-label">Storage Rack <span class="text-danger">*</span></label>
                <select id="domesticRack" class="form-control form-control-sm select2" required>
                    @php
                        $dom_racks_count = 0;
                        foreach($storerooms as $store) {
                            $dom_racks_count += $store->racks->count();
                        }
                    @endphp
                    @if($dom_racks_count > 1)
                    <option value="">Select Storage</option>
                    @endif
                    @foreach($storerooms as $store)
                        <optgroup label="{{ $store->name }}">
                            @foreach($store->racks as $rack)
                                <option value="{{ $rack->id }}" {{ $dom_racks_count === 1 ? 'selected' : '' }}>{{ $rack->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="erp-filter-label d-none d-md-block" style="visibility: hidden;">&nbsp;</label>
                <button type="button" id="btnOpenDomesticModal" class="btn-erp btn-erp-primary w-100 justify-content-center" style="height: 31px;" disabled>
                    <i class="fas fa-boxes mr-1"></i> Allocate & Pack
                </button>
            </div>
        </div>
    </div>
</div>

<!-- DOMESTIC SIZE ALLOCATION & PACKING MODAL -->
<div class="modal fade" id="modalDomesticSizeMapping" tabindex="-1" role="dialog" aria-labelledby="modalDomesticSizeMappingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header py-2 px-3 text-white" style="background: #05421c;">
                <h5 class="modal-title font-weight-bold" id="modalDomesticSizeMappingLabel" style="font-size: 15px;">
                    <i class="fas fa-random mr-2"></i> Allocate Corporate Lots to Domestic Size Set
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <!-- Summary bar -->
                <div class="d-flex flex-wrap align-items-center justify-content-between p-2 mb-3 bg-light rounded border" style="font-size: 0.88rem; gap: 8px;">
                    <div><strong>Design:</strong> <span id="mdlDomDesign" class="badge badge-light border text-dark px-2 py-1"></span></div>
                    <div><strong>Domestic Size Set:</strong> <span id="mdlDomSizeSet" class="badge px-2 py-1 font-weight-bold" style="background:#edf7e4; color:#05421c; border:1px solid #c3e6cb;"></span></div>
                    <div><strong>Color:</strong> <span id="mdlDomColor" class="badge badge-light border text-dark px-2 py-1"></span></div>
                    <div><strong>Storage Rack:</strong> <span id="mdlDomRack" class="badge px-2 py-1 font-weight-bold" style="background:#fff3cd; color:#856404; border:1px solid #ffeeba;"></span></div>
                </div>

                <!-- Available Corporate Lot Stock badges -->
                <div class="mb-3">
                    <label class="small font-weight-bold text-muted text-uppercase mb-1 d-block">
                        <i class="fas fa-layer-group mr-1 text-success"></i> Available Corporate Lot Stock in Slip:
                    </label>
                    <div id="mdlDomAvailableLotsBadges" class="d-flex flex-wrap" style="gap: 6px;">
                        <!-- dynamic badges -->
                    </div>
                </div>

                <!-- Allocation Table -->
                <div class="table-responsive border rounded mb-3">
                    <table class="table erp-table table-sm table-hover text-center align-middle mb-0" id="tblDomesticSizeMapping">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Domestic Size (in Set)</th>
                                <th style="width: 15%;">Pcs / Box</th>
                                <th style="width: 40%;">Map from Corporate Lot Size</th>
                                <th style="width: 20%;">Stock Status</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDomesticSizeMapping">
                            <!-- Dynamic rows -->
                        </tbody>
                    </table>
                </div>

                <!-- Boxes to pack input -->
                <div class="p-3 mb-2 rounded border" style="background: #fafdf8; border-color: #c3e6cb !important;">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <label class="font-weight-bold mb-0" style="color: #05421c;">
                                <i class="fas fa-box-open mr-1 text-success"></i> Total Domestic Boxes to Pack:
                            </label>
                            <small class="text-muted d-block">Number of complete boxes/sets to create</small>
                        </div>
                        <div class="col-md-3">
                            <input type="number" id="mdlDomesticBoxQty" class="form-control font-weight-bold text-center" min="1" value="1" style="font-size: 1.1rem; color: #05421c; border: 1px solid #c3e6cb;">
                        </div>
                        <div class="col-md-4 text-md-right mt-2 mt-md-0">
                            <span id="mdlDomesticMaxBadge" class="badge font-weight-bold px-3 py-2" style="font-size: 0.9rem; background: #05421c; color: #fff;">
                                Max: 0 Boxes
                            </span>
                        </div>
                    </div>
                    <div id="mdlDomesticError" class="alert alert-danger py-1 px-2 mt-2 mb-0 small font-weight-bold" style="display: none;"></div>
                </div>
            </div>
            <div class="modal-footer py-2 px-3 bg-light">
                <button type="button" class="btn-erp btn-erp-outline btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnConfirmDomesticSave" class="btn-erp btn-erp-primary btn-sm font-weight-bold">
                    <i class="fas fa-check-circle mr-1"></i> Confirm & Pack to Domestic
                </button>
            </div>
        </div>
    </div>
</div>

<hr class="my-3">

<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center">
        <i class="fas fa-box-open mr-2 text-success"></i>
        <h6 class="font-weight-bold mb-0" style="color: #05421c;">Saved Domestic Boxes</h6>
        <span class="badge ml-2 px-2 py-1 font-weight-bold" style="background:#edf7e4; color:#05421c; border:1px solid #c3e6cb;">{{ $saved_domestic->count() }}</span>
    </div>
    <button type="button" class="btn-erp btn-erp-danger btn-xs btn-bulk-delete-domestic" style="display:none;">
        <i class="fas fa-trash-alt mr-1"></i> Delete Selected (<span class="selected-count">0</span>)
    </button>
</div>

<div class="table-responsive bg-white rounded border mb-3" style="max-height: 400px; overflow-y: auto;">
    <table class="table erp-table table-hover table-sm text-center align-middle mb-0">
        <thead>
            <tr>
                <th width="3%" class="text-center"><input type="checkbox" class="select-all-domestic"></th>
                <th>Box/Carton NO</th>
                <th>Design</th>
                <th>Size Set</th>
                <th>Color</th>
                <th>Pcs/Box</th>
                <th>Total Boxes</th>
                <th>Total Pcs</th>
                <th>Storage Rack</th>
                <th>Barcode</th>
                <th style="width: 80px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($saved_domestic as $dom)
            <tr>
                <td class="text-center align-middle"><input type="checkbox" class="domestic-chk" value="{{ $dom->id }}"></td>
                <td class="font-weight-bold align-middle" style="color: #05421c;">{{ $dom->box_no }} (Carton #{{ $dom->carton_no }})</td>
                <td class="align-middle font-weight-bold">{{ $dom->product->design_number ?? 'N/A' }}</td>
                <td class="align-middle">{{ $dom->sizeSet->name ?? 'N/A' }}</td>
                <td class="align-middle">{{ $dom->color->name ?? 'N/A' }}</td>
                <td class="align-middle">{{ $dom->quantity }} pcs</td>
                <td class="align-middle"><strong style="color: #05421c;">{{ $dom->total_boxes }}</strong></td>
                <td class="align-middle"><strong class="text-success">{{ $dom->quantity * $dom->total_boxes }} pcs</strong></td>
                <td class="align-middle">
                    @if($dom->rack)
                        <span class="badge badge-light border text-dark">{{ $dom->rack->storeroom->name ?? '' }} / {{ $dom->rack->name }}</span>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
                <td class="align-middle"><code>{{ $dom->barcode }}</code></td>
                <td class="align-middle text-center">
                    <button class="erp-action-btn erp-btn-delete btn-delete-domestic" data-id="{{ $dom->id }}" title="Delete Box">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-muted py-4">No domestic packing saved for this slip yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
