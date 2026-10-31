@extends('owner.layouts.app')

@section('title', 'Home')

@section('content')
    @include('owner.common.page_head', ['title' => 'Owner dashboard', 'sub' => now()->format('l, d M Y')])

    @include('owner.common.totals', ['totals' => [
        ['label' => 'Customers owe us', 'value' => '₹ ' . number_format($receivable, 2), 'tone' => 'yellow'],
        ['label' => 'We owe vendors', 'value' => '₹ ' . number_format($payable, 2), 'tone' => 'red'],
        ['label' => 'Fabric stock (m)', 'value' => number_format($fabric_metres, 2), 'tone' => 'light'],
        ['label' => 'Ready stock (boxes)', 'value' => number_format($ready_boxes), 'tone' => 'light'],
        ['label' => 'Orders open', 'value' => number_format($open_orders)],
        ['label' => 'Lots in production', 'value' => number_format($lots_in_production)],
    ]])
    <p class="small text-muted mb-3">Lots in production = lots not yet fully dispatched or put in stock; lots of orders marked Completed are left out (tagged Order completed).</p>

    @php
        $heldTotal = 0;   // Dr balances: money in the account
        $usedTotal = 0;   // Cr balances: money used from the bank (CC / OD / loan)

        $menu = [
            'Orders' => [
                ['owner.order-summary.index', 'fa-file-invoice', 'Order Summary', 'Each order: pieces ordered, packed and sent'],
                ['owner.orders', 'fa-shopping-cart', 'Sales Orders', 'Orders with their lots and current stage'],
                ['owner.wip-complete', 'fa-clipboard-check', 'WIP Complete', 'Customer-wise lot progress by stage'],
            ],
            'Production' => [
                ['owner.lots', 'fa-layer-group', 'Lots', 'Every lot, its pieces and where it is now'],
                ['owner.reports.unit-assignments', 'fa-industry', 'Unit Assignments', 'Work given to units, pending and delayed'],
            ],
            'Stock' => [
                ['owner.stock', 'fa-warehouse', 'Fabric Stock', 'Fabric metres by warehouse'],
                ['owner.report.stock.rolls', 'fa-barcode', 'Stock by Roll', 'Find and track single rolls'],
                ['owner.ready-stock.index', 'fa-boxes', 'Ready Stock', 'Finished boxes ready to sell'],
            ],
            'Money' => [
                ['owner.party-ledger.index', 'fa-book-open', 'Party Ledger', 'Customer and vendor accounts'],
                ['owner.bank-cash-ledger.index', 'fa-university', 'Bank & Cash', 'Bank and cash book entries'],
                ['owner.payment.pending.index', 'fa-hourglass-half', 'Pending Payments', 'Who owes us and whom we owe'],
                ['owner.payment.history.index', 'fa-history', 'Payment History', 'Payments and adjustments made'],
                ['owner.reports.delayed-payments', 'fa-exclamation-circle', 'Delayed Payments', 'Parties who have not paid for long'],
                ['owner.reports.selling-items', 'fa-list-ol', 'Selling Items', 'Designs and quantities ordered'],
            ],
        ];
    @endphp

    <div class="row">
        {{-- cash & bank: stored balances (= bank/cash ledger closing); negative = Dr = money held --}}
        <div class="col-lg-5">
            <div class="ow-table-title"><i class="fas fa-university"></i> Cash &amp; bank</div>
            <div class="ow-table-wrap">
                <table class="erp-table sticky-first">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th class="num">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $acc)
                            @php
                                $bal = (float) $acc->balance;
                                $isDr = $bal < 0;
                                if ($isDr) { $heldTotal += -$bal; } else { $usedTotal += $bal; }
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('owner.bank-cash-ledger.show', ['type' => 'bank account', 'id' => $acc->id]) }}">{{ $acc->bank_name }}</a>
                                    <small class="d-block text-muted">{{ $acc->account_name }}</small>
                                </td>
                                <td class="num {{ $bal == 0 ? '' : ($isDr ? 'ow-good' : 'ow-bad') }}">{{ number_format(abs($bal), 2) }} {{ $bal == 0 ? '' : ($isDr ? 'Dr' : 'Cr') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="ow-empty">No bank or cash accounts found</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($accounts))
                        <tfoot>
                            <tr>
                                <td>Money held (Dr)</td>
                                <td class="num ow-good">{{ number_format($heldTotal, 2) }} Dr</td>
                            </tr>
                            <tr>
                                <td>Used from bank (Cr)</td>
                                <td class="num ow-bad">{{ number_format($usedTotal, 2) }} Cr</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            <p class="small text-muted mb-3">Dr = money in the account (cash in hand or in the bank). Cr = money used from the bank (CC, OD or loan).</p>
        </div>

        {{-- menu of every owner page --}}
        <div class="col-lg-7 mt-lg-4">
            <div class="row">
                @foreach($menu as $group => $items)
                    <div class="col-md-6">
                        <nav class="ow-menu">
                            <div class="ow-menu-title">{{ $group }}</div>
                            @foreach($items as [$routeName, $icon, $label, $desc])
                                <a href="{{ route($routeName) }}">
                                    <span class="ow-ico"><i class="fas {{ $icon }}"></i></span>
                                    <span class="ow-txt"><b>{{ $label }}</b><small>{{ $desc }}</small></span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            @endforeach
                        </nav>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
