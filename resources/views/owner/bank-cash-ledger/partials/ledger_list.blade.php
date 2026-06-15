@forelse($parties as $party)
    <a href="{{ route('owner.bank-cash-ledger.show', ['type' => $party->party_type, 'id' => $party->id]) }}" class="ledger-card">
        <div class="party-header">
            <div>
                <div class="party-name">{{ $party->name }}</div>
                <div class="party-phone">
                    <i class="fas fa-phone-alt"></i> {{ $party->phone ?: 'No Phone' }}
                </div>
            </div>
            @php
                $typeClass = in_array($party->party_type, ['customer', 'vendor', 'sales_agent']) ? $party->party_type : 'default';
            @endphp
            <span class="type-badge party-type-{{ $typeClass }}">
                {{ str_replace('_', ' ', $party->party_type) }}
            </span>
        </div>

        <div class="balance-box">
            <div>
                <div class="balance-label">Current Balance</div>
                @php 
                    $bal = (float)$party->balance;
                    $isCr = $bal >= 0;
                @endphp
                <div class="balance-amount {{ $isCr ? 'balance-cr' : 'balance-dr' }}">
                    ₹ {{ number_format(abs($bal), 2) }}
                    <span class="cr-dr-indicator {{ $isCr ? 'cr' : 'dr' }}">
                        {{ $isCr ? 'Cr' : 'Dr' }}
                    </span>
                </div>
            </div>
            <div class="action-btn">
                <i class="fas fa-chevron-right"></i>
            </div>
        </div>
    </a>
@empty
    <div class="text-center py-5 opacity-50">
        <i class="fas fa-book fa-3x mb-3 text-muted"></i>
        <h6>No parties found in the ledger</h6>
    </div>
@endforelse
