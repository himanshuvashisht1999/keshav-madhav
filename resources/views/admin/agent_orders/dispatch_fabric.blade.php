@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper erp-page p-2">
        <div class="container-fluid p-0">
            <div class="erp-header-bar mb-2">
                <div style="min-width: 0;">
                    <div class="erp-header-title text-truncate">
                        <i class="fas fa-shipping-fast text-primary"></i> Fabric Dispatch Selection
                    </div>
                    <div class="erp-page-subtitle text-truncate">
                        Order #ORD-{{ $order->id }} &nbsp;|&nbsp; <span class="erp-party-name">{{ $order->shop_name }}</span>
                    </div>
                </div>
                <div class="erp-header-actions">
                    <a href="{{ route('admin.agent-orders.show', $order->id) }}" class="btn-erp btn-erp-outline">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Details
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.agent-orders.dispatch-fabric', $order->id) }}" method="POST">
                @csrf
                <div class="erp-card mb-2">
                    <div class="erp-card-header d-flex justify-content-between align-items-center">
                        <h3 class="erp-card-title mb-0"><i class="fas fa-layer-group"></i> Select Fabric Rolls to Dispatch</h3>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="checkAllRolls">
                            <label class="custom-control-label font-weight-bold" for="checkAllRolls">Select All</label>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table erp-table mb-0">
                            <thead>
                                <tr>
                                    <th width="60" class="text-center">Select</th>
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
                                        <td><span class="badge-erp-status badge-erp-info">{{ $item->roll_number }}</span></td>
                                        <td><strong>{{ $item->fabric_name }}</strong></td>
                                        <td class="text-center">{{ $item->batch_no }}</td>
                                        <td class="text-center font-weight-bold">{{ number_format($item->meter, 2) }} m</td>
                                        <td class="text-right">₹{{ number_format($item->selling_price, 2) }}</td>
                                        <td class="text-right font-weight-bold">₹{{ number_format($item->meter * $item->selling_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-check-circle fa-3x text-success mb-3" style="opacity:.3"></i>
                                            <p class="text-muted mb-0">All rolls from this order have already been dispatched.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="erp-filter-bar">
                    <div class="row align-items-center">
                        <div class="col-md-5 text-muted small mb-2 mb-md-0">
                            <i class="fas fa-info-circle mr-1"></i> Selected rolls will be marked as dispatched and added to a new dispatch record.
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <textarea name="remark" class="form-control form-control-sm" rows="2" placeholder="Enter dispatch remark (optional)"></textarea>
                        </div>
                        <div class="col-md-3 text-md-right">
                            <button type="submit" class="btn-erp btn-erp-primary px-4" id="dispatchBtn" disabled>
                                <i class="fas fa-shipping-fast mr-1"></i> DISPATCH SELECTED <span class="badge badge-light ml-1" id="selCount">0</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            function updateDispatchBtn() {
                const count = $('.roll-checkbox:checked').length;
                $('#dispatchBtn').prop('disabled', count === 0);
                $('#selCount').text(count);
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
