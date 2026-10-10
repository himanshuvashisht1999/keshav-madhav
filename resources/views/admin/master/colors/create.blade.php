@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-palette text-primary"></i> Create Product Color
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.colors.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Colors
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="erp-card">
        <div class="px-3 py-2 border-bottom bg-light font-weight-bold" style="font-size: 13px; color: var(--erp-text-main);">
            <i class="fas fa-edit mr-1 text-primary"></i> Color Information
        </div>
        <form action="{{ route('admin.master.colors.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control erp-filter-input" placeholder="Enter color name" value="{{ old('name') }}" required>
                        @if ($errors->has('name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Status</label>
                        <select name="status" id="status" class="form-control select2 erp-filter-select">
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @if ($errors->has('status'))
                            <span class="invalid-feedback d-block">{{ $errors->first('status') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 border-top bg-white d-flex justify-content-end" style="gap: 8px;">
                <a href="{{ route('admin.master.colors.index') }}" class="btn-erp btn-erp-outline">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        var $input = $('input[name="name"]');
        var $form = $input.closest('form');
        var $submitBtn = $form.find('button[type="submit"]');

        function checkDuplicateColor() {
            var colorName = $input.val().trim();
            var $formGroup = $input.closest('div');

            $formGroup.find('.color-duplicate-error').remove();

            if (colorName) {
                $.ajax({
                    url: "{{ route('admin.master.colors.check-color-name') }}",
                    type: "GET",
                    data: { name: colorName },
                    success: function (data) {
                        if (data.exists) {
                            $input.addClass('is-invalid');
                            $formGroup.append('<span class="text-danger small color-duplicate-error font-weight-bold d-block mt-1"><i class="fas fa-exclamation-circle mr-1"></i> This color name already exists in the master list.</span>');
                            $submitBtn.prop('disabled', true);
                        } else {
                            $input.removeClass('is-invalid');
                            $submitBtn.prop('disabled', false);
                        }
                    }
                });
            } else {
                $input.removeClass('is-invalid');
                $submitBtn.prop('disabled', false);
            }
        }

        $input.on('blur', checkDuplicateColor);

        var typingTimer;
        $input.on('keyup input', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(checkDuplicateColor, 400);
        });

        $form.on('submit', function(e) {
            if ($input.hasClass('is-invalid')) {
                e.preventDefault();
                alert('Please resolve the duplicate color name before submitting.');
                return false;
            }
        });
    });
</script>
@endsection
@endsection
