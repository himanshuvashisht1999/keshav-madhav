@extends('sales_agent.layouts.app', ['title' => 'Box Details'])

@section('content')
    <div class="container pb-5 mb-5 mt-3">
        <div class="mb-4 d-flex align-items-center">
            <a href="{{ route('agent.inventory.index') }}" class="btn btn-light rounded-pill mr-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-weight-bold h4 mb-0 text-dark">Inventory Details</h2>
                <p class="text-muted small mb-0">
                    {{ $variation->product_name ?: $variation->design_number }} |
                    {{ $variation->design_number }} - {{ $variation->color_name }}
                    ({{ $variation->size_set_name }})
                </p>
            </div>
        </div>

        <div class="alert alert-primary bg-white shadow-sm border-left mb-4" style="border-left-width: 4px !important;">
            <div class="row text-center">
                <div class="col-6 border-right">
                    <div class="small text-muted">Total Available</div>
                    <div class="h5 font-weight-bold mb-0">{{ $items->count() }} Boxes</div>
                </div>
                <div class="col-6">
                    <div class="small text-muted">Group Total</div>
                    <div class="h5 font-weight-bold mb-0 text-primary">{{ $items->sum('total_qty') }} Pcs</div>
                </div>
            </div>
        </div>

        <h6 class="font-weight-bold text-muted small mb-3 text-uppercase px-1">Individual Box List</h6>

        @foreach($items as $item)
            <div class="app-card mb-3 shadow-sm border-0 bg-white" style="border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge badge-light border px-2 py-1 mb-2">Box: #{{ $item->box_no }}</span>
                        <h6 class="font-weight-bold text-dark mb-0">{{ $item->total_qty }} Pieces</h6>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-outline-secondary small">Carton: {{ $item->carton_no }}</span>
                    </div>
                </div>

                <div class="bg-light p-2 rounded d-flex justify-content-between align-items-center mt-2">
                    <span class="small text-muted">Box ID: {{ $item->packing_box_id }}</span>
                    @if(Auth::guard('sales_agent')->user()->see_price)
                        <span class="h6 font-weight-bold text-primary mb-0">₹{{ number_format($item->price, 2) }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

@endsection