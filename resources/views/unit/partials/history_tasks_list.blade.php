@foreach($tasks as $task)
    <div class="card p-0 overflow-hidden shadow-sm" style="border-radius: 16px;">
        <div style="background: {{ $task['event_type'] == 'received' ? '#eff6ff' : '#f0fdf4' }}; 
                    padding: 12px 16px; 
                    border-bottom: 1px solid {{ $task['event_type'] == 'received' ? '#dbeafe' : '#dcfce7' }};
                    display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                @if($task['event_type'] == 'received')
                    <div style="background: #3b82f6; color: white; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <span style="font-weight: 700; color: #1e40af; font-size: 14px;">Received Task</span>
                @else
                    <div style="background: #22c55e; color: white; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <span style="font-weight: 700; color: #166534; font-size: 14px;">Sent / Alotted</span>
                @endif
            </div>
            
            <div style="text-align: right;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">
                    <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($task['created_at'])->format('d M Y, h:i A') }}
                </div>
            </div>
        </div>

        <div style="padding: 16px;">
            <div class="info-grid">
                @if($task['lot_no'] && $task['lot_no'] !== '-')
                <div class="info-item">
                    <span class="info-label">Lot Number</span>
                    <span class="info-value text-primary">#{{ $task['lot_no'] }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Design No</span>
                    <span class="info-value">{{ $task['design_no'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Customer</span>
                    <span class="info-value">{{ $task['customer'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Quantity</span>
                    <span class="info-value" style="font-size: 16px; font-weight: 800; color: #0f172a;">{{ $task['quantity'] }} Pcs</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Size Sets</span>
                    <span class="info-value">{{ $task['size_sets'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ $task['event_type'] == 'received' ? 'From Stage' : 'To Stage' }}</span>
                    <span class="info-value" style="color: #6366f1;">{{ $task['from_stage'] }}</span>
                </div>

                @if(isset($task['start_date']) || isset($task['end_date']))
                <div class="info-item info-full mt-2" style="background: #f8fafc; padding: 10px; border-radius: 8px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 5px; text-transform: uppercase;">Expected Timeline</div>
                    <div style="display: flex; justify-content: space-between;">
                        <div>
                            <span style="font-size: 10px; color: #94a3b8;">Start:</span>
                            <span style="font-size: 12px; font-weight: 600; color: #334155;">{{ $task['start_date'] ? date('d M Y, h:i A', strtotime($task['start_date'])) : '-' }}</span>
                        </div>
                        <div>
                            <span style="font-size: 10px; color: #94a3b8;">End:</span>
                            <span style="font-size: 12px; font-weight: 600; color: {{ !$task['complete_date'] && now() > $task['end_date'] ? '#dc2626' : '#334155' }};">
                                {{ $task['end_date'] ? date('d M Y, h:i A', strtotime($task['end_date'])) : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <div style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px dashed #e2e8f0;">
                <div>
                    @if($task['status'] == 1)
                        <span class="badge" style="background: #dcfce7; color: #166534; padding: 6px 12px; font-size: 12px;">
                            <i class="fas fa-check-circle mr-1"></i> Completed
                        </span>
                    @else
                        <span class="badge" style="background: #fef9c3; color: #854d0e; padding: 6px 12px; font-size: 12px;">
                            <i class="fas fa-hourglass-half mr-1"></i> In Progress
                        </span>
                    @endif
                </div>
                
                @if($task['slip_id'])
                    <a href="{{ route('unit.view.slip', ['type' => 'production', 'id' => $task['slip_id']]) }}" class="btn btn-sm btn-light" style="font-weight: 600; border: 1px solid #e2e8f0;">
                        <i class="fas fa-file-invoice mr-1"></i> View Slip
                    </a>
                @elseif($task['event_type'] == 'received' && $task['status'] == 0 && isset($task['id']))
                    @php 
                        $routeParams = ['type' => $task['type'], 'id' => $task['id']];
                        $routeUrl = route('unit.assignments.details', $routeParams);
                    @endphp
                    <a href="{{ $routeUrl }}" class="btn btn-sm btn-primary" style="font-weight: 600; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);">
                        <i class="fas fa-play mr-1"></i> Process Task
                    </a>
                @endif
            </div>
        </div>
    </div>
@endforeach
