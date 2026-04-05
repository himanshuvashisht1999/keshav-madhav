<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionOutflowInventory;
use Yajra\DataTables\Facades\DataTables;

class OutflowInventoryController extends Controller
{
    public function index()
    {
        return view('admin.inventory.outflow.index');
    }

    public function indexList(Request $request)
    {
        $data = ProductionOutflowInventory::with(['orderMain', 'product', 'color', 'size', 'rack.storeroom', 'responsibleStage', 'responsibleUnit', 'slip'])
            ->orderBy('created_at', 'desc');

        if ($request->type) {
            $data->where('type', $request->type);
        }

        if ($request->search) {
            $search = $request->search;
            $data->where(function($q) use ($search) {
                $q->whereHas('orderMain', function($query) use ($search) {
                    $query->where('sku', 'like', "%{$search}%");
                })->orWhereHas('slip', function($query) use ($search) {
                    $query->where('lot_no', 'like', "%{$search}%");
                })->orWhereHas('product', function($query) use ($search) {
                    $query->where('design_number', 'like', "%{$search}%");
                });
            });
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('order_no', function($row){
                $sku = $row->orderMain ? $row->orderMain->sku : 'N/A';
                $lot = $row->lot_no ?: ($row->slip ? $row->slip->lot_no : '-');
                return "Ord: <strong>$sku</strong><br><small>Lot: $lot</small>";
            })
            ->addColumn('product_name', function($row){
                $design = $row->product ? $row->product->design_number : 'N/A';
                $color = $row->color ? $row->color->name : 'N/A';
                return "<strong>$design</strong><br><small>$color</small>";
            })
            ->addColumn('size', function($row){
                return $row->size ? $row->size->size : 'N/A';
            })
            ->addColumn('storage', function($row){
                if ($row->rack && $row->rack->storeroom) {
                    return $row->rack->storeroom->name . ' / ' . $row->rack->name;
                }
                return 'N/A';
            })
            ->addColumn('quantity_display', function($row){
                return '<strong>' . $row->quantity . '</strong> Pcs';
            })
            ->addColumn('amount_display', function($row){
                if ($row->type === 'debit') {
                    $total = number_format($row->total_amount, 2);
                    $per = number_format($row->per_piece_amount, 2);
                    return "₹$total<br><small>(₹$per/pc)</small>";
                }
                return '-';
            })
            ->addColumn('responsible', function($row){
                if ($row->type === 'debit' && $row->responsibleUnit) {
                    return $row->responsibleUnit->name . ' (' . ($row->responsibleStage ? $row->responsibleStage->name : 'N/A') . ')';
                }
                return 'N/A';
            })
            ->addColumn('type_label', function($row){
                $class = 'badge-secondary';
                $label = ucfirst($row->type);
                if ($row->type === 'dead') { $class = 'badge-danger'; $label = 'Dead Stock'; }
                if ($row->type === 'sampling') { $class = 'badge-info'; }
                if ($row->type === 'debit') { $class = 'badge-warning'; }
                return '<span class="badge ' . $class . '">' . $label . '</span>';
            })
            ->rawColumns(['order_no', 'product_name', 'quantity_display', 'amount_display', 'type_label'])
            ->make(true);
    }
}
