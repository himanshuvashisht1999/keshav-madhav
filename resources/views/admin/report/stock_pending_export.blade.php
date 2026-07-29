<table>
    <thead>
        <tr>
            <th>Stage</th>
            <th>Lot No</th>
            <th>Design No</th>
            <th>Size Set</th>
            <th>Pending Quantity</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $totalPending = 0;
        @endphp
        @foreach($assignments as $item)
            @php 
                $qty = $item->pending_qty ?? $item->quantity ?? 0;
                $totalPending += $qty;
                $stageName = $item->to_stage->name ?? $item->from_stage->name ?? 'Cutting';
                $lotNo = $item->lot_no ?? ($item->productSet->lot_no ?? '-');
                if(isset($type) && $type == 'cutting') {
                    $stageName = 'Cutting';
                    $lotNo = $item->lot_no ?? '-';
                }
            @endphp
            <tr>
                <td>{{ $stageName }}</td>
                <td>{{ $lotNo }}</td>
                <td>{{ $item->design_number ?? '-' }}</td>
                <td>{{ $item->size_set_name ?? '-' }}</td>
                <td>{{ $qty }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">Grand Total:</td>
            <td>{{ $totalPending }}</td>
        </tr>
    </tfoot>
</table>
