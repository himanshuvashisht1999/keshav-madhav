{{--
    Totals of the filtered list, shown in the page header (scrolls sideways on a phone).
    @include('owner.common.totals', ['totals' => [
        ['label' => 'Lots', 'value' => number_format($n)],
        ['label' => 'Balance', 'value' => '₹ 1,200.00 Dr', 'tone' => 'red'],   // tone: red | word | yellow | light (optional)
    ], 'id' => 'optional-id-for-ajax-refresh'])
--}}
<div class="ow-totals" @if(!empty($id)) id="{{ $id }}" @endif>
    @foreach($totals as $t)
        <div class="ow-total {{ !empty($t['tone']) ? 'is-' . $t['tone'] : '' }}">
            <span class="lbl">{{ $t['label'] }}</span>
            <span class="val">{{ $t['value'] }}</span>
        </div>
    @endforeach
</div>
