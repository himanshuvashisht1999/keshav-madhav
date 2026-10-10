@extends('admin.layouts.app')
@section('title', 'Attribute History Details')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- PAGE HEADER -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title m-0">Attribute History Details</h1>
                <p class="erp-page-subtitle m-0">Log record #{{ $history->id }} &bull; Changed on {{ $history->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
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
                        <a href="{{ route('admin.inventory.attribute-history.edit', $history->id) }}" class="btn-erp btn-erp-primary">
                            <i class="fas fa-edit mr-1"></i> Edit attributes
                        </a>
                    @endif
                @endif
                <a href="{{ route('admin.inventory.attribute-history.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to history
                </a>
            </div>
        </div>

        <!-- SUMMARY CARD -->
        <div class="erp-card mb-3 p-3">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="erp-label mb-0 text-muted">Movement type</label>
                    <div class="mt-1">
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
                        <span class="badge {{ $type['class'] }} px-2 py-1 font-weight-normal">{{ $type['label'] }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="erp-label mb-0 text-muted">Date & time</label>
                    <div class="font-weight-bold text-dark mt-1">{{ $history->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div class="col-md-3">
                    <label class="erp-label mb-0 text-muted">Boxes moved / changed</label>
                    <div class="font-weight-bold mt-1" style="color: #05421c;">{{ $history->box_quantity }} Boxes</div>
                </div>
                <div class="col-md-3 text-md-right">
                    <span class="badge badge-light border text-muted px-3 py-2 font-weight-normal" style="font-size: 0.85rem;">
                        <i class="fas fa-history mr-1"></i> Log record verified
                    </span>
                </div>
            </div>
        </div>

        <!-- COMPARISON SECTION -->
        <div class="row">
            <!-- OLD ATTRIBUTES -->
            <div class="col-md-6 mb-3">
                <div class="erp-card h-100 p-0" style="border-left: 4px solid #dc3545 !important;">
                    <div class="p-3 border-bottom" style="background-color: #fff5f5;">
                        <h6 class="m-0 font-weight-bold text-danger">
                            <i class="fas fa-history mr-2"></i> Old attributes (Source state)
                        </h6>
                    </div>
                    <div class="p-3">
                        <div class="detail-item mb-2">
                            <label class="erp-label mb-1 text-muted">Design</label>
                            <div class="font-weight-bold text-dark">{{ $history->oldProduct ? $history->oldProduct->design_number : 'N/A' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="erp-label mb-1 text-muted">Color</label>
                                <div class="font-weight-bold text-dark">{{ $history->oldColor ? $history->oldColor->name : 'N/A' }}</div>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="erp-label mb-1 text-muted">Size set</label>
                                <div class="font-weight-bold text-dark">{{ $history->oldSizeSet ? $history->oldSizeSet->name : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="detail-item mb-2">
                            <label class="erp-label mb-1 text-muted">Location (Warehouse / Rack)</label>
                            <div class="p-2 rounded font-weight-bold" style="background-color: #f8fafc; font-size: 0.9rem;">
                                {{ $history->oldRack ? ($history->oldRack->storeroom->name . ' / ' . $history->oldRack->name) : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW ATTRIBUTES -->
            <div class="col-md-6 mb-3">
                <div class="erp-card h-100 p-0" style="border-left: 4px solid #05421c !important;">
                    <div class="p-3 border-bottom" style="background-color: #edf5ee;">
                        <h6 class="m-0 font-weight-bold" style="color: #05421c;">
                            <i class="fas fa-check-circle mr-2"></i> New attributes (Target state)
                        </h6>
                    </div>
                    <div class="p-3">
                        <div class="detail-item mb-2">
                            <label class="erp-label mb-1 text-muted">Design</label>
                            <div class="font-weight-bold text-dark">{{ $history->newProduct ? $history->newProduct->design_number : 'N/A' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="erp-label mb-1 text-muted">Color</label>
                                <div class="font-weight-bold text-dark">{{ $history->newColor ? $history->newColor->name : 'N/A' }}</div>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="erp-label mb-1 text-muted">Size set</label>
                                <div class="font-weight-bold text-dark">{{ $history->newSizeSet ? $history->newSizeSet->name : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="detail-item mb-2">
                            <label class="erp-label mb-1 text-muted">Location (Warehouse / Rack)</label>
                            <div class="p-2 rounded font-weight-bold border" style="background-color: #f8fafc; border-color: #cce3d0 !important; font-size: 0.9rem;">
                                {{ $history->newRack ? ($history->newRack->storeroom->name . ' / ' . $history->newRack->name) : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
