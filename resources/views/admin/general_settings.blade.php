@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-cog text-primary"></i> General Settings
        </div>
        <div class="erp-header-actions">
            <button type="submit" form="general-settings-form" class="btn-erp btn-erp-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-2" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form id="general-settings-form" action="{{ route('admin.settings.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{ $data->id }}">

        <!-- Core General Settings Card -->
        <div class="erp-card mb-3">
            <div class="px-3 py-2 border-bottom bg-light font-weight-bold" style="font-size: 13px; color: var(--erp-text-main);">
                <i class="fas fa-sliders-h mr-1 text-primary"></i> Basic Information & Branding
            </div>
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Logo</label>
                        <div class="custom-file mb-2">
                            <input type="file" name="logo" class="custom-file-input" id="image-input" onchange="previewImage()" accept=".jpg,.jpeg,.png">
                            <label class="custom-file-label" for="image-input">Choose file</label>
                        </div>
                        @if ($errors->has('logo'))
                            <span class="invalid-feedback d-block">{{ $errors->first('logo') }}</span>
                        @endif
                        <div class="p-2 border rounded bg-light d-inline-block">
                            <img src="{{ $data->logo }}" alt="Logo Preview" id="image-preview" style="max-height: 60px; max-width: 140px; object-fit: contain;">
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Fav Icon</label>
                        <div class="custom-file mb-2">
                            <input type="file" name="fav_icon" class="custom-file-input" id="icon-input" onchange="previewIcon()" accept=".jpg,.jpeg,.png">
                            <label class="custom-file-label" for="icon-input">Choose file</label>
                        </div>
                        @if ($errors->has('fav_icon'))
                            <span class="invalid-feedback d-block">{{ $errors->first('fav_icon') }}</span>
                        @endif
                        <div class="p-2 border rounded bg-light d-inline-block">
                            <img src="{{ $data->fav_icon }}" alt="Icon Preview" id="icon-preview" style="max-height: 48px; max-width: 48px; object-fit: contain;">
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Phone</label>
                        <input type="text" name="phone" class="form-control erp-filter-input" placeholder="Enter phone" value="{{ $data->phone }}">
                        @if ($errors->has('phone'))
                            <span class="invalid-feedback d-block">{{ $errors->first('phone') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Email</label>
                        <input type="text" name="email" class="form-control erp-filter-input" placeholder="Enter email" value="{{ $data->email }}">
                        @if ($errors->has('email'))
                            <span class="invalid-feedback d-block">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Website Name</label>
                        <input type="text" name="website_name" class="form-control erp-filter-input" placeholder="Enter website name" value="{{ $data->website_name }}">
                        @if ($errors->has('website_name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('website_name') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Copyright Text</label>
                        <input type="text" name="copy_right" class="form-control erp-filter-input" placeholder="Enter copyright" value="{{ $data->copy_right }}">
                        @if ($errors->has('copy_right'))
                            <span class="invalid-feedback d-block">{{ $errors->first('copy_right') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Address</label>
                        <textarea rows="3" name="address" class="form-control erp-filter-input" placeholder="Enter address">{{ $data->address }}</textarea>
                        @if ($errors->has('address'))
                            <span class="invalid-feedback d-block">{{ $errors->first('address') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Footer Sub Heading</label>
                        <textarea rows="3" name="sub_heading" class="form-control erp-filter-input" placeholder="Enter sub heading">{{ $data->sub_heading }}</textarea>
                        @if ($errors->has('sub_heading'))
                            <span class="invalid-feedback d-block">{{ $errors->first('sub_heading') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">GST % (Order Default)</label>
                        <input type="number" step="any" name="gst_order" class="form-control erp-filter-input" placeholder="Enter GST %" value="{{ $data->gst_order }}">
                        @if ($errors->has('gst_order'))
                            <span class="invalid-feedback d-block">{{ $errors->first('gst_order') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Agent App Settings Card -->
        <div class="erp-card mb-3">
            <div class="px-3 py-2 border-bottom bg-light font-weight-bold" style="font-size: 13px; color: var(--erp-text-main);">
                <i class="fas fa-mobile-alt mr-1 text-primary"></i> Agent App Settings
            </div>
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="agent_app_show_stock" name="agent_app_show_stock" value="1" {{ $data->agent_app_show_stock ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="agent_app_show_stock">Show Available Stock</label>
                            <small class="form-text text-muted">If enabled, agents will see the available stock quantities.</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="agent_app_allow_over_stock" name="agent_app_allow_over_stock" value="1" {{ $data->agent_app_allow_over_stock ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="agent_app_allow_over_stock">Allow Ordering Over Stock</label>
                            <small class="form-text text-muted">If enabled, agents can submit orders with quantities greater than stock.</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="agent_app_allow_over_stock_sample" name="agent_app_allow_over_stock_sample" value="1" {{ $data->agent_app_allow_over_stock_sample ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="agent_app_allow_over_stock_sample">Allow Over Stock for Sample Set (Fair Product)</label>
                            <small class="form-text text-muted">If enabled, agents can overstock only on sample set products.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Meta Card -->
        <div class="erp-card mb-3">
            <div class="px-3 py-2 border-bottom bg-light font-weight-bold" style="font-size: 13px; color: var(--erp-text-main);">
                <i class="fas fa-search mr-1 text-primary"></i> SEO Meta
            </div>
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Website Title</label>
                        <input type="text" name="title" class="form-control erp-filter-input" placeholder="Enter title" value="{{ $data->title }}">
                        @if ($errors->has('title'))
                            <span class="invalid-feedback d-block">{{ $errors->first('title') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control erp-filter-input" placeholder="Enter Meta Title" value="{{ $data->meta_title }}">
                        @if ($errors->has('meta_title'))
                            <span class="invalid-feedback d-block">{{ $errors->first('meta_title') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control erp-filter-input" placeholder="Enter Meta Keywords" value="{{ $data->meta_keywords }}">
                        @if ($errors->has('meta_keywords'))
                            <span class="invalid-feedback d-block">{{ $errors->first('meta_keywords') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Meta Description</label>
                        <input type="text" name="meta_description" class="form-control erp-filter-input" placeholder="Enter Meta Description" value="{{ $data->meta_description }}">
                        @if ($errors->has('meta_description'))
                            <span class="invalid-feedback d-block">{{ $errors->first('meta_description') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media Card -->
        <div class="erp-card mb-3">
            <div class="px-3 py-2 border-bottom bg-light font-weight-bold" style="font-size: 13px; color: var(--erp-text-main);">
                <i class="fas fa-share-alt mr-1 text-primary"></i> Social Media Links
            </div>
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Facebook</label>
                        <input type="url" name="facebook" class="form-control erp-filter-input" placeholder="Enter facebook url" value="{{ $data->facebook }}">
                        @if ($errors->has('facebook'))
                            <span class="invalid-feedback d-block">{{ $errors->first('facebook') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Twitter (X)</label>
                        <input type="url" name="twitter" class="form-control erp-filter-input" placeholder="Enter twitter url" value="{{ $data->twitter }}">
                        @if ($errors->has('twitter'))
                            <span class="invalid-feedback d-block">{{ $errors->first('twitter') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Instagram</label>
                        <input type="text" name="instagram" class="form-control erp-filter-input" placeholder="Enter instagram url" value="{{ $data->instagram }}">
                        @if ($errors->has('instagram'))
                            <span class="invalid-feedback d-block">{{ $errors->first('instagram') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">LinkedIn</label>
                        <input type="text" name="linkedin" class="form-control erp-filter-input" placeholder="Enter linkedin url" value="{{ $data->linkedin }}">
                        @if ($errors->has('linkedin'))
                            <span class="invalid-feedback d-block">{{ $errors->first('linkedin') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Bottom Bar -->
        <div class="d-flex justify-content-end mb-4">
            <button type="submit" class="btn-erp btn-erp-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </div>
    </form>
</div>

<script>
    function previewImage() {
        var imageInput = document.getElementById('image-input');
        var imagePreview = document.getElementById('image-preview');

        if (imageInput.files && imageInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
            };
            reader.readAsDataURL(imageInput.files[0]);
        }
    }

    function previewIcon() {
        var iconInput = document.getElementById('icon-input');
        var iconPreview = document.getElementById('icon-preview');

        if (iconInput.files && iconInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                iconPreview.src = e.target.result;
            };
            reader.readAsDataURL(iconInput.files[0]);
        }
    }
</script>
@endsection