@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-layer-group text-primary"></i> Create Size Group
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.size-measurement.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Size Groups
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
            <i class="fas fa-edit mr-1 text-primary"></i> Size Group Information
        </div>
        <form action="{{ route('admin.master.size-measurement.store') }}" method="post" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Set Size Name <span class="text-danger">*</span></label>
                        <input type="text" name="set_size" class="form-control erp-filter-input" placeholder="Enter Set Size" value="{{ old('set_size') }}" required>
                        @if ($errors->has('set_size'))
                            <span class="invalid-feedback d-block">{{ $errors->first('set_size') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">No of Pcs (per Set)</label>
                        <input type="text" name="no_of_pcs" id="no_of_pcs" class="form-control erp-filter-input" placeholder="Auto-calculated" value="{{ old('no_of_pcs', 0) }}" readonly>
                        @if ($errors->has('no_of_pcs'))
                            <span class="invalid-feedback d-block">{{ $errors->first('no_of_pcs') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label font-weight-bold mb-0" style="font-size: 13px;">Size Group <span class="text-danger">*</span></label>
                            <a href="{{ route('admin.master.size.create') }}" target="_blank" style="font-size: 12px; color: var(--brand-dark-green); font-weight: 600;">Create New +</a>
                        </div>
                        <div class="wrapper position-relative">
                            <button type="button" class="form-control erp-filter-input toggle-next text-left">
                                Select Size group
                            </button>
                            <div class="checkboxes" id="sizesCheckboxes">
                                <div class="inner-wrap">
                                    @foreach ($sizes as $size_data)
                                        <label>
                                            <input type="checkbox" name="size_group[]" value="{{ $size_data->size }}" class="ckkBox val" />
                                            <span class="ml-2">{{ $size_data->size }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('size_group'))
                            <span class="invalid-feedback d-block">{{ $errors->first('size_group') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Status</label>
                        <select name="status" class="form-control select2 erp-filter-select">
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
                <a href="{{ route('admin.master.size-measurement.index') }}" class="btn-erp btn-erp-outline">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.toggle-next').click(function () {
            $(this).next('.checkboxes').slideToggle(150);
        });

        $('.ckkBox').change(function () {
            updateSelectionText(this);
            let count = $('.ckkBox:checked').length;
            $('#no_of_pcs').val(count);
        });

        function updateSelectionText(elem) {
            let wrapper = $(elem).closest('.wrapper');
            let button = wrapper.find('.toggle-next');
            let checked = wrapper.find('.val:checked');

            if (checked.length === 0) {
                button.text("Select Size group");
                return;
            }

            let names = [];
            checked.each(function () {
                names.push($(this).next().text().trim());
            });

            button.text(names.join(", "));
        }

        $(document).mouseup(function (e) {
            $(".wrapper").each(function () {
                let dropdown = $(this).find('.checkboxes');
                let button = $(this).find('.toggle-next');

                if (!dropdown.is(e.target) && dropdown.has(e.target).length === 0 &&
                    !button.is(e.target) && button.has(e.target).length === 0) {
                    dropdown.slideUp(150);
                }
            });
        });
    });
</script>
@endsection