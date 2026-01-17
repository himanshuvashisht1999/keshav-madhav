@extends('admin.layouts.app')

@section('content')
<style>
.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.report-header h3 {
    font-weight: 600;
    margin: 0;
}

.report-card {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
}

.table-report thead th {
    background: #343a40;
    color: #fff;
    font-weight: 600;
    white-space: nowrap;
    vertical-align: middle;
}

.fabric-cell {
    background: #f8f9fa;
    font-weight: 600;
    vertical-align: middle;
}

.expand-btn {
    font-size: 13px;
}
</style>

<div class="content-wrapper">

    {{-- HEADER --}}

    <section class="content-header">
        <div class="container-fluid">
            <div class="report-header">
                <div>
                    <div class="report-meta">Report No : RJ 2</div>
                </div>
                <div>
                    <h3>Fabric Stock Report</h3>
                </div>
                <div class="report-meta">
                    Date : {{ now()->format('d M Y h:i A') }}
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

        </div>
    </section>
</div>

{{-- ================= SCRIPT ================= --}}

@endsection