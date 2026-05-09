@extends('layouts.unit')

@section('title', 'Order Tasks')

@push('styles')
<style>
    body {
        background: #f1f5f9;
        color: #1e293b;
    }

    .page-header-simple {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 0;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .breadcrumb-simple {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        list-style: none;
        padding: 0;
    }

    .breadcrumb-simple li a {
        color: #64748b;
        font-size: 13px;
        text-decoration: none;
    }

    .breadcrumb-simple li::after {
        content: "/";
        margin-left: 8px;
        color: #cbd5e1;
    }

    .breadcrumb-simple li:last-child::after {
        content: "";
    }

    .breadcrumb-simple li.active {
        color: #1e293b;
        font-weight: 600;
        font-size: 13px;
    }

    .header-content-simple {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .title-simple {
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .order-badge-simple {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
    }

    .btn-back-simple {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        text-decoration: none !important;
        transition: all 0.2s;
    }

    .btn-back-simple:hover {
        background: #f1f5f9;
        color: #1e293b;
    }
</style>
@endpush

@section('content')
<div class="page-header-simple">
    <div class="container">
        <div class="header-content-simple">
            <div>
                <ul class="breadcrumb-simple">
                    <li><a href="{{ route('unit.assignments') }}">Assignments</a></li>
                    <li class="active">Tasks</li>
                </ul>
                <h1 class="title-simple">
                    Tasks for {{ $groupLabel ?? 'Order' }} <span class="order-badge-simple">{{ $orderSku }}</span>
                </h1>
            </div>
            <a href="{{ route('unit.assignments') }}" class="btn-back-simple">
                <i class="fas fa-arrow-left mr-2"></i> Back to {{ Str::plural($groupLabel ?? 'Order') }}
            </a>
        </div>
    </div>
</div>

<div class="container pb-5">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if($assignments->isEmpty())
        <div class="card border-0 shadow-sm p-5 text-center">
            <i class="fas fa-clipboard-list fa-3x text-muted mb-3 opacity-25"></i>
            <h4 class="text-dark">No Active Tasks</h4>
            <p class="text-muted">All tasks for this {{ strtolower($groupLabel ?? 'order') }} are either completed or archived.</p>
            <a href="{{ route('unit.assignments') }}" class="btn btn-primary mt-3">Go to Dashboard</a>
        </div>
    @else
        <div class="row">
            @foreach($assignments as $assignment)
                <div class="col-md-6 col-lg-4">
                    @include('unit.partials.assignment_card', [
                        'item' => $assignment,
                        'type' => $type,
                        'view' => $view,
                        'canCloseTasks' => $canCloseTasks
                    ])
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var forms = document.querySelectorAll('.task-action-form');
        forms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var action = form.getAttribute('data-action');
                var isClose = action === 'close';
                
                Swal.fire({
                    title: isClose ? 'Close Task?' : 'Re-open Task?',
                    text: isClose ? 'This task will be moved to archives.' : 'This task will be active again.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: isClose ? '#dc2626' : '#16a34a',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: isClose ? 'Yes, close' : 'Yes, re-open'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
