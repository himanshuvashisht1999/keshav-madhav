@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Purchase Order Items</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Purchase Order Items</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Create Purchase Order Items</h3>
                </div>
                <form action="{{route('admin.purchase_order_material.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Purchase Order Date</label>
                                    <input type="date" name="date" class="form-control" placeholder="Enter date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                                    @if ($errors->has('date'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('date') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vendor</label>
                                    <select name="vendor_id" class="form-control select2" style="width: 100%;">
                                        @foreach($vendors as $single_data)
                                        <option value="{{$single_data->id}}" {{old('vendor_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('vendor_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('vendor_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Expected Delivery Date</label>
                                    <input type="date" name="delivery_date" class="form-control" placeholder="Enter delivery date" value="{{old('delivery_date')}}" required>
                                    @if ($errors->has('delivery_date'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('delivery_date') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Is Notify To Vendor</label>
                                    <select name="is_notify" class="form-control select2" style="width: 100%;">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                        
                                    </select>
                                    @if ($errors->has('is_notify'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('is_notify') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6" style="display:none;">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" name="sku" id="sku" class="form-control" placeholder="Auto-generated SKU">
                                    @if ($errors->has('sku'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('sku') }}
                                        </span>
                                    @endif
                                </div>
                            </div>


                            <!-- Dynamic Items & Prices Section -->
                            <div class="col-md-12">
                                <label>Items & Prices</label>
                                <div id="itemsContainer">
                                    <div class="row items-row mb-2">
                                        <div class="col-md-4">
                                            <select name="items[0][item_sku]" class="form-control item-select select2" style="width:100%" required>
                                                <option value="">Select Item</option>
                                                @foreach($items as $single_data)
                                                <option value="{{$single_data->sku}}" data-sku="{{$single_data->sku}}">{{$single_data->sku}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="items[0][quantity]" class="form-control quantity-input" placeholder="Enter quantity" min="1" step="1" required>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="items[0][price]" class="form-control price-input" placeholder="Enter Price" min="0.01" step="0.01" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="items[0][total_price]" class="form-control total-input" placeholder="Enter total price" value="0" readonly required>
                                        </div>
                                        
                                        <div class="col-md-4" style="display:none;">
                                            <input type="hidden" name="items[0][sku]" class="form-control item-sku" placeholder="Auto SKU">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-success addRow">+</button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            
                            <div class="col-md-12">
                                <div class="mt-2" style="float:right">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
        </div>
    </section>
</div>

<script>
    let rowCount = 1;

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('addRow')) {
            e.preventDefault();
            let container = document.getElementById('itemsContainer');
            let firstRow = container.querySelector('.items-row');
            
            // Create new row HTML manually instead of cloning
            let newRowHTML = `
                <div class="row items-row mb-2">
                    <div class="col-md-4">
                        <select name="items[${rowCount}][item_sku]" class="form-control item-select select2" style="width:100%" required>
                            <option value="">Select Item</option>
                            @foreach($items as $single_data)
                            <option value="{{$single_data->sku}}" data-sku="{{$single_data->sku}}">{{$single_data->sku}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="items[${rowCount}][quantity]" class="form-control quantity-input" placeholder="Enter quantity" min="1" step="1" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="items[${rowCount}][price]" class="form-control price-input" placeholder="Enter Price" min="0.01" step="0.01" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="items[${rowCount}][total_price]" class="form-control total-input" placeholder="Enter total price" value="0" readonly required>
                    </div>
                    <div class="col-md-4" style="display:none;">
                        <input type="hidden" name="items[${rowCount}][sku]" class="form-control item-sku" placeholder="Auto SKU">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-success addRow">+</button>
                    </div>
                </div>
            `;
            
            // Create element from HTML string
            let tempDiv = document.createElement('div');
            tempDiv.innerHTML = newRowHTML;
            let newRow = tempDiv.querySelector('.items-row');
            
            container.appendChild(newRow);
            
            // Initialize select2 for new row
            $(newRow.querySelector('.item-select')).select2();
            
            attachRowEvents(newRow);
            rowCount++;
        }
        
        if (e.target.classList.contains('removeRow')) {
            e.preventDefault();
            e.target.closest('.items-row').remove();
        }
    });

    function attachRowEvents(row) {
        let quantityInput = row.querySelector('.quantity-input');
        let priceInput = row.querySelector('.price-input');
        let totalInput = row.querySelector('.total-input');

        function calculateTotal() {
            let quantity = parseFloat(quantityInput.value) || 0;
            let price = parseFloat(priceInput.value) || 0;
            totalInput.value = (quantity * price).toFixed(2);
        }

        if (quantityInput && priceInput && totalInput) {
            quantityInput.addEventListener('input', calculateTotal);
            priceInput.addEventListener('input', calculateTotal);
        }
    }

    // Attach events to initial row
    document.querySelectorAll('.items-row').forEach(row => {
        attachRowEvents(row);
    });
</script>
@endsection