@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-layer-group mr-2 text-primary"></i>Product Group Details
                        </h1>
                        <small class="text-muted">Viewing details for: {{ $group_info->product_name }} ({{ $group_info->design_number }})</small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- LEFT COLUMN: INFO -->
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-primary py-3">
                                <h3 class="card-title font-weight-bold mb-0">General Information</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">Product Name</th>
                                        <td class="py-3 font-weight-bold">{{ $group_info->product_name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">Design Number</th>
                                        <td class="py-3 font-weight-bold text-primary">{{ $group_info->design_number }}</td>
                                    </tr>
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">Size Set</th>
                                        <td class="py-3"><span class="badge badge-light border">{{ $group_info->size_set_name }}</span></td>
                                    </tr>

                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">Fitting</th>
                                        <td class="py-3 font-weight-bold">{{ $group_info->fitting_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">Pattern</th>
                                        <td class="py-3 font-weight-bold">{{ $group_info->pattern_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">MRP</th>
                                        <td class="py-3 font-weight-bold text-dark">₹{{ number_format($group_info->mrp, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">Total Boxes</th>
                                        <td class="py-3 font-weight-bold">{{ $items->sum('total_boxes') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="pl-4 py-3 text-muted small text-uppercase">Total Quantity</th>
                                        <td class="py-3 font-weight-bold text-primary" style="font-size: 1.2rem;">
                                            {{ $items->sum(function($item) { return $item->quantity * $item->total_boxes; }) }} <small>Pcs</small>
                                        </td>
                                    </tr>

                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: TABLE -->
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-success py-3 d-flex justify-content-between align-items-center">
                                <h3 class="card-title font-weight-bold mb-0">Content Details</h3>
                                <select id="colorFilter" class="form-control form-control-sm w-auto">
                                    <option value="">All Colors</option>
                                    @php
                                        $unique_colors = $items->pluck('color_name')->unique()->filter();
                                    @endphp
                                    @foreach($unique_colors as $color)
                                        <option value="{{ $color }}">{{ $color }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="bg-light contrast-text">
                                        <tr>
                                            <th class="pl-4 py-3">Rack</th>
                                            <th class="py-3">Barcode</th>
                                            <th class="py-3">Color</th>
                                            <th class="py-3">Pcs/Box</th>
                                            <th class="text-center py-3">Total Boxes</th>
                                            <th class="text-center py-3">Total Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            <tr class="item-row" data-color="{{ $item->color_name ?? 'N/A' }}">
                                                <td class="pl-4 py-3">
                                                    @if($item->rack)
                                                        <span class="badge badge-info">{{ $item->rack->name }}</span>
                                                    @else
                                                        <span class="text-muted">Unassigned</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 font-weight-bold text-dark">{{ $item->barcode }}</td>
                                                <td class="py-3 font-weight-bold">{{ $item->color_name ?? 'N/A' }}</td>
                                                <td class="py-3 text-center">{{ $item->quantity }}</td>
                                                <td class="text-center py-3 font-weight-bold text-success">{{ $item->total_boxes }}</td>
                                                <td class="text-center py-3 font-weight-bold text-success" style="font-size: 1.1rem;">{{ $item->quantity * $item->total_boxes }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .contrast-text th {
            color: #444;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        .card-header {
            border: none;
        }
    </style>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#colorFilter').on('change', function() {
                var selectedColor = $(this).val();
                if (selectedColor) {
                    $('.item-row').hide();
                    $('.item-row').filter(function() {
                        return $(this).data('color') == selectedColor;
                    }).show();
                } else {
                    $('.item-row').show();
                }
            });
        });
    </script>
@endsection