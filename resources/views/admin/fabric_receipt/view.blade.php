@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-truck-loading text-primary"></i> Fabric Shipment Details: 
            <span class="erp-badge-yellow ml-2 px-2 py-0 rounded">{{ $data->shipment_id }}</span>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.fabric_receipt.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="{{ route('admin.fabric_receipt.return', ['id' => $data->id]) }}" class="btn-erp btn-erp-danger">
                <i class="fas fa-undo"></i> Return Shipment
            </a>
            <a href="{{ route('admin.fabric_receipt.download_report', ['id' => $data->id]) }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-download"></i> Download Report
            </a>
        </div>
    </div>

    <!-- Financial Metric Summary Strip -->
    <div class="row mb-2">
        <div class="col-md-2 col-sm-4 col-6 mb-1">
            <div class="erp-card erp-metric-card-neutral p-2 text-center h-100">
                <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                    <i class="fas fa-coins mr-1"></i> Base Amount
                </div>
                <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-md); color: var(--erp-text-heading);">
                    Rs. {{ number_format($data->amount ?? 0, 2) }}
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-1">
            <div class="erp-card erp-metric-card-neutral p-2 text-center h-100">
                <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                    <i class="fas fa-percentage mr-1"></i> GST ({{ $data->gst_percentage ?? 0 }}%)
                </div>
                <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-md); color: var(--erp-yellow-dark);">
                    Rs. {{ number_format($data->gst_amount ?? 0, 2) }}
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-1">
            <div class="erp-card erp-metric-card-neutral p-2 text-center h-100">
                <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                    <i class="fas fa-receipt mr-1"></i> Other Charges
                </div>
                <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-md); color: var(--erp-text-main);">
                    Rs. {{ number_format($data->other_charges ?? 0, 2) }}
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-6 mb-1">
            <div class="erp-card erp-metric-card-primary p-2 text-center h-100">
                <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-green-primary);">
                    <i class="fas fa-wallet mr-1"></i> Total Amount
                </div>
                <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-lg); color: var(--erp-text-heading);">
                    Rs. {{ number_format($data->total_amount ?? 0, 2) }}
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12 mb-1">
            <div class="erp-card erp-metric-card-yellow p-2 text-center h-100">
                <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-yellow-dark);">
                    <i class="fas fa-layer-group mr-1"></i> Total Rolls Received
                </div>
                <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-lg); color: var(--erp-text-heading);">
                    {{ $data->details->count() }} Rolls
                </div>
            </div>
        </div>
    </div>

    <!-- Voucher Details & Challan Attachments Side-by-Side -->
    <div class="row mb-2">
        <!-- Left: Shipment Info -->
        <div class="col-lg-7 mb-2">
            <div class="erp-card h-100 mb-0">
                <div class="erp-card-header py-1">
                    <span class="erp-card-title">
                        <i class="fas fa-file-invoice"></i> Shipment & Vendor Information
                    </span>
                    @php
                        $paid = $data->paid_amount;
                        $total = $data->total_amount;
                        $is_paid = ($paid >= $total && $total > 0);
                    @endphp
                    <div>
                        @if($is_paid)
                            <a href="{{ route('admin.payment.history.index', ['paymentable_type' => 'App\Models\FabricReceipt', 'paymentable_id' => $data->id]) }}">
                                <span class="badge badge-success px-2 py-1 font-weight-bold">PAID</span>
                            </a>
                        @else
                            <span class="badge badge-danger px-2 py-1 font-weight-bold">UNPAID</span>
                            <span class="ml-1 font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                                (Paid: Rs. {{ number_format($paid, 2) }})
                            </span>
                        @endif
                    </div>
                </div>
                <div class="erp-card-body p-2">
                    <table class="erp-info-table">
                        <tr>
                            <td class="label-col" style="width: 125px;">Shipment No.</td>
                            <td class="val-col" style="width: 35%;">: <b>{{ $data->shipment_id }}</b></td>
                            <td class="label-col" style="width: 110px;">Bill Number</td>
                            <td class="val-col" style="white-space: nowrap;">: <b class="text-dark">{{ $data->bill_no ?? 'N/A' }}</b></td>
                        </tr>
                        <tr>
                            <td class="label-col">Vendor</td>
                            <td class="val-col">: <b>{{ $data->vendor->name ?? 'N/A' }}</b></td>
                            <td class="label-col">Warehouse</td>
                            <td class="val-col">: {{ $data->cutting_master->cutting_master_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="label-col">Receipt Date</td>
                            <td class="val-col">: {{ \Carbon\Carbon::parse($data->time)->format('d M Y') }}</td>
                            <td class="label-col">Received By</td>
                            <td class="val-col">: {{ !empty($data->received_by) ? $data->received_by : 'Not Specified' }}</td>
                        </tr>
                        @php
                            $linkedPo = $data->details->first(function($d) { return !empty($d->purchase_order_id); })?->purchase_order;
                        @endphp
                        @if($linkedPo)
                        <tr>
                            <td class="label-col">Linked PO</td>
                            <td class="val-col" colspan="3">: 
                                <a href="{{ route('admin.purchase_order.view', ['id' => $linkedPo->id]) }}" class="font-weight-bold text-success" target="_blank">
                                    {{ $linkedPo->sku }} <i class="fas fa-external-link-alt" style="font-size: 10px;"></i>
                                </a>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Challan Slip & Other Images -->
        <div class="col-lg-5 mb-2">
            <div class="erp-card h-100 mb-0">
                <div class="erp-card-header py-1">
                    <span class="erp-card-title">
                        <i class="fas fa-images"></i> Attached Documents & Images
                    </span>
                    <div class="d-flex align-items-center" style="gap: 4px;">
                        <button type="button" class="btn-erp btn-erp-outline py-0" onclick="$('#uploadChallanModal').modal('show')" title="Upload/Change Challan">
                            <i class="fas fa-file-upload"></i> Challan
                        </button>
                        <button type="button" class="btn-erp btn-erp-outline py-0" onclick="$('#uploadOtherImagesModal').modal('show')" title="Add More Images">
                            <i class="fas fa-plus"></i> Images
                        </button>
                    </div>
                </div>
                <div class="erp-card-body p-2">
                    <div class="d-flex align-items-center mb-2" style="gap: 10px;">
                        <span class="font-weight-bold" style="font-size: var(--erp-font-sm); min-width: 80px; color: var(--erp-text-muted);">CHALLAN:</span>
                        @php
                            $rawChallan = $data->getRawOriginal('challan_photo');
                            $hasChallan = !empty($rawChallan) && $rawChallan !== 'image-placeholder.png';
                            $challanExists = $hasChallan && file_exists(public_path('assets/receipts/challan-image/' . $rawChallan));
                            $isPdf = $hasChallan && str_contains(strtolower($rawChallan), '.pdf');
                            $challanUrl = $challanExists ? asset('assets/receipts/challan-image/' . $rawChallan) : null;
                        @endphp
                        @if($challanExists)
                            @if($isPdf)
                                <a href="#" onclick="openChallanModal('{{ $challanUrl }}', 'pdf'); return false;" class="btn-erp btn-erp-outline">
                                    <i class="fas fa-file-pdf text-danger mr-1"></i> View Challan PDF
                                </a>
                            @else
                                <a href="#" onclick="openChallanModal('{{ $challanUrl }}', 'image'); return false;" class="btn-erp btn-erp-outline py-1">
                                    <i class="fas fa-file-image text-primary mr-1"></i> View Challan Image
                                </a>
                            @endif
                        @else
                            <span class="badge badge-light border text-muted" style="font-size: var(--erp-font-xs);">No Challan Attached</span>
                        @endif
                    </div>

                    <div class="d-flex align-items-start" style="gap: 10px;">
                        <span class="font-weight-bold" style="font-size: var(--erp-font-sm); min-width: 80px; color: var(--erp-text-muted); margin-top: 4px;">PHOTOS:</span>
                        <div class="d-flex flex-wrap" style="gap: 6px;">
                            @if($data->other_images && $data->other_images->count() > 0)
                                @foreach($data->other_images as $otherImage)
                                    <div class="position-relative" style="width: 44px; height: 44px; border: 1px solid var(--erp-border); border-radius: 4px; overflow: hidden; background: #f8fafc;">
                                        <a href="#" onclick="openChallanModal('{{ asset('assets/receipts/other-images/' . $otherImage->image) }}', 'image'); return false;">
                                            <img src="{{ asset('assets/receipts/other-images/' . $otherImage->image) }}" alt="Img" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='{{ asset('images/image-placeholder.png') }}';">
                                        </a>
                                        <a href="{{ route('admin.fabric_receipt.delete_other_image', $otherImage->id) }}" class="position-absolute text-danger" style="top: -2px; right: 2px; font-size: 11px; text-shadow: 0 0 2px #fff;" onclick="return confirm('Delete this image?')" title="Delete">&times;</a>
                                    </div>
                                @endforeach
                            @else
                                <span class="badge badge-light border text-muted mt-1" style="font-size: var(--erp-font-xs);">No Other Images</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Details Table -->
    <div class="erp-card mb-2">
        <div class="erp-card-header py-1">
            <span class="erp-card-title">
                <i class="fas fa-layer-group"></i> Fabric Roll Details Breakdown
            </span>
            <button type="button" class="btn-erp btn-erp-outline py-0" id="btnToggleAllRolls">
                <i class="fas fa-expand-alt mr-1"></i> Expand / Collapse All
            </button>
        </div>
        <div class="table-responsive p-0">
            <table class="erp-table table table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Fabric Item</th>
                        <th style="width: 120px;" class="text-center">Total Rolls</th>
                        <th style="width: 140px;" class="text-right">Total Meters</th>
                        <th style="width: 130px;" class="text-right">Price / Mtr (Rs.)</th>
                        <th style="width: 150px;" class="text-right">Total Amount (Rs.)</th>
                        <th style="width: 120px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedDetails = $data->details->groupBy('fabric_id');
                        $grandRolls = 0;
                        $grandMeters = 0;
                        $grandAmount = 0;
                    @endphp

                    @forelse($groupedDetails as $fabricId => $rolls)
                        @php
                            $fabric = $rolls->first()->fabric;
                            $totalRolls = $rolls->count();
                            $totalMeters = $rolls->sum('meter');
                            $pricePerMeter = $rolls->first()->price_per_meter ?? 0;
                            $fabricAmount = $rolls->sum(function($r) { return $r->meter * $r->price_per_meter; });

                            $grandRolls += $totalRolls;
                            $grandMeters += $totalMeters;
                            $grandAmount += $fabricAmount;
                        @endphp
                        <tr class="fabric-row" data-fabric-id="{{ $fabricId }}" style="cursor: pointer;">
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td><b class="text-dark">{{ $fabric->name ?? '-' }}</b></td>
                            <td class="text-center">
                                <span class="erp-badge-yellow px-2 py-0 rounded font-weight-bold">{{ $totalRolls }} Rolls</span>
                            </td>
                            <td class="text-right font-weight-bold text-dark">{{ number_format($totalMeters, 2) }}</td>
                            <td class="text-right">{{ number_format($pricePerMeter, 2) }}</td>
                            <td class="text-right font-weight-bold text-dark">{{ number_format($fabricAmount, 2) }}</td>
                            <td class="text-center">
                                <button type="button" class="btn-erp btn-erp-outline py-0 toggle-rolls" data-target="rolls-{{ $fabricId }}">
                                    <i class="fas fa-list-ul mr-1"></i> Rolls ({{ $totalRolls }})
                                </button>
                            </td>
                        </tr>
                        <!-- Sub-table for individual rolls -->
                        <tr id="rolls-{{ $fabricId }}" class="roll-details-row" style="display: none; background-color: var(--erp-bg-page);">
                            <td colspan="7" class="p-2">
                                <div class="p-2 border rounded" style="background-color: #ffffff; border-color: var(--erp-border) !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="font-weight-bold text-uppercase" style="font-size: var(--erp-font-xs); color: var(--erp-text-heading);">
                                            <i class="fas fa-scroll mr-1 text-success"></i> Individual Rolls Breakdown for {{ $fabric->name ?? '-' }}
                                        </div>
                                        <span class="badge badge-light border font-weight-bold">{{ $totalRolls }} Rolls | {{ number_format($totalMeters, 2) }} Meters</span>
                                    </div>
                                    <table class="erp-table table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px;" class="text-center">#</th>
                                                <th>Roll No</th>
                                                <th class="text-right">Meters</th>
                                                <th class="text-right text-danger">Returned</th>
                                                <th class="text-right text-success">Remaining</th>
                                                <th class="text-right">Rate / Mtr (Rs.)</th>
                                                <th class="text-right">Roll Amount (Rs.)</th>
                                                <th style="width: 100px;" class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rolls as $rollKey => $roll)
                                                <tr id="roll-row-{{ $roll->id }}">
                                                    <td class="text-center">{{ $rollKey + 1 }}</td>
                                                    <td><b class="text-dark">{{ $roll->roll_number }}</b></td>
                                                    <td class="text-right font-weight-bold">{{ number_format($roll->meter, 2) }}</td>
                                                    <td class="text-right text-danger font-weight-bold">{{ number_format($roll->returns->sum('return_meter'), 2) }}</td>
                                                    <td class="text-right text-success font-weight-bold">{{ number_format($roll->remaining_quantity, 2) }}</td>
                                                    <td class="text-right">{{ number_format($roll->price_per_meter, 2) }}</td>
                                                    <td class="text-right font-weight-bold">{{ number_format($roll->meter * $roll->price_per_meter, 2) }}</td>
                                                    <td class="text-center">
                                                        @if($roll->status == 2)
                                                            <span class="badge badge-danger">Returned</span>
                                                        @elseif($roll->remaining_quantity <= 0)
                                                            <span class="badge badge-secondary">Used</span>
                                                        @else
                                                            <span class="badge badge-success">Available</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-3 text-muted">
                                No fabric items found in this shipment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="erp-table-grand-total">
                        <td colspan="2" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">GRAND TOTAL:</td>
                        <td class="text-center font-weight-bold grand-total-val">{{ $grandRolls }} Rolls</td>
                        <td class="text-right font-weight-bold grand-total-val">{{ number_format($grandMeters, 2) }}</td>
                        <td class="text-right"></td>
                        <td class="text-right font-weight-bold grand-total-val">Rs. {{ number_format($grandAmount, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Return History Section -->
    @if($data->returns->count() > 0)
    <div class="erp-card mb-2" style="border-top: 2.5px solid #dc2626;">
        <div class="erp-card-header py-1">
            <span class="erp-card-title text-danger">
                <i class="fas fa-history text-danger"></i> Return History
            </span>
        </div>
        <div class="table-responsive p-0">
            <table class="erp-table table table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width: 100px;">Date</th>
                        <th style="width: 130px;">Return No</th>
                        <th>Fabrics Returned</th>
                        <th style="width: 90px;" class="text-right">Meters</th>
                        <th style="width: 180px;" class="text-right">Breakup</th>
                        <th style="width: 130px;" class="text-right">Total (Rs.)</th>
                        <th>Remarks</th>
                        <th style="width: 100px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->returns as $return)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($return->date)->format('d M, Y') }}</td>
                            <td><span class="badge badge-secondary">{{ $return->return_number }}</span></td>
                            <td>
                                <ul class="mb-0 pl-3" style="font-size: 11px;">
                                    @foreach($return->details as $rd)
                                        <li>{{ $rd->fabric->name ?? '-' }} ({{ $rd->return_meter }} mtr @ Rs. {{ $rd->price_per_meter }})</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="text-right"><b>{{ number_format($return->details->sum('return_meter'), 2) }}</b></td>
                            <td class="text-right" style="font-size: 10.5px; line-height: 1.3;">
                                Sub: Rs. {{ number_format($return->sub_total, 2) }}<br>
                                GST ({{ $return->gst_percentage }}%): Rs. {{ number_format($return->gst_amount, 2) }}<br>
                                Other: Rs. {{ number_format($return->other_charges, 2) }}<br>
                                Disc: -Rs. {{ number_format($return->discount, 2) }}
                            </td>
                            <td class="text-right font-weight-bold text-danger">Rs. {{ number_format($return->total_amount, 2) }}</td>
                            <td>{{ $return->remarks ?? '-' }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center">
                                    <a href="{{ route('admin.fabric_receipt.download_return_report', ['id' => $return->id]) }}" 
                                       class="erp-action-btn erp-btn-view" title="Download Report">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="{{ route('admin.fabric_receipt.edit_return', ['id' => $return->id]) }}" 
                                       class="erp-action-btn erp-btn-edit" title="Edit Return">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a href="{{ route('admin.fabric_receipt.delete_return', ['id' => $return->id]) }}" 
                                       class="erp-action-btn erp-btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete this return record? This will revert the roll quantities and vendor balance.')"
                                       title="Delete Return">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Challan Preview Modal -->
<div class="modal fade" id="challanPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title font-weight-bold" style="font-size: 14px;"><i class="fas fa-file-alt mr-2 text-warning"></i>Challan Preview</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 bg-dark d-flex align-items-center justify-content-center"
                style="min-height: 480px; height: 75vh; overflow: auto;">
                <iframe id="challan-modal-pdf" src="" class="w-100 h-100 border-0 d-none"></iframe>
                <img id="challan-modal-image" src="" class="img-fluid d-none"
                    style="transition: transform 0.25s ease;">
            </div>
            <div class="modal-footer bg-light py-2 justify-content-between">
                <div id="modal-zoom-controls">
                    <button type="button" class="btn-erp btn-erp-outline" onclick="zoomOutChallan()"><i class="fas fa-search-minus"></i></button>
                    <button type="button" class="btn-erp btn-erp-outline" onclick="resetZoomChallan()"><i class="fas fa-sync-alt"></i></button>
                    <button type="button" class="btn-erp btn-erp-outline" onclick="zoomInChallan()"><i class="fas fa-search-plus"></i></button>
                </div>
                <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Challan Modal -->
<div class="modal fade" id="uploadChallanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.fabric_receipt.upload_challan') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="receipt_id" value="{{ $data->id }}">
                <div class="modal-header py-2" style="background: var(--erp-bg-header);">
                    <h5 class="modal-title font-weight-bold" style="font-size: 13px; color: var(--erp-text-heading);">Upload Challan Slip</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <div class="form-group mb-0">
                        <label class="erp-label">Select File (Image or PDF)</label>
                        <input type="file" name="challan_photo" class="form-control erp-input" accept="image/*,.pdf" required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-erp btn-erp-primary">Upload Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Other Images Modal -->
<div class="modal fade" id="uploadOtherImagesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.fabric_receipt.upload_other_images') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="receipt_id" value="{{ $data->id }}">
                <div class="modal-header py-2" style="background: var(--erp-bg-header);">
                    <h5 class="modal-title font-weight-bold" style="font-size: 13px; color: var(--erp-text-heading);">Upload Other Images</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <div class="form-group mb-0">
                        <label class="erp-label">Select File(s) (Images)</label>
                        <input type="file" name="other_images[]" class="form-control erp-input" accept="image/*" multiple required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-erp btn-erp-primary">Upload Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        let allExpanded = false;

        $('#btnToggleAllRolls').on('click', function () {
            allExpanded = !allExpanded;
            if (allExpanded) {
                $('.roll-details-row').show();
                $('.toggle-rolls').html('<i class="fas fa-eye-slash mr-1"></i> Hide Rolls');
                $(this).html('<i class="fas fa-compress-alt mr-1"></i> Collapse All');
            } else {
                $('.roll-details-row').hide();
                $('.toggle-rolls').each(function () {
                    let total = $(this).closest('tr').find('.erp-badge-yellow').text().trim();
                    $(this).html('<i class="fas fa-list-ul mr-1"></i> Rolls (' + total.split(' ')[0] + ')');
                });
                $(this).html('<i class="fas fa-expand-alt mr-1"></i> Expand All');
            }
        });

        $('.toggle-rolls').on('click', function (e) {
            e.stopPropagation();
            let targetId = $(this).data('target');
            let targetRow = $('#' + targetId);
            let btn = $(this);

            targetRow.fadeToggle(180, function () {
                let isVisible = targetRow.is(':visible');
                if (isVisible) {
                    btn.html('<i class="fas fa-eye-slash mr-1"></i> Hide Rolls');
                } else {
                    let total = btn.closest('tr').find('.erp-badge-yellow').text().trim();
                    btn.html('<i class="fas fa-list-ul mr-1"></i> Rolls (' + total.split(' ')[0] + ')');
                }
            });
        });

        // Also toggle on row click except button cell
        $('.fabric-row td:not(:last-child)').on('click', function () {
            $(this).closest('tr').find('.toggle-rolls').trigger('click');
        });
    });

    let challanZoom = 1;
    let isPdfMode = false;

    function openChallanModal(src, type) {
        if (!src) return;
        challanZoom = 1;
        isPdfMode = (type === 'pdf');
        
        let img = document.getElementById('challan-modal-image');
        let frame = document.getElementById('challan-modal-pdf');
        let controls = document.getElementById('modal-zoom-controls');

        if (isPdfMode) {
            $(img).addClass('d-none');
            $(frame).removeClass('d-none').attr('src', src);
            $(controls).addClass('d-none');
        } else {
            $(frame).addClass('d-none').attr('src', '');
            $(img).removeClass('d-none').attr('src', src).css('transform', 'scale(1)');
            $(controls).removeClass('d-none');
        }
        
        $('#challanPreviewModal').modal('show');
    }

    function zoomInChallan() {
        challanZoom += 0.2;
        applyChallanZoom();
    }

    function zoomOutChallan() {
        if (challanZoom > 0.4) {
            challanZoom -= 0.2;
            applyChallanZoom();
        }
    }

    function resetZoomChallan() {
        challanZoom = 1;
        applyChallanZoom();
    }

    function applyChallanZoom() {
        let img = document.getElementById('challan-modal-image');
        if (img) img.style.transform = `scale(${challanZoom})`;
    }
</script>
@endsection