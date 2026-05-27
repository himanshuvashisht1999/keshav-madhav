@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Edit Washing Voucher</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admin.payment.voucher.washing.index')}}">Washing Voucher</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <form action="{{route('admin.payment.voucher.washing.update')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Voucher Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-user mr-1 text-primary"></i> Select Washing Master <span class="text-danger">*</span></label>
                                        <select name="washing_master_id" class="form-control select2" required>
                                            <option value="">Select Master</option>
                                            @foreach($washingMasters as $master)
                                                <option value="{{ $master->id }}" {{ $data->washing_master_id == $master->id ? 'selected' : '' }}>{{ $master->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar-alt mr-1 text-info"></i> Voucher Date <span class="text-danger">*</span></label>
                                        <input type="date" name="voucher_date" class="form-control" value="{{ $data->voucher_date }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-hashtag mr-1 text-secondary"></i> Voucher Number</label>
                                        <input type="text" name="voucher_number" class="form-control" value="{{ $data->voucher_number }}" placeholder="Enter Voucher Number">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-cloud-upload-alt mr-1 text-success"></i> Upload Document (Leave blank to keep current)</label>
                                        <div class="custom-file">
                                            <input type="file" name="document" class="custom-file-input" id="documentFile">
                                            <label class="custom-file-label" for="documentFile">Choose file</label>
                                        </div>
                                        @if($data->document)
                                            <small class="text-muted">Current: <a href="{{ asset($data->document) }}" target="_blank">View File</a></small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h5 class="mb-3 text-muted"><i class="fas fa-list mr-1"></i> Item Details</h5>
                            <table class="table table-bordered" id="itemTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 30%">Item Name</th>
                                        <th style="width: 25%">Lot No</th>
                                        <th style="width: 10%">Quantity</th>
                                        <th style="width: 10%">Rate</th>
                                        <th style="width: 15%">Amount</th>
                                        <th style="width: 10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data->items as $index => $item)
                                    <tr>
                                        <td><input type="text" name="items[{{ $index }}][item_name]" class="form-control" value="{{ $item->item_name }}" required></td>
                                        <td>
                                            <select name="items[{{ $index }}][order_lot_id]" class="form-control select2">
                                                <option value="">Select Lot</option>
                                                @foreach($lots as $lot)
                                                    <option value="{{ $lot->id }}" {{ $item->order_lot_id == $lot->id ? 'selected' : '' }}>{{ $lot->lot_no }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" step="0.01" name="items[{{ $index }}][quantity]" class="form-control qty" value="{{ $item->quantity }}" required></td>
                                        <td><input type="number" step="0.01" name="items[{{ $index }}][rate]" class="form-control rate" value="{{ $item->rate }}" required></td>
                                        <td><input type="number" step="0.01" name="items[{{ $index }}][amount]" class="form-control amount" value="{{ $item->amount }}" readonly></td>
                                        <td class="text-center">
                                            @if($index == 0)
                                                <button type="button" class="btn btn-sm btn-success addRow"><i class="fas fa-plus"></i></button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-danger removeRow"><i class="fas fa-trash"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">Sub Total</th>
                                        <td><input type="number" step="0.01" name="sub_total" id="sub_total" class="form-control" value="{{ $data->sub_total }}" readonly></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-right">GST</th>
                                        <td><input type="number" step="0.01" name="gst" id="gst" class="form-control total-calc" value="{{ $data->gst }}"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-right">Other Charges</th>
                                        <td><input type="number" step="0.01" name="other_charges" id="other_charges" class="form-control total-calc" value="{{ $data->other_charges }}"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-right">Round off</th>
                                        <td><input type="number" step="0.01" name="round_off" id="round_off" class="form-control total-calc" value="{{ $data->round_off }}"></td>
                                        <td></td>
                                    </tr>
                                    <tr class="bg-light">
                                        <th colspan="4" class="text-right text-lg">Total Amount</th>
                                        <td><input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control font-weight-bold" value="{{ $data->total_amount }}" readonly></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label><i class="fas fa-comment-alt mr-1 text-muted"></i> Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="2" placeholder="Enter Remarks">{{ $data->remarks }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-right">
                            <a href="{{ route('admin.payment.voucher.washing.index') }}" class="btn btn-default mr-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Update Voucher</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let rowCount = {{ count($data->items) }};

            // Update file label
            $('#documentFile').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            $(document).on('click', '.addRow', function() {
                let newRow = `
                    <tr>
                        <td><input type="text" name="items[${rowCount}][item_name]" class="form-control" required></td>
                        <td>
                            <select name="items[${rowCount}][order_lot_id]" class="form-control select2">
                                <option value="">Select Lot</option>
                                @foreach($lots as $lot)
                                    <option value="{{ $lot->id }}">{{ $lot->lot_no }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" step="0.01" name="items[${rowCount}][quantity]" class="form-control qty" required></td>
                        <td><input type="number" step="0.01" name="items[${rowCount}][rate]" class="form-control rate" required></td>
                        <td><input type="number" step="0.01" name="items[${rowCount}][amount]" class="form-control amount" readonly></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger removeRow"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#itemTable tbody').append(newRow);
                $('.select2').select2();
                rowCount++;
            });

            $(document).on('click', '.removeRow', function() {
                $(this).closest('tr').remove();
                calculateTotals();
            });

            $(document).on('input', '.qty, .rate', function() {
                let tr = $(this).closest('tr');
                let qty = parseFloat(tr.find('.qty').val()) || 0;
                let rate = parseFloat(tr.find('.rate').val()) || 0;
                let amount = qty * rate;
                tr.find('.amount').val(amount.toFixed(2));
                calculateTotals();
            });

            $(document).on('input', '.total-calc', function() {
                calculateTotals();
            });

            function calculateTotals() {
                let subTotal = 0;
                $('.amount').each(function() {
                    subTotal += parseFloat($(this).val()) || 0;
                });
                $('#sub_total').val(subTotal.toFixed(2));

                let gst = parseFloat($('#gst').val()) || 0;
                let otherCharges = parseFloat($('#other_charges').val()) || 0;
                let roundOff = parseFloat($('#round_off').val()) || 0;
                let totalAmount = subTotal + gst + otherCharges + roundOff;
                $('#total_amount').val(totalAmount.toFixed(2));
            }
        });
    </script>
@endsection
