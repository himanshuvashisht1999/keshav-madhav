<h5 class="text-info border-bottom pb-2 mb-3"><i class="fas fa-random"></i> {{ $domesticTitle }}</h5>

<div class="card bg-light border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <!-- <h6 class="font-weight-bold mb-3 small text-uppercase text-primary">Quick Add</h6> -->
        <div class="row align-items-end">
             <div class="col-md-2 mb-2">
                <label class="small font-weight-bold">Design</label>
                <select id="domesticDesign" class="form-control form-control-sm select2">
                    <option value="">Select Design</option>
                    @foreach($designs_with_ids as $d)
                    <option value="{{ $d->design_number }}" data-product-id="{{ $d->id }}">{{ $d->design_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold">Size Set</label>
                <select id="domesticSizeSet" class="form-control form-control-sm select2">
                    <option value="">Select Size Set</option>
                    @foreach($filtered_size_sets as $set)
                        <option value="{{ $set->id }}" data-sizes="{{ $set->size_group }}">{{ $set->name }} ({{ $set->no_of_pcs }} pcs)</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold">Color</label>
                <select id="domesticColor" class="form-control form-control-sm select2">
                    <option value="">Select Color</option>
                    @foreach($all_master_colors as $color)
                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold">Quantity (Sets)</label>
                <input type="number" id="domesticQty" class="form-control form-control-sm" min="1" placeholder="e.g. 5" disabled>
                <small id="domesticQtyInfo" class="text-muted d-block mt-1" style="font-size: 0.72rem;">Select size set first</small>
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
                <button type="button" id="btnSaveDomesticDirect" class="btn btn-sm btn-primary w-100 font-weight-bold text-uppercase">
                    <i class="fas fa-save"></i> Save
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
