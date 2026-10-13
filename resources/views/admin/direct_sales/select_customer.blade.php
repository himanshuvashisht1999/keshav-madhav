@extends('admin.layouts.app')

@section('title', 'Select Customer for Direct Sales')

@section('content')
    <div class="content-wrapper erp-page p-2">
        <div class="erp-header-bar">
        <div>
            <div class="erp-header-title"><i class="fas fa-file-alt text-primary"></i> Direct Sales Order</div>
        </div>
    </div>

        <section class="content">
            <div class="container-fluid p-0">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Choose a Customer</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">Select the customer who is purchasing directly from the warehouse. Total
                            inventory available will be based on this selection.</p>

                        <form action="{{ route('admin.direct-sales.create') }}" method="GET">
                            <div class="form-group">
                                <label for="master_customer_id">Select Customer</label>
                                <select name="master_customer_id" id="master_customer_id" class="form-control select2"
                                    required>
                                    <option value="">-- Search Customer --</option>
                                    @foreach($shops as $shop)
                                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg mt-3">Proceed to Order <i
                                    class="fas fa-arrow-right ml-2"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        $(document)ready(function () {
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Search Customer...'
            });
        });
    </script>
@endpush