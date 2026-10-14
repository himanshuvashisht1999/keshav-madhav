@extends('layouts.unit')

@section('title', 'History')
@section('header_icon')
    <i class="fas fa-history"></i>
@endsection

@push('styles')
<style>
    .hs-switch { display: flex; background: #fff; border: 1px solid var(--u-line); border-radius: 8px; padding: 3px; margin-bottom: 10px; }
    .hs-switch label { flex: 1; margin: 0; }
    .hs-switch input { display: none; }
    .hs-switch span { display: block; text-align: center; padding: 7px 6px; border-radius: 6px; font-weight: 700; font-size: 13px; color: var(--u-muted); cursor: pointer; }
    .hs-switch input:checked + span { background: var(--u-green); color: #fff; }
    .hs-switch input:checked + span i { color: var(--u-yellow); }
    .hs-filter { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)) auto auto; gap: 8px; align-items: end; }
    .hs-hint { font-size: 12px; color: var(--u-muted); margin: 0 2px 8px; }
    .hs-filter label { display: block; font-size: 11px; font-weight: 700; color: var(--u-muted); text-transform: uppercase; margin-bottom: 3px; }
    .hs-filter input, .hs-filter select { width: 100%; height: 34px; padding: 4px 10px; border: 1px solid var(--u-line); border-radius: 6px; font-size: 14px; background: #fff; }
    .hs-filter .xl-btn { height: 34px; }
    .hs-filter .hs-actions { display: contents; }
    .hs-table .hs-row[data-href] { cursor: pointer; }
    .hs-table .hs-lots { font-weight: 700; color: var(--u-green); min-width: 110px; }
    .hs-table .hs-sub { font-size: 11px; color: var(--u-muted); font-weight: 500; }
    .hs-table .hs-size { min-width: 110px; }
    .hs-table .hs-icon { color: var(--u-word); }
    .hs-table .hs-go { color: var(--u-lime); margin-left: 4px; }
    .hs-table .hs-del { border: 0; background: #fee2e2; color: #b91c1c; width: 26px; height: 26px; border-radius: 6px; cursor: pointer; }
    .hs-table .hs-late { color: #b91c1c; font-weight: 700; }
    .hs-dir { font-size: 12px; font-weight: 800; }
    .hs-in { color: var(--u-green); }
    .hs-out { color: #b45309; }
    .hs-empty { padding: 30px 16px !important; text-align: center; color: var(--u-muted); }
    @media (max-width: 640px) {
        .hs-filter { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .hs-filter .hs-actions { grid-column: 1 / -1; display: flex; gap: 8px; }
        .hs-filter .hs-actions .xl-btn { flex: 1; }
    }
</style>
@endpush

@section('content')
    <div class="hs-switch">
        <label>
            <input type="radio" name="view_mode" value="slips" {{ $viewType === 'slips' ? 'checked' : '' }}
                onchange="window.location.href='{{ route('unit.history', array_merge(request()->query(), ['view_type' => 'slips'])) }}'">
            <span><i class="fas fa-file-invoice mr-1"></i> Slips I Sent</span>
        </label>
        <label>
            <input type="radio" name="view_mode" value="tasks" {{ $viewType === 'tasks' ? 'checked' : '' }}
                onchange="window.location.href='{{ route('unit.history', array_merge(request()->query(), ['view_type' => 'tasks'])) }}'">
            <span><i class="fas fa-exchange-alt mr-1"></i> Lots In &amp; Out</span>
        </label>
    </div>

    <!-- Filters -->
    <form action="{{ route('unit.history') }}" method="GET" class="xl-panel">
        <input type="hidden" name="view_type" value="{{ $viewType }}">
        <div class="hs-filter">
            <div>
                <label for="lot_no">Lot No</label>
                <input type="text" id="lot_no" name="lot_no" placeholder="e.g. 557" value="{{ request('lot_no') }}">
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ $viewType === 'slips' ? 'Waiting' : 'In progress' }}</option>
                    <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                </select>
            </div>
            @if(($viewType ?? '') === 'tasks')
                <div>
                    <label for="activity_type">In / Out</label>
                    <select id="activity_type" name="activity_type">
                        <option value="">All</option>
                        <option value="received" {{ request('activity_type') === 'received' ? 'selected' : '' }}>Received only</option>
                        <option value="sent" {{ request('activity_type') === 'sent' ? 'selected' : '' }}>Sent only</option>
                    </select>
                </div>
            @endif
            <div class="hs-actions">
                <button type="submit" class="xl-btn"><i class="fas fa-search"></i> Find</button>
                <a href="{{ route('unit.history', ['view_type' => $viewType]) }}" class="xl-btn xl-btn-outline"><i class="fas fa-undo"></i> Clear</a>
            </div>
        </div>
    </form>

    @if($viewType === 'slips')
        <div class="hs-hint">
            <span class="xl-pill xl-pill-open">Waiting</span> office has not entered your slip yet &nbsp;
            <span class="xl-pill xl-pill-done">Done</span> entered
        </div>
    @endif

    @if($viewType === 'slips')
        <div class="xl-wrap">
            <table class="hs-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Lots (pcs)</th>
                        <th class="xl-num">Pieces</th>
                        <th>Type</th>
                        <th>Design</th>
                        <th>Size</th>
                        <th>Customer</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="slipsContainer">
                    @if($slips->isEmpty())
                        <tr><td colspan="9" class="hs-empty"><strong>No slips yet</strong><br>Your uploaded slips will appear here.</td></tr>
                    @else
                        @include('unit.partials.history_slips_list', ['slips' => $slips])
                    @endif
                </tbody>
            </table>
        </div>
    @else
        <div class="xl-wrap">
            <table class="hs-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>In / Out</th>
                        <th>Lot</th>
                        <th class="xl-num">Qty</th>
                        <th>Status</th>
                        <th>Design</th>
                        <th>Size Sets</th>
                        <th>Stage</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Customer</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tasksContainer">
                    @if($tasks->isEmpty())
                        <tr><td colspan="12" class="hs-empty"><strong>No tasks found</strong><br>Assigned tasks will appear here.</td></tr>
                    @else
                        @include('unit.partials.history_tasks_list', ['tasks' => $tasks])
                    @endif
                </tbody>
            </table>
        </div>
    @endif

    @if($paginator->hasMorePages())
        <div id="loadingMore" style="text-align: center; padding: 14px; color: var(--u-muted); font-size: 13px;">
            <i class="fas fa-spinner fa-spin"></i> Loading more...
        </div>
    @endif
@endsection
@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        var row = e.target.closest('tr.hs-row[data-href]');
        if (!row || e.target.closest('a, button, form, input, select')) return;
        window.location.href = row.getAttribute('data-href');
    });

    document.addEventListener('DOMContentLoaded', function() {
        let page = {{ $paginator->currentPage() }};
        let hasMorePages = {{ $paginator->hasMorePages() ? 'true' : 'false' }};
        let loading = false;
        
        window.addEventListener('scroll', function() {
            if (loading || !hasMorePages) return;
            
            // Check if we are near the bottom of the page
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) {
                loading = true;
                page++;
                
                let url = new URL(window.location.href);
                url.searchParams.set('page', page);
                
                fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    if (html.trim() !== '') {
                        let container = document.getElementById('slipsContainer') || document.getElementById('tasksContainer');
                        container.insertAdjacentHTML('beforeend', html);
                        loading = false;
                    } else {
                        hasMorePages = false;
                        let loader = document.getElementById('loadingMore');
                        if (loader) loader.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading more:', error);
                    loading = false;
                });
            }
        });
    });
</script>
@endpush