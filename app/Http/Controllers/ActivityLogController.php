<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a paginated listing of activity logs.
     * Filters: user_id, subject_type, search (description)
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) ($request->attributes->get('tenant_id') ?? 0);

        $query = DB::table('activity_log')
            ->leftJoin('users', 'users.id', '=', 'activity_log.causer_id')
            ->select([
                'activity_log.*',
                'users.name as user_name',
            ]);

        if ($tenantId > 0) {
            $query->whereRaw("JSON_EXTRACT(properties, '$.tenant_id') = ?", [$tenantId]);
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->integer('user_id'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->string('subject_type'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->lower();
            $query->whereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
        }

        $perPage = min($request->integer('per_page', 50), 500);

        return response()->json($query->orderByDesc('activity_log.created_at')->paginate($perPage));
    }

    /**
     * Display the specified activity log.
     */
    public function show(Request $request, int $activity): JsonResponse
    {
        $tenantId = (int) ($request->attributes->get('tenant_id') ?? 0);

        $log = DB::table('activity_log')
            ->leftJoin('users', 'users.id', '=', 'activity_log.causer_id')
            ->where('activity_log.id', $activity)
            ->select([
                'activity_log.*',
                'users.name as user_name',
            ]);

        if ($tenantId > 0) {
            $log->whereRaw("JSON_EXTRACT(activity_log.properties, '$.tenant_id') = ?", [$tenantId]);
        }

        $log = $log
            ->first();

        return response()->json($log);
    }
}
