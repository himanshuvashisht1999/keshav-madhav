<h5 class="text-info border-bottom pb-2 mb-3"><i class="fas fa-random"></i> {{ $domesticTitle }}</h5>

<div class="card bg-light border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <h6 class="font-weight-bold mb-3 small text-uppercase text-primary">Quick Add</h6>
        <div class="row align-items-end">
             <div class="col-md-2 mb-2">
                <label class="small font-weight-bold">Design</label>
                <select id="domesticDesign" class="form-control form-control-sm">
                    <option value="">Select Design</option>
                    @php
                        $designsToRender = $isDomesticOrder ? $all_designs : $unique_designs;
                    @endphp
                    @foreach($designsToRender as $design)
                    <option value="{{ $design }}">{{ $design }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold">Size Set</label>
                <select id="domesticSizeSet" class="form-control form-control-sm" disabled>
                    <option value="">Select Size Set</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold">Color</label>
                <select id="domesticColor" class="form-control form-control-sm" disabled>
                    <option value="">Select Color</option>
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
                <button type="button" id="btnAddDomestic" class="btn btn-sm btn-info w-100 font-weight-bold text-uppercase">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive bg-white rounded shadow-sm border mb-3">
    <table class="table table-hover table-sm text-center align-middle mb-0" id="domesticTable">
        <thead class="bg-light">
            <tr>
                <th>Design</th>
                <th>Size Set</th>
                <th>Color</th>
                <th>Rack</th>
                <th>Qty</th>
                <th style="width: 80px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr id="domesticEmptyRow">
                <td colspan="6" class="text-muted py-4">No items added to diversion queue yet.</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mb-4">
    <button type="button" id="btnSaveDomesticBulk" class="btn btn-primary font-weight-bold">
        <i class="fas fa-save mr-1"></i> {{ $domesticSubmitBtn }}
    </button>
</div>

<hr class="my-4">

<h6 class="font-weight-bold mb-3">Saved Domestic Boxes <span class="badge badge-secondary">{{ $saved_domestic->count() }}</span></h6>
<div class="table-responsive bg-white rounded shadow-sm border mb-3" style="max-height: 400px; overflow-y: auto;">
    <table class="table table-hover table-sm text-center align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th>Box/Carton NO</th>
                <th>Design</th>
                <th>Size Set</th>
                <th>Color</th>
                <th>Total Pcs</th>
                <th>Storage Rack</th>
                <th>Barcode</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($saved_domestic as $dom)
            <tr>
                <td class="font-weight-bold text-primary">{{ $dom->box_no }} (Carton #{{ $dom->carton_no }})</td>
                <td>{{ $dom->product->design_number ?? 'N/A' }}</td>
                <td>{{ $dom->sizeSet->name ?? 'N/A' }}</td>
                <td>{{ $dom->color->name ?? 'N/A' }}</td>
                <td>{{ $dom->quantity }} pcs</td>
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
                <td colspan="8" class="text-muted py-4">No domestic packing saved for this slip yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
