@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div class="erp-header-title d-flex align-items-center">
            <i class="fas fa-id-card text-primary mr-2" style="font-size: 1.25rem;"></i>
            <span>Customer Details: <strong class="text-dark">{{ $data->name }}</strong></span>
        </div>
        <div class="erp-header-actions">
            @if(Route::has('admin.ledger.party.show'))
                <a href="{{ route('admin.ledger.party.show', ['type' => 'customer', 'id' => $data->id]) }}" class="btn-erp btn-erp-outline mr-1" title="View Full Ledger">
                    <i class="fas fa-book mr-1 text-primary"></i> View Ledger
                </a>
            @endif
            <a href="{{ route('admin.master.customer.edit', ['id' => $data->id]) }}" class="btn-erp btn-erp-primary mr-1" title="Edit Customer">
                <i class="fas fa-edit mr-1"></i> Edit Customer
            </a>
            <a href="javascript:void(0)" onclick="deleteData({{ $data->id }})" class="btn-erp mr-1" style="background: #dc2626; color: #ffffff; border: 1px solid #dc2626;" title="Delete Customer">
                <i class="fas fa-trash mr-1"></i> Delete Customer
            </a>
            <a href="{{ route('admin.master.customer.index') }}" class="btn-erp btn-erp-outline" title="Back to Customer List">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Summary Metrics Strip -->
    <div class="row mb-2">
        <!-- Current Balance -->
        <div class="col-lg-3 col-md-6 mb-1">
            <div class="erp-card py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 42px; height: 42px; border-radius: 8px; background: {{ ($data->calculated_closing_balance ?? $data->balance ?? 0) >= 0 ? 'var(--erp-green-light)' : '#fee2e2' }}; color: {{ ($data->calculated_closing_balance ?? $data->balance ?? 0) >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }}; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Current Balance</div>
                        <div class="font-weight-bold" style="font-size: 1.2rem; color: {{ ($data->calculated_closing_balance ?? $data->balance ?? 0) >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }}; line-height: 1.2;">
                            ₹ {{ number_format(abs($data->calculated_closing_balance ?? $data->balance ?? 0), 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge px-2 py-1" style="{{ ($data->calculated_closing_balance ?? $data->balance ?? 0) >= 0 ? 'background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border);' : 'background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;' }} font-size: var(--erp-font-xs); font-weight: 700;">
                    {{ ($data->calculated_closing_balance ?? $data->balance ?? 0) >= 0 ? 'Credit' : 'Debit' }}
                </span>
            </div>
        </div>

        <!-- Opening Balance -->
        <div class="col-lg-3 col-md-6 mb-1">
            <div class="erp-card py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 42px; height: 42px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Opening Balance</div>
                        <div class="font-weight-bold" style="font-size: 1.2rem; color: #1e293b; line-height: 1.2;">
                            @php
                                $opAmount = $data->currentOpeningBalance ? $data->currentOpeningBalance->amount : abs($data->calculated_opening_balance ?? 0);
                                $opType = $data->currentOpeningBalance ? $data->currentOpeningBalance->balance_type : (($data->calculated_opening_balance ?? 0) >= 0 ? 'Credit' : 'Debit');
                            @endphp
                            ₹ {{ number_format($opAmount, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge badge-info px-2 py-1" style="font-size: var(--erp-font-xs); font-weight: 700;">
                    {{ $opType }}
                </span>
            </div>
        </div>

        <!-- Customer Type -->
        <div class="col-lg-3 col-md-6 mb-1">
            <div class="erp-card py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 42px; height: 42px; border-radius: 8px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Customer Type</div>
                        <div class="font-weight-bold" style="font-size: 1.1rem; color: #1e293b; line-height: 1.2;">
                            @if ($data->type == 'domestic')
                                @if ($data->subtype == 'agent')
                                    Domestic (Agent)
                                @elseif ($data->subtype == 'direct')
                                    Domestic (Direct)
                                @else
                                    Domestic
                                @endif
                            @elseif ($data->type == 'corporate')
                                Corporate
                            @else
                                {{ ucfirst($data->type ?? 'Standard') }}
                            @endif
                        </div>
                    </div>
                </div>
                <span class="badge badge-secondary px-2 py-1" style="font-size: var(--erp-font-xs); font-weight: 700;">
                    Type
                </span>
            </div>
        </div>

        <!-- Status & Terms -->
        <div class="col-lg-3 col-md-6 mb-1">
            <div class="erp-card py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 42px; height: 42px; border-radius: 8px; background: {{ $data->status == 1 ? 'var(--erp-green-light)' : '#f1f5f9' }}; color: {{ $data->status == 1 ? 'var(--erp-green-primary)' : '#64748b' }}; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Payment Term</div>
                        <div class="font-weight-bold" style="font-size: 1.1rem; color: #1e293b; line-height: 1.2;">
                            {{ $data->payment_term_days ?? 120 }} Days
                        </div>
                    </div>
                </div>
                <span class="badge {{ $data->status == 1 ? 'badge-success' : 'badge-danger' }} px-2 py-1" style="font-size: var(--erp-font-xs); font-weight: 700;">
                    {{ $data->status == 1 ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="row">
        <!-- Left Column: Customer Profile & Details -->
        <div class="col-lg-4 col-md-5 mb-3">
            <!-- Profile Card -->
            <div class="erp-card p-3 mb-3">
                <div class="text-center pb-3 border-bottom mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center mb-2" style="width: 76px; height: 76px; border-radius: 50%; background: var(--erp-green-light); color: var(--erp-green-primary); font-size: 2rem; border: 2px solid var(--erp-green-border); box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        @if($data->type == 'corporate')
                            <i class="fas fa-building"></i>
                        @else
                            <i class="fas fa-store"></i>
                        @endif
                    </div>
                    <h5 class="font-weight-bold mb-1 text-dark">{{ $data->name }}</h5>
                    <div>
                        <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 8px;">
                            ID: #{{ $data->id }}
                        </span>
                        <span class="badge {{ $data->status == 1 ? 'badge-success' : 'badge-danger' }} px-2 py-1" style="font-size: 11px;">
                            {{ $data->status == 1 ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <!-- Info List -->
                <ul class="list-group list-group-flush mb-0" style="font-size: var(--erp-font-sm);">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="fas fa-phone-alt mr-1 text-primary"></i> Phone</span>
                        <span class="font-weight-bold text-dark">
                            @if($data->phone)
                                <a href="tel:{{ $data->phone }}" class="text-dark">{{ $data->phone }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="fas fa-envelope mr-1 text-primary"></i> Email</span>
                        <span class="font-weight-bold text-dark text-truncate" style="max-width: 200px;">
                            @if($data->email)
                                <a href="mailto:{{ $data->email }}" class="text-primary">{{ $data->email }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="fas fa-receipt mr-1 text-primary"></i> GST Number</span>
                        <span class="font-weight-bold text-dark">
                            @if($data->gst_number)
                                <span class="badge badge-light border px-2 py-1 text-monospace">{{ $data->gst_number }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </li>

                    @if(!empty($data->contact_person))
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="fas fa-user mr-1 text-primary"></i> Contact Person</span>
                        <span class="font-weight-bold text-dark">{{ $data->contact_person }}</span>
                    </li>
                    @endif

                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="fas fa-tag mr-1 text-primary"></i> Pricing Visibility</span>
                        <span class="badge {{ ($data->see_price ?? 1) == 1 ? 'badge-info' : 'badge-secondary' }} px-2 py-1">
                            {{ ($data->see_price ?? 1) == 1 ? 'Allowed' : 'Hidden' }}
                        </span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="fas fa-calendar-alt mr-1 text-primary"></i> Registered Date</span>
                        <span class="text-muted">
                            {{ $data->created_at ? $data->created_at->format('d M, Y') : '-' }}
                        </span>
                    </li>

                    <li class="list-group-item px-0 pt-2 pb-0">
                        <span class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt mr-1 text-danger"></i> Full Address</span>
                        <div class="p-2 bg-light rounded text-dark" style="font-size: 12px; line-height: 1.4;">
                            {{ $data->address ?: 'No address specified.' }}
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Parent Agent Card (if domestic agent customer) -->
            @if($data->agent)
                <div class="erp-card p-3 mb-3" style="border-left: 4px solid var(--erp-green-primary);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-tie mr-2" style="color: var(--erp-green-primary); font-size: 1.1rem;"></i>
                            <h6 class="font-weight-bold mb-0 text-dark">Parent Sales Agent</h6>
                        </div>
                        @if(Route::has('admin.master.sales-agent.view'))
                            <a href="{{ route('admin.master.sales-agent.view', ['id' => $data->agent->id]) }}" class="btn btn-xs btn-outline-success">
                                View Agent <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        @endif
                    </div>
                    <ul class="list-group list-group-flush mb-0" style="font-size: var(--erp-font-sm);">
                        <li class="list-group-item d-flex justify-content-between px-0 py-1 border-0">
                            <span class="text-muted">Agent Name</span>
                            <span class="font-weight-bold text-dark">{{ $data->agent->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-1 border-0">
                            <span class="text-muted">Phone</span>
                            <span class="text-dark">{{ $data->agent->phone ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-1 border-0">
                            <span class="text-muted">Email</span>
                            <span class="text-dark">{{ $data->agent->email ?? '-' }}</span>
                        </li>
                    </ul>
                </div>
            @endif

            <!-- Associated Child Shops (if any) -->
            @if(isset($data->shops) && count($data->shops) > 0)
                <div class="erp-card p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-store-alt mr-2 text-primary"></i>
                            <h6 class="font-weight-bold mb-0">Sub Shops ({{ count($data->shops) }})</h6>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered m-0" style="font-size: 12px;">
                            <thead class="thead-light">
                                <tr>
                                    <th>Shop Name</th>
                                    <th>Phone</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->shops as $shop)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.master.customer.view', ['id' => $shop->id]) }}" class="font-weight-bold text-primary">
                                                {{ $shop->name }}
                                            </a>
                                        </td>
                                        <td>{{ $shop->phone ?? '-' }}</td>
                                        <td class="text-right font-weight-bold" style="color: {{ ($shop->balance ?? 0) >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }};">
                                            ₹ {{ number_format(abs($shop->balance ?? 0), 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Brand Discounts, Orders & Activity -->
        <div class="col-lg-8 col-md-7">
            <!-- Brand Discounts Card -->
            <div class="erp-card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="mr-2" style="width: 28px; height: 28px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: 13px;">
                            <i class="fas fa-percent"></i>
                        </div>
                        <h6 class="font-weight-bold mb-0 text-dark">Brand Discounts</h6>
                    </div>
                    <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px;">
                        {{ count($items['brands'] ?? []) }} Total Brands
                    </span>
                </div>

                @php
                    $agentDiscountMap = \Illuminate\Support\Facades\DB::table("sales_agent_brand_discounts")
                        ->get()
                        ->groupBy("sales_agent_id")
                        ->map(function ($g) { return $g->pluck('discount_percentage', 'brand_id'); });
                @endphp

                <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                    <table class="table table-bordered table-sm table-hover mb-0" style="font-size: var(--erp-font-sm);">
                        <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th>Brand Name</th>
                                <th style="width: 130px;" class="text-center">Discount (%)</th>
                                <th style="width: 170px;" class="text-center">Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items['brands'] ?? [] as $index => $brand)
                                @php
                                    $existingDiscount = $data->brandDiscounts->where('brand_id', $brand->id)->first();
                                    $isCustom = $existingDiscount !== null;
                                    $agentHasDiscount = $data->sales_agent_id && isset(optional($agentDiscountMap->get($data->sales_agent_id))[$brand->id]);
                                    $val = $existingDiscount
                                        ? $existingDiscount->discount_percentage
                                        : (optional($agentDiscountMap->get($data->sales_agent_id))[$brand->id] ?? 0);
                                @endphp
                                <tr>
                                    <td class="text-center text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="font-weight-bold text-dark">{{ $brand->name }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($val > 0)
                                            <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 12px; font-weight: 700; padding: 3px 8px;">
                                                {{ number_format($val, 2) }}%
                                            </span>
                                        @else
                                            <span class="text-muted">0.00%</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($isCustom)
                                            <span class="badge badge-primary px-2 py-1" style="font-size: 11px;">
                                                <i class="fas fa-user-cog mr-1"></i> Custom Override
                                            </span>
                                        @elseif($agentHasDiscount)
                                            <span class="badge badge-info px-2 py-1" style="font-size: 11px;">
                                                <i class="fas fa-sitemap mr-1"></i> Inherited from Agent
                                            </span>
                                        @else
                                            <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px;">
                                                Default
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted p-3">No brand discounts available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Orders & Dispatches Section -->
            <div class="erp-card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="mr-2" style="width: 28px; height: 28px; border-radius: 6px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h6 class="font-weight-bold mb-0 text-dark">Recent Activity & Orders</h6>
                    </div>
                    @if(Route::has('admin.ledger.party.show'))
                        <a href="{{ route('admin.ledger.party.show', ['type' => 'customer', 'id' => $data->id]) }}" class="btn btn-xs btn-outline-primary">
                            <i class="fas fa-external-link-alt mr-1"></i> Full Ledger History
                        </a>
                    @endif
                </div>

                <!-- Tabs for Orders / Dispatches -->
                <ul class="nav nav-pills mb-3" id="customer-activity-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active py-1 px-3" id="orders-tab" data-toggle="pill" href="#tab-orders" role="tab" style="font-size: 12px; font-weight: 600;">
                            <i class="fas fa-file-invoice mr-1"></i> Production Orders ({{ count($data->recent_orders ?? []) }})
                        </a>
                    </li>
                    @if(isset($data->recent_agent_orders) && count($data->recent_agent_orders) > 0)
                        <li class="nav-item">
                            <a class="nav-link py-1 px-3" id="agent-orders-tab" data-toggle="pill" href="#tab-agent-orders" role="tab" style="font-size: 12px; font-weight: 600;">
                                <i class="fas fa-clipboard-list mr-1"></i> Agent Orders ({{ count($data->recent_agent_orders) }})
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3" id="dispatches-tab" data-toggle="pill" href="#tab-dispatches" role="tab" style="font-size: 12px; font-weight: 600;">
                            <i class="fas fa-truck mr-1"></i> Dispatches ({{ count($data->recent_dispatches ?? []) + count($data->recent_order_dispatches ?? []) }})
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="customer-activity-content">
                    <!-- Tab 1: Production Orders -->
                    <div class="tab-pane fade show active" id="tab-orders" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover mb-0" style="font-size: 12px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>PO Number</th>
                                        <th>PO Date</th>
                                        <th>Order Type</th>
                                        <th class="text-right">Total Amount</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data->recent_orders ?? [] as $order)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">{{ $order->po_number ?: ('#' . $order->id) }}</strong>
                                            </td>
                                            <td>{{ $order->po_date ? \Carbon\Carbon::parse($order->po_date)->format('d M, Y') : '-' }}</td>
                                            <td><span class="badge badge-light border">{{ ucfirst($order->order_type ?? 'Standard') }}</span></td>
                                            <td class="text-right font-weight-bold">₹ {{ number_format($order->total_amount ?? 0, 2) }}</td>
                                            <td class="text-center">
                                                @if($order->status == 1)
                                                    <span class="badge badge-success px-2 py-1">Active</span>
                                                @else
                                                    <span class="badge badge-secondary px-2 py-1">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted p-3">No recent production orders recorded for this customer.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Agent Orders (if applicable) -->
                    @if(isset($data->recent_agent_orders) && count($data->recent_agent_orders) > 0)
                        <div class="tab-pane fade" id="tab-agent-orders" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover mb-0" style="font-size: 12px;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Order No</th>
                                            <th>Order Date</th>
                                            <th class="text-right">Total Amount</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data->recent_agent_orders as $aOrder)
                                            <tr>
                                                <td><strong class="text-primary">{{ $aOrder->order_number ?: ('#' . $aOrder->id) }}</strong></td>
                                                <td>{{ $aOrder->order_date ? \Carbon\Carbon::parse($aOrder->order_date)->format('d M, Y') : '-' }}</td>
                                                <td class="text-right font-weight-bold">₹ {{ number_format($aOrder->grand_total ?? $aOrder->total_amount ?? 0, 2) }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-info px-2 py-1">{{ ucfirst($aOrder->status ?? 'Pending') }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Tab 3: Dispatches -->
                    <div class="tab-pane fade" id="tab-dispatches" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover mb-0" style="font-size: 12px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Bill / LR No</th>
                                        <th>Transport</th>
                                        <th class="text-right">Grand Total</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $hasDispatches = (isset($data->recent_dispatches) && count($data->recent_dispatches) > 0) || (isset($data->recent_order_dispatches) && count($data->recent_order_dispatches) > 0);
                                    @endphp
                                    @if($hasDispatches)
                                        @foreach($data->recent_dispatches ?? [] as $dispatch)
                                            <tr>
                                                <td>{{ $dispatch->dispatch_date ? \Carbon\Carbon::parse($dispatch->dispatch_date)->format('d M, Y') : '-' }}</td>
                                                <td>
                                                    <strong>{{ $dispatch->bill_no ?: '-' }}</strong>
                                                    @if($dispatch->lr_no)
                                                        <br><small class="text-muted">LR: {{ $dispatch->lr_no }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $dispatch->transport_name ?: '-' }}</td>
                                                <td class="text-right font-weight-bold text-dark">₹ {{ number_format($dispatch->grand_total ?? 0, 2) }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-success px-2 py-1">{{ ucfirst($dispatch->status ?? 'Dispatched') }}</span>
                                                </td>
                                            </tr>
                                        @endforeach

                                        @foreach($data->recent_order_dispatches ?? [] as $oDispatch)
                                            <tr>
                                                <td>{{ $oDispatch->dispatch_date ? \Carbon\Carbon::parse($oDispatch->dispatch_date)->format('d M, Y') : '-' }}</td>
                                                <td><strong>{{ $oDispatch->bill_number ?: ('#' . $oDispatch->id) }}</strong></td>
                                                <td>{{ $oDispatch->dispatch_by ?: '-' }}</td>
                                                <td class="text-right font-weight-bold text-dark">₹ {{ number_format($oDispatch->total_amount ?? 0, 2) }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-success px-2 py-1">{{ ucfirst($oDispatch->status ?? 'Dispatched') }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center text-muted p-3">No dispatches found for this customer.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function deleteData(id){
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.master.customer.delete') }}?id=" + id;
            }
        });
    }
</script>
@endsection
