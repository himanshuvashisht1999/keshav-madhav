@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Payment Details</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.payment.history.index') }}">Payment
                                    History</a></li>
                            <li class="breadcrumb-item active">View Payment</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <!-- Main Payment Info Card -->
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-bottom py-3">
                                <h3 class="card-title font-weight-bold text-dark">
                                    <i class="fas fa-receipt text-primary mr-2"></i> Payment Info
                                </h3>
                                <div class="card-tools">
                                    <a href="{{ route('admin.payment.history.edit', $payment->id) }}" class="btn btn-sm btn-outline-primary border-pill px-3">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <h5 class="text-muted small uppercase mb-1">Amount Paid</h5>
                                    <h2 class="text-primary font-weight-bold mb-0">₹{{ number_format($payment->amount, 2) }}</h2>
                                    <div class="mt-2">
                                        <span class="badge badge-soft-info p-2 px-3" style="background: #e1f5fe; color: #01579b; border-radius: 20px;">
                                            {{ ucwords(str_replace('_', ' ', $payment->payment_category)) }}
                                        </span>
                                        @if($payment->payment_type == 'received')
                                            <span class="badge badge-success p-2 px-3 ml-1" style="border-radius: 20px;">
                                                <i class="fas fa-arrow-down mr-1"></i> Received
                                            </span>
                                        @else
                                            <span class="badge badge-danger p-2 px-3 ml-1" style="border-radius: 20px;">
                                                <i class="fas fa-arrow-up mr-1"></i> Paid
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                        <span class="text-muted"><i class="fas fa-calendar-day mr-2"></i> Date</span>
                                        <span class="font-weight-500">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d F, Y') }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                        <span class="text-muted"><i class="fas fa-wallet mr-2"></i> Mode</span>
                                        <span class="font-weight-500">{{ ucwords(str_replace('_', ' ', $payment->payment_mode)) }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                        <span class="text-muted"><i class="fas fa-hashtag mr-2"></i> Ref #</span>
                                        <span class="font-weight-500 text-truncate" style="max-width: 150px;">{{ $payment->reference_id ?? 'N/A' }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                        <span class="text-muted"><i class="fas fa-user-circle mr-2"></i> Recorded By</span>
                                        <span class="font-weight-500">Admin</span>
                                    </li>
                                </ul>

                                <div class="mt-4">
                                    <label class="small text-muted mb-1"><i class="fas fa-comment-dots mr-1"></i> Remarks</label>
                                    <p class="small text-dark p-2 bg-light rounded border-left" style="border-left: 4px solid #dee2e6 !important;">
                                        {{ $payment->remarks ?? 'No remarks provided.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if($payment->image)
                        <div class="card shadow-sm border-0 mt-4">
                            <div class="card-header bg-white border-bottom py-3">
                                <h3 class="card-title font-weight-bold text-dark">
                                    <i class="fas fa-image text-primary mr-2"></i> Receipt Copy
                                </h3>
                            </div>
                            <div class="card-body p-2">
                                <a href="{{ asset('storage/' . $payment->image) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $payment->image) }}" class="img-fluid rounded w-100" style="object-fit: cover; max-height: 300px;" alt="Receipt">
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="col-md-8">
                        <!-- Linked Reference Details -->
                        @if($payment->paymentable)
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white border-bottom py-3">
                                <h3 class="card-title font-weight-bold text-dark">
                                    <i class="fas fa-link text-primary mr-2"></i> 
                                    @if($payment->payment_category == 'fabric_shipment') Fabric Shipment Details 
                                    @elseif($payment->payment_category == 'agent_order') Agent Order Details
                                    @elseif($payment->payment_category == 'salary') Employee Information
                                    @else Linked Reference
                                    @endif
                                </h3>
                            </div>
                            <div class="card-body">
                                @if($payment->payment_category == 'fabric_shipment' && $payment->paymentable_type == 'App\Models\FabricReceipt')
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="p-3 bg-light rounded text-center border">
                                                <div class="small text-muted mb-1">Total Bill</div>
                                                <div class="h5 font-weight-bold mb-0 text-dark">₹{{ number_format($payment->paymentable->total_amount, 2) }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-light rounded text-center border">
                                                <div class="small text-muted mb-1">Paid Amount</div>
                                                <div class="h5 font-weight-bold mb-0 text-success">₹{{ number_format($payment->paymentable->paid_amount, 2) }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-light rounded text-center border" style="border: 2px solid #ffc107 !important; background: #fffdf5 !important;">
                                                <div class="small text-warning mb-1 font-weight-bold">Balanced Amount</div>
                                                <div class="h5 font-weight-bold mb-0 text-danger">₹{{ number_format($payment->paymentable->balance_amount, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted w-50">Shipment ID:</td><td class="font-weight-500">{{ $payment->paymentable->shipment_id }}</td></tr>
                                                <tr><td class="text-muted">Truck Number:</td><td class="font-weight-500">{{ $payment->paymentable->truck_number }}</td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted w-50">Received By:</td><td class="font-weight-500">{{ $payment->paymentable->received_by }}</td></tr>
                                                <tr><td class="text-muted">Time:</td><td class="font-weight-500">{{ $payment->paymentable->time }}</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="text-right mt-3 border-top pt-3">
                                        <a href="{{ url('admin/fabric-receipt/view?id=' . $payment->paymentable->id) }}" class="btn btn-primary px-4 shadow-sm">
                                            <i class="fas fa-external-link-alt mr-2"></i> View Full Shipment Record
                                        </a>
                                    </div>

                                @elseif($payment->payment_category == 'agent_order' && $payment->paymentable_type == 'App\Models\AgentOrder')
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="p-3 bg-light rounded text-center border">
                                                <div class="small text-muted mb-1">Total Bill</div>
                                                <div class="h5 font-weight-bold mb-0 text-dark">₹{{ number_format($payment->paymentable->grand_total, 2) }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-light rounded text-center border">
                                                <div class="small text-muted mb-1">Paid Amount</div>
                                                <div class="h5 font-weight-bold mb-0 text-success">₹{{ number_format($payment->paymentable->paid_amount, 2) }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-light rounded text-center border" style="border: 2px solid #ffc107 !important; background: #fffdf5 !important;">
                                                <div class="small text-warning mb-1 font-weight-bold">Balanced Amount</div>
                                                <div class="h5 font-weight-bold mb-0 text-danger">₹{{ number_format($payment->paymentable->balance_amount, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted w-50">Order ID:</td><td class="font-weight-500">#{{ $payment->paymentable->id }}</td></tr>
                                                <tr><td class="text-muted">Order Date:</td><td class="font-weight-500">{{ \Carbon\Carbon::parse($payment->paymentable->order_date)->format('d-m-Y') }}</td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted w-50">Agent:</td><td class="font-weight-500">{{ $payment->paymentable->agent->name ?? 'N/A' }}</td></tr>
                                                <tr><td class="text-muted">Shop/Customer:</td><td class="font-weight-500">{{ $payment->paymentable->shop->name ?? 'N/A' }}</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="text-right mt-3 border-top pt-3">
                                        <a href="{{ url('admin/agent-orders/' . $payment->paymentable->id . '/show') }}" class="btn btn-primary px-4 shadow-sm">
                                            <i class="fas fa-external-link-alt mr-2"></i> View Full Order Record
                                        </a>
                                    </div>

                                @elseif($payment->payment_category == 'salary' && $payment->paymentable_type == 'App\Models\Employee')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="small text-muted d-block mb-1">Employee Name</label>
                                                <div class="h6 font-weight-bold">{{ $payment->paymentable->name }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="small text-muted d-block mb-1">Contact Phone</label>
                                                <div class="h6 font-weight-bold">{{ $payment->paymentable->phone }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="small text-muted d-block mb-1">Residential Address</label>
                                                <div class="h6 font-weight-normal">{{ $payment->paymentable->address ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                        <p>No additional reference details available for this category.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-receipt fa-4x mb-3 text-light"></i>
                                <h5 class="text-muted">Direct Payment Entry</h5>
                                <p class="small text-muted">This payment was recorded manually without a linked order or shipment.</p>
                            </div>
                        </div>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('admin.payment.history.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-arrow-left mr-2"></i> Back to Payment History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
    