@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-book mr-2"></i>Purchase Ledger</h1>
                    <p class="text-muted">Invoice-wise details of all purchased goods and fabrics.</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-primary text-white px-4 py-2 rounded shadow-sm">
                        <span class="d-block small font-weight-bold text-uppercase text-white-50">Total Grand Total</span>
                        <span class="h4 mb-0 font-weight-bold">₹{{ number_format($totalGrandTotal ?? 0, 2) }}</span>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.ledger.purchase.export-pdf', request()->all()) }}" class="btn btn-danger btn-sm mr-2 shadow-sm font-weight-bold" style="border-radius: 6px;">
                        <i class="fas fa-file-pdf mr-1"></i> PDF
                    </a>
                    <a href="{{ route('admin.ledger.purchase.export-excel', request()->all()) }}" class="btn btn-success btn-sm shadow-sm font-weight-bold" style="border-radius: 6px;">
                        <i class="fas fa-file-excel mr-1"></i> Excel
                    </a>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- FILTER CARD -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.ledger.purchase.index') }}" method="GET" class="row align-items-end">
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">Filter by Vendor</label>
                                <select name="vendor_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">Bill No.</label>
                                <input type="text" name="bill_no" class="form-control" placeholder="Search Bill No" value="{{ request('bill_no') }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">Item Type</label>
                                <select name="item_type" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Types</option>
                                    <option value="Product/Accessory" {{ request('item_type') === 'Product/Accessory' ? 'selected' : '' }}>Product/Accessory</option>
                                    <option value="Fabric" {{ request('item_type') === 'Fabric' ? 'selected' : '' }}>Fabric</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">From Date</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">To Date</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-2 mb-2 d-flex">
                                <button type="submit" class="btn btn-primary flex-fill mr-1 shadow-sm font-weight-bold">
                                    <i class="fas fa-filter mr-1"></i> APPLY
                                </button>
                                <a href="{{ route('admin.ledger.purchase.index') }}" class="btn btn-outline-secondary shadow-sm" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Bill No.</th>
                                    <th>Vendor Name</th>
                                    <th>Receipt Type</th>
                                    <th>Grand Total</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $item)
                                    <tr>
                                        <td>{{ $item->date ? date('d M Y', strtotime($item->date)) : 'N/A' }}</td>
                                        <td>
                                            @if($item->invoice_no)
                                                <span class="badge px-2 py-1 font-weight-bold text-white shadow-sm" style="background-color: #4f46e5; color: #ffffff !important; font-size: 12.5px; border-radius: 4px;">{{ $item->invoice_no }}</span>
                                            @else
                                                <span class="text-muted font-weight-bold">N/A</span>
                                            @endif
                                        </td>
                                        <td><strong>{{ $item->vendor_name }}</strong></td>
                                        <td>
                                            @if($item->item_type == 'Fabric')
                                                <span class="badge badge-info">{{ $item->item_type }}</span>
                                            @else
                                                <span class="badge badge-primary">{{ $item->item_type }}</span>
                                            @endif
                                        </td>
                                        <td><span class="text-primary font-weight-bold">₹{{ number_format($item->grand_total, 2) }}</span></td>
                                        <td class="text-right">
                                            @if($item->item_type == 'Fabric')
                                                <a href="{{ route('admin.fabric_receipt.view', ['id' => $item->ref_id]) }}"
                                                    class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                                                    <i class="fas fa-eye mr-1"></i> View
                                                </a>
                                            @else
                                                <a href="{{ route('admin.inventory.purchase_history.show', ['id' => $item->ref_id]) }}"
                                                    class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 6px;">
                                                    <i class="fas fa-eye mr-1"></i> View
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No purchase receipts found matching the criteria.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($purchases->hasPages())
                        <div class="card-footer bg-white">
                            {{ $purchases->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
