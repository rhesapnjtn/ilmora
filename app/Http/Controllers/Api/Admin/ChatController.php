<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\ChatMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->string('status')->toString();

        $conversations = ChatConversation::query()
            ->with(['user', 'cs', 'messages' => fn ($q) => $q->latest('created_at')->limit(1)])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest('last_message_at')
            ->latest('id')
            ->paginate(20);

        return response()->json([
            'conversations' => collect($conversations->items())->map(fn (ChatConversation $c): array => $this->presentConversation($c))->all(),
            'meta' => [
                'page' => $conversations->currentPage(),
                'perPage' => $conversations->perPage(),
                'total' => $conversations->total(),
                'lastPage' => $conversations->lastPage(),
            ],
        ]);
    }

    public function show(ChatConversation $conversation): JsonResponse
    {
        $conversation->load(['user', 'cs', 'messages.sender']);
        $this->markRead($conversation, $conversation->cs ?? auth()->user());

        return response()->json([
            'conversation' => $this->presentConversation($conversation, true),
        ]);
    }

    public function sendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $cs = $request->user();

        if (! $conversation->isOpen()) {
            return response()->json(['message' => 'Percakapan sudah ditutup.'], 422);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $cs->id,
            'body' => $validated['body'],
        ]);

        $conversation->update([
            'cs_user_id' => $conversation->cs_user_id ?? $cs->id,
            'last_message_at' => now(),
            'cs_typing_at' => null,
        ]);

        $conversation->user?->notify(new ChatMessageNotification($message, $conversation));

        return response()->json([
            'message' => 'Pesan terkirim.',
            'chatMessage' => $this->presentMessage($message->load('sender')),
        ], 201);
    }

    public function poll(Request $request, ChatConversation $conversation): JsonResponse
    {
        $after = $request->integer('after', 0);

        $messages = $conversation->messages()
            ->when($after > 0, fn ($q) => $q->where('id', '>', $after))
            ->get()
            ->map(fn (ChatMessage $m): array => $this->presentMessage($m))
            ->all();

        $this->markRead($conversation, $request->user());

        return response()->json([
            'messages' => $messages,
            'status' => $conversation->status,
            'typing' => [
                'cs' => $conversation->isCSTyping(),
                'user' => $conversation->isUserTyping(),
            ],
        ]);
    }

    public function close(ChatConversation $conversation): JsonResponse
    {
        $conversation->update(['status' => 'closed']);

        return response()->json(['message' => 'Percakapan ditutup.']);
    }

    private function markRead(ChatConversation $conversation, ?User $reader): void
    {
        if ($reader === null) {
            return;
        }

        ChatMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function presentMessage(ChatMessage $message): array
    {
        $message->loadMissing(['sender', 'attachments']);

        return [
            'id' => $message->id,
            'senderId' => $message->sender_id,
            'senderName' => $message->sender?->name,
            'isAdmin' => (bool) $message->sender?->isAdmin(),
            'body' => $message->body,
            'read' => $message->read_at !== null,
            'createdAt' => $message->created_at?->toIso8601String(),
            'attachments' => $message->attachments->map(fn (ChatAttachment $a): array => [
                'id' => $a->id,
                'name' => $a->name,
                'url' => Storage::url($a->path),
                'mimeType' => $a->mime_type,
                'size' => $a->size,
            ])->all(),
        ];
    }

    private function presentConversation(ChatConversation $conversation, bool $withMessages = false): array
    {
        $last = $conversation->messages->last() ?? $conversation->messages()->latest('created_at')->first();

        $unread = ChatMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $conversation->user_id)
            ->whereNull('read_at')
            ->count();

        $data = [
            'id' => $conversation->id,
            'userId' => $conversation->user_id,
            'userName' => $conversation->user?->name,
            'userEmail' => $conversation->user?->email,
            'topic' => $conversation->topic,
            'subject' => $conversation->subject,
            'status' => $conversation->status,
            'csName' => $conversation->cs?->name,
            'lastMessage' => $last?->body,
            'lastMessageAt' => $conversation->last_message_at?->toIso8601String(),
            'unreadCount' => $unread,
            'createdAt' => $conversation->created_at?->toIso8601String(),
            'userTyping' => $conversation->isUserTyping(),
            'csTyping' => $conversation->isCSTyping(),
        ];

        if ($withMessages) {
            $data['messages'] = $conversation->messages
                ->map(fn (ChatMessage $m): array => $this->presentMessage($m))
                ->values()
                ->all();
        }

        return $data;
    }
}
