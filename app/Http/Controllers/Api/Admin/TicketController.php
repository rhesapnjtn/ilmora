<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();

        $tickets = ChatConversation::query()
            ->with(['user:id,name,email', 'assignedAdmin:id,name'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($priority !== '', fn ($q) => $q->where('priority', $priority))
            ->latest('last_message_at')
            ->paginate(20);

        return response()->json(['tickets' => $tickets]);
    }

    public function update(Request $request, ChatConversation $conversation): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', 'in:open,closed'],
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
            'assigned_admin_id' => ['sometimes', 'nullable', 'exists:users,id'],
        ]);

        if (isset($data['status']) && $data['status'] === 'closed') {
            $data['resolved_at'] = now();
        }

        $conversation->update($data);

        return response()->json(['ticket' => $conversation->fresh(['user', 'assignedAdmin'])]);
    }
}
