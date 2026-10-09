<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentOrder;
use App\Models\DeletionLog;
use App\Models\FabricReceiptDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeletionLogController extends Controller
{
    public function index(Request $request)
    {
        $query = DeletionLog::with(['user', 'restoredByUser'])->orderBy('id', 'desc');

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('record_id')) {
            $query->where('record_id', $request->record_id);
        }

        if ($request->filled('deleted_by')) {
            $query->where('deleted_by', $request->deleted_by);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('record_id', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('payload', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->appends($request->all());
        $modules = DeletionLog::distinct()->pluck('module')->filter()->values();
        $users = User::whereIn('id', DeletionLog::distinct()->pluck('deleted_by')->filter())->get();

        // Check which Agent Orders currently exist in database to determine restored state
        $agentOrderIds = $logs->where('module', 'Agent Order')->pluck('record_id')->filter()->unique()->toArray();
        $existingOrderIds = !empty($agentOrderIds) ? AgentOrder::whereIn('id', $agentOrderIds)->pluck('id')->toArray() : [];

        return view('admin.reports.deletion_logs.index', compact('logs', 'modules', 'users', 'existingOrderIds'));
    }

    public function show($id)
    {
        $log = DeletionLog::with(['user', 'restoredByUser'])->findOrFail($id);
        $orderExists = ($log->module === 'Agent Order' && AgentOrder::where('id', $log->record_id)->exists());
        $isRestored = ($log->restored_at !== null) || $orderExists;

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'data' => [
                    'id'          => $log->id,
                    'module'      => $log->module,
                    'record_id'   => $log->record_id,
                    'party_name'  => $log->party_name,
                    'deleted_by'  => $log->user->name ?? 'System / Unknown',
                    'created_at'  => $log->created_at ? $log->created_at->format('d M Y h:i:s A') : 'N/A',
                    'is_restored' => $isRestored,
                    'restored_at' => $log->restored_at ? $log->restored_at->format('d M Y h:i:s A') : null,
                    'restored_by' => $log->restoredByUser->name ?? null,
                    'payload'     => $log->payload,
                ]
            ]);
        }

        return view('admin.reports.deletion_logs.show', compact('log', 'isRestored'));
    }

    /**
     * Undo a deletion and restore the record (currently supports Agent Order).
     */
    public function undo($id)
    {
        $log = DeletionLog::findOrFail($id);

        if ($log->module !== 'Agent Order') {
            return response()->json([
                'status'  => false,
                'message' => 'Undo is currently supported for Agent Order records only.',
            ], 400);
        }

        $payload = $log->payload;
        if (empty($payload) || !isset($payload['order'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Cannot undo: Order snapshot data was not found in deletion log.',
            ], 400);
        }

        $orderData = $payload['order'];
        $orderId = $orderData['id'] ?? $log->record_id;

        // Check if order already exists in database
        if (AgentOrder::where('id', $orderId)->exists()) {
            return response()->json([
                'status'  => false,
                'message' => "Agent Order #{$orderId} already exists in the system (cannot restore duplicate).",
            ], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Prepare agent_orders columns
            $orderColumns = Schema::getColumnListing('agent_orders');
            $orderInsert = array_intersect_key($orderData, array_flip($orderColumns));
            $orderInsert['id'] = $orderId;
            if (empty($orderInsert['created_at'])) {
                $orderInsert['created_at'] = now();
            }
            if (empty($orderInsert['updated_at'])) {
                $orderInsert['updated_at'] = now();
            }

            DB::table('agent_orders')->insert($orderInsert);

            $saleType = strtolower(trim($orderData['sale_type'] ?? ''));

            // 2. Restore fabric items if fabric order
            if ($saleType === 'fabric') {
                $fabricItems = $payload['fabric_items'] ?? [];
                $fabricItemColumns = Schema::getColumnListing('agent_order_fabric_items');
                $fabricItemColFlip = array_flip($fabricItemColumns);

                foreach ($fabricItems as $fItem) {
                    $insertFItem = array_intersect_key($fItem, $fabricItemColFlip);
                    $insertFItem['agent_order_id'] = $orderId;

                    if (isset($insertFItem['id']) && DB::table('agent_order_fabric_items')->where('id', $insertFItem['id'])->exists()) {
                        unset($insertFItem['id']);
                    }

                    if (empty($insertFItem['created_at'])) {
                        $insertFItem['created_at'] = now();
                    }
                    if (empty($insertFItem['updated_at'])) {
                        $insertFItem['updated_at'] = now();
                    }

                    DB::table('agent_order_fabric_items')->insert($insertFItem);

                    // Re-deduct from fabric_receipt_details because deleting incremented remaining_quantity
                    if (!empty($fItem['fabric_receipt_detail_id']) && !empty($fItem['meter'])) {
                        FabricReceiptDetail::where('id', $fItem['fabric_receipt_detail_id'])
                            ->decrement('remaining_quantity', (float) $fItem['meter']);
                    }
                }
            } else {
                // 3. Restore regular items
                $items = $payload['items'] ?? [];
                $itemColumns = Schema::getColumnListing('agent_order_items');
                $itemColFlip = array_flip($itemColumns);

                foreach ($items as $item) {
                    $insertItem = array_intersect_key($item, $itemColFlip);
                    $insertItem['agent_order_id'] = $orderId;

                    if (isset($insertItem['id']) && DB::table('agent_order_items')->where('id', $insertItem['id'])->exists()) {
                        unset($insertItem['id']);
                    }

                    if (empty($insertItem['created_at'])) {
                        $insertItem['created_at'] = now();
                    }
                    if (empty($insertItem['updated_at'])) {
                        $insertItem['updated_at'] = now();
                    }

                    DB::table('agent_order_items')->insert($insertItem);
                }
            }

            // 4. Mark deletion log as restored
            $userId = function_exists('get_current_auth_user_id') ? get_current_auth_user_id() : auth()->id();
            $log->update([
                'restored_at' => now(),
                'restored_by' => $userId,
            ]);

            DB::commit();

            return response()->json([
                'status'   => true,
                'message'  => "Agent Order #{$orderId} has been successfully restored!",
                'order_id' => $orderId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Failed to restore order: ' . $e->getMessage(),
            ], 500);
        }
    }
}
