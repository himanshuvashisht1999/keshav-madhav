@extends('admin.layouts.app')

@section('title', 'Dispatch #' . str_pad($dispatch->id, 5, '0', STR_PAD_LEFT))

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">
                    Dispatch #DSP-{{ str_pad($dispatch->id, 5, '0', STR_PAD_LEFT) }}
                </h1>
                <p class="erp-page-subtitle">
                    Party: <strong class="text-dark">
                        @if ($dispatch->party_type === 'vendor')
                            {{ $dispatch->vendor->name ?? 'N/A' }} <span class="badge-erp-status badge-erp-warning ml-1">Vendor</span>
                        @else
                            {{ $dispatch->shop->name ?? 'N/A' }}
                        @endif
                    </strong> 
                    <span class="mx-2 text-muted">|</span>
                    Agent: <strong class="text-dark">{{ $dispatch->agent->name ?? 'Direct' }}</strong>
                    
                    @if($dispatch->company_id)
                        <span class="mx-2 text-muted">|</span>
                        Company: <strong class="text-dark">{{ $dispatch->company->name ?? 'N/A' }}</strong>
                    @endif

                    @if($dispatch->bill_no)
                        <span class="mx-2 text-muted">|</span>
                        Bill no: <strong class="text-dark">{{ $dispatch->bill_no }}</strong>
                    @endif
                </p>
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-end" style="gap: 5px;">
                @if(!$isFabric)
                <div>
                    <select id="brandSelect" class="form-control form-control-sm erp-input" style="min-width: 120px; height: 31px;">
                        <option value="actual">Actual Brand</option>
                        <option value="2">Surgical</option>
                        <option value="1">Snapkid</option>
                    </select>
                </div>
                @endif
                
                <button type="button" class="btn-erp btn-erp-outline" data-toggle="modal" data-target="#editInvoiceModal" title="Edit Invoice">
                    <i class="fas fa-edit mr-1"></i> Edit Invoice
                </button>

                <button type="button" class="btn-erp btn-erp-outline" data-toggle="modal" data-target="#editItemsModal" title="Edit Items Prices">
                    <i class="fas fa-tags mr-1"></i> Edit Items
                </button>
                
                <a href="{{ route('admin.agent-orders.dispatches.return.create', $dispatch->id) }}" class="btn-erp btn-erp-outline text-danger border-danger" title="Sales Return">
                    <i class="fas fa-undo mr-1"></i> Sales Return
                </a>
                
                @php
                    $hasReturns = $dispatch->returns()->exists();
                @endphp
                @if($hasReturns)
                <button type="button" class="btn-erp btn-erp-secondary" onclick="alert('Cannot delete this dispatch because one or more sales returns are created against it. Please delete the sales return(s) first.')" title="Cannot delete: Sales Return exists" style="cursor: not-allowed; opacity: 0.6;">
                    <i class="fas fa-trash-alt mr-1"></i> Delete
                </button>
                @else
                <a href="{{ route('admin.agent-orders.dispatches.destroy', $dispatch->id) }}" class="btn-erp btn-erp-danger" onclick="return confirm('Are you sure you want to PERMANENTLY delete this dispatch? This will reverse stock and customer balance.')" title="Delete Dispatch">
                    <i class="fas fa-trash-alt mr-1"></i> Delete
                </a>
                @endif

                @if(!$isFabric)
                <div class="btn-group">
                    <button type="button" class="btn-erp btn-erp-primary" data-toggle="modal" data-target="#customPrnModal" title="Download Barcode (PRN)">
                        <i class="fas fa-barcode mr-1"></i> PRN
                    </button>
                    <button type="button" class="btn-erp btn-erp-primary dropdown-toggle dropdown-toggle-split px-2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Barcode Options">
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow border-0">
                        <a class="dropdown-item py-2 font-weight-bold" href="#" data-toggle="modal" data-target="#customPrnModal" style="color: #05421c;">
                            <i class="fas fa-sliders-h mr-2"></i> Custom PRN (Boxes / Pieces / MRP)
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.dispatches.generate-prn', $dispatch->id) }}">
                            <i class="fas fa-barcode text-muted mr-2"></i> Default PRN (Boxes, Standard)
                        </a>
                        <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.dispatches.download-barcode-pdf', $dispatch->id) }}" target="_blank">
                            <i class="fas fa-file-pdf text-danger mr-2"></i> Download Barcodes (PDF)
                        </a>
                    </div>
                </div>
                @endif

                <a href="{{ route('admin.agent-orders.dispatches.download-packing-slip', $dispatch->id) }}" id="packingSlipBtn" class="btn-erp btn-erp-outline">
                    <i class="fas fa-box-open mr-1"></i> Packing Slip
                </a>
                
                <a href="{{ route('admin.agent-orders.dispatches.send-whatsapp-packing-slip', $dispatch->id) }}" id="waPackingSlipBtn" class="btn-erp text-white" style="background-color: #25D366; border-color: #25D366;" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $dispatch->shop->phone ?? $dispatch->vendor->phone ?? '' }}'); if(phone) { window.location.href = this.href + (this.href.includes('?') ? '&' : '?') + 'phone=' + encodeURIComponent(phone); }">
                    <i class="fab fa-whatsapp mr-1"></i> WA Packing Slip
                </a>
                
                <a href="{{ route('admin.agent-orders.dispatches.download-invoice', $dispatch->id) }}" id="invoiceBtn" class="btn-erp btn-erp-primary">
                    <i class="fas fa-file-invoice mr-1"></i> Invoice
                </a>

                <a href="{{ route('admin.agent-orders.dispatches.download-retail-invoice', $dispatch->id) }}" id="retailInvoiceBtn" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-file-invoice-dollar mr-1"></i> Busy Invoice
                </a>

                <a href="{{ route('admin.agent-orders.dispatches.download-retail-invoice-excel', $dispatch->id) }}" id="retailInvoiceExcelBtn" class="btn-erp btn-erp-outline">
                    <i class="fas fa-file-excel text-success mr-1"></i> Busy Excel
                </a>

                <a href="{{ route('admin.agent-orders.dispatches.send-whatsapp-invoice', $dispatch->id) }}" id="waInvoiceBtn" class="btn-erp text-white" style="background-color: #25D366; border-color: #25D366;" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $dispatch->shop->phone ?? $dispatch->vendor->phone ?? '' }}'); if(phone) { window.location.href = this.href + (this.href.includes('?') ? '&' : '?') + 'phone=' + encodeURIComponent(phone); }">
                    <i class="fab fa-whatsapp mr-1"></i> WA Invoice
                </a>
            </div>
        </div>

        <!-- KPI Summary Row 1 -->
        @php
            $overallQty = $groupedItems->sum('total_qty');
            $overallFabricMeters = $fabricItems->sum('meter');
        @endphp
        <div class="row mb-2">
            <div class="col-md-3 mb-1">
                <div class="erp-card p-2 d-flex align-items-center">
                    <div class="mr-3 text-center" style="width: 40px; height: 40px; line-height: 40px; border-radius: 6px; background: #edf5ee; color: #05421c;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <div class="erp-label mb-0 text-muted">Dispatch date</div>
                        <div class="font-weight-bold text-dark">{{ $dispatch->dispatch_date ? date('d M Y, h:i A', strtotime($dispatch->dispatch_date)) : 'N/A' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-1">
                <div class="erp-card p-2 d-flex align-items-center">
                    <div class="mr-3 text-center" style="width: 40px; height: 40px; line-height: 40px; border-radius: 6px; background: #e0f2fe; color: #0284c7;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <div class="erp-label mb-0 text-muted">Total units</div>
                        <div class="font-weight-bold text-dark">
                            @if($overallQty > 0) {{ number_format($overallQty) }} PCs @endif
                            @if($overallQty > 0 && $overallFabricMeters > 0) + @endif
                            @if($overallFabricMeters > 0) {{ number_format($overallFabricMeters, 2) }} m @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-1">
                <div class="erp-card p-2 d-flex align-items-center">
                    <div class="mr-3 text-center" style="width: 40px; height: 40px; line-height: 40px; border-radius: 6px; background: #dcfce7; color: #16a34a;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="erp-label mb-0 text-muted">Status</div>
                        <div class="font-weight-bold text-success">{{ strtoupper($dispatch->status) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-1">
                <div class="erp-card p-2 d-flex align-items-center">
                    <div class="mr-3 text-center" style="width: 40px; height: 40px; line-height: 40px; border-radius: 6px; background: #f1f5f9; color: #475569;">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div>
                        <div class="erp-label mb-0 text-muted">Subtotal</div>
                        <div class="font-weight-bold text-dark">₹{{ number_format($dispatch->total_amount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Summary Row 2 -->
        <div class="row mb-2">
            <div class="col-md-3 mb-1">
                <div class="erp-card p-2 d-flex align-items-center">
                    <div class="mr-3 text-center" style="width: 40px; height: 40px; line-height: 40px; border-radius: 6px; background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div>
                        <div class="erp-label mb-0 text-muted">Extra discount</div>
                        <div class="font-weight-bold text-danger">₹{{ number_format($dispatch->discount_amount ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-1">
                <div class="erp-card p-2 d-flex align-items-center">
                    <div class="mr-3 text-center" style="width: 40px; height: 40px; line-height: 40px; border-radius: 6px; background: #fef3c7; color: #d97706;">
                        <i class="fas fa-percent"></i>
                    </div>
                    <div>
                        <div class="erp-label mb-0 text-muted">GST ({{ number_format($dispatch->gst_percentage ?? 5, 1) }}%)</div>
                        <div class="font-weight-bold text-warning">₹{{ number_format($dispatch->gst_amount, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-1">
                <div class="erp-card p-2 d-flex align-items-center">
                    <div class="mr-3 text-center" style="width: 40px; height: 40px; line-height: 40px; border-radius: 6px; background: #f1f5f9; color: #475569;">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <div class="erp-label mb-0 text-muted">Other charges</div>
                        <div class="font-weight-bold text-dark">₹{{ number_format($dispatch->other_charges ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-1">
                <div class="erp-card p-2 d-flex align-items-center" style="background: #edf5ee; border-color: #cce3d0;">
                    <div class="mr-3 text-center" style="width: 40px; height: 40px; line-height: 40px; border-radius: 6px; background: #05421c; color: #fff;">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div>
                        <div class="erp-label mb-0" style="color: #05421c;">Final amount</div>
                        <div class="font-weight-bold" style="color: #05421c; font-size: 1.15rem;">₹{{ number_format($dispatch->grand_total, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($dispatch->remark)
        <div class="erp-card mb-2 p-2 bg-light">
            <span class="erp-label text-muted d-block mb-1"><i class="fas fa-comment-dots mr-1"></i> Dispatch Remark:</span>
            <div class="font-weight-bold text-dark">{{ $dispatch->remark }}</div>
        </div>
        @endif

        <!-- Items Grouped By Order -->
        @foreach($dispatch->orders as $order)
            @php
                $sessionItems = $order->items->where('agent_order_dispatch_id', $dispatch->id);
                $sessionFabricItems = $fabricItems->where('agent_order_id', $order->id);
                
                if ($sessionItems->isEmpty() && $sessionFabricItems->isEmpty())
                    continue;

                $orderDispatchedQty = $sessionItems->sum('quantity');
                $orderDispatchedMeters = $sessionFabricItems->sum('meter');
                $orderSubtotal = $sessionItems->sum(function ($i) {
                    return $i->quantity * $i->selling_price;
                }) + $sessionFabricItems->sum(function ($i) {
                    return $i->meter * $i->selling_price;
                });
            @endphp

            <div class="erp-card p-0 mb-2">
                <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                        <i class="fas fa-shopping-cart mr-1"></i>
                        Order <a href="{{ route('admin.agent-orders.show', $order->id) }}" class="text-dark font-weight-bold" target="_blank">
                            #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                        </a>
                    </h6>
                    <div class="d-flex align-items-center" style="gap: 6px;">
                        <span class="badge-erp-status badge-erp-info font-weight-bold">
                            @if($orderDispatchedQty > 0) {{ number_format($orderDispatchedQty) }} PCs @endif
                            @if($orderDispatchedQty > 0 && $orderDispatchedMeters > 0) + @endif
                            @if($orderDispatchedMeters > 0) {{ number_format($orderDispatchedMeters, 2) }} m @endif
                        </span>
                        <span class="badge-erp-status badge-erp-primary font-weight-bold">
                            ₹{{ number_format($orderSubtotal, 2) }}
                        </span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table erp-table table-bordered table-hover mb-0">
                        <thead>
                            @if($sessionItems->isNotEmpty())
                                <tr>
                                    <th>Design #</th>
                                    <th>Product name</th>
                                    <th>Color</th>
                                    <th>Size set</th>
                                    <th class="text-center" style="width: 80px;">Boxes</th>
                                    <th class="text-center" style="width: 100px;">Total pcs</th>
                                    <th class="text-right" style="width: 110px;">Price/pc</th>
                                    <th class="text-right" style="width: 130px;">Total amount</th>
                                </tr>
                            @else
                                <tr>
                                    <th>Roll #</th>
                                    <th>Fabric name</th>
                                    <th class="text-center" style="width: 120px;">Batch no</th>
                                    <th class="text-center" style="width: 100px;">Meters</th>
                                    <th class="text-right" style="width: 110px;">Price/m</th>
                                    <th class="text-right" style="width: 130px;">Total amount</th>
                                </tr>
                            @endif
                        </thead>
                        <tbody>
                            @foreach($sessionItems as $item)
                                <tr>
                                    <td class="font-weight-bold text-dark">{{ $item->design_number }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->color_name }} ({{ $item->color_id }})</td>
                                    <td>{{ $item->size_set_name ?? 'N/A' }}</td>
                                    <td class="text-center font-weight-bold">{{ number_format($item->box_qty, 0) }}</td>
                                    <td class="text-center font-weight-bold text-dark">{{ number_format($item->quantity, 0) }}</td>
                                    <td class="text-right text-muted">₹{{ number_format($item->selling_price, 2) }}</td>
                                    <td class="text-right font-weight-bold text-dark">₹{{ number_format($item->quantity * $item->selling_price, 2) }}</td>
                                </tr>
                            @endforeach

                            @foreach($sessionFabricItems as $fItem)
                                <tr>
                                    <td><span class="badge-erp-status badge-erp-info font-weight-bold">{{ $fItem->roll_number }}</span></td>
                                    <td><div class="font-weight-bold text-dark">{{ $fItem->fabric_name }}</div></td>
                                    <td class="text-center">{{ $fItem->batch_no }}</td>
                                    <td class="text-center font-weight-bold text-dark">{{ number_format($fItem->meter, 2) }} m</td>
                                    <td class="text-right text-muted">₹{{ number_format($fItem->selling_price, 2) }}</td>
                                    <td class="text-right font-weight-bold text-dark">₹{{ number_format($fItem->meter * $fItem->selling_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <!-- Edit Invoice Modal -->
        <div class="modal fade" id="editInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="editInvoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow border-0" style="border-radius: 8px;">
                    <div class="modal-header py-2" style="background: #05421c; color: #fff; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                        <h6 class="modal-title font-weight-bold" id="editInvoiceModalLabel">
                            <i class="fas fa-edit mr-1"></i> Update Dispatch Invoice
                        </h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="editInvoiceForm">
                        @csrf
                        <div class="modal-body p-3">
                            <div class="form-group mb-2">
                                <label class="erp-label">Dispatch date</label>
                                <input type="datetime-local" class="form-control form-control-sm erp-input" id="dispatch_date" name="dispatch_date" value="{{ date('Y-m-d\TH:i', strtotime($dispatch->dispatch_date)) }}" required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="erp-label">Company</label>
                                <select class="form-control form-control-sm erp-input select2" id="company_id" name="company_id">
                                    <option value="">Select Company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ $dispatch->company_id == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="erp-label">Bill number</label>
                                <input type="text" class="form-control form-control-sm erp-input" id="bill_no" name="bill_no" value="{{ $dispatch->bill_no }}" placeholder="Enter Bill Number">
                            </div>
                            <div class="form-group mb-2">
                                <label class="erp-label">Subtotal amount (Pcs * Price)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm erp-input" id="total_amount" name="total_amount" value="{{ $dispatch->total_amount }}" required>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 mb-2">
                                    <label class="erp-label">Extra discount %</label>
                                    @php
                                        $discPct = ($dispatch->total_amount > 0 && $dispatch->discount_amount > 0) ? ($dispatch->discount_amount / $dispatch->total_amount * 100) : 0;
                                    @endphp
                                    <input type="number" step="any" class="form-control form-control-sm erp-input" id="discount_percentage" name="discount_percentage" value="{{ round($discPct, 6) }}">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="erp-label">Extra discount amount</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm erp-input" id="discount_amount" name="discount_amount" value="{{ $dispatch->discount_amount ?? 0 }}">
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="erp-label">Other charges</label>
                                <input type="number" step="0.01" class="form-control form-control-sm erp-input" id="other_charges" name="other_charges" value="{{ $dispatch->other_charges ?? 0 }}">
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 mb-2">
                                    <label class="erp-label">GST %</label>
                                    <input type="number" step="any" class="form-control form-control-sm erp-input" id="gst_percentage" name="gst_percentage" value="{{ $dispatch->gst_percentage ?? 5 }}">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="erp-label">GST amount</label>
                                    <input type="number" step="any" class="form-control form-control-sm erp-input" id="gst_amount_input" value="{{ round($dispatch->gst_amount, 2) }}">
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="erp-label">Remark</label>
                                <textarea name="remark" class="form-control form-control-sm erp-input" rows="2">{{ $dispatch->remark }}</textarea>
                            </div>
                            <div class="p-2 rounded text-center border mt-3" style="background: #edf5ee; border-color: #cce3d0 !important;">
                                <span class="erp-label d-block text-muted mb-0">Final Grand Total</span>
                                <h4 class="mb-0 font-weight-bold" style="color: #05421c;" id="grand_total_display">₹{{ number_format($dispatch->grand_total, 2) }}</h4>
                            </div>
                        </div>
                        <div class="modal-footer p-2 bg-light">
                            <button type="button" class="btn-erp btn-erp-secondary mr-2" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-erp btn-erp-primary font-weight-bold">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Items Modal -->
        <div class="modal fade" id="editItemsModal" tabindex="-1" role="dialog" aria-labelledby="editItemsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content shadow border-0" style="border-radius: 8px;">
                    <div class="modal-header py-2" style="background: #05421c; color: #fff; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                        <h6 class="modal-title font-weight-bold" id="editItemsModalLabel">
                            <i class="fas fa-tags mr-1"></i> Update Items Price / Discount
                        </h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="editItemsForm">
                        @csrf
                        <div class="modal-body p-3" style="max-height: 65vh; overflow-y: auto;">
                            <div class="form-row mb-2">
                                @php
                                    $uniqueBrands = $groupedItems->unique('brand_id');
                                @endphp
                                @foreach($uniqueBrands as $brand)
                                    @php
                                        $firstItemOfBrand = $groupedItems->where('brand_id', $brand->brand_id)->first();
                                        $brandDiscount = 0;
                                        if($firstItemOfBrand && $firstItemOfBrand->mrp > 0) {
                                            $brandDiscount = (($firstItemOfBrand->mrp - $firstItemOfBrand->selling_price) / $firstItemOfBrand->mrp) * 100;
                                        }
                                    @endphp
                                    <div class="col-md-3 mb-2">
                                        <label class="erp-label">{{ $brand->brand->name ?? 'Brand' }} Discount %</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="any" class="form-control form-control-sm erp-input brand-discount-input" data-brand="{{ $brand->brand_id }}" value="{{ round($brandDiscount, 2) }}">
                                            <div class="input-group-append">
                                                <button type="button" class="btn-erp btn-erp-primary apply-brand-discount" data-brand="{{ $brand->brand_id }}">Apply</button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted d-block mb-2 border-bottom pb-2">Apply a discount to instantly update all items belonging to that brand.</small>
                            <div class="table-responsive">
                                <table class="table erp-table table-bordered table-hover mb-0" id="editItemsTable">
                                    <thead>
                                        <tr>
                                            <th>Design #</th>
                                            <th>Color</th>
                                            <th>Size set</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-right">MRP</th>
                                            <th class="text-center" style="width: 140px;">Discount (%)</th>
                                            <th class="text-center" style="width: 140px;">Sale price (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($groupedItems as $index => $item)
                                            <tr class="item-edit-row" data-brand="{{ $item->brand_id }}">
                                                <td class="font-weight-bold text-dark">{{ $item->design_number }}</td>
                                                <td>{{ $item->color_name }} ({{ $item->color_id }})</td>
                                                <td>{{ $item->size_set_name ?? 'N/A' }}</td>
                                                <td class="text-center font-weight-bold">{{ $item->total_qty }}</td>
                                                <td class="text-right text-muted item-mrp" data-mrp="{{ $item->mrp }}">₹{{ number_format($item->mrp, 2) }}</td>
                                                <td class="text-center">
                                                    @php
                                                        $calculatedDiscount = $item->mrp > 0 ? (($item->mrp - $item->selling_price) / $item->mrp) * 100 : 0;
                                                    @endphp
                                                    <input type="number" step="any" class="form-control form-control-sm erp-input text-center item-discount mx-auto" value="{{ round($calculatedDiscount, 2) }}">
                                                </td>
                                                <td class="text-center">
                                                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                                    <input type="hidden" name="items[{{ $index }}][color_id]" value="{{ $item->color_id }}">
                                                    <input type="hidden" name="items[{{ $index }}][size_set_id]" value="{{ $item->size_set_id }}">
                                                    <input type="number" step="0.01" class="form-control form-control-sm erp-input text-center item-sale-price mx-auto" name="items[{{ $index }}][selling_price]" value="{{ $item->selling_price }}" required>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info border-0 mt-2 mb-0 small">
                                <i class="fas fa-info-circle mr-1"></i> Updating the prices here will automatically recalculate the dispatch total, GST, and the party balance.
                            </div>
                        </div>
                        <div class="modal-footer p-2 bg-light">
                            <button type="button" class="btn-erp btn-erp-secondary mr-2" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-erp btn-erp-primary font-weight-bold">Update Prices</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Custom PRN Modal -->
        <div class="modal fade" id="customPrnModal" tabindex="-1" role="dialog" aria-labelledby="customPrnModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow border-0" style="border-radius: 8px;">
                    <div class="modal-header py-2" style="background: #05421c; color: #fff; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                        <h6 class="modal-title font-weight-bold" id="customPrnModalLabel">
                            <i class="fas fa-barcode mr-1"></i> Download Barcodes (PRN)
                        </h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.agent-orders.dispatches.generate-prn', $dispatch->id) }}" method="GET" target="_blank">
                        <div class="modal-body p-3">
                            @php
                                $dItems = DB::table('agent_order_items')->where('agent_order_dispatch_id', $dispatch->id)->get();
                                $dTotalBoxes = $dItems->sum('box_qty') ?: $dItems->sum('scanned_box_qty');
                                $dTotalPieces = $dItems->sum('quantity');
                            @endphp
                            
                            <div class="form-row mb-3">
                                <div class="col-6">
                                    <div class="p-2 border rounded text-center bg-light">
                                        <small class="text-muted d-block text-uppercase font-weight-bold erp-label">Total boxes</small>
                                        <strong class="font-weight-bold text-dark" style="font-size: 1.15rem;">{{ number_format($dTotalBoxes) }}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded text-center bg-light">
                                        <small class="text-muted d-block text-uppercase font-weight-bold erp-label">Total pieces</small>
                                        <strong class="font-weight-bold text-dark" style="font-size: 1.15rem;">{{ number_format($dTotalPieces) }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="erp-label mb-2">1. Select Barcode Quantity Mode</label>
                                <div class="d-flex" style="gap: 8px;">
                                    <label class="border rounded p-2 flex-fill d-flex align-items-center mb-0 prn-mode-card" style="cursor: pointer; background: #edf5ee; border-color: #05421c !important;">
                                        <input type="radio" name="mode" value="boxes" checked class="mr-2 prn-mode-radio">
                                        <div>
                                            <div class="font-weight-bold text-dark"><i class="fas fa-boxes mr-1" style="color: #05421c;"></i> Box Wise</div>
                                            <small class="text-muted">1 label per box (Total: {{ $dTotalBoxes }} barcodes)</small>
                                        </div>
                                    </label>
                                    <label class="border rounded p-2 flex-fill d-flex align-items-center mb-0 prn-mode-card" style="cursor: pointer; background: #fff;">
                                        <input type="radio" name="mode" value="pieces" class="mr-2 prn-mode-radio">
                                        <div>
                                            <div class="font-weight-bold text-dark"><i class="fas fa-tshirt text-info mr-1"></i> Piece Wise</div>
                                            <small class="text-muted">1 label per piece (Total: {{ $dTotalPieces }} barcodes)</small>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="erp-label mb-1" for="prnMultiplierInput">2. MRP Multiplier (Sales Price &times; Multiplier)</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="fas fa-times text-muted"></i></span>
                                    </div>
                                    <input type="number" step="any" min="0.1" name="multiplier" id="prnMultiplierInput" value="2" class="form-control form-control-sm erp-input font-weight-bold text-dark" required>
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    Printed MRP will be calculated as: <code>Sales Price &times; Multiplier</code>.
                                </small>
                            </div>

                            <div class="alert alert-light border mb-0 text-muted small">
                                <i class="fas fa-shield-alt text-success mr-1"></i> <strong>Barcode Structure:</strong> Code-128 format and encoding remain identical and completely unchanged.
                            </div>
                        </div>
                        <div class="modal-footer p-2 bg-light">
                            <button type="button" class="btn-erp btn-erp-secondary mr-2" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-erp btn-erp-primary font-weight-bold">
                                <i class="fas fa-download mr-1"></i> Download PRN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const packingBaseUrl = "{{ route('admin.agent-orders.dispatches.download-packing-slip', $dispatch->id) }}";
            const invoiceBaseUrl = "{{ route('admin.agent-orders.dispatches.download-invoice', $dispatch->id) }}";
            const waPackingBaseUrl = "{{ route('admin.agent-orders.dispatches.send-whatsapp-packing-slip', $dispatch->id) }}";
            const waInvoiceBaseUrl = "{{ route('admin.agent-orders.dispatches.send-whatsapp-invoice', $dispatch->id) }}";

            function updateUrls() {
                const val = $('#brandSelect').val();
                let packingUrl = packingBaseUrl;
                let invoiceUrl = invoiceBaseUrl;
                let waPackingUrl = waPackingBaseUrl;
                let waInvoiceUrl = waInvoiceBaseUrl;

                if (val === 'actual') {
                    packingUrl += '?type=actual';
                    invoiceUrl += '?type=actual';
                    waPackingUrl += '?type=actual';
                    waInvoiceUrl += '?type=actual';
                } else if (val) {
                    packingUrl += '?brand_id=' + val;
                    invoiceUrl += '?brand_id=' + val;
                    waPackingUrl += '?brand_id=' + val;
                    waInvoiceUrl += '?brand_id=' + val;
                }

                $('#packingSlipBtn').attr('href', packingUrl);
                $('#invoiceBtn').attr('href', invoiceUrl);
                $('#waPackingSlipBtn').attr('href', waPackingUrl);
                $('#waInvoiceBtn').attr('href', waInvoiceUrl);
            }

            // On change
            $('#brandSelect').on('change', updateUrls);

            // 🔥 Run once on page load
            updateUrls();

            // Invoice Modal Calculations
            function calculateInvoice(source) {
                const totalAmount = Math.ceil(parseFloat($('#total_amount').val()) || 0);
                let discountAmount = Math.ceil(parseFloat($('#discount_amount').val()) || 0);
                let discountPercentage = parseFloat($('#discount_percentage').val()) || 0;

                if (source === 'discount_percentage') {
                    discountAmount = Math.ceil(totalAmount * (discountPercentage / 100));
                    $('#discount_amount').val(discountAmount.toFixed(2));
                } else if (source === 'discount_amount' || source === 'total_amount') {
                    if (totalAmount > 0) {
                        discountPercentage = (discountAmount / totalAmount) * 100;
                        $('#discount_percentage').val(discountPercentage.toFixed(6));
                    } else {
                        $('#discount_percentage').val(0);
                    }
                }
                
                discountAmount = Math.ceil(parseFloat($('#discount_amount').val()) || 0);

                const otherCharges = Math.ceil(parseFloat($('#other_charges').val()) || 0);
                const taxableAmount = totalAmount - discountAmount;

                let gstPercentage = parseFloat($('#gst_percentage').val()) || 0;
                let gstAmount = Math.ceil(parseFloat($('#gst_amount_input').val()) || 0);

                if (source === 'gst_percentage') {
                    gstAmount = Math.ceil(taxableAmount * (gstPercentage / 100));
                    $('#gst_amount_input').val(gstAmount.toFixed(2));
                } else if (source === 'gst_amount') {
                    if (taxableAmount > 0) {
                        gstPercentage = (gstAmount / taxableAmount) * 100;
                        $('#gst_percentage').val(gstPercentage.toFixed(6));
                    } else {
                        $('#gst_percentage').val(0);
                    }
                } else {
                    gstAmount = Math.ceil(taxableAmount * (gstPercentage / 100));
                    $('#gst_amount_input').val(gstAmount.toFixed(2));
                }

                const grandTotal = Math.ceil(taxableAmount + gstAmount + otherCharges);
                $('#grand_total_display').text('₹' + grandTotal.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }

            $('#discount_percentage').on('input', function() {
                calculateInvoice('discount_percentage');
            });

            $('#discount_amount').on('input', function() {
                calculateInvoice('discount_amount');
            });

            $('#gst_percentage').on('input', function() {
                calculateInvoice('gst_percentage');
            });

            $('#gst_amount_input').on('input', function() {
                calculateInvoice('gst_amount');
            });

            $('#total_amount').on('input', function() {
                calculateInvoice('total_amount');
            });

            $('#other_charges').on('input', function() {
                calculateInvoice('default');
            });

            // Invoice Modal Submission
            $('#editInvoiceForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $(this).find('button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');

                $.ajax({
                    url: "{{ route('admin.agent-orders.dispatches.update-invoice', $dispatch->id) }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message);
                            btn.prop('disabled', false).text('Save Changes');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong. Please try again.');
                        btn.prop('disabled', false).text('Save Changes');
                    }
                });
            });

            // Edit Items Modal Submission
            $('#editItemsForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $(this).find('button[type="submit"]');
                const originalHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');

                $.ajax({
                    url: "{{ route('admin.agent-orders.dispatches.update-items', $dispatch->id) }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message);
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Something went wrong. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Handle Item Discount calculation
            $('.item-discount').on('input', function() {
                let row = $(this).closest('.item-edit-row');
                let mrp = parseFloat(row.find('.item-mrp').data('mrp')) || 0;
                let discount = parseFloat($(this).val()) || 0;
                let salePrice = Math.ceil(mrp - (mrp * (discount / 100)));
                row.find('.item-sale-price').val(salePrice);
            });

            // Handle Item Sale Price calculation back to Discount
            $('.item-sale-price').on('input', function() {
                let row = $(this).closest('.item-edit-row');
                let mrp = parseFloat(row.find('.item-mrp').data('mrp')) || 0;
                let salePrice = parseFloat($(this).val()) || 0;
                if(mrp > 0) {
                    let discount = ((mrp - salePrice) / mrp) * 100;
                    row.find('.item-discount').val(discount.toFixed(2));
                }
            });

            // Handle Brand-wise Discount Apply
            $('.apply-brand-discount').on('click', function() {
                let brandId = $(this).data('brand');
                let discountInput = $('.brand-discount-input[data-brand="' + brandId + '"]').val();
                let globalDiscount = parseFloat(discountInput);
                
                if(isNaN(globalDiscount)) return;
                
                $('.item-edit-row[data-brand="' + brandId + '"]').each(function() {
                    let mrp = parseFloat($(this).find('.item-mrp').data('mrp')) || 0;
                    if(mrp > 0) {
                        $(this).find('.item-discount').val(globalDiscount);
                        let salePrice = Math.ceil(mrp - (mrp * (globalDiscount / 100)));
                        $(this).find('.item-sale-price').val(salePrice);
                    }
                });
            });

            // Brand selection for print/download buttons
            $('#brandSelect').on('change', function() {
                let brandId = $(this).val();
                let params = brandId !== 'actual' ? '?brand_id=' + brandId : '?type=actual';

                let packingSlipBase = "{{ route('admin.agent-orders.dispatches.download-packing-slip', $dispatch->id) }}";
                let waPackingSlipBase = "{{ route('admin.agent-orders.dispatches.send-whatsapp-packing-slip', $dispatch->id) }}";
                let invoiceBase = "{{ route('admin.agent-orders.dispatches.download-invoice', $dispatch->id) }}";
                let waInvoiceBase = "{{ route('admin.agent-orders.dispatches.send-whatsapp-invoice', $dispatch->id) }}";
                let retailInvoiceBase = "{{ route('admin.agent-orders.dispatches.download-retail-invoice', $dispatch->id) }}";
                let retailExcelBase = "{{ route('admin.agent-orders.dispatches.download-retail-invoice-excel', $dispatch->id) }}";

                $('#packingSlipBtn').attr('href', packingSlipBase + params);
                $('#waPackingSlipBtn').attr('href', waPackingSlipBase + params);
                $('#invoiceBtn').attr('href', invoiceBase + params);
                $('#waInvoiceBtn').attr('href', waInvoiceBase + params);
                $('#retailInvoiceBtn').attr('href', retailInvoiceBase + params);
                $('#retailInvoiceExcelBtn').attr('href', retailExcelBase + params);
            });

            // Toggle active styling on PRN mode radio selection
            $(document).on('change', '.prn-mode-radio', function() {
                $('.prn-mode-card').css({'border-color': '#e2e8f0', 'background': '#fff'});
                $(this).closest('.prn-mode-card').css({'border-color': '#05421c', 'background': '#edf5ee'});
            });
        });
    </script>
@endpush