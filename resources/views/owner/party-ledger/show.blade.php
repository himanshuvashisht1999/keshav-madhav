@extends('owner.layouts.app')

@section('title', 'Ledger Details')

@section('styles')
<style>
    :root {
        --success-gradient: var(--primary-gradient);
        --danger-gradient: var(--primary-gradient);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
        --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    body {
        background: #f8fafc;
    }

    .app-header {
        background: var(--primary-gradient);
        padding: 40px 20px 80px;
        border-radius: 0 0 40px 40px;
        color: white;
        margin-bottom: -50px;
        position: relative;
        z-index: 1;
    }

    .breadcrumb-custom {
        display: flex;
        gap: 8px;
        font-size: 12px;
        opacity: 0.8;
        margin-bottom: 20px;
        align-items: center;
    }

    .breadcrumb-custom a {
        color: white;
        text-decoration: none;
    }

    .party-title {
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 5px;
        line-height: 1.2;
    }

    .party-meta {
        font-size: 12px;
        opacity: 0.8;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .balance-cards {
        display: flex;
        gap: 15px;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }

    .bal-card {
        flex: 1;
        background: var(--glass-bg);
        backdrop-filter: blur(8px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 15px;
        box-shadow: var(--card-shadow);
    }

    .bal-label {
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 800;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .bal-value {
        font-size: 18px;
        font-weight: 900;
        color: var(--text-main);
    }

    .filters-section {
        padding: 20px;
        background: white;
        margin: 20px 20px;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
    }

    .filter-input {
        background: #f1f5f9;
        border: none;
        border-radius: 12px;
        padding: 10px 15px;
        font-size: 13px;
        width: 100%;
        margin-bottom: 10px;
        font-weight: 600;
        color: var(--text-main);
    }

    .transaction-list {
        padding: 0 20px 40px;
    }

    .tx-card {
        background: white;
        border-radius: 16px;
        padding: 15px;
        margin-bottom: 12px;
        box-shadow: var(--card-shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .tx-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-right: 12px;
    }

    .icon-cr { background: #dcfce7; color: #16a34a; }
    .icon-dr { background: #fee2e2; color: #dc2626; }
    .icon-neutral { background: #f1f5f9; color: var(--text-muted); }

    .tx-left { display: flex; align-items: center; flex: 1; min-width: 0; }
    
    .tx-details { flex: 1; min-width: 0; }
    .tx-title { font-size: 14px; font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px; }
    .tx-date { font-size: 11px; color: var(--text-muted); font-weight: 600; }
    .tx-ref { font-size: 10px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: var(--text-muted); font-weight: 700; margin-left: 6px; }

    .tx-right { text-align: right; min-width: 90px; }
    .tx-amount { font-size: 15px; font-weight: 800; }
    .text-cr { color: #16a34a; }
    .text-dr { color: #dc2626; }
    .tx-bal { font-size: 10px; color: var(--text-muted); font-weight: 700; margin-top: 2px; }

    .section-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--text-main);
        margin: 20px 20px 15px;
    }
</style>
@endsection

@section('content')
<div class="responsive-app-view">
    <div class="app-header">
        <div class="breadcrumb-custom">
            <a href="{{ route('owner.party-ledger.index') }}">Party Ledger</a>
            <i class="fas fa-chevron-right" style="font-size: 8px;"></i>
            <span>Details</span>
        </div>
        <h1 class="party-title">{{ $party->name }}</h1>
        <div class="party-meta">
            <span><i class="fas fa-tag"></i> {{ ucfirst($type) }}</span>
            <span><i class="fas fa-phone-alt"></i> {{ $party->phone ?: 'No Phone' }}</span>
        </div>
    </div>

    <div class="balance-cards">
        <div class="bal-card">
            <div class="bal-label">Opening</div>
            <div class="bal-value {{ $openingBalAmount >= 0 ? 'text-cr' : 'text-dr' }}">
                ₹{{ number_format(abs($openingBalAmount)) }}
                <span style="font-size: 10px;">{{ $openingBalAmount >= 0 ? 'CR' : 'DR' }}</span>
            </div>
        </div>
        <div class="bal-card">
            <div class="bal-label">Current Balance</div>
            <div class="bal-value {{ $party->balance >= 0 ? 'text-cr' : 'text-dr' }}">
                ₹{{ number_format(abs($party->balance)) }}
                <span style="font-size: 10px;">{{ $party->balance >= 0 ? 'CR' : 'DR' }}</span>
            </div>
        </div>
    </div>

    <div class="filters-section">
        <form action="{{ route('owner.party-ledger.show', ['type' => $type, 'id' => $party->id]) }}" method="GET" id="filterForm">
            <div class="row" style="margin: 0 -5px;">
                <div class="col-6" style="padding: 0 5px;">
                    <div style="font-size: 10px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Start Date</div>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="filter-input" onchange="document.getElementById('filterForm').submit()">
                </div>
                <div class="col-6" style="padding: 0 5px;">
                    <div style="font-size: 10px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">End Date</div>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="filter-input" onchange="document.getElementById('filterForm').submit()">
                </div>
            </div>
            @if($type === 'sales_agent')
                <div style="font-size: 10px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Customer Filter</div>
                <select name="customer_id" class="filter-input" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Customers</option>
                    @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ request('customer_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                    @endforeach
                </select>
                @if(!request('customer_id'))
                    <div style="font-size: 10px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">View Mode</div>
                    <select name="view_mode" class="filter-input" onchange="document.getElementById('filterForm').submit()">
                        <option value="mix" {{ request('view_mode', 'mix') === 'mix' ? 'selected' : '' }}>Consolidated</option>
                        <option value="party_wise" {{ request('view_mode') === 'party_wise' ? 'selected' : '' }}>Party-wise</option>
                    </select>
                @endif
            @endif
            
            <div class="d-flex justify-content-between mt-2">
                <a href="{{ route('owner.party-ledger.show', ['type' => $type, 'id' => $party->id]) }}" class="btn btn-sm" style="background: #f1f5f9; color: var(--text-muted); font-weight: 700; border-radius: 10px;">Clear</a>
                <a href="{{ route('owner.party-ledger.download', ['type' => $type, 'id' => $party->id] + request()->all()) }}" class="btn btn-sm text-white" style="background: #ef4444; font-weight: 700; border-radius: 10px;"><i class="fas fa-file-pdf mr-1"></i> PDF</a>
            </div>
        </form>
    </div>

    <div class="section-title">Transactions</div>
    <div class="transaction-list">
        @if(isset($viewMode) && $viewMode === 'party_wise' && isset($groupedLedgers))
            {{-- Loop for party-wise view --}}
            @forelse($groupedLedgers as $ledger)
                <div style="font-size: 14px; font-weight: 800; color: var(--text-main); margin: 20px 0 10px;">
                    <i class="fas fa-store mr-2 text-primary"></i>{{ $ledger->shop->name }}
                </div>
                @php $currentBalance = $ledger->opening_balance; @endphp
                @forelse($ledger->transactions as $tx)
                    @php 
                        $currentBalance = $tx->running_balance;
                        $isCr = $tx->credit > 0;
                        $isDr = $tx->debit > 0;
                    @endphp
                    <div class="tx-card">
                        <div class="tx-left">
                            <div class="tx-icon {{ $isCr ? 'icon-cr' : ($isDr ? 'icon-dr' : 'icon-neutral') }}">
                                <i class="fas {{ $isCr ? 'fa-arrow-down' : ($isDr ? 'fa-arrow-up' : 'fa-exchange-alt') }}"></i>
                            </div>
                            <div class="tx-details">
                                <div class="tx-title">{{ $tx->type }} <span class="tx-ref">{{ $tx->ref }}</span></div>
                                <div class="tx-date">{{ date('d M Y', strtotime($tx->date)) }} • <span style="color: var(--text-muted);">{{ Str::limit($tx->description, 20) }}</span></div>
                            </div>
                        </div>
                        <div class="tx-right">
                            @if($isCr)
                                <div class="tx-amount text-cr">+₹{{ number_format($tx->credit) }}</div>
                            @elseif($isDr)
                                <div class="tx-amount text-dr">-₹{{ number_format($tx->debit) }}</div>
                            @else
                                <div class="tx-amount text-muted">₹0</div>
                            @endif
                            <div class="tx-bal">Bal: ₹{{ number_format(abs($currentBalance)) }}{{ $currentBalance >= 0 ? 'CR' : 'DR' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted font-weight-600">No transactions found.</div>
                @endforelse
            @empty
                <div class="text-center py-5 text-muted">No customers found.</div>
            @endforelse
        @else
            {{-- Default mix view --}}
            @php $currentBalance = 0; @endphp
            @forelse($transactions as $tx)
                @php 
                    $currentBalance = $tx->running_balance;
                    $isCr = $tx->credit > 0;
                    $isDr = $tx->debit > 0;
                @endphp
                <div class="tx-card">
                    <div class="tx-left">
                        <div class="tx-icon {{ $isCr ? 'icon-cr' : ($isDr ? 'icon-dr' : 'icon-neutral') }}">
                            <i class="fas {{ $isCr ? 'fa-arrow-down' : ($isDr ? 'fa-arrow-up' : 'fa-exchange-alt') }}"></i>
                        </div>
                        <div class="tx-details">
                            <div class="tx-title">{{ $tx->type }} <span class="tx-ref">{{ $tx->ref }}</span></div>
                            <div class="tx-date">{{ date('d M Y', strtotime($tx->date)) }} • <span style="color: var(--text-muted);">{{ Str::limit($tx->description, 20) }}</span></div>
                        </div>
                    </div>
                    <div class="tx-right">
                        @if($isCr)
                            <div class="tx-amount text-cr">+₹{{ number_format($tx->credit) }}</div>
                        @elseif($isDr)
                            <div class="tx-amount text-dr">-₹{{ number_format($tx->debit) }}</div>
                        @else
                            <div class="tx-amount text-muted">₹0</div>
                        @endif
                        <div class="tx-bal">Bal: ₹{{ number_format(abs($currentBalance)) }}{{ $currentBalance >= 0 ? 'CR' : 'DR' }}</div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-receipt text-muted" style="font-size: 40px; opacity: 0.3; margin-bottom: 10px;"></i>
                    <div class="font-weight-bold text-muted">No transactions found.</div>
                </div>
            @endforelse
        @endif
    </div>
</div>


@endsection
