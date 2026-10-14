@extends('layouts.unit')

@section('title', 'Track Lot')

@push('styles')
<style>
    .ls-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 6px; padding: 9px 12px; font-size: 13px; font-weight: 600; margin-bottom: 10px; }
    .ls-form { display: flex; gap: 8px; align-items: flex-end; }
    .ls-form .xl-field { flex: 1; }
    .ls-form .xl-field input { min-height: 42px; font-size: 16px; font-weight: 700; }
    .ls-form .xl-btn { height: 42px; }
    .ls-help { font-size: 12px; color: var(--u-muted); margin-top: 8px; }
</style>
@endpush

@section('content')
    @if(session('error'))
        <div class="ls-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <form action="{{ route('unit.lot.details') }}" method="GET" class="xl-panel">
        <div class="xl-panel-title"><i class="fas fa-search"></i> Search Lot</div>
        <div class="ls-form">
            <div class="xl-field">
                <label for="lot_no">Lot No</label>
                <input type="text" id="lot_no" name="lot_no" placeholder="e.g. 557" value="{{ request('lot_no') }}" required autofocus>
            </div>
            <button type="submit" class="xl-btn"><i class="fas fa-search"></i> Search</button>
        </div>
        <div class="ls-help">Shows the lot's design, fabric, size-wise pieces, rolls used and the cutting slip.</div>
    </form>
@endsection
