@extends('admin.layouts.app')
@section('title', 'Attribute History Details')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Attribute History Details</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @if (in_array($history->type, ['attribute_change', 'stock_consume', 'creation']) && $history->new_product_id)
                        @php
                            $current_inventory = \App\Models\DomesticInventory::where('product_id', $history->new_product_id)
                                ->where('size_set_id', $history->new_size_set_id)
                                ->where('color_id', $history->new_color_id)
                                ->where('rack_id', $history->new_rack_id)
                                ->first();
                            $isEditable = $current_inventory && $current_inventory->total_boxes >= $history->box_quantity;
                        @endphp
                        @if($isEditable)
                            <a href="{{ route('admin.inventory.attribute-history.edit', $history->id) }}" class="btn btn-warning mr-2">
                                <i class="fas fa-edit"></i> Edit Attributes
                            </a>
                        @endif
                    @endif
                    <a href="{{ route('admin.inventory.attribute-history.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to History
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Change Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Movement Type:</th>
                            <td>
                                @php
                                    $badges = [
                                        'creation' => ['label' => 'Entry', 'class' => 'badge-success'],
                                        'packing' => ['label' => 'Packing', 'class' => 'badge-info'],
                                        'attribute_change' => ['label' => 'Update', 'class' => 'badge-warning'],
                                        'stock_consume' => ['label' => 'Consume', 'class' => 'badge-danger'],
                                        'transfer' => ['label' => 'Transfer', 'class' => 'badge-primary'],
                                        'deletion' => ['label' => 'Deletion', 'class' => 'badge-danger'],
                                    ];
                                    $type = $badges[$history->type] ?? ['label' => ucfirst($history->type), 'class' => 'badge-secondary'];
                                @endphp
                                <span class="badge {{ $type['class'] }} px-2 py-1">{{ $type['label'] }}</span>
                            </td>
                            <th>Date:</th>
                            <td>{{ $history->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Quantity Moved/Changed:</th>
                            <td colspan="3"><span class="badge badge-light border px-2 py-1 font-weight-bold" style="font-size: 0.9rem;">{{ $history->box_quantity }} Boxes</span></td>
                        </tr>
                    </table>

                    <h4 class="mt-4 mb-3">Attribute Details</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light shadow-sm">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">Old Attributes</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th>Design:</th>
                                            <td>{{ $history->oldProduct ? $history->oldProduct->design_number : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Color:</th>
                                            <td>{{ $history->oldColor ? $history->oldColor->name : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Size:</th>
                                            <td>{{ $history->oldSizeSet ? $history->oldSizeSet->name : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Location:</th>
                                            <td>{{ $history->oldRack ? ($history->oldRack->storeroom->name . ' / ' . $history->oldRack->name) : 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">New Attributes</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th>Design:</th>
                                            <td>{{ $history->newProduct ? $history->newProduct->design_number : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Color:</th>
                                            <td>{{ $history->newColor ? $history->newColor->name : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Size:</th>
                                            <td>{{ $history->newSizeSet ? $history->newSizeSet->name : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Location:</th>
                                            <td>{{ $history->newRack ? ($history->newRack->storeroom->name . ' / ' . $history->newRack->name) : 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
