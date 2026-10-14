@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title"><i class="fas fa-tools text-primary"></i> Correct balances</div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.balance-issues') }}" class="btn-erp btn-erp-outline"><i class="fas fa-list mr-1"></i> List of wrong ledgers</a>
        </div>
    </div>

    @if(session('fix_error'))
        <div class="alert alert-danger">{{ session('fix_error') }}</div>
    @endif

    @if(session('fix_result'))
        @php $r = session('fix_result'); @endphp
        <div class="alert {{ $r['still_wrong'] ? 'alert-warning' : 'alert-success' }}">
            <strong>{{ $r['corrected'] }}</strong> account(s) corrected.
            @if($r['still_wrong'])
                {{ $r['still_wrong'] }} account(s) still differ - see the <a href="{{ route('admin.balance-issues') }}" class="alert-link">list</a>.
            @else
                All {{ $r['checked'] }} accounts now match their ledgers.
            @endif
        </div>
    @endif

    <div class="erp-card p-4">
        @if(!$prepared)
            <h5 class="font-weight-bold mb-2">The database needs a one-time preparation</h5>
            <p class="mb-3">Clicking the button will:</p>
            <ol class="mb-3">
                <li>Prepare the database once (opening balances become one per account, a correction log is created).</li>
                <li>Correct every account whose balance does not match its ledger.</li>
                <li>Check all accounts again.</li>
            </ol>
        @elseif($wrong->isEmpty())
            <div class="text-center py-3">
                <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                <h5 class="font-weight-bold mb-0">Every balance matches its ledger. Nothing to correct.</h5>
            </div>
        @else
            <h5 class="font-weight-bold mb-2">{{ $wrong->count() }} account(s) have a wrong balance</h5>
            <p class="mb-3">
                The button sets each account's balance to what its ledger (opening balance + all records) adds up to.
                Capital accounts whose opening balance was never saved get their opening saved instead.
                Every change is logged (old and new value) and shown on the <a href="{{ route('admin.balance-issues') }}">list</a>.
            </p>
        @endif

        @if(!$prepared || $wrong->isNotEmpty())
            <form method="POST" action="{{ route('admin.balance-fix.run') }}"
                  onsubmit="if(!confirm('Correct the database now?\n\nTake a database backup first (Download DB).')) return false; this.querySelector('button').disabled = true; this.querySelector('button').innerHTML = '<i class=\'fas fa-spinner fa-spin mr-1\'></i> Correcting, please wait (about 1 minute)...';">
                @csrf
                <a href="{{ route('admin.download-db') }}" class="btn-erp btn-erp-outline mr-2"><i class="fas fa-database mr-1"></i> 1. Download backup</a>
                <button type="submit" class="btn-erp btn-erp-primary px-4"><i class="fas fa-magic mr-1"></i> 2. Correct database</button>
            </form>
        @endif
    </div>
</div>
@endsection
