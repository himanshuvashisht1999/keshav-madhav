{{--
    "Load more" under a paged table: fetches ?page=N of the current URL as JSON {html, next_page, left}
    and appends the <tr> rows to the tbody with id $target.
    @include('owner.reports.partials.stock_load_more', ['paginator' => $rows, 'target' => 'rows-body'])
--}}
@if($paginator->hasMorePages())
    <div class="ow-loading">
        <button type="button" class="ow-btn is-outline" data-load-more data-target="{{ $target }}" data-next="{{ $paginator->currentPage() + 1 }}">
            <i class="fas fa-chevron-down"></i> Load more (<span class="js-left">{{ number_format($paginator->total() - (int) $paginator->lastItem()) }}</span> left)
        </button>
    </div>
@endif

@once
    @push('scripts')
        <script>
            $(document).on('click', '[data-load-more]', function () {
                var $btn = $(this);
                if ($btn.prop('disabled')) { return; }
                var url = new URL(window.location.href);
                url.searchParams.set('page', $btn.attr('data-next'));
                $btn.prop('disabled', true);
                $.ajax({ url: url.toString(), dataType: 'json', cache: false, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .done(function (res) {
                        $('#' + $btn.attr('data-target')).append(res.html || '');
                        if (res.next_page) {
                            $btn.attr('data-next', res.next_page).prop('disabled', false);
                            $btn.find('.js-left').text(Number(res.left || 0).toLocaleString('en-US'));
                        } else {
                            $btn.closest('.ow-loading').remove();
                        }
                    })
                    .fail(function () { $btn.prop('disabled', false); });
            });
        </script>
    @endpush
@endonce
