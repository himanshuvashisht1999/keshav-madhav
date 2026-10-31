{{--
    Page header of every owner page.
    @include('owner.common.page_head', ['title' => 'Lots', 'sub' => 'optional line', 'back' => route(...) or null, 'actions' => '<a ...>' html or null])
--}}
<div class="ow-head">
    @if(!empty($back))
        <a href="{{ $back }}" class="ow-back" title="Back"><i class="fas fa-arrow-left"></i></a>
    @endif
    <div class="ow-head-text">
        <h1>{{ $title }}</h1>
        @if(!empty($sub))
            <div class="ow-sub">{{ $sub }}</div>
        @endif
    </div>
    @if(!empty($actions))
        <div class="ow-actions">{!! $actions !!}</div>
    @endif
</div>
