<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeletionLog;
use App\Models\User;
use Illuminate\Http\Request;

class DeletionLogController extends Controller
{
    public function index(Request $request)
    {
        $query = DeletionLog::with('user')->orderBy('id', 'desc');

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

        return view('admin.reports.deletion_logs.index', compact('logs', 'modules', 'users'));
    }

    public function show($id)
    {
        $log = DeletionLog::with('user')->findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'data' => [
                    'id'         => $log->id,
                    'module'     => $log->module,
                    'record_id'  => $log->record_id,
                    'deleted_by' => $log->user->name ?? 'System / Unknown',
                    'created_at' => $log->created_at ? $log->created_at->format('d M Y h:i:s A') : 'N/A',
                    'payload'    => $log->payload,
                ]
            ]);
        }

        return view('admin.reports.deletion_logs.show', compact('log'));
    }
}
