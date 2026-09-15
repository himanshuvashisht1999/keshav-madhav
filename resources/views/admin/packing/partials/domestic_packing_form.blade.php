<h5 class="text-info border-bottom pb-2 mb-3"><i class="fas fa-random"></i> {{ $domesticTitle }}</h5>

<div class="card bg-light border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <!-- <h6 class="font-weight-bold mb-3 small text-uppercase text-primary">Quick Add</h6> -->
        <div class="row align-items-end">
             <div class="col-md-3 mb-2">
                <label class="small font-weight-bold">Design</label>
                <select id="domesticDesign" class="form-control form-control-sm select2">
                    <option value="">Select Design</option>
                    @foreach($designs_with_ids as $d)
                    <option value="{{ $d->design_number }}" data-product-id="{{ $d->id }}">{{ $d->design_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold">Size Set</label>
                <select id="domesticSizeSet" class="form-control form-control-sm select2" disabled>
                    <option value="">Select Size Set</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold">Color</label>
                <select id="domesticColor" class="form-control form-control-sm select2" disabled>
                    <option value="">Select Color</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold">Storage Rack</label>
                <select id="domesticRack" class="form-control form-control-sm select2">
                    <option value="">Select Storage</option>
                    @foreach($storerooms as $store)
                        <optgroup label="{{ $store->name }}">
                            @foreach($store->racks as $rack)
                                <option value="{{ $rack->id }}">{{ $rack->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="button" id="btnOpenDomesticModal" class="btn btn-sm btn-primary w-100 font-weight-bold text-uppercase" disabled>
                    <i class="fas fa-boxes"></i> Allocate & Pack
                </button>
            </div>
        </div>
    </div>
</div>

<!-- DOMESTIC SIZE ALLOCATION & PACKING MODAL -->
<div class="modal fade" id="modalDomesticSizeMapping" tabindex="-1" role="dialog" aria-labelledby="modalDomesticSizeMappingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-2 px-3">
                <h5 class="modal-title font-weight-bold" id="modalDomesticSizeMappingLabel">
                    <i class="fas fa-random mr-2"></i> Allocate Corporate Lots to Domestic Size Set
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <!-- Summary bar -->
                <div class="d-flex flex-wrap align-items-center justify-content-between p-2 mb-3 bg-light rounded border" style="font-size: 0.88rem; gap: 8px;">
                    <div><strong>Design:</strong> <span id="mdlDomDesign" class="badge badge-primary px-2 py-1"></span></div>
                    <div><strong>Domestic Size Set:</strong> <span id="mdlDomSizeSet" class="badge badge-info px-2 py-1"></span></div>
                    <div><strong>Color:</strong> <span id="mdlDomColor" class="badge badge-secondary px-2 py-1"></span></div>
                    <div><strong>Storage Rack:</strong> <span id="mdlDomRack" class="badge badge-warning px-2 py-1"></span></div>
                </div>

                <!-- Available Corporate Lot Stock badges -->
                <div class="mb-3">
                    <label class="small font-weight-bold text-muted text-uppercase mb-1 d-block">
                        <i class="fas fa-layer-group mr-1"></i> Available Corporate Lot Stock in Slip:
                    </label>
                    <div id="mdlDomAvailableLotsBadges" class="d-flex flex-wrap" style="gap: 6px;">
                        <!-- dynamic badges -->
                    </div>
                </div>

                <!-- Allocation Table -->
                <div class="table-responsive border rounded mb-3">
                    <table class="table table-sm table-hover text-center align-middle mb-0" id="tblDomesticSizeMapping">
                        <thead class="bg-light">
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
                <div class="card bg-light border-0 p-3 mb-2">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <label class="font-weight-bold mb-0 text-dark">
                                <i class="fas fa-box-open text-primary mr-1"></i> Total Domestic Boxes to Pack:
                            </label>
                            <small class="text-muted d-block">Number of complete boxes/sets to create</small>
                        </div>
                        <div class="col-md-3">
                            <input type="number" id="mdlDomesticBoxQty" class="form-control font-weight-bold text-center text-primary" min="1" value="1" style="font-size: 1.1rem;">
                        </div>
                        <div class="col-md-4 text-md-right mt-2 mt-md-0">
                            <span id="mdlDomesticMaxBadge" class="badge badge-success px-3 py-2" style="font-size: 0.9rem;">
                                Max: 0 Boxes
                            </span>
                        </div>
                    </div>
                    <div id="mdlDomesticError" class="alert alert-danger py-1 px-2 mt-2 mb-0 small font-weight-bold" style="display: none;"></div>
                </div>
            </div>
            <div class="modal-footer py-2 px-3 bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnConfirmDomesticSave" class="btn btn-primary btn-sm font-weight-bold">
                    <i class="fas fa-check-circle mr-1"></i> Confirm & Pack to Domestic
                </button>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

<div class="d-flex align-items-center mb-3">
    <h6 class="font-weight-bold mb-0">Saved Domestic Boxes <span class="badge badge-secondary">{{ $saved_domestic->count() }}</span></h6>
    <button type="button" class="btn btn-xs btn-danger ml-3 btn-bulk-delete-domestic" style="display:none;"><i class="fas fa-trash-alt"></i> Delete Selected (<span class="selected-count">0</span>)</button>
</div>
<div class="table-responsive bg-white rounded shadow-sm border mb-3" style="max-height: 400px; overflow-y: auto;">
    <table class="table table-hover table-sm text-center align-middle mb-0">
        <thead class="bg-light">
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
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($saved_domestic as $dom)
            <tr>
                <td class="text-center"><input type="checkbox" class="domestic-chk" value="{{ $dom->id }}"></td>
                <td class="font-weight-bold text-primary">{{ $dom->box_no }} (Carton #{{ $dom->carton_no }})</td>
                <td>{{ $dom->product->design_number ?? 'N/A' }}</td>
                <td>{{ $dom->sizeSet->name ?? 'N/A' }}</td>
                <td>{{ $dom->color->name ?? 'N/A' }}</td>
                <td>{{ $dom->quantity }} pcs</td>
                <td><strong class="text-primary">{{ $dom->total_boxes }}</strong></td>
                <td><strong class="text-success">{{ $dom->quantity * $dom->total_boxes }} pcs</strong></td>
                <td>
                    @if($dom->rack)
                        <span class="badge badge-info">{{ $dom->rack->storeroom->name ?? '' }} / {{ $dom->rack->name }}</span>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
                <td><code>{{ $dom->barcode }}</code></td>
                <td>
                    <button class="btn btn-xs btn-outline-danger btn-delete-domestic" data-id="{{ $dom->id }}">
                        <i class="fas fa-trash-alt"></i> Delete
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
