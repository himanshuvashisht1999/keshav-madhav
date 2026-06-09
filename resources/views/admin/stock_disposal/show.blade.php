@extends('admin.layouts.app')

@section('title', 'Stock Disposal Details')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Stock Disposal Details</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.inventory.stock_disposal.index') }}" class="btn btn-default shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back to History
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-info card-outline shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h3 class="card-title font-weight-bold">Disposal No: {{ $main->disposal_no }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4 pb-3 border-bottom">
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase mb-1">Date</label>
                                    <p class="font-weight-bold text-dark mb-0">{{ $main->created_at->format('D MMM YYYY') }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase mb-1">Reason</label>
                                    <p class="font-weight-bold text-dark mb-0">{{ $main->reason }}</p>
                                </div>
                                <div class="col-md-4 text-md-right">
                                    <label class="text-muted small text-uppercase mb-1">Remarks</label>
                                    <p class="text-dark mb-0">{{ $main->remarks ?: 'No remarks provided.' }}</p>
                                </div>
                            </div>
                            
                            <h5 class="font-weight-bold mb-3">Disposed Items ({{ ucfirst($main->item_type) }})</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Item Details</th>
                                            <th class="text-right">Quantity Disposed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($main->items as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    @if($main->item_type === 'fabric')
                                                        Roll: <span class="badge badge-info">{{ optional($item->fabricReceiptDetail)->roll_number }}</span>
                                                        <div class="small text-muted">{{ optional(optional($item->fabricReceiptDetail)->fabric)->name }}</div>
                                                    @else
                                                        <span class="font-weight-bold">{{ optional(optional($item->domesticInventory)->product)->design_number }}</span>
                                                        <div class="small text-muted">{{ optional(optional($item->domesticInventory)->color)->name }} / {{ optional(optional($item->domesticInventory)->sizeSet)->name }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    <strong>{{ $item->quantity }}</strong> {{ $main->item_type === 'fabric' ? 'Mtr' : 'Boxes' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
