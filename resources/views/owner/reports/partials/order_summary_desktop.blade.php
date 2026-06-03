@foreach($salesOrders as $row)
    <tr>
        <td>{{ date('d-m-Y', strtotime($row['created_at'])) }}</td>
        <td><b>{{ $row['order_no'] }}</b></td>
        <td>{{ $row['customer'] }}</td>
        <td>
            @if(count($row['designs']) === 1)
                <b>{{ array_key_first($row['designs']) }}</b>
            @elseif(count($row['designs']) > 1)
                <a href="javascript:void(0)" class="badge badge-info" style="font-size: 12px; cursor: pointer;" onclick='showDesignsModal(@json($row['designs']))'>Multiple</a>
            @else
                -
            @endif
        </td>
        <td>{{ $row['total_pcs'] }}</td>
        <td>
            @php
                $remaining = $row['total_pcs'] - $row['scanned_pcs'];
            @endphp
            @if($remaining <= 0)
                <span class="badge badge-success">Completed</span>
            @elseif($row['scanned_pcs'] > 0)
                <span class="badge badge-warning">Partial</span>
            @else
                <span class="badge badge-primary">In Progress</span>
            @endif
        </td>
        <td>
            <a href="{{ route('owner.order-summary.view', $row['id']) }}"
                class="btn btn-sm btn-primary" style="border-radius: 6px;">
                View Manifest
            </a>
        </td>
    </tr>
@endforeach
