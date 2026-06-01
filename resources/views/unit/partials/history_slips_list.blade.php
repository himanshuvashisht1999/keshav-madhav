@foreach($slips as $slip)
    <a href="{{ route('unit.view.slip', ['type' => $slip['type'], 'id' => $slip['id']]) }}" style="text-decoration: none;">
        <div class="card p-0 overflow-hidden shadow-sm hover-elevate">
            <div style="padding: 16px; border-left: 4px solid {{ $slip['status'] == 1 ? '#10b981' : '#6366f1' }};">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div style="font-size: 12px; color: #64748b; font-weight: 600;">
                        <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($slip['created_at'])->format('d M Y, h:i A') }}
                    </div>
                    <div>
                        @if($slip['status'] == 0 && (!isset($slip['sessions']) || $slip['sessions']->isEmpty()))
                            <form action="{{ route('unit.delete.slip', ['type' => $slip['type'], 'id' => $slip['id']]) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm text-danger" style="background: #fee2e2; border-radius: 8px; padding: 4px 10px; font-size: 12px; margin-right: 8px;" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this slip?')) { this.closest('form').submit(); }">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge" style="background: #e0e7ff; color: #4338ca; padding: 6px 12px; font-size: 12px;">
                        <i class="fas {{ $slip['type'] == 'fabric' ? 'fa-layer-group' : 'fa-box' }} mr-1"></i> 
                        {{ ucfirst($slip['type']) }}
                    </span>
                    @if($slip['status'] == 1)
                        <span class="badge" style="background: #dcfce7; color: #166534; padding: 6px 12px; font-size: 12px;">
                            <i class="fas fa-check mr-1"></i> Done
                        </span>
                    @endif
                    <span class="badge" style="background: #f1f5f9; color: #0f172a; padding: 6px 12px; font-size: 12px;">
                        <i class="fas fa-tshirt mr-1"></i> {{ $slip['pieces'] }} Pcs
                    </span>
                </div>

                @if($slip['type'] == 'production' && isset($slip['sessions']) && $slip['sessions']->isNotEmpty())
                    <div style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">
                        Digitized Sessions ({{ $slip['sessions']->count() }})
                    </div>
                    
                    @foreach($slip['sessions']->take(3) as $session)
                    <div style="background: #f8fafc; border-radius: 8px; padding: 10px; margin-bottom: 6px; border: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 2px;">
                                <span style="color: #10b981;">{{ $session['type'] }}</span> - Lot #{{ $session['lot_no'] }}
                            </div>
                            <div style="font-size: 11px; color: #64748b; display: flex; gap: 8px;">
                                <span><i class="fas fa-ruler-combined"></i> {{ $session['size_sets'] }}</span>
                                <span><i class="fas fa-hashtag"></i> {{ $session['design_no'] }}</span>
                                <span><i class="fas fa-user"></i> {{ $session['customer'] }}</span>
                            </div>
                        </div>
                        <div style="font-weight: 800; color: #059669; font-size: 13px;">
                            {{ $session['pieces'] }} Pcs
                        </div>
                    </div>
                    @endforeach
                    
                    @if($slip['sessions']->count() > 3)
                    <div style="text-align: center; font-size: 11px; color: #6366f1; font-weight: 700; margin-top: 5px;">
                        + {{ $slip['sessions']->count() - 3 }} more sessions
                    </div>
                    @endif
                @else
                    <div class="info-grid mt-3">
                        <div class="info-item">
                            <span class="info-label">Lot Numbers</span>
                            <span class="info-value text-primary" style="font-size: 13px;">{{ $slip['lot_no'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Customer</span>
                            <span class="info-value" style="font-size: 13px;">{{ $slip['customer'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Design No</span>
                            <span class="info-value" style="font-size: 13px;">{{ $slip['design_no'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Size Range</span>
                            <span class="info-value" style="font-size: 13px;">{{ $slip['size_sets'] }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </a>
@endforeach
