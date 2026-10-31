{{-- Owner panel scripts: only what the owner pages use (jQuery comes from owner.common.meta in <head>). --}}
<script src="{{ asset('admin_assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin_assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    $(function () {
        // searchable dropdowns: <select class="select2"> (works inside .ow-filters on phones)
        $('select.select2').each(function () {
            $(this).select2({ width: '100%', placeholder: $(this).data('placeholder') || '', allowClear: !!$(this).data('allow-clear') });
        });
        // open the filters panel on wide screens; on phones it starts closed unless a filter is active
        if (window.matchMedia('(min-width: 992px)').matches) {
            $('details.ow-filters').attr('open', true);
        }
    });
</script>
