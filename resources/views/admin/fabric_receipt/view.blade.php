@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">

        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Fabric Shipment Details ({{ $data->shipment_id }})</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.fabric_receipt.return', ['id' => $data->id]) }}"
                            class="btn btn-danger mr-1">
                            <i class="fas fa-undo"></i> Return Shipment
                        </a>
                        <a href="{{ route('admin.fabric_receipt.download_report', ['id' => $data->id]) }}"
                            class="btn btn-success">
                            <i class="fas fa-download"></i> Download Report
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">

                <!-- ================= Receipt Information ================= -->
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold text-dark">
                                <i class="fas fa-file-invoice text-primary mr-2"></i>
                                Receipt Information
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-4">
                        {{-- Metric Summary --}}
                        <div class="row mb-4">
                            <div class="col-lg-3 col-6 mb-3 mb-lg-0">
                                <div class="p-3 border rounded bg-light h-100 shadow-xs">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-coins text-secondary mr-2"></i>
                                        <small class="text-uppercase font-weight-bold text-muted"
                                            style="letter-spacing: 0.5px; font-size: 10px;">Base Amount</small>
                                    </div>
                                    <h4 class="mb-0 font-weight-bold">₹ {{ number_format($data->amount ?? 0, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-lg-2 col-6 mb-3 mb-lg-0">
                                <div class="p-3 border rounded bg-light h-100 shadow-xs">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-percentage text-warning mr-2"></i>
                                        <small class="text-uppercase font-weight-bold text-muted"
                                            style="letter-spacing: 0.5px; font-size: 10px;">GST
                                            ({{ $data->gst_percentage ?? 0 }}%)</small>
                                    </div>
                                    <h4 class="mb-0 font-weight-bold text-warning">₹
                                        {{ number_format($data->gst_amount ?? 0, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-lg-2 col-6 mb-3 mb-lg-0">
                                <div class="p-3 border rounded bg-light h-100 shadow-xs">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-truck-loading text-info mr-2"></i>
                                        <small class="text-uppercase font-weight-bold text-muted"
                                            style="letter-spacing: 0.5px; font-size: 10px;">Others</small>
                                    </div>
                                    <h4 class="mb-0 font-weight-bold text-info">₹
                                        {{ number_format($data->other_charges ?? 0, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6 mb-3 mb-lg-0">
                                <div class="p-3 border rounded bg-success-light h-100 shadow-xs"
                                    style="background-color: rgba(40, 167, 69, 0.05); border-color: rgba(40, 167, 69, 0.2) !important;">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-receipt text-success mr-2"></i>
                                        <small class="text-uppercase font-weight-bold text-success"
                                            style="letter-spacing: 0.5px; font-size: 10px;">Total Amount</small>
                                    </div>
                                    <h4 class="mb-0 font-weight-bold text-success">₹
                                        {{ number_format($data->total_amount ?? 0, 0) }}</h4>
                                </div>
                            </div>
                            <div class="col-lg-2 col-12">
                                <div class="p-3 border rounded bg-light h-100 shadow-xs text-center border-primary"
                                    style="border-style: dashed !important; background-color: rgba(0, 123, 255, 0.02);">
                                    <small class="text-uppercase font-weight-bold text-muted d-block"
                                        style="letter-spacing: 0.5px; font-size: 10px;">Total Rolls</small>
                                    <h3 class="mb-0 font-weight-bold text-primary">{{ $data->details->count() }}</h3>
                                </div>
                            </div>
                        </div>

                        <div class="row pt-2 border-top">
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-hashtag"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold"
                                            style="font-size: 10px;">Shipment Number</small>
                                        <div class="font-weight-bold text-dark">{{ $data->shipment_id }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mr-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold"
                                            style="font-size: 10px;">Bill Number</small>
                                        <div class="font-weight-bold text-dark">{{ $data->bill_no ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mr-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold"
                                            style="font-size: 10px;">Vendor</small>
                                        <div class="font-weight-bold text-dark text-truncate" style="max-width: 180px;">
                                            {{ $data->vendor->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light text-dark rounded-circle d-flex align-items-center justify-content-center mr-3 border"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold"
                                            style="font-size: 10px;">Date</small>
                                        <div class="font-weight-bold text-dark">
                                            {{ \Carbon\Carbon::parse($data->time)->format('d M Y') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center mr-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-warehouse"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold"
                                            style="font-size: 10px;">Warehouse</small>
                                        <div class="font-weight-bold text-dark">
                                            {{ $data->cutting_master->cutting_master_name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center mr-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold"
                                            style="font-size: 10px;">Received By</small>
                                        <div class="font-weight-bold text-dark">{{ $data->received_by ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="{{ ($data->paid_amount >= $data->total_amount && $data->total_amount > 0) ? 'bg-success' : 'bg-danger' }} text-white rounded-circle d-flex align-items-center justify-content-center mr-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-hand-holding-usd"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold"
                                            style="font-size: 10px;">Payment Status</small>
                                        <div>
                                            @php
                                                $paid = $data->paid_amount;
                                                $total = $data->total_amount;
                                            @endphp
                                            @if($paid >= $total && $total > 0)
                                                <a
                                                    href="{{ route('admin.payment.history.index', ['paymentable_type' => 'App\Models\FabricReceipt', 'paymentable_id' => $data->id]) }}">
                                                    <span class="badge badge-success px-3 py-1 shadow-xs">PAID</span>
                                                </a>
                                            @else
                                                <span class="badge badge-danger px-3 py-1 shadow-xs">UNPAID</span>
                                                <small class="text-muted d-block mt-1 font-weight-bold"
                                                    style="font-size: 9px;">Paid: ₹{{ number_format($paid, 2) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-start">
                                    <div class="bg-light text-dark rounded-circle d-flex align-items-center justify-content-center mr-3 border"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-1" style="width: 180px;">
                                            <small class="text-muted text-uppercase font-weight-bold" style="font-size: 10px;">Challan Photo</small>
                                            <button type="button" class="btn btn-xs btn-outline-primary border-0" onclick="$('#uploadChallanModal').modal('show')" title="Upload/Change">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                        <div class="mt-1">
                                            @php
                                                $isPdf = str_contains($data->challan_photo, '.pdf');
                                            @endphp
                                            @if($data->challan_photo && !str_contains($data->challan_photo, 'image-placeholder.png'))
                                                @if($isPdf)
                                                    <a href="#" onclick="openChallanModal('{{ $data->challan_photo }}', 'pdf'); return false;" class="d-flex align-items-center p-2 border rounded bg-white shadow-xs hover-brighten" style="width: 150px;">
                                                        <i class="fas fa-file-pdf text-danger fa-2x mr-2"></i>
                                                        <span class="small font-weight-bold text-dark text-truncate">View PDF</span>
                                                    </a>
                                                @else
                                                    <a href="#" onclick="openChallanModal('{{ $data->challan_photo }}', 'image'); return false;">
                                                        <img src="{{ $data->challan_photo }}" height="60" class="border rounded shadow-sm hover-brighten" alt="Challan Photo">
                                                    </a>
                                                @endif
                                            @else
                                                <div class="text-muted italic" style="font-size: 13px;">No photo attached</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                        </div>
                        
                        <div class="row pt-2 border-top mt-4">
                            <div class="col-12 mb-2 d-flex align-items-center justify-content-between">
                                <h6 class="font-weight-bold text-dark text-uppercase mb-0" style="font-size: 12px;"><i class="fas fa-images text-secondary mr-2"></i>Other Images</h6>
                                <button type="button" class="btn btn-xs btn-outline-primary border-0" onclick="$('#uploadOtherImagesModal').modal('show')" title="Upload More Images">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                            <div class="col-12">
                                @if($data->other_images && $data->other_images->count() > 0)
                                    <div class="d-flex flex-wrap" style="gap: 15px;">
                                        @foreach($data->other_images as $otherImage)
                                            <div class="position-relative" style="width: 120px; height: 120px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden;">
                                                <a href="#" onclick="openChallanModal('{{ asset('assets/receipts/other-images/' . $otherImage->image) }}', 'image'); return false;">
                                                    <img src="{{ asset('assets/receipts/other-images/' . $otherImage->image) }}" alt="Other Image" style="width: 100%; height: 100%; object-fit: cover;" class="hover-brighten">
                                                </a>
                                                <a href="{{ route('admin.fabric_receipt.delete_other_image', $otherImage->id) }}" class="btn btn-sm btn-danger position-absolute" style="top: 2px; right: 2px; padding: 2px 5px; font-size: 10px;" onclick="return confirm('Are you sure you want to delete this image?')"><i class="fas fa-times"></i></a>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted italic" style="font-size: 13px;">No other images uploaded.</div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ================= Receipt Details ================= -->
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Receipt Details</h3>
                    </div>

                    <div class="card-body table-responsive">

                        <table class="table table-bordered table-striped text-center align-middle">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Fabric Name</th>
                                    <th>Total Rolls</th>
                                    <th>Total Meters</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php
                                    $groupedDetails = $data->details->groupBy('fabric_id');
                                @endphp

                                @forelse($groupedDetails as $fabricId => $rolls)
                                    @php
                                        $fabric = $rolls->first()->fabric;
                                        $totalRolls = $rolls->count();
                                        $totalMeters = $rolls->sum('meter');
                                    @endphp
                                    <tr class="fabric-row" data-fabric-id="{{ $fabricId }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $fabric->name ?? '-' }}</td>
                                        <td><span class="badge badge-info">{{ $totalRolls }} Rolls</span></td>
                                        <td><strong>{{ number_format($totalMeters, 2) }} Mtr</strong></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary toggle-rolls"
                                                data-target="rolls-{{ $fabricId }}">
                                                <i class="fas fa-eye"></i> View Rolls
                                            </button>
                                        </td>
                                    </tr>
                                    <tr id="rolls-{{ $fabricId }}" class="roll-details-row" style="display: none;">
                                        <td colspan="6" class="p-0">
                                            <div class="p-3 bg-light border-bottom">
                                                <h6 class="text-left font-weight-bold mb-2">Roll Details for
                                                    {{ $fabric->name ?? '-' }}</h6>
                                                <table class="table table-sm table-bordered bg-white mb-0">
                                                    <thead class="bg-secondary text-white">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Roll No</th>
                                                            <th>Meter</th>
                                                            <th class="text-danger">Returned</th>
                                                            <th class="text-primary">Remaining</th>
                                                            <th>Price/Mtr</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($rolls as $rollKey => $roll)
                                                            <tr id="roll-row-{{ $roll->id }}">
                                                                <td>{{ $rollKey + 1 }}</td>
                                                                <td>{{ $roll->roll_number }}</td>
                                                                <td>{{ $roll->meter }}</td>
                                                                <td class="text-danger font-weight-bold">{{ number_format($roll->returns->sum('return_meter'), 2) }}</td>
                                                                <td class="text-primary font-weight-bold">{{ number_format($roll->remaining_quantity, 2) }}</td>
                                                                <td>Rs. {{ $roll->price_per_meter }}</td>
                                                                <td>
                                                                    @if($roll->status == 2)
                                                                        <span class="badge badge-danger">Returned</span>
                                                                    @elseif($roll->remaining_quantity <= 0)
                                                                        <span class="badge badge-secondary">Used</span>
                                                                    @else
                                                                        <span class="badge badge-success">Available</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    -
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
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No details found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>

                <!-- Return History Section -->
    <div class="card card-outline card-danger shadow-sm mt-4">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <h3 class="card-title font-weight-bold text-dark">
                <i class="fas fa-history text-danger mr-2"></i>
                Return History
            </h3>
        </div>
        <div class="card-body px-4 pb-4">
            @if($data->returns->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Return No</th>
                                <th>Fabrics Returned</th>
                                <th class="text-right">Meters</th>
                                <th class="text-right">Breakup</th>
                                <th class="text-right">Total Amount</th>
                                <th>Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->returns as $return)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($return->date)->format('d M, Y') }}</td>
                                    <td><span class="badge badge-secondary">{{ $return->return_number }}</span></td>
                                    <td>
                                        <ul class="mb-0 pl-3 small">
                                            @foreach($return->details as $rd)
                                                <li>{{ $rd->fabric->name ?? '-' }} ({{ $rd->return_meter }} mtr @ ₹{{ $rd->price_per_meter }})</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="text-right">{{ number_format($return->details->sum('return_meter'), 2) }}</td>
                                    <td class="text-right small">
                                        Sub: ₹{{ number_format($return->sub_total, 2) }}<br>
                                        GST ({{ $return->gst_percentage }}%): ₹{{ number_format($return->gst_amount, 2) }}<br>
                                        Other: ₹{{ number_format($return->other_charges, 2) }}<br>
                                        Disc: -₹{{ number_format($return->discount, 2) }}
                                    </td>
                                    <td class="text-right text-danger font-weight-bold">₹ {{ number_format($return->total_amount, 2) }}</td>
                                    <td>{{ $return->remarks ?? '-' }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.fabric_receipt.download_return_report', ['id' => $return->id]) }}" 
                                               class="btn btn-sm btn-outline-danger" title="Download Report">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="{{ route('admin.fabric_receipt.edit_return', ['id' => $return->id]) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Edit Return">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.fabric_receipt.delete_return', ['id' => $return->id]) }}" 
                                               class="btn btn-sm btn-outline-secondary" 
                                               onclick="return confirm('Are you sure you want to delete this return record? This will revert the roll quantities and vendor balance.')"
                                               title="Delete Return">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center p-4 text-muted border rounded">
                    <i class="fas fa-info-circle mr-1"></i> No return history found for this shipment.
                </div>
            @endif
        </div>
    </div>

                <!-- ================= Back Button ================= -->
                <div class="mt-3">
                    <a href="{{ route('admin.fabric_receipt.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </section>
    </div>

    <!-- Challan Preview Modal -->
    <div class="modal fade shadow" id="challanPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-file-alt mr-2"></i>Challan Preview</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 bg-dark d-flex align-items-center justify-content-center"
                    style="min-height: 500px; height: 80vh; overflow: auto;">
                    <iframe id="challan-modal-pdf" src="" class="w-100 h-100 border-0 d-none"></iframe>
                    <img id="challan-modal-image" src="" class="img-fluid shadow d-none"
                        style="transition: transform 0.3s ease;">
                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <div id="modal-zoom-controls">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="zoomOutChallan()"><i
                                class="fas fa-search-minus"></i></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetZoomChallan()"><i
                                class="fas fa-sync-alt"></i></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="zoomInChallan()"><i
                                class="fas fa-search-plus"></i></button>
                    </div>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold">Upload Challan Slip</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Select File (Image or PDF)</label>
                            <input type="file" name="challan_photo" class="form-control" accept="image/*,.pdf" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload Now</button>
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
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold">Upload Other Images</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Select File(s) (Images)</label>
                            <input type="file" name="other_images[]" class="form-control" accept="image/*" multiple required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
@endsection

@section('scripts')
    <style>
        .shadow-xs {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.045) !important;
        }

        .hover-brighten:hover {
            filter: brightness(1.1);
            cursor: pointer;
            transition: all 0.2s;
            border-color: #007bff !important;
        }

        .bg-success-light {
            background-color: rgba(40, 167, 69, 0.05);
        }

        .italic {
            font-style: italic;
        }

        .fabric-row {
            background-color: #ffffff;
            cursor: pointer;
            transition: all 0.2s;
        }

        .fabric-row:hover {
            background-color: #f1f4f9;
        }

        .roll-details-row {
            background-color: #fdfdfd;
        }

        /* Custom Scrollbar for Modal Body */
        .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #343a40;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #6c757d;
            border-radius: 4px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #adb5bd;
        }
    </style>

    <script>
        $(document).ready(function () {
            $('.toggle-rolls').on('click', function (e) {
                e.stopPropagation();
                let targetId = $(this).data('target');
                let targetRow = $('#' + targetId);
                let icon = $(this).find('i');

                targetRow.fadeToggle(200);

                $(this).toggleClass('btn-primary btn-secondary');
                icon.toggleClass('fa-eye fa-eye-slash');

                // Use a timeout to ensure visibility check works after animation starts
                setTimeout(() => {
                    let isVisible = $('#' + targetId).is(':visible');
                    if (isVisible) {
                        $(this).html('<i class="fas fa-eye-slash"></i> Hide Rolls');
                    } else {
                        $(this).html('<i class="fas fa-eye"></i> View Rolls');
                    }
                }, 220);
            });

            // Also toggle on row click
            $('.fabric-row').on('click', function () {
                $(this).find('.toggle-rolls').trigger('click');
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