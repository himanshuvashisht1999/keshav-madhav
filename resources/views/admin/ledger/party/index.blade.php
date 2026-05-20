@extends('admin.layouts.app')

@section('content')
<style>
    .ledger-card {
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        border: none;
        transition: transform 0.3s ease;
    }
    .ledger-card:hover {
        transform: translateY(-5px);
    }
    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .party-type-customer {
        background: #e3f2fd;
        color: #1976d2;
    }
    .party-type-vendor {
        background: #fff3e0;
        color: #f57c00;
    }
    .party-type-sales_agent {
        background: #f3e5f5;
        color: #8e24aa;
    }
    .search-box {
        border-radius: 25px;
        padding-left: 20px;
    }
    .balance-positive {
        color: #2e7d32;
        font-weight: 700;
    }
    .balance-negative {
        color: #d32f2f;
        font-weight: 700;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold">Party Financial Ledger</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card ledger-card mb-4">
                <div class="card-body">
                    <form action="{{ route('admin.ledger.party.index') }}" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="small font-weight-bold text-muted">Search Party Name</label>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control search-box" placeholder="Search by name...">
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-muted">Filter by Master Type</label>
                                <select name="type_id" class="form-control" style="border-radius: 20px;">
                                    <option value="">All Master Types</option>
                                    <option value="sales_agent" {{ request('type_id') === 'sales_agent' ? 'selected' : '' }}>Sales Agent</option>
                                    @foreach($masters as $m)
                                        <option value="{{ $m->id }}" {{ request('type_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary px-4" style="border-radius: 20px;">
                                    <i class="fas fa-filter mr-2"></i>Apply Filter
                                </button>
                                <a href="{{ route('admin.ledger.party.index') }}" class="btn btn-light ml-2" style="border-radius: 20px;">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card ledger-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 pl-4 py-3">Party Name</th>
                                    <th class="border-0 py-3">Type</th>
                                    <th class="border-0 py-3">Phone</th>
                                    <th class="border-0 py-3 text-right">Current Balance</th>
                                    <th class="border-0 pr-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($parties as $party)
                                    <tr>
                                        <td class="pl-4 py-3 align-middle font-weight-bold text-dark">
                                            {{ $party->name }}
                                        </td>
                                        <td class="align-middle">
                                            <span class="status-badge party-type-{{ $party->party_type }}">
                                                {{ ucfirst($party->party_type) }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-muted">{{ $party->phone ?? '-' }}</td>
                                        <td class="align-middle text-right">
                                            @php 
                                                $bal = (float)$party->balance;
                                            @endphp
                                            <span class="{{ $bal >= 0 ? 'balance-positive' : 'balance-negative' }}">
                                                ₹ {{ number_format(abs($bal), 2) }}
                                                <small class="text-muted ml-1">
                                                    {{ $bal >= 0 ? 'CR' : 'DR' }}
                                                </small>
                                            </span>
                                        </td>
                                        <td class="pr-4 py-3 align-middle text-center">
                                            <a href="{{ route('admin.ledger.party.show', ['type' => $party->party_type, 'id' => $party->id]) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 15px;">
                                                <i class="fas fa-eye mr-1"></i> View Ledger
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <img src="{{ asset('images/no-data.png') }}" alt="No Data" style="height: 100px; opacity: 0.5;">
                                            <p class="text-muted mt-3">No parties found matching your search.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
