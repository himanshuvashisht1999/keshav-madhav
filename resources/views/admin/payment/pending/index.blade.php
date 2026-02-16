@extends($layout)

@section('styles')
    <style>
        :root {
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334115;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --KM-purple: #6f42c1;
            --KM-purple-dark: #5a32a3;
            --KM-purple-light: #8b5cf6;
        }

        /* =========================================
                   MOBILE APP STYLES (Screen < 992px)
                ========================================= */
        @media (max-width: 991.98px) {
            .mobile-only {
                display: block !important;
            }

            .desktop-only {
                display: none !important;
            }

            .owner-app-wrapper {
                background: #f8fafc !important;
                min-height: 100vh;
                margin-top: 0 !important;
            }

            .app-hero {
                background: var(--KM-purple);
                padding: 40px 24px 80px;
                color: white;
                position: relative;
                overflow: hidden;
            }

            .app-hero::after {
                content: '';
                position: absolute;
                top: -50px;
                right: -50px;
                width: 150px;
                height: 150px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
            }

            .app-hero-label {
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 2px;
                font-weight: 800;
                opacity: 0.8;
                display: block;
                margin-bottom: 4px;
            }

            .app-hero-title {
                font-size: 28px;
                font-weight: 900;
                margin-bottom: 6px;
                letter-spacing: -1px;
            }

            .app-hero-subtitle {
                font-size: 14px;
                font-weight: 500;
                opacity: 0.9;
            }

            .app-stats-container {
                padding: 0 20px;
                margin-top: -45px;
                position: relative;
                z-index: 10;
            }

            .app-stats-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
            }

            .app-stat-card {
                background: white;
                border-radius: 16px;
                padding: 16px 10px;
                text-align: center;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
                border: 1px solid var(--slate-100);
            }

            .app-stat-value {
                display: block;
                font-size: 16px;
                /* Adjusted for 3 columns */
                font-weight: 900;
                color: var(--slate-900);
                margin-bottom: 2px;
            }

            .app-stat-label {
                display: block;
                font-size: 8px;
                font-weight: 800;
                color: var(--slate-500);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .segmented-control-wrapper {
                padding: 24px 24px 0;
            }

            .segmented-nav {
                background: #f1f5f9;
                padding: 4px;
                border-radius: 12px;
                display: flex;
                width: 100%;
                border: none;
            }

            .segmented-nav .nav-link {
                flex: 1;
                text-align: center;
                padding: 10px 4px;
                border-radius: 10px;
                text-decoration: none !important;
                color: #64748b;
                transition: all 0.2s;
                border: none !important;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                font-size: 11px;
                font-weight: 700;
            }

            .segmented-nav .nav-link.active {
                background: white !important;
                color: var(--KM-purple) !important;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05) !important;
            }

            .app-card {
                background: white;
                border-radius: 18px;
                padding: 20px;
                margin: 0 20px 16px;
                border: 1px solid var(--slate-100);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            }

            .float-filter-btn {
                position: fixed;
                bottom: 85px;
                right: 20px;
                width: 54px;
                height: 54px;
                border-radius: 50%;
                background: var(--KM-purple);
                color: white;
                border: none;
                box-shadow: 0 8px 25px rgba(111, 66, 193, 0.4);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                font-size: 1.2rem;
            }

            /* Filter Drawer */
            .app-filter-drawer {
                position: fixed;
                bottom: -100%;
                left: 0;
                right: 0;
                background: white;
                border-radius: 24px 24px 0 0;
                z-index: 2000;
                padding: 30px 24px;
                transition: bottom 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                max-height: 80vh;
                overflow-y: auto;
                box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15);
            }

            .app-filter-drawer.open {
                bottom: 0;
            }

            .body-scroll-lock {
                overflow: hidden;
            }
        }

        /* =========================================
                   DESKTOP ADMIN STYLES (Screen >= 992px)
                ========================================= */
        @media (min-width: 992px) {
            .mobile-only {
                display: none !important;
            }

            .desktop-only {
                display: block !important;
            }

            .content-header h1 {
                font-size: 28px;
                font-weight: 900;
                color: var(--slate-900);
                letter-spacing: -1px;
            }
        }

        .text-purple {
            color: var(--KM-purple) !important;
        }

        .bg-gradient-success {
            background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%) !important;
        }

        .bg-gradient-danger {
            background: linear-gradient(87deg, #f5365c 0, #f56036 100%) !important;
        }

        .badge-soft-purple {
            background: rgba(111, 66, 193, 0.1);
            color: var(--KM-purple);
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 10px;
        }

        .btn-purple-gradient {
            background: linear-gradient(135deg, var(--KM-purple) 0%, var(--KM-purple-dark) 100%);
            color: white !important;
            border: none;
        }

        .btn-success-gradient {
            background: linear-gradient(135deg, #2dce89 0%, #2dcecc 100%);
            color: white !important;
            border: none;
        }

        .btn-danger-gradient {
            background: linear-gradient(135deg, #f5365c 0%, #f56036 100%);
            color: white !important;
            border: none;
        }
    </style>
@endsection

@section('content')
    <div class="{{ $isOwner ? 'owner-app-wrapper' : 'content-wrapper' }}">
        @if($isOwner)
            {{-- Standard Owner Hero --}}
            <div class="mobile-only">
                <div class="app-hero">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route($homeRoute) }}" class="text-white mr-3"><i class="fas fa-chevron-left"></i></a>
                        <span class="app-hero-label">Finance Module</span>
                    </div>
                    <h1 class="app-hero-title">Pending Payments</h1>
                    <p class="app-hero-subtitle">Outstanding receivables & payables</p>
                </div>

                {{-- Standard Stats Grid --}}
                <div class="app-stats-container">
                    <div class="app-stats-grid">
                        <div class="app-stat-card">
                            <span class="app-stat-label">Net Balance</span>
                            <span
                                class="app-stat-value text-purple">₹{{ number_format($totalReceivable - $totalPayable, 0) }}</span>
                        </div>
                        <div class="app-stat-card">
                            <span class="app-stat-label">Receivable</span>
                            <span class="app-stat-value text-success">₹{{ number_format($totalReceivable, 0) }}</span>
                        </div>
                        <div class="app-stat-card">
                            <span class="app-stat-label">Payable</span>
                            <span class="app-stat-value text-danger">₹{{ number_format($totalPayable, 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="content-header {{ $isOwner ? 'desktop-only' : '' }}">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Pending Payments</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route($homeRoute) }}">Home</a></li>
                            <li class="breadcrumb-item active">Pending Payments</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content {{ $isOwner ? 'p-0' : '' }}">
            <div class="container-fluid {{ $isOwner ? 'p-0' : '' }}">

                {{-- Tabs & Content --}}
                <div
                    class="card card-primary card-outline card-outline-tabs shadow-sm border-0 {{ $isOwner ? 'bg-transparent mb-0' : '' }}">
                    @if($isOwner)
                        {{-- Segmented Navigation (Mobile Only) --}}
                        <div class="segmented-control-wrapper mobile-only">
                            <div class="nav segmented-nav" role="tablist">
                                <a class="nav-link {{ $activeTab == 'agent_orders' ? 'active' : '' }}" data-toggle="pill"
                                    href="#agent-orders-tab" role="tab" onclick="switchTabHub(this)">
                                    <i class="fas fa-store"></i> <span>Agent</span>
                                </a>
                                <a class="nav-link {{ $activeTab == 'fabric_shipments' ? 'active' : '' }}" data-toggle="pill"
                                    href="#fabric-shipments-tab" role="tab" onclick="switchTabHub(this)">
                                    <i class="fas fa-truck"></i> <span>Fabric</span>
                                </a>
                                <a class="nav-link {{ $activeTab == 'corporate_orders' ? 'active' : '' }}" data-toggle="pill"
                                    href="#corporate-orders-tab" role="tab" onclick="switchTabHub(this)">
                                    <i class="fas fa-building"></i> <span>Corp</span>
                                </a>
                            </div>
                        </div>

                        <button class="float-filter-btn mobile-only" onclick="toggleFilters()">
                            <i class="fas fa-sliders-h"></i>
                        </button>
                    @endif

                    <div class="card-header p-0 border-bottom-0 {{ $isOwner ? 'desktop-only' : '' }}">
                        <ul class="nav nav-tabs" id="pendingTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab == 'agent_orders' ? 'active' : '' }}" data-toggle="pill"
                                    href="#agent-orders-tab" role="tab">
                                    <i class="fas fa-store mr-2"></i> Agent Orders
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab == 'fabric_shipments' ? 'active' : '' }}"
                                    data-toggle="pill" href="#fabric-shipments-tab" role="tab">
                                    <i class="fas fa-truck mr-2"></i> Fabric Shipments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab == 'corporate_orders' ? 'active' : '' }}"
                                    data-toggle="pill" href="#corporate-orders-tab" role="tab">
                                    <i class="fas fa-building mr-2"></i> Corporate Dispatches
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body {{ $isOwner ? 'p-0 pt-3' : '' }}">
                        <div class="tab-content" id="pendingTabsContent">

                            <!-- Agent Orders Tab -->
                            <div class="tab-pane fade {{ $activeTab == 'agent_orders' ? 'show active' : '' }}"
                                id="agent-orders-tab" role="tabpanel">

                                {{-- Filter Drawer --}}
                                <div class="{{ $isOwner ? 'app-filter-drawer' : 'p-3 border-bottom' }}"
                                    id="agentFilterDrawer">
                                    @if($isOwner)
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h5 class="m-0 font-weight-900">Filter Orders</h5>
                                            <button type="button" class="btn btn-light rounded-circle"
                                                onclick="toggleFilters()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <form method="GET" action="{{ route($routePrefix . 'index') }}">
                                        <input type="hidden" name="tab" value="agent_orders">
                                        <div class="row">
                                            <div class="col-12 col-md-3 mb-3">
                                                <label
                                                    class="small font-weight-800 text-muted uppercase letter-spacing-1">Agent</label>
                                                <select name="agent_id" class="form-control select2"
                                                    onchange="this.form.submit()">
                                                    <option value="">All Agents</option>
                                                    @foreach ($agents as $agent)
                                                        <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2 mb-3">
                                                <label
                                                    class="small font-weight-800 text-muted uppercase letter-spacing-1">From</label>
                                                <input type="date" name="from_date" class="form-control"
                                                    value="{{ request('from_date') }}" onchange="this.form.submit()">
                                            </div>
                                            <div class="col-6 col-md-2 mb-3">
                                                <label
                                                    class="small font-weight-800 text-muted uppercase letter-spacing-1">To</label>
                                                <input type="date" name="to_date" class="form-control"
                                                    value="{{ request('to_date') }}" onchange="this.form.submit()">
                                            </div>
                                            <div class="col-12 col-md-1 mb-3">
                                                <label class="d-none d-md-block">&nbsp;</label>
                                                <a href="{{ route($routePrefix . 'index', ['tab' => 'agent_orders']) }}"
                                                    class="btn btn-default btn-block">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                {{-- Desktop Table --}}
                                <div class="table-responsive {{ $isOwner ? 'desktop-only px-3' : '' }}">
                                    <table class="table table-hover mb-0 datatable-pending" style="width:100%">
                                        <thead class="bg-light">
                                            <tr>
                                                <th
                                                    class="border-top-0 py-3 px-4 text-muted small font-weight-800 uppercase">
                                                    Date</th>
                                                <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">
                                                    Agent</th>
                                                <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">
                                                    Shop</th>
                                                <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">
                                                    Total</th>
                                                <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">
                                                    Balance</th>
                                                <th
                                                    class="border-top-0 py-3 px-4 text-center text-muted small font-weight-800 uppercase">
                                                    Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($agentOrders as $order)
                                                <tr>
                                                    <td class="px-4 align-middle">
                                                        {{ \Carbon\Carbon::parse($order->order_date)->format('d-m-Y') }}</td>
                                                    <td class="align-middle font-weight-600">{{ $order->agent->name ?? '-' }}
                                                    </td>
                                                    <td class="align-middle">{{ $order->shop->name ?? '-' }}</td>
                                                    <td class="align-middle">₹{{ number_format($order->grand_total, 2) }}</td>
                                                    <td class="align-middle text-danger font-weight-800">
                                                        ₹{{ number_format($order->balance_amount, 2) }}</td>
                                                    <td class="px-4 align-middle text-center">
                                                        @if($isOwner)
                                                            <a href="{{ route('owner.order-summary.view', ['id' => $order->id]) }}"
                                                                class="btn btn-sm btn-purple-gradient rounded-pill px-3">
                                                                <i class="fas fa-eye mr-1"></i> View Detail
                                                            </a>
                                                        @else
                                                            <a href="{{ route('admin.payment.agent-order.create', ['agent_id' => $order->sales_agent_id, 'order_id' => $order->id]) }}"
                                                                class="btn btn-sm btn-success-gradient rounded-pill px-3">
                                                                <i class="fas fa-plus mr-1"></i> Receive
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Mobile Cards --}}
                                @if($isOwner)
                                    <div class="mobile-only">
                                        @forelse($agentOrders as $order)
                                            <div class="app-card">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <span class="badge-soft-purple mb-2 d-inline-block">ORDER
                                                            #{{ $order->id }}</span>
                                                        <h5 class="font-weight-900 m-0">{{ $order->shop->name ?? 'Unknown Shop' }}
                                                        </h5>
                                                        <p class="small text-muted m-0 font-weight-600">
                                                            {{ $order->agent->name ?? '-' }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <span
                                                            class="small font-weight-800 text-muted uppercase d-block">{{ \Carbon\Carbon::parse($order->order_date)->format('d M') }}</span>
                                                    </div>
                                                </div>
                                                <hr class="my-3 opacity-50">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span
                                                            class="small font-weight-800 text-muted uppercase d-block mb-1">Outstanding</span>
                                                        <span
                                                            class="h5 font-weight-900 text-danger mb-0">₹{{ number_format($order->balance_amount, 2) }}</span>
                                                    </div>
                                                    <a href="{{ route('owner.order-summary.view', ['id' => $order->id]) }}"
                                                        class="btn btn-purple-gradient rounded-pill px-4 font-weight-800">
                                                        Details <i class="fas fa-chevron-right ml-1"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3 opacity-20"></i>
                                                <p class="font-weight-700">No pending orders</p>
                                            </div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>

                            <!-- Fabric Shipments Tab -->
                            <div class="tab-pane fade {{ $activeTab == 'fabric_shipments' ? 'show active' : '' }}"
                                id="fabric-shipments-tab" role="tabpanel">

                                {{-- Filter Drawer --}}
                                <div class="{{ $isOwner ? 'app-filter-drawer' : 'p-3 border-bottom' }}"
                                    id="fabricFilterDrawer">
                                    @if($isOwner)
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h5 class="m-0 font-weight-900">Filter Shipments</h5>
                                            <button type="button" class="btn btn-light rounded-circle"
                                                onclick="toggleFilters()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <form method="GET" action="{{ route($routePrefix . 'index') }}">
                                        <input type="hidden" name="tab" value="fabric_shipments">
                                        <div class="row">
                                            <div class="col-12 col-md-3 mb-3">
                                                <label
                                                    class="small font-weight-800 text-muted uppercase letter-spacing-1">Vendor</label>
                                                <select name="vendor_id" class="form-control select2"
                                                    onchange="this.form.submit()">
                                                    <option value="">All Vendors</option>
                                                    @foreach ($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2 mb-3">
                                                <label
                                                    class="small font-weight-800 text-muted uppercase letter-spacing-1">From</label>
                                                <input type="date" name="from_date" class="form-control"
                                                    value="{{ request('from_date') }}" onchange="this.form.submit()">
                                            </div>
                                            <div class="col-6 col-md-2 mb-3">
                                                <label
                                                    class="small font-weight-800 text-muted uppercase letter-spacing-1">To</label>
                                                <input type="date" name="to_date" class="form-control"
                                                    value="{{ request('to_date') }}" onchange="this.form.submit()">
                                            </div>
                                            <div class="col-12 col-md-1 mb-3">
                                                <label class="d-none d-md-block">&nbsp;</label>
                                                <a href="{{ route($routePrefix . 'index', ['tab' => 'fabric_shipments']) }}"
                                                    class="btn btn-default btn-block">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                {{-- Desktop Table --}}
                                <div class="table-responsive {{ $isOwner ? 'desktop-only px-3' : '' }}">
                                    <table class="table table-hover mb-0 datatable-pending" style="width:100%">
                                        <thead class="bg-light">
                                            <tr>
                                                <th
                                                    class="border-top-0 py-3 px-4 text-muted small font-weight-800 uppercase">
                                                    Received Date</th>
                                                <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">
                                                    Vendor</th>
                                                <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">SKU
                                                </th>
                                                <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">
                                                    Total</th>
                                                <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">
                                                    Balance</th>
                                                <th
                                                    class="border-top-0 py-3 px-4 text-center text-muted small font-weight-800 uppercase">
                                                    Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($fabricReceipts as $receipt)
                                                <tr>
                                                    <td class="px-4 align-middle">
                                                        {{ \Carbon\Carbon::parse($receipt->created_at)->format('d-m-Y') }}</td>
                                                    <td class="align-middle font-weight-600">{{ $receipt->vendor->name ?? '-' }}
                                                    </td>
                                                    <td class="align-middle"><span
                                                            class="badge badge-light border">{{ $receipt->sku }}</span></td>
                                                    <td class="align-middle">₹{{ number_format($receipt->total_amount, 2) }}
                                                    </td>
                                                    <td class="align-middle text-danger font-weight-800">
                                                        ₹{{ number_format($receipt->balance_amount, 2) }}</td>
                                                    <td class="px-4 align-middle text-center">
                                                        @if(!$isOwner)
                                                            <a href="{{ route('admin.payment.fabric-shipment.create', ['vendor_id' => $receipt->vendor_id, 'receipt_id' => $receipt->id]) }}"
                                                                class="btn btn-sm btn-danger-gradient rounded-pill px-3">
                                                                <i class="fas fa-paper-plane mr-1"></i> Pay Now
                                                            </a>
                                                        @else
                                                            <span class="badge badge-light px-3 py-2">View Only</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Mobile Cards --}}
                                @if($isOwner)
                                    <div class="mobile-only">
                                        @forelse($fabricReceipts as $receipt)
                                            <div class="app-card border-left-danger"
                                                style="border-left: 4px solid #f5365c !important;">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <span
                                                            class="badge badge-light border mb-2 d-inline-block">{{ $receipt->sku }}</span>
                                                        <h5 class="font-weight-900 m-0">
                                                            {{ $receipt->vendor->name ?? 'Unknown Vendor' }}</h5>
                                                        <p class="small text-muted m-0 font-weight-600">Fabric Receipt</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <span
                                                            class="small font-weight-800 text-muted uppercase d-block">{{ \Carbon\Carbon::parse($receipt->created_at)->format('d M') }}</span>
                                                    </div>
                                                </div>
                                                <hr class="my-3 opacity-50">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span
                                                            class="small font-weight-800 text-muted uppercase d-block mb-1">Payable</span>
                                                        <span
                                                            class="h5 font-weight-900 text-danger mb-0">₹{{ number_format($receipt->balance_amount, 2) }}</span>
                                                    </div>
                                                    <div class="px-4 py-2 bg-light rounded-pill small font-weight-800 text-muted">
                                                        View Only Reference
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 text-muted">
                                                <i class="fas fa-truck-loading fa-3x mb-3 opacity-20"></i>
                                                <p class="font-weight-700">No pending shipments</p>
                                            </div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>

                            <!-- Corporate Orders Tab -->
                            <div class="tab-pane fade {{ $activeTab == 'corporate_orders' ? 'show active' : '' }}"
                                id="corporate-orders-tab" role="tabpanel">

                                {{-- Filter Drawer --}}
                                <div class="{{ $isOwner ? 'app-filter-drawer' : 'p-3 border-bottom' }}"
                                    id="corporateFilterDrawer">
                                    @if($isOwner)
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h5 class="m-0 font-weight-900">Filter Corporate</h5>
                                            <button type="button" class="btn btn-light rounded-circle"
                                                onclick="toggleFilters()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <form method="GET" action="{{ route($routePrefix . 'index') }}">
                                        <input type="hidden" name="tab" value="corporate_orders">
                                        <div class="row">
                                            <div class="col-12 col-md-3 mb-3">
                                                <label
                                                    class="small font-weight-800 text-muted uppercase letter-spacing-1">Customer</label>
                                                <select name="customer_id" class="form-control select2"
                                                    onchange="this.form.submit()">
                                                    <option value="">All Customers</option>
                                                    @foreach ($customers as $customer)
                                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                                            {{ $customer->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2 mb-3">
                                                <label
                                                    class="small font-weight-800 text-muted uppercase letter-spacing-1">From</label>
                                                <input type="date" name="from_date" class="form-control"
                                                    value="{{ request('from_date') }}" onchange="this.form.submit()">
                                            </div>
                                            <div class="col-6 col-md-2 mb-3">
                                                <label
                                                    class="small font-weight-800 text-muted uppercase letter-spacing-1">To</label>
                                                <input type="date" name="to_date" class="form-control"
                                                    value="{{ request('to_date') }}" onchange="this.form.submit()">
                                            </div>
                                            <div class="col-12 col-md-1 mb-3">
                                                <label class="d-none d-md-block">&nbsp;</label>
                                                <a href="{{ route($routePrefix . 'index', ['tab' => 'corporate_orders']) }}"
                                                    class="btn btn-default btn-block">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                {{-- Desktop Table --}}
                                <div class="table-responsive {{ $isOwner ? 'desktop-only px-3' : '' }}">
                                    <table class="table table-hover mb-0 datatable-pending" style="width:100%">
                                        <thead class="bg-light">
                                            <tr>
                                                <th
                                                    class="border-top-0 py-3 px-4 text-muted small font-weight-800 uppercase">
                                                    Dispatch Date</th>
                                                <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">
                                                    Customer</th>
                                                <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">SKU
                                                </th>
                                                <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">
                                                    Total</th>
                                                <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">
                                                    Balance</th>
                                                <th
                                                    class="border-top-0 py-3 px-4 text-center text-muted small font-weight-800 uppercase">
                                                    Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($corporateDispatches as $dispatch)
                                                <tr>
                                                    <td class="px-4 align-middle">
                                                        {{ \Carbon\Carbon::parse($dispatch->dispatch_date)->format('d-m-Y') }}
                                                    </td>
                                                    <td class="align-middle font-weight-600">
                                                        {{ $dispatch->customer->name ?? '-' }}</td>
                                                    <td class="align-middle"><span
                                                            class="badge badge-light border">{{ $dispatch->sku }}</span></td>
                                                    <td class="align-middle">₹{{ number_format($dispatch->total_amount, 2) }}
                                                    </td>
                                                    <td class="align-middle text-danger font-weight-800">
                                                        ₹{{ number_format($dispatch->balance_amount, 2) }}</td>
                                                    <td class="px-4 align-middle text-center">
                                                        @if($isOwner)
                                                            <a href="{{ route('owner.order-summary.view', ['id' => $dispatch->order_main_id]) }}"
                                                                class="btn btn-sm btn-purple-gradient rounded-pill px-3">
                                                                <i class="fas fa-eye mr-1"></i> View Detail
                                                            </a>
                                                        @else
                                                            <a href="{{ route('admin.payment.corporate-order.create', ['customer_id' => $dispatch->customer_id, 'dispatch_id' => $dispatch->id]) }}"
                                                                class="btn btn-sm btn-success-gradient rounded-pill px-3">
                                                                <i class="fas fa-plus mr-1"></i> Receive
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Mobile Cards --}}
                                @if($isOwner)
                                    <div class="mobile-only">
                                        @forelse($corporateDispatches as $dispatch)
                                            <div class="app-card border-left-success"
                                                style="border-left: 4px solid #2dce89 !important;">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <span
                                                            class="badge badge-light border mb-2 d-inline-block">{{ $dispatch->sku }}</span>
                                                        <h5 class="font-weight-900 m-0">
                                                            {{ $dispatch->customer->name ?? 'Unknown Customer' }}</h5>
                                                        <p class="small text-muted m-0 font-weight-600">Corporate Dispatch</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <span
                                                            class="small font-weight-800 text-muted uppercase d-block">{{ \Carbon\Carbon::parse($dispatch->dispatch_date)->format('d M') }}</span>
                                                    </div>
                                                </div>
                                                <hr class="my-3 opacity-50">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span
                                                            class="small font-weight-800 text-muted uppercase d-block mb-1">Receivable</span>
                                                        <span
                                                            class="h5 font-weight-900 text-success mb-0">₹{{ number_format($dispatch->balance_amount, 2) }}</span>
                                                    </div>
                                                    <a href="{{ route('owner.order-summary.view', ['id' => $dispatch->order_main_id]) }}"
                                                        class="btn btn-success-gradient rounded-pill px-4 font-weight-800">
                                                        Details <i class="fas fa-chevron-right ml-1"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 text-muted">
                                                <i class="fas fa-building fa-3x mb-3 opacity-20"></i>
                                                <p class="font-weight-700">No pending corporate</p>
                                            </div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.datatable-pending').DataTable({
                "pageLength": 25,
                "ordering": true,
                "info": true,
                "responsive": true
            });
        });

        function toggleFilters() {
            var activePaneId = $('.tab-pane.active').attr('id');
                var filterId = '';

                if (activePaneId == 'agent-orders-tab') filterId = 'agentFilterDrawer';
                else if (activePaneId == 'fabric-shipments-tab') filterId = 'fabricFilterDrawer';
                else if (activePaneId == 'corporate-orders-tab') filterId = 'corporateFilterDrawer';

                $('#' + filterId).toggleClass('open');
            }

            function switchTabHub(el) {
                var target = $(el).attr('href');

                // Trigger Bootstrap Tab Show
                $('a[href="' + target + '"]').tab('show');

                // Close any open filters
                $('.app-filter-drawer').removeClass('open');
            }

            // Sync Mobile and Desktop tabs
            $(document).on('shown.bs.tab', 'a[data-toggle="pill"]', function (e) {
                var target = $(e.target).attr('href');

                // Sync active state across all navs (Mobile + Desktop)
                $('a[data-toggle="pill"]').removeClass('active');
                $('a[href="' + target + '"]').addClass('active');

                // Specific hub-nav sync if on mobile
                $('.segmented-nav .nav-link').removeClass('active');
                $('.segmented-nav a[href="' + target + '"]').addClass('active');
            });
        </script>
@endsection