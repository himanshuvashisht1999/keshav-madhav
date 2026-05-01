@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card mt-4 shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">View/Edit Purchase Summary</h3>
                    </div>
                    <form action="{{ route('admin.inventory.purchase_history.update', $purchase->id) }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Source</label>
                                    <div class="form-control-plaintext border-bottom">
                                        {{ $purchase->vendor_id ? 'Vendor: ' . ($purchase->vendor->company_name ?? $purchase->vendor->name) : ($purchase->customer_id ? 'Customer: ' . ($purchase->customer->company_name ?? $purchase->customer->name) : 'N/A') }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Created By</label>
                                    <div class="form-control-plaintext border-bottom">{{ $purchase->user->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Date</label>
                                    <div class="form-control-plaintext border-bottom">{{ $purchase->created_at->format('d-M-Y H:i') }}</div>
                                </div>
                            </div>

                            <h5 class="mb-3 text-secondary border-bottom pb-2">Purchase Items</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Design No</th>
                                            <th>Product Name</th>
                                            <th>Boxes</th>
                                            <th>Pcs/Box</th>
                                            <th>MRP</th>
                                            <th>Purchase Rate</th>
                                            <th>Line Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $calculatedSubTotal = 0; @endphp
                                        @foreach($purchase->items as $item)
                                            @php 
                                                $lineTotal = $item->box_quantity * $item->pieces_per_box * $item->purchase_rate;
                                                $calculatedSubTotal += $lineTotal;
                                            @endphp
                                            <tr>
                                                <td>{{ $item->newProduct->design_number ?? 'N/A' }}</td>
                                                <td>{{ $item->newProduct->name_of_garment ?? 'N/A' }}</td>
                                                <td>{{ $item->box_quantity }}</td>
                                                <td>{{ $item->pieces_per_box }}</td>
                                                <td>{{ number_format($item->mrp, 2) }}</td>
                                                <td>{{ number_format($item->purchase_rate, 2) }}</td>
                                                <td class="text-right">{{ number_format($lineTotal, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <h5 class="mb-3 text-secondary border-bottom pb-2">Financial Summary</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Sub Total (Auto-calculated)</label>
                                        <input type="number" name="sub_total" id="sub_total" class="form-control bg-light" value="{{ $purchase->sub_total }}" step="0.01" readonly>
                                        <small class="text-muted">Calculated: {{ number_format($calculatedSubTotal, 2) }}</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>GST</label>
                                        <div class="input-group">
                                            <input type="number" name="gst_value" id="gst_value" class="form-control" value="{{ $purchase->gst_value }}" step="0.01">
                                            <div class="input-group-append">
                                                <select name="gst_type" id="gst_type" class="custom-select bg-light" style="width: auto;">
                                                    <option value="percentage" {{ $purchase->gst_type == 'percentage' ? 'selected' : '' }}>%</option>
                                                    <option value="amount" {{ $purchase->gst_type == 'amount' ? 'selected' : '' }}>₹</option>
                                                </select>
                                            </div>
                                        </div>
                                        <input type="hidden" name="gst" id="gst_amount" value="{{ $purchase->gst }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Other Amount</label>
                                        <input type="number" name="other_amount" id="other_amount" class="form-control" value="{{ $purchase->other_amount }}" step="0.01">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Discount</label>
                                        <input type="number" name="discount" id="discount" class="form-control" value="{{ $purchase->discount }}" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="text-primary font-weight-bold" style="font-size: 1.1rem;">Grand Total Amount</label>
                                        <input type="number" name="total_amount" id="total_amount" class="form-control form-control-lg bg-light text-primary font-weight-bold" value="{{ $purchase->total_amount }}" step="0.01" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-right">
                            <a href="{{ route('admin.inventory.purchase_history.index') }}" class="btn btn-secondary px-4">Back to List</a>
                            <button type="submit" class="btn btn-success px-4 ml-2">Update Summary</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(function () {
        function calculate() {
            let subTotal = parseFloat($('#sub_total').val()) || 0;
            let gstValue = parseFloat($('#gst_value').val()) || 0;
            let gstType = $('#gst_type').val();
            let other = parseFloat($('#other_amount').val()) || 0;
            let discount = parseFloat($('#discount').val()) || 0;

            let gstAmount = 0;
            if (gstType === 'percentage') {
                gstAmount = (subTotal * gstValue) / 100;
            } else {
                gstAmount = gstValue;
            }

            $('#gst_amount').val(gstAmount.toFixed(2));
            let total = subTotal + gstAmount + other - discount;

            $('#total_amount').val(total.toFixed(2));
        }

        $('#gst_value, #gst_type, #other_amount, #discount').on('input change', function() {
            calculate();
        });
    });
</script>
@endpush
@endsection
