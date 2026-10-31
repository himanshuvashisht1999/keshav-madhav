@extends('admin.layouts.app')

@section('content')
@php
    $m = fn($v) => number_format((float) $v, 2);
    $fmt = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y, h:i A') : '-';
    $rows = $plan['rows'];
@endphp
<div class="content-wrapper erp-page p-2">
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title"><i class="fas fa-tools text-primary"></i> Move RECHARGE lines to RIKSHA KHARCH</div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.payment.adjustment.index') }}" class="btn-erp btn-erp-outline"><i class="fas fa-list mr-1"></i> Payment Adjustments</a>
        </div>
    </div>

    @if(session('fix_error'))
        <div class="alert alert-danger">{{ session('fix_error') }}</div>
    @endif
    @if(session('fix_result'))
        @php $r = session('fix_result'); @endphp
        <div class="alert alert-success">
            <strong>Done.</strong> {{ $r['moved'] }} line(s) worth Rs {{ $m($r['amount']) }} moved to {{ $plan['to']['master'] }} &gt; {{ $plan['to']['party'] }}.
            The cash book did not change. Old values saved in <code>storage/app/{{ $r['log_file'] }}</code>.
        </div>
    @endif

    <div class="erp-card p-3 mb-2">
        @if($rows->isEmpty())
            <div class="text-center py-3">
                <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                <h5 class="font-weight-bold mb-0">No lines are booked to {{ $plan['from']['master'] }} &gt; {{ $plan['from']['party'] }}. Nothing to move.</h5>
            </div>
        @else
            <h5 class="font-weight-bold mb-1">These lines are booked to {{ $plan['from']['master'] }} &gt; {{ $plan['from']['party'] }}</h5>
            <p class="mb-2" style="max-width: 900px;">
                The button moves every <strong>ticked</strong> line to <strong>{{ $plan['to']['master'] }} &gt; {{ $plan['to']['party'] }}</strong>.
                Only the expense head changes: amount, date and the cash / bank account stay the same, so the cash book does not move.
                Untick any line that really was a mobile recharge.
            </p>
            <form method="POST" action="{{ route('admin.recharge-fix.run') }}"
                  onsubmit="var n = this.querySelectorAll('input.line:checked').length; if (!n) { alert('Tick at least one line.'); return false; } if(!confirm('Move ' + n + ' ticked line(s) to {{ $plan['to']['party'] }}?\n\nTake a database backup first (Download DB).')) return false; this.querySelector('button[type=submit]').disabled = true;">
                @csrf
                <table class="table table-sm table-bordered mb-2" style="max-width: 1000px;">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" checked onclick="document.querySelectorAll('input.line').forEach(c => c.checked = this.checked)"></th>
                            <th>Date</th><th class="text-right">Amount (Rs)</th><th>Remark</th><th>Typed in on</th><th>Entry no</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td><input type="checkbox" class="line" name="ids[]" value="{{ $row->id }}" checked></td>
                                <td>{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                                <td class="text-right">{{ $m($row->amount) }}</td>
                                <td>{{ $row->remarks ?: '-' }}</td>
                                <td>{{ $fmt($row->created_at) }}</td>
                                <td><a href="{{ route('admin.payment.adjustment.show', $row->batch_id) }}" target="_blank">{{ $row->batch_id }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold"><td></td><td>{{ $rows->count() }} lines</td><td class="text-right">{{ $m($rows->sum('amount')) }}</td><td colspan="3"></td></tr>
                    </tfoot>
                </table>
                <button type="submit" class="btn-erp btn-erp-primary px-4"><i class="fas fa-exchange-alt mr-1"></i> Move ticked lines to {{ $plan['to']['party'] }}</button>
            </form>
        @endif
    </div>

    @if($runs->isNotEmpty())
        <div class="erp-card p-3 mb-2">
            <h6 class="font-weight-bold mb-1">Earlier runs (old values saved)</h6>
            @foreach($runs as $file)
                <div style="font-size: 13px;"><code>storage/app/{{ $file }}</code></div>
            @endforeach
        </div>
    @endif
</div>
@endsection
