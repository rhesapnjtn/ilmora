<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $action = $request->string('action')->toString();
        $logs = AdminAuditLog::query()
            ->with('admin:id,name,email')
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->latest()
            ->paginate(20);

        return response()->json([
            'logs' => collect($logs->items())->map(fn (AdminAuditLog $log): array => [
                'id' => $log->id,
                'action' => $log->action,
                'admin' => $log->admin ? ['name' => $log->admin->name, 'email' => $log->admin->email] : null,
                'subjectType' => $log->subject_type,
                'subjectId' => $log->subject_id,
                'ipAddress' => $log->ip_address,
                'createdAt' => $log->created_at?->toIso8601String(),
            ])->all(),
            'meta' => ['page' => $logs->currentPage(), 'lastPage' => $logs->lastPage(), 'total' => $logs->total()],
        ]);
    }
}
