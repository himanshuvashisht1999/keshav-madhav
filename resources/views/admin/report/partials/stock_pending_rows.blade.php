@foreach($assignments as $item)
    @php 
        $qty = $item->pending_qty ?? $item->quantity ?? 0;
        $stageName = $item->to_stage->name ?? $item->from_stage->name ?? 'Cutting';
        $lotNo = $item->lot_no ?? ($item->productSet->lot_no ?? '-');
        if($type == 'cutting') {
            $stageName = 'Cutting';
            $lotNo = $item->lot_no ?? '-';
        }
        $unitPersonName = $item->getToUnitMaster->name ?? $item->stage_master_unit->name ?? '-';
    @endphp
    <tr>
        <td>{{ $stageName }}</td>
        <td>{{ $unitPersonName }}</td>
        <td>
            {{ $lotNo }}
        </td>
        <td>{{ $item->design_number ?? '-' }}</td>
        <td>{{ $item->size_set_name ?? '-' }}</td>
        <td>{{ number_format($qty) }} Pcs</td>
    </tr>
@endforeach
