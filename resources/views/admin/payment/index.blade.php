@extends($layout ?? 'admin.layouts.app')

@section('content')
    @if($isOwner ?? false)
        {{-- ================= OWNER PORTAL DESIGN ================= --}}
        <div class="owner-container">
            {{-- Mobile Header --}}
            <div class="mobile-only">
                <div class="app-hero">
                    <h1 class="app-hero-title">Payment History</h1>
                    <p class="app-hero-subtitle">Complete payment records</p>
                </div>
            </div>

            {{-- Desktop Header --}}
            <div class="desktop-only desktop-wrapper">
                <div class="desktop-welcome">
                    <h1 class="welcome-title">Payment History</h1>
                    <div class="welcome-meta">
                        <i class="far fa-calendar-alt mr-2"></i> {{ now()->format('l, d F Y') }}
                        <span class="mx-3">|</span>
                        <i class="far fa-clock mr-2"></i> {{ now()->format('h:i A') }}
                    </div>
                </div>
            </div>

            {{-- Filter Button (Mobile Only) --}}
            <div class="mobile-only px-3 mb-3">
                <button type="button" class="btn btn-block btn-light-purple rounded-pill font-weight-800"
                    onclick="toggleFilters()">
                    <i class="fas fa-filter mr-2"></i> Filter Payments
                </button>
            </div>

            {{-- Filter Drawer (Mobile) / Filter Bar (Desktop) --}}
            <div class="{{ ($isOwner ?? false) ? 'app-filter-drawer' : 'card shadow-sm border-0 mb-3' }}"
                id="paymentFilterDrawer">
                @if($isOwner ?? false)
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="m-0 font-weight-900">Filter Payments</h5>
                        <button type="button" class="btn btn-light rounded-circle" onclick="toggleFilters()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                <form method="GET" action="{{ route(($routePrefix ?? 'admin.payment.history.') . 'index') }}">
                    <div class="row">
                        <div class="col-12 col-md-2 mb-3">
                            <label class="small font-weight-800 text-muted uppercase letter-spacing-1">From Date</label>
                            <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}"onchange="this.form.submit()">
                                </div>
                                <div class="col-12 col-md-2 mb-3">
                                    <label class="small font-weight-800 text-muted uppercase letter-spacing-1">To Date</label>
                                    <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}" onchange="this.form.submit()">
                                </div>
                                <div class="col-12 col-md-2 mb-3">
                                    <label class="small font-weight-800 text-muted uppercase letter-spacing-1">Category</label>
                                    <select class="form-control" name="payment_category" onchange="this.form.submit()">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}" {{ request('payment_category') == $category ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $category)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 mb-3">
                                    <label class="small font-weight-800 text-muted uppercase letter-spacing-1">Mode</label>
                                    <select class="form-control" name="payment_mode" onchange="this.form.submit()">
                                        <option value="">All Modes</option>
                                        <option value="Bank" {{ request('payment_mode') == 'Bank' ? 'selected' : '' }}>All Banks</option>
                                        <option value="Cash" {{ request('payment_mode') == 'Cash' ? 'selected' : '' }}>All Cash</option>
                                        <optgroup label="Specific Banks">
                                            @foreach($banks as $bank)
                                                <option value="Bank:{{ $bank->id }}" {{ request('payment_mode') == "Bank:{$bank->id}" ? 'selected' : '' }}>
                                                    {{ $bank->bank_name }} ({{ substr($bank->account_number, -4) }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Specific Cash">
                                            @foreach($cashAccounts as $cash)
                                                <option value="Cash:{{ $cash->id }}" {{ request('payment_mode') == "Cash:{$cash->id}" ? 'selected' : '' }}>
                                                    {{ $cash->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 mb-3">
                                    <label class="small font-weight-800 text-muted uppercase letter-spacing-1">Type</label>
                                    <select class="form-control" name="payment_type" onchange="this.form.submit()">
                                        <option value="">All Types</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type }}" {{ request('payment_type') == $type ? 'selected' : '' }}>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-purple-gradient btn-block font-weight-800">
                                        <i class="fas fa-search mr-2"></i> Apply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Desktop Table --}}
                    <div class="desktop-only px-3">
                        <div class="card shadow-sm border-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-top-0 py-3 px-4 text-muted small font-weight-800 uppercase">Date</th>
                                            <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">Category</th>
                                            <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">Type</th>
                                            <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">Payee</th>
                                            <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">Amount</th>
                                            <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">Mode</th>
                                            <th class="border-top-0 py-3 text-muted small font-weight-800 uppercase">Ref #</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($payments as $payment)
                                            <tr>
                                                <td class="px-4 align-middle">
                                                    <span class="font-weight-600">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge-soft-purple">{{ ucfirst(str_replace('_', ' ', $payment->payment_category)) }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    @if($payment->payment_type == 'received')
                                                        <span class="badge badge-success px-3 py-2">
                                                            <i class="fas fa-arrow-down mr-1"></i> Received
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger px-3 py-2">
                                                            <i class="fas fa-arrow-up mr-1"></i> Paid
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    @if($payment->payee_name)
                                                        <div class="font-weight-600">{{ $payment->payee_name }}</div>
                                                        <small class="text-muted">Manual</small>
                                                    @elseif($payment->payment_category == 'other' && $payment->paymentType)
                                                        <div class="font-weight-600 text-info">{{ $payment->paymentType->name }}</div>
                                                        <small class="text-muted">Expense Type</small>
                                                    @elseif($payment->party)
                                                        <div class="font-weight-600">{{ $payment->party->name }}</div>
                                                        <small class="text-muted">{{ class_basename($payment->party_type) }}</small>
                                                    @else
                                                        <span class="text-muted">Unknown</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <span class="font-weight-900">₹{{ number_format($payment->amount, 2) }}</span>
                                                </td>
                                                <td class="align-middle text-muted">{{ ucwords(str_replace('_', ' ', $payment->payment_mode)) }}</td>
                                                <td class="align-middle text-muted small">{{ $payment->reference_id ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <i class="fas fa-receipt fa-3x mb-3 opacity-20"></i>
                                                    <p class="font-weight-700 text-muted">No payments found</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="mobile-only">
                        @forelse($payments as $payment)
                            <div class="app-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <span class="badge-soft-purple mb-2 d-inline-block">
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_category)) }}
                                        </span>
                                        <h5 class="font-weight-900 m-0">
                                            @if($payment->payee_name)
                                                {{ $payment->payee_name }}
                                            @elseif($payment->payment_category == 'other' && $payment->paymentType)
                                                {{ $payment->paymentType->name }}
                                            @elseif($payment->party)
                                                {{ $payment->party->name }}
                                            @else
                                                Unknown
                                            @endif
                                        </h5>
                                        <p class="small text-muted m-0 font-weight-600">
                                            {{ ucwords(str_replace('_', ' ', $payment->payment_mode)) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="small font-weight-800 text-muted uppercase d-block">
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M') }}
                                        </span>
                                        @if($payment->payment_type == 'received')
                                            <span class="badge badge-success mt-1">
                                                <i class="fas fa-arrow-down"></i>
                                            </span>
                                        @else
                                            <span class="badge badge-danger mt-1">
                                                <i class="fas fa-arrow-up"></i>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <hr class="my-3 opacity-50">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="small font-weight-800 text-muted uppercase d-block mb-1">Amount</span>
                                        <span class="h5 font-weight-900 {{ $payment->payment_type == 'received' ? 'text-success' : 'text-danger' }} mb-0">
                                            ₹{{ number_format($payment->amount, 2) }}
                                        </span>
                                    </div>
                                    @if($payment->reference_id)
                                        <div class="text-right">
                                            <span class="small font-weight-800 text-muted uppercase d-block mb-1">Ref #</span>
                                            <span class="small font-weight-600">{{ $payment->reference_id }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-receipt fa-3x mb-3 opacity-20"></i>
                                <p class="font-weight-700">No payments found</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <style>
                    /* Owner Portal Specific Styles */
                    .owner-container {
                        background: #f8f9fa;
                        min-height: 100vh;
                        padding-bottom: 2rem;
                    }

                    .app-hero {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        padding: 2rem 1.5rem;
                        color: white;
                        margin-bottom: 1.5rem;
                    }

                    .app-hero-title {
                        font-size: 1.75rem;
                        font-weight: 900;
                        margin: 0 0 0.5rem 0;
                    }

                    .app-hero-subtitle {
                        font-size: 0.95rem;
                        opacity: 0.9;
                        margin: 0;
                    }

                    .app-filter-drawer {
                        position: fixed;
                        bottom: 0;
                        left: 0;
                        right: 0;
                        background: white;
                        padding: 1.5rem;
                        border-radius: 1.5rem 1.5rem 0 0;
                        box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
                        transform: translateY(100%);
                        transition: transform 0.3s ease;
                        z-index: 1000;
                        max-height: 80vh;
                        overflow-y: auto;
                    }

                    .app-filter-drawer.open {
                        transform: translateY(0);
                    }

                    .app-card {
                        background: white;
                        border-radius: 1rem;
                        padding: 1.25rem;
                        margin: 0 1rem 1rem 1rem;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                        transition: all 0.2s ease;
                    }

                    .app-card:active {
                        transform: scale(0.98);
                    }

                    .badge-soft-purple {
                        background: rgba(102, 126, 234, 0.1);
                        color: #667eea;
                        padding: 0.4rem 0.8rem;
                        border-radius: 0.5rem;
                        font-size: 0.75rem;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }

                    .btn-light-purple {
                        background: rgba(102, 126, 234, 0.1);
                        color: #667eea;
                        border: none;
                    }

                    .btn-purple-gradient {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        border: none;
                    }

                    .btn-purple-gradient:hover {
                        background: linear-gradient(135deg, #5568d3 0%, #65408b 100%);
                        color: white;
                    }

                    /* Desktop Styles */
                    .desktop-wrapper {
                        padding: 2rem 3rem 1rem 3rem;
                    }

                    .desktop-welcome {
                        margin-bottom: 2rem;
                    }

                    .welcome-title {
                        font-size: 2rem;
                        font-weight: 900;
                        color: #1a202c;
                        margin: 0 0 0.5rem 0;
                    }

                    .welcome-meta {
                        color: #718096;
                        font-size: 0.95rem;
                    }

                    /* Responsive */
                    @media (max-width: 768px) {
                        .mobile-only { display: block !important; }
                        .desktop-only { display: none !important; }
                    }

                    @media (min-width: 769px) {
                        .mobile-only { display: none !important; }
                        .desktop-only { display: block !important; }
                        .app-filter-drawer {
                            position: relative;
                            transform: none;
                            border-radius: 1rem;
                            padding: 1.5rem;
                            margin: 0 3rem 1.5rem 3rem;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                        }
                    }

                    .uppercase {
                        text-transform: uppercase;
                    }

                    .letter-spacing-1 {
                        letter-spacing: 0.5px;
                    }

                    .opacity-20 {
                        opacity: 0.2;
                    }

                    .opacity-50 {
                        opacity: 0.5;
                    }
                </style>

                <script>
                    function toggleFilters() {
                        document.getElementById('paymentFilterDrawer').classList.toggle('open');
                    }
                </script>
    @else
            {{-- ================= ADMIN PORTAL DESIGN ================= --}}
            <div class="content-wrapper">
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1>Payment History</h1>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    <li class="breadcrumb-item"><a href="{{ route($homeRoute ?? 'admin.dashboard') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Payment History</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-white border-bottom-0 pt-3">
                                        <form method="GET" action="{{ route(($routePrefix ?? 'admin.payment.history.') . 'index') }}" class="w-100">
                                            <div class="row align-items-end">
                                                <div class="col-md-2 mb-2">
                                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-calendar-alt mr-1"></i> From</label>
                                                    <input type="date" class="form-control form-control-sm" name="from_date" value="{{ request('from_date') }}" onchange="this.form.submit()">
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-calendar-alt mr-1"></i> To</label>
                                                    <input type="date" class="form-control form-control-sm" name="to_date" value="{{ request('to_date') }}" onchange="this.form.submit()">
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-tags mr-1"></i> Category</label>
                                                    <select class="form-control form-control-sm" name="payment_category" onchange="this.form.submit()">
                                                        <option value="">All Categories</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category }}" {{ request('payment_category') == $category ? 'selected' : '' }}>
                                                                {{ ucfirst(str_replace('_', ' ', $category)) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-credit-card mr-1"></i> Mode</label>
                                                    <select class="form-control form-control-sm" name="payment_mode" onchange="this.form.submit()">
                                                        <option value="">All Modes</option>
                                                        <option value="Bank" {{ request('payment_mode') == 'Bank' ? 'selected' : '' }}>All Banks</option>
                                                        <option value="Cash" {{ request('payment_mode') == 'Cash' ? 'selected' : '' }}>All Cash</option>
                                                        <optgroup label="Specific Banks">
                                                            @foreach($banks as $bank)
                                                                <option value="Bank:{{ $bank->id }}" {{ request('payment_mode') == "Bank:{$bank->id}" ? 'selected' : '' }}>
                                                                    {{ $bank->bank_name }} ({{ substr($bank->account_number, -4) }})
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                        <optgroup label="Specific Cash">
                                                            @foreach($cashAccounts as $cash)
                                                                <option value="Cash:{{ $cash->id }}" {{ request('payment_mode') == "Cash:{$cash->id}" ? 'selected' : '' }}>
                                                                    {{ $cash->name }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-exchange-alt mr-1"></i> Type</label>
                                                    <select class="form-control form-control-sm" name="payment_type" onchange="this.form.submit()">
                                                        <option value="">All Types</option>
                                                        @foreach($types as $type)
                                                            <option value="{{ $type }}" {{ request('payment_type') == $type ? 'selected' : '' }}>
                                                                {{ ucfirst($type) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2 mb-2 text-right">
                                                    <div class="d-inline-flex">
                                                        <a href="{{ route(($routePrefix ?? 'admin.payment.history.') . 'index') }}" class="btn btn-sm btn-outline-secondary mr-2">
                                                            <i class="fas fa-undo"></i>
                                                        </a>
                                                        <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm">
                                                            <i class="fas fa-filter"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table id="paymentTable" class="table table-hover mb-0" style="width:100%">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="border-top-0 py-3 px-4 text-primary small font-weight-bold uppercase">Date</th>
                                                        <th class="border-top-0 py-3 text-primary small font-weight-bold uppercase">Category</th>
                                                        <th class="border-top-0 py-3 text-primary small font-weight-bold uppercase">Type</th>
                                                        <th class="border-top-0 py-3 text-primary small font-weight-bold uppercase">Payee</th>
                                                        <th class="border-top-0 py-3 text-primary small font-weight-bold uppercase">Amount</th>
                                                        <th class="border-top-0 py-3 text-primary small font-weight-bold uppercase">Mode</th>
                                                        <th class="border-top-0 py-3 text-primary small font-weight-bold uppercase">Ref #</th>
                                                        <th class="border-top-0 py-3 px-4 text-primary small font-weight-bold uppercase text-center">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($payments as $payment)
                                                        <tr>
                                                            <td class="px-4 align-middle">
                                                                <span class="text-dark font-weight-500">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</span>
                                                            </td>
                                                            <td class="align-middle">
                                                                <span class="badge badge-soft-info p-2 px-3" style="background: #e1f5fe; color: #01579b; border-radius: 4px;">
                                                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_category)) }}
                                                                </span>
                                                            </td>
                                                            <td class="align-middle">
                                                                @if($payment->payment_type == 'received')
                                                                    <span class="badge badge-success p-2 px-3" style="border-radius: 4px;">
                                                                        <i class="fas fa-arrow-down mr-1"></i> Received
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-danger p-2 px-3" style="border-radius: 4px;">
                                                                        <i class="fas fa-arrow-up mr-1"></i> Paid
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="align-middle text-nowrap">
                                                                @if($payment->payee_name)
                                                                    <div class="font-weight-500">{{ $payment->payee_name }}</div>
                                                                    <small class="text-muted">Manual</small>
                                                                @elseif($payment->payment_category == 'other' && $payment->paymentType)
                                                                    <div class="font-weight-500 text-info">{{ $payment->paymentType->name }}</div>
                                                                    <small class="text-muted">Expense Type</small>
                                                                @elseif($payment->party)
                                                                    <div class="font-weight-500">{{ $payment->party->name }}</div>
                                                                    <small class="text-muted">{{ class_basename($payment->party_type) }}</small>
                                                                @else
                                                                    <span class="text-danger">Unknown</span>
                                                                @endif
                                                            </td>
                                                            <td class="align-middle">
                                                                <span class="text-dark font-weight-bold">₹{{ number_format($payment->amount, 2) }}</span>
                                                            </td>
                                                            <td class="align-middle">
                                                                <span class="text-secondary">{{ ucwords(str_replace('_', ' ', $payment->payment_mode)) }}</span>
                                                            </td>
                                                            <td class="align-middle text-muted small">{{ $payment->reference_id ?? '-' }}</td>
                                                            <td class="px-4 align-middle text-center">
                                                                <div class="btn-group">
                                                                    <a href="{{ route('admin.payment.history.show', $payment->id) }}"
                                                                        class="btn btn-sm btn-outline-info mr-1" title="View"><i
                                                                            class="fas fa-eye"></i></a>
                                                                    <a href="{{ route('admin.payment.history.edit', $payment->id) }}"
                                                                        class="btn btn-sm btn-outline-warning" title="Edit"><i
                                                                            class="fas fa-edit"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="8" class="text-center py-5">
                                                                <div class="text-muted">
                                                                    <i class="fas fa-receipt fa-3x mb-3 opacity-25"></i>
                                                                    <p>No payments found matching your filters.</p>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforelse
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
        @endif
@endsection

@section('scripts')
    @if(!($isOwner ?? false))
        <script>
            $(function () {
                $("#paymentTable").DataTable({
                    "responsive": true,
                    "lengthChange": false,
                    "autoWidth": false,
                    "ordering": false, 
                    "searching": false,
                    "paging": true,
                    "info": true,
                    "language": {
                        "emptyTable": "No data available in table"
                    }
                });
            });
        </script>
    @endif
@endsection