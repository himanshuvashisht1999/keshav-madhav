@forelse($items as $item)
    <div class="item-card">
        <div class="design-badge">
            #{{ $item->rank ?? ($loop->iteration) }}
        </div>
        
        <div class="item-header">
            <div class="item-icon">
                <i class="fas fa-tshirt"></i>
            </div>
            <div>
                <h3 class="design-title">{{ $item->design_number }}</h3>
                <p class="design-subtitle">Design / Product</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-label">
                    <i class="fas fa-shopping-cart text-info"></i> Sales Orders
                </div>
                <div class="stat-val text-info">{{ number_format($item->sales_qty) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">
                    <i class="fas fa-user-tie text-warning"></i> Agent Orders
                </div>
                <div class="stat-val text-warning">{{ number_format($item->agent_qty) }}</div>
            </div>
        </div>

        <div class="total-banner">
            <span class="label">Total Quantity</span>
            <span class="value">{{ number_format($item->total_qty) }}</span>
        </div>
    </div>
@empty
    <!-- Handled in the main view for page 1 -->
@endforelse
