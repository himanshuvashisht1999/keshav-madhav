@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="m-0 font-weight-bold text-dark">Fabric Dispatch Selection</h1>
                        <p class="text-muted mb-0">Order #ORD-{{ $order->id }} | {{ $order->shop_name }}</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.agent-orders.show', $order->id) }}"
                            class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Details
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card shadow-sm border-0">
                    <form action="{{ route('admin.agent-orders.dispatch-fabric', $order->id) }}" method="POST">
                        @csrf
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-layer-group mr-2"></i>Select Fabric Rolls to Dispatch</h3>
                            <div class="custom-control custom-checkbox ml-2">
                                <input type="checkbox" class="custom-control-input" id="checkAllRolls">
                                <label class="custom-control-label font-weight-bold" for="checkAllRolls">Select All</label>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th width="50" class="text-center">Select</th>
                                            <th>Roll Number</th>
                                            <th>Fabric Name</th>
                                            <th class="text-center">Batch No</th>
                                            <th class="text-center">Meter</th>
                                            <th class="text-right">Price/m</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($items as $item)
                                            <tr>
                                                <td class="text-center">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" name="fabric_item_ids[]" value="{{ $item->id }}" 
                                                            class="custom-control-input roll-checkbox" id="roll_{{ $item->id }}">
                                                        <label class="custom-control-label" for="roll_{{ $item->id }}"></label>
                                                    </div>
                                                </td>
                                                <td><span class="badge badge-primary">{{ $item->roll_number }}</span></td>
                                                <td><strong>{{ $item->fabric_name }}</strong></td>
                                                <td class="text-center">{{ $item->batch_no }}</td>
                                                <td class="text-center font-weight-bold">{{ number_format($item->meter, 2) }} m</td>
                                                <td class="text-right">₹{{ number_format($item->selling_price, 2) }}</td>
                                                <td class="text-right font-weight-bold text-primary">₹{{ number_format($item->meter * $item->selling_price, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <i class="fas fa-check-circle fa-3x text-success mb-3 opacity-25"></i>
                                                    <p class="text-muted">All rolls from this order have already been dispatched.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white py-3">
                            <div class="row align-items-center">
                                <div class="col-md-6 text-muted small">
                                    <i class="fas fa-info-circle mr-1"></i> Selected rolls will be marked as dispatched and added to a new dispatch record.
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm rounded-pill font-weight-bold" id="dispatchBtn" disabled>
                                        <i class="fas fa-shipping-fast mr-2"></i> DISPATCH SELECTED ROLLS
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            function updateDispatchBtn() {
                const count = $('.roll-checkbox:checked').length;
                $('#dispatchBtn').prop('disabled', count === 0);
            }

            $('#checkAllRolls').on('change', function() {
                $('.roll-checkbox').prop('checked', $(this).prop('checked'));
                updateDispatchBtn();
            });

            $('.roll-checkbox').on('change', function() {
                updateDispatchBtn();
                if($('.roll-checkbox:checked').length === $('.roll-checkbox').length) {
                    $('#checkAllRolls').prop('checked', true);
                } else {
                    $('#checkAllRolls').prop('checked', false);
                }
            });
        });
    </script>
    @endpush
@endsection
