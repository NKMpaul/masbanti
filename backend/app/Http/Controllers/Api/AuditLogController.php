<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('model')) {
            $query->where('model', $request->model);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        return response()->json($query->paginate(20));
    }
}