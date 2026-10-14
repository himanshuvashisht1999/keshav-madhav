{{-- Warns when the account's stored running balance differs from this ledger (opening + all records).
     Only meaningful for the all-time closing, i.e. when no "to" date is set. --}}
@php
    $storedBalance = (isset($party) && method_exists($party, 'getOriginal')) ? $party->getOriginal('balance') : null;
    $ledgerClosing = isset($party) ? (float) $party->balance : null;
@endphp
@if($storedBalance !== null && empty($endDate) && abs((float) $storedBalance - $ledgerClosing) >= 0.01)
    <div class="alert alert-danger d-flex align-items-start mb-2 py-2" role="alert">
        <i class="fas fa-exclamation-triangle mt-1 mr-2"></i>
        <div class="small">
            <strong>Balance mismatch:</strong> the stored balance of this account is
            <strong>₹{{ number_format((float) $storedBalance, 2) }}</strong>, but this ledger closes at
            <strong>₹{{ number_format($ledgerClosing, 2) }}</strong>
            (difference ₹{{ number_format((float) $storedBalance - $ledgerClosing, 2) }}).
            See the <a href="{{ route('admin.balance-issues') }}" class="alert-link">list of wrong ledgers</a>.
        </div>
    </div>
@endif
