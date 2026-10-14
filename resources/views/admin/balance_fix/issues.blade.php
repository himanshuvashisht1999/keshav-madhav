@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title"><i class="fas fa-list text-primary"></i> Wrong ledgers</div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.balance-fix.show') }}" class="btn-erp btn-erp-outline"><i class="fas fa-tools mr-1"></i> Correct balances</a>
        </div>
    </div>

    @if(!$prepared)
        <div class="alert alert-warning">
            The database has not been prepared yet, so this list cannot be made. Open
            <a href="{{ route('admin.balance-fix.show') }}" class="alert-link">Correct balances</a> first.
        </div>
    @else
        <div class="erp-card mb-2">
            <div class="erp-card-header">
                <h3 class="erp-card-title mb-0">
                    @if($wrong->isEmpty())
                        <i class="fas fa-check-circle text-success"></i> No wrong ledgers - every balance matches its ledger
                    @else
                        <i class="fas fa-exclamation-triangle text-danger"></i> {{ $wrong->count() }} account(s) where the balance does not match the ledger
                    @endif
                </h3>
            </div>
            @if($wrong->isNotEmpty())
                <div class="table-responsive">
                    <table class="table erp-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Account</th>
                                <th class="text-right">Balance shown</th>
                                <th class="text-right">Ledger balance (correct)</th>
                                <th class="text-right">Difference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wrong as $i => $m)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $m['type'] }}</td>
                                    <td>
                                        <a href="{{ route('admin.ledger.party.show', ['type' => $m['ledger_type'], 'id' => $m['id']]) }}" target="_blank" class="font-weight-bold">{{ $m['name'] }}</a>
                                    </td>
                                    <td class="text-right">₹{{ number_format($m['stored'], 2) }}</td>
                                    <td class="text-right">₹{{ number_format($m['ledger'], 2) }}</td>
                                    <td class="text-right font-weight-bold text-danger">₹{{ number_format($m['difference'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($corrected->isNotEmpty())
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title mb-0"><i class="fas fa-history"></i> Already corrected ({{ $corrected->count() }})</h3>
                </div>
                <div class="table-responsive">
                    <table class="table erp-table mb-0">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>Type</th>
                                <th>Account</th>
                                <th>Changed</th>
                                <th class="text-right">Old</th>
                                <th class="text-right">New</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($corrected as $c)
                                <tr>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($c->created_at)->format('d M Y, h:i A') }}</td>
                                    <td>{{ ucwords($c->ledger_type) }}</td>
                                    <td>
                                        <a href="{{ route('admin.ledger.party.show', ['type' => $c->ledger_type, 'id' => $c->party_id]) }}" target="_blank">{{ $c->party_name }}</a>
                                    </td>
                                    <td>{{ $c->field === 'opening' ? 'Opening balance' : 'Balance' }}</td>
                                    <td class="text-right">₹{{ number_format($c->old_value, 2) }}</td>
                                    <td class="text-right">₹{{ number_format($c->new_value, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
