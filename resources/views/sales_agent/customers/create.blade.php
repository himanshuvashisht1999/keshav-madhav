@extends('sales_agent.layouts.app', ['title' => 'Add Customer'])

@section('content')
    <div class="container">
        <div class="mb-4">
            <a href="{{ route('agent.dashboard') }}" class="text-muted small text-decoration-none">
                <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
            </a>
            <h2 class="font-weight-bold h4 mt-2">Add Customer</h2>
        </div>

        <div class="app-card shadow-sm border-0">
            <form action="{{ route('agent.customers.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="domestic">

                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted">Domestic Type <span class="text-danger">*</span></label>
                    <select name="subtype" id="customer_subtype" class="form-control rounded-lg" required>
                        <option value="">Select Subtype</option>
                        <option value="direct" {{ old('subtype') == 'direct' ? 'selected' : '' }}>Direct</option>
                        <option value="agent" {{ old('subtype') == 'agent' ? 'selected' : '' }}>Agent</option>
                    </select>
                </div>

                <div id="standard_fields" style="display: none;">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-lg" placeholder="Enter name" value="{{ old('name') }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control rounded-lg" placeholder="Enter phone" value="{{ old('phone') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted">Email (Optional)</label>
                        <input type="email" name="email" class="form-control rounded-lg" placeholder="Enter email" value="{{ old('email') }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted">GST Number (Optional)</label>
                        <input type="text" name="gst_number" class="form-control rounded-lg" placeholder="Enter GST number" value="{{ old('gst_number') }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted">Full Address (Optional)</label>
                        <textarea name="address" class="form-control rounded-lg" rows="2" placeholder="Enter address">{{ old('address') }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted">Opening Balance</label>
                                <input type="number" step="0.01" name="balance" class="form-control rounded-lg" value="{{ old('balance', 0) }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted">Balance Type</label>
                                <select name="balance_type" class="form-control rounded-lg">
                                    <option value="Credit" {{ old('balance_type') == 'Credit' ? 'selected' : '' }}>Credit</option>
                                    <option value="Debit" {{ old('balance_type') == 'Debit' ? 'selected' : '' }}>Debit</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted">Payment Term (Days)</label>
                        <input type="number" name="payment_term_days" class="form-control rounded-lg" value="{{ old('payment_term_days', 120) }}">
                    </div>
                </div>

                <!-- Direct Fields (Per Brand Discounts) -->
                <div id="direct_fields" style="display: none;">
                    @foreach($items['brands'] as $brand)
                        <input type="hidden" name="brand_discounts[{{ $brand->id }}]" value="0">
                    @endforeach
                </div>

                <!-- Agent Fields -->
                <div id="agent_fields" style="display: none;">
                    <hr>
                    <h6 class="font-weight-bold text-muted mb-3">Agent/Shop Details</h6>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted">Select Parent Agent <span class="text-danger">*</span></label>
                        <select name="sales_agent_id" class="form-control rounded-lg">
                            <option value="">Select Agent</option>
                            @foreach($items['agents'] as $agent)
                                <option value="{{ $agent->id }}" {{ old('sales_agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted">Shop/Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="shop_name" class="form-control rounded-lg" placeholder="Enter shop or company name" value="{{ old('shop_name') }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted">Shop Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="shop_phone" class="form-control rounded-lg" placeholder="E.g. 98XXXXXXXX" value="{{ old('shop_phone') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted">Shop Email Address (Optional)</label>
                        <input type="email" name="shop_email" class="form-control rounded-lg" placeholder="example@gmail.com" value="{{ old('shop_email') }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted">Shop GST Number (Optional)</label>
                        <input type="text" name="shop_gst_number" class="form-control rounded-lg" placeholder="Enter GST number" value="{{ old('shop_gst_number') }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted">Shop Full Address (Optional)</label>
                        <textarea name="shop_address" class="form-control rounded-lg" rows="2" placeholder="Street, landmark, city...">{{ old('shop_address') }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted">Opening Balance</label>
                                <input type="number" step="0.01" name="balance" class="form-control rounded-lg" value="{{ old('balance', 0) }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted">Balance Type</label>
                                <select name="balance_type" class="form-control rounded-lg">
                                    <option value="Credit" {{ old('balance_type') == 'Credit' ? 'selected' : '' }}>Credit</option>
                                    <option value="Debit" {{ old('balance_type') == 'Debit' ? 'selected' : '' }}>Debit</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-app py-3 font-weight-bold">
                        Create Customer <i class="fas fa-check-circle ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function () {
            function toggleFields() {
                var subtype = $('#customer_subtype').val();

                if (subtype === 'direct') {
                    $('#standard_fields').show().find('input, select, textarea').prop('disabled', false);
                    $('#direct_fields').show().find('input, select, textarea').prop('disabled', false);
                    $('#agent_fields').hide().find('input, select, textarea').prop('disabled', true);
                } else if (subtype === 'agent') {
                    $('#standard_fields').hide().find('input, select, textarea').prop('disabled', true);
                    $('#direct_fields').hide().find('input, select, textarea').prop('disabled', true);
                    $('#agent_fields').show().find('input, select, textarea').prop('disabled', false);
                } else {
                    $('#standard_fields').hide().find('input, select, textarea').prop('disabled', true);
                    $('#direct_fields').hide().find('input, select, textarea').prop('disabled', true);
                    $('#agent_fields').hide().find('input, select, textarea').prop('disabled', true);
                }
            }

            $('#customer_subtype').on('change', function () {
                toggleFields();
            });

            // Initial call
            toggleFields();
        });
    </script>
    @endpush
@endsection
