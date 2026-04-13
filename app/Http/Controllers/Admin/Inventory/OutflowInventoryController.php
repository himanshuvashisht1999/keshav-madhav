<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionOutflowInventory;
use App\Models\DomesticInventoryHistory;
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
            $data->where(function ($q) use ($search) {
                $q->whereHas('orderMain', function ($query) use ($search) {
                    $query->where('sku', 'like', "%{$search}%");
                })->orWhereHas('slip', function ($query) use ($search) {
                    $query->where('lot_no', 'like', "%{$search}%");
                })->orWhereHas('product', function ($query) use ($search) {
                    $query->where('design_number', 'like', "%{$search}%");
                });
            });
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('order_no', function ($row) {
                $sku = $row->orderMain ? $row->orderMain->sku : 'N/A';
                $lot = $row->lot_no ?: ($row->slip ? $row->slip->lot_no : '-');
                return "Ord: <strong>$sku</strong><br><small>Lot: $lot</small>";
            })
            ->addColumn('product_name', function ($row) {
                $design = $row->product ? $row->product->design_number : 'N/A';
                $color = $row->color ? $row->color->name : 'N/A';
                return "<strong>$design</strong><br><small>$color</small>";
            })
            ->addColumn('size', function ($row) {
                return $row->size ? $row->size->size : 'N/A';
            })
            ->addColumn('storage', function ($row) {
                if ($row->rack && $row->rack->storeroom) {
                    return $row->rack->storeroom->name . ' / ' . $row->rack->name;
                }
                return 'N/A';
            })
            ->addColumn('quantity_display', function ($row) {
                return '<strong>' . $row->quantity . '</strong> Pcs';
            })
            ->addColumn('amount_display', function ($row) {
                if ($row->type === 'debit') {
                    $total = number_format($row->total_amount, 2);
                    $per = number_format($row->per_piece_amount, 2);
                    return "₹$total<br><small>(₹$per/pc)</small>";
                }
                return '-';
            })
            ->addColumn('responsible', function ($row) {
                if ($row->type === 'debit' && $row->responsibleUnit) {
                    return $row->responsibleUnit->name . ' (' . ($row->responsibleStage ? $row->responsibleStage->name : 'N/A') . ')';
                }
                return 'N/A';
            })
            ->addColumn('type_label', function ($row) {
                $class = 'badge-secondary';
                $label = ucfirst($row->type);
                if ($row->type === 'dead') {
                    $class = 'badge-danger';
                    $label = 'Dead Stock';
                }
                if ($row->type === 'sampling') {
                    $class = 'badge-info';
                }
                if ($row->type === 'debit') {
                    $class = 'badge-warning';
                }
                return '<span class="badge ' . $class . '">' . $label . '</span>';
            })
            ->rawColumns(['order_no', 'product_name', 'quantity_display', 'amount_display', 'type_label'])
            ->make(true);
    }

    public function attributeHistoryIndex()
    {
        return view('admin.inventory.history.index');
    }

    public function attributeHistoryList(Request $request)
    {
        $data = DomesticInventoryHistory::with([
            'user',
            'oldProduct',
            'newProduct',
            'oldColor',
            'newColor',
            'oldSizeSet',
            'newSizeSet',
            'oldFitting',
            'newFitting',
            'oldPattern',
            'newPattern',
            'oldRack.storeroom',
            'newRack.storeroom'
        ])->orderBy('created_at', 'desc');

        if ($request->type) {
            $data->where('type', $request->type);
        }

        if ($request->design_search) {
            $search = $request->design_search;
            $data->where(function ($q) use ($search) {
                $q->whereHas('oldProduct', function ($query) use ($search) {
                    $query->where('design_number', 'like', "%{$search}%");
                })->orWhereHas('newProduct', function ($query) use ($search) {
                    $query->where('design_number', 'like', "%{$search}%");
                });
            });
        }

        if ($request->has('load_more')) {
            $perPage = 20;
            $results = $data->paginate($perPage);

            $html = '';
            $start = ($results->currentPage() - 1) * $perPage + 1;
            foreach ($results as $index => $row) {
                $html .= view('admin.inventory.history.partials.row', [
                    'row' => $row,
                    'index' => $start + $index
                ])->render();
            }

            return response()->json([
                'html' => $html,
                'next_page' => $results->nextPageUrl() ? $results->currentPage() + 1 : null
            ]);
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('user_name', function ($row) {
                return '<div class="font-weight-bold text-dark">' . ($row->user ? $row->user->name : 'System') . '</div>';
            })
            ->addColumn('type_label', function ($row) {
                $badges = [
                    'creation' => ['label' => 'Entry', 'class' => 'badge-success'],
                    'packing' => ['label' => 'Packing', 'class' => 'badge-info'],
                    'attribute_change' => ['label' => 'Update', 'class' => 'badge-warning'],
                    'stock_consume' => ['label' => 'Consume', 'class' => 'badge-danger'],
                    'transfer' => ['label' => 'Transfer', 'class' => 'badge-primary'],
                    'deletion' => ['label' => 'Deletion', 'class' => 'badge-danger'],
                ];
                $type = $badges[$row->type] ?? ['label' => ucfirst($row->type), 'class' => 'badge-secondary'];
                return '<span class="badge ' . $type['class'] . ' px-2 py-1">' . $type['label'] . '</span>';
            })
            ->addColumn('old_details', function ($row) {
                if (in_array($row->type, ['creation', 'packing'])) {
                    return '<div class="text-muted small">New Stock Inbound</div>';
                }

                $design = $row->oldProduct ? $row->oldProduct->design_number : 'N/A';
                $color = $row->oldColor ? $row->oldColor->name : 'N/A';
                $size = $row->oldSizeSet ? $row->oldSizeSet->name : 'N/A';
                $fitting = $row->oldFitting ? $row->oldFitting->name : 'N/A';
                $pattern = $row->oldPattern ? $row->oldPattern->name : 'N/A';
                $rack = $row->oldRack ? ($row->oldRack->storeroom->name . ' / ' . $row->oldRack->name) : 'N/A';

                return '<div class="small">' .
                    '<strong class="text-dark">D: ' . $design . '</strong> | C: ' . $color . ' | S: ' . $size . '<br>' .
                    '<span class="text-muted">F: ' . $fitting . ' | P: ' . $pattern . ' | R: ' . $rack . '</span>' .
                    '</div>';
            })
            ->addColumn('new_details', function ($row) {
                if ($row->type === 'deletion') {
                    return '<div class="text-danger small font-weight-bold">Stock Removed from System</div>';
                }

                $design = $row->newProduct ? $row->newProduct->design_number : 'N/A';
                $color = $row->newColor ? $row->newColor->name : 'N/A';
                $size = $row->newSizeSet ? $row->newSizeSet->name : 'N/A';
                $fitting = $row->newFitting ? $row->newFitting->name : 'N/A';
                $pattern = $row->newPattern ? $row->newPattern->name : 'N/A';
                $rack = $row->newRack ? ($row->newRack->storeroom->name . ' / ' . $row->newRack->name) : 'N/A';

                return '<div class="small">' .
                    '<strong class="text-success">D: ' . $design . '</strong> | C: ' . $color . ' | S: ' . $size . '<br>' .
                    '<span class="text-muted">F: ' . $fitting . ' | P: ' . $pattern . ' | R: ' . $rack . '</span>' .
                    '</div>';
            })
            ->editColumn('box_quantity', function ($row) {
                return '<span class="badge badge-light border px-2 py-1 font-weight-bold" style="font-size: 0.9rem;">' . $row->box_quantity . ' Boxes</span>';
            })
            ->rawColumns(['user_name', 'type_label', 'old_details', 'new_details', 'box_quantity'])
            ->make(true);
    }
}
