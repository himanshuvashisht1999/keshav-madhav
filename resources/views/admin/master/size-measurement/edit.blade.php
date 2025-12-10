@extends('admin.layouts.app')
@section('content')
<style>
    .inner-wrap label {
        display: flex;
        align-items: center;
        width: 100%;
        cursor: pointer;
    }
    .toggle-next {
        border-radius: 6px;
        background: #fff;
        border: 1px solid #ccc;
        padding: 8px;
        text-align: left;
        cursor: pointer;
    }

    .toggle-next:hover {
        border-color: #007bff;
    }


    .ajax-link {
        display: none;
    }

    .checkboxes {
        display: none;
        border: 1px solid #ccc;
        border-top: 0;
        position: absolute;
        width: 100%;
        background: #fff;
        z-index: 99;
        border-radius: 0 0 6px 6px;
    }

    .inner-wrap {
        padding: 5px 10px;
        max-height: 150px;
        overflow-y: auto;
    }

</style>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Product Size</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Edit Product Size</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Edit Product Size</h3>
                </div>
                <form action="{{route('admin.master.size-measurement.update')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$data->id}}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter size name" value="{{$data->name}}">
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('name') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Size Group</label>
                                    <div class="wrapper" style="position: relative;">
                                        
                                        <!-- Button to open dropdown -->
                                        <button type="button" class="form-control toggle-next">
                                            Select Size group
                                        </button>

                                        <!-- Dropdown -->
                                        <div class="checkboxes" id="sizesCheckboxes" >
                                            <div class="inner-wrap">
                                                @foreach ($sizes as $size_data)
                                                <label>
                                                    <input type="checkbox" name="size_group[]" value="{{$size_data->size}}" class="ckkBox val"  @if( in_array($size_data->size, $selectedSizes)) checked @endif />
                                                    <span class="ml-1"> {{$size_data->size}}</span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @if ($errors->has('size_group'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('size_group') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
                                        <option value="1" {{$data->status == '1' ? 'selected' : ''}}>Active</option>
                                        <option value="0" {{$data->status == '0' ? 'selected' : ''}}>Inactive</option>
                                    </select>
                                    @if ($errors->has('status'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('status') }}
                                        </span>
                                    @endif
                                </div>
                            </div>                            
                            <div class="col-md-6" style="display: none">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" name="sku" id="sku_n" class="form-control" placeholder="Auto-generated SKU" value="{{$data->sku}}" readonly>
                                    @if ($errors->has('sku'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('sku') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mt-2" style="float:right">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
        </div>
    </section>
</div>
<script>
  $(document).ready(function() {

    // Toggle dropdown
    $('.toggle-next').click(function () {
        $(this).next('.checkboxes').slideToggle(200);
    });

    // Checkbox logic
    $('.ckkBox').change(function () {
        updateSelectionText(this);
    });

    // Update button text function
    function updateSelectionText(elem) {
        let wrapper = $(elem).closest('.wrapper');
        let button = wrapper.find('.toggle-next');

        let checked = wrapper.find('.val:checked');

        if (checked.length === 0) {
            button.text("Select Categories");
            return;
        }

        let names = [];
        checked.each(function () {
            names.push($(this).next().text());
        });

        button.text(names.join(", "));
    }

    // ---- On Page Load: Update Selected values in button ----
    $('.wrapper').each(function () {
        let checked = $(this).find('.val:checked');
        let button = $(this).find('.toggle-next');

        if (checked.length === 0) {
            button.text("Select Categories");
            return;
        }

        let names = [];
        checked.each(function () {
            names.push($(this).next().text());
        });

        button.text(names.join(", "));
    });

    // Hide dropdown when clicking outside
    $(document).mouseup(function (e) {
        $(".wrapper").each(function () {
            let dropdown = $(this).find('.checkboxes');
            let button = $(this).find('.toggle-next');

            if (!dropdown.is(e.target) && dropdown.has(e.target).length === 0 &&
                !button.is(e.target) && button.has(e.target).length === 0) {
                dropdown.slideUp(200);
            }
        });
    });

});

</script>
@endsection
