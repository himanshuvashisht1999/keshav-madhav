<tr>
    <td class="text-center text-muted font-weight-bold">{{ $index }}</td>
    <td>
        @php
            $badges = [
                'creation' => ['label' => 'Entry', 'class' => 'badge-success'],
                'packing' => ['label' => 'Packing', 'class' => 'badge-info'],
                'attribute_change' => ['label' => 'Update', 'class' => 'badge-warning'],
                'stock_consume' => ['label' => 'Consume', 'class' => 'badge-danger'],
                'transfer' => ['label' => 'Transfer', 'class' => 'badge-primary'],
                'deletion' => ['label' => 'Deletion', 'class' => 'badge-danger'],
            ];
            $type = $badges[$row->type] ?? ['label' => ucfirst($row->type), 'class' => 'badge-secondary'];
        @endphp
        <span class="badge {{ $type['class'] }} px-2 py-1">{{ $type['label'] }}</span>
    </td>
    <td>
        @if (in_array($row->type, ['creation', 'packing']))
            <div class="text-muted small">New Stock Inbound</div>
        @else
            @php
                $design = $row->oldProduct ? $row->oldProduct->design_number : 'N/A';
                $color = $row->oldColor ? $row->oldColor->name : 'N/A';
                $size = $row->oldSizeSet ? $row->oldSizeSet->name : 'N/A';
                $fitting = $row->oldFitting ? $row->oldFitting->name : 'N/A';
                $pattern = $row->oldPattern ? $row->oldPattern->name : 'N/A';
                $rack = $row->oldRack ? ($row->oldRack->storeroom->name . ' / ' . $row->oldRack->name) : 'N/A';
            @endphp
            <div class="small">
                <strong class="text-dark">D: {{ $design }}</strong> | C: {{ $color }} | S: {{ $size }}<br>
                <span class="text-muted small">F: {{ $fitting }} | P: {{ $pattern }} | R: {{ $rack }}</span>
            </div>
        @endif
    </td>
    <td class="text-center">
        <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
    </td>
    <td>
        @if ($row->type === 'deletion')
            <div class="text-danger small font-weight-bold">Stock Removed from System</div>
        @else
            @php
                $design = $row->newProduct ? $row->newProduct->design_number : 'N/A';
                $color = $row->newColor ? $row->newColor->name : 'N/A';
                $size = $row->newSizeSet ? $row->newSizeSet->name : 'N/A';
                $fitting = $row->newFitting ? $row->newFitting->name : 'N/A';
                $pattern = $row->newPattern ? $row->newPattern->name : 'N/A';
                $rack = $row->newRack ? ($row->newRack->storeroom->name . ' / ' . $row->newRack->name) : 'N/A';
            @endphp
            <div class="small">
                <strong class="text-success">D: {{ $design }}</strong> | C: {{ $color }} | S: {{ $size }}<br>
                <span class="text-muted small">F: {{ $fitting }} | P: {{ $pattern }} | R: {{ $rack }}</span>
            </div>
        @endif
    </td>
    <td class="text-center">
        <span class="badge badge-light border px-2 py-1 font-weight-bold"
            style="font-size: 0.9rem;">{{ $row->box_quantity }} Boxes</span>
    </td>
    <!-- <td>
        <div class="font-weight-bold text-dark small">{{ $row->user ? $row->user->name : 'System' }}</div>
    </td> -->
    <td>
        <div class="small font-weight-bold text-dark">{{ $row->created_at->format('d-m-Y') }}</div>
        <div class="text-muted small">{{ $row->created_at->format('H:i') }}</div>
    </td>
    <td class="text-center">
        <a href="{{ route('admin.inventory.attribute-history.show', $row->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
    </td>
</tr>