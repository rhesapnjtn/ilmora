<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

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
    public function typing(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ((int) $conversation->user_id !== (int) $user->id && ! $user->isAdmin()) {
            return response()->json(['message' => 'Percakapan tidak ditemukan.'], 404);
        }

        if ($user->isAdmin()) {
            $conversation->update(['cs_typing_at' => now()]);
        } else {
            $conversation->update(['user_typing_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    public function stopTyping(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ((int) $conversation->user_id !== (int) $user->id && ! $user->isAdmin()) {
            return response()->json(['message' => 'Percakapan tidak ditemukan.'], 404);
        }

        if ($user->isAdmin()) {
            $conversation->update(['cs_typing_at' => null]);
        } else {
            $conversation->update(['user_typing_at' => null]);
        }

        return response()->json(['ok' => true]);
    }

    public function uploadAttachment(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ((int) $conversation->user_id !== (int) $user->id && ! $user->isAdmin()) {
            return response()->json(['message' => 'Percakapan tidak ditemukan.'], 404);
        }

        if (! $conversation->isOpen()) {
            return response()->json(['message' => 'Percakapan sudah ditutup.'], 422);
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf,txt,doc,docx', 'max:10240'],
        ]);

        $file = $validated['file'];
        $path = $file->store('chat-attachments', 'public');

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => '[Lampiran] '.$file->getClientOriginalName(),
        ]);

        ChatAttachment::create([
            'message_id' => $message->id,
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        if ($user->isAdmin()) {
            $conversation->update([
                'cs_user_id' => $conversation->cs_user_id ?? $user->id,
                'last_message_at' => now(),
                'cs_typing_at' => null,
            ]);
            $conversation->user?->notify(new ChatMessageNotification($message, $conversation));
        } else {
            $conversation->update(['last_message_at' => now(), 'user_typing_at' => null]);
            if ($conversation->cs_user_id !== null) {
                $conversation->cs?->notify(new ChatMessageNotification($message, $conversation));
            }
        }

        $message->load(['sender', 'attachments']);

        return response()->json([
            'message' => 'Lampiran terkirim.',
            'chatMessage' => $this->presentMessage($message),
        ], 201);
    }

    public static function availableTopics(): array
    {
        return [
            ['code' => 'payment', 'label' => 'Masalah Pembayaran'],
            ['code' => 'access', 'label' => 'Akses Kursus'],
            ['code' => 'subscription', 'label' => 'Langganan'],
            ['code' => 'certificate', 'label' => 'Sertifikat'],
            ['code' => 'account', 'label' => 'Akun & Profil'],
            ['code' => 'other', 'label' => 'Lainnya'],
        ];
    }

    public function topics(Request $request): JsonResponse
    {
        return response()->json(['topics' => self::availableTopics()]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = ChatConversation::query()
            ->where('user_id', $user->id)
            ->with(['cs', 'messages' => fn ($q) => $q->latest('created_at')->limit(1)])
            ->latest('last_message_at')
            ->latest('id')
            ->get()
            ->map(fn (ChatConversation $c): array => $this->presentConversation($c, $user))
            ->all();

        return response()->json(['conversations' => $conversations]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'topic' => ['required', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = ChatConversation::create([
            'user_id' => $user->id,
            'topic' => $validated['topic'],
            'subject' => $validated['subject'] ?? null,
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $validated['message'],
        ]);

        $conversation->load(['cs', 'messages.sender']);

        return response()->json([
            'message' => 'Percakapan dimulai.',
            'conversation' => $this->presentConversation($conversation, $user),
        ], 201);
    }

    public function show(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ((int) $conversation->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Percakapan tidak ditemukan.'], 404);
        }

        $conversation->load(['cs', 'messages.sender']);
        $this->markRead($conversation, $user);

        return response()->json([
            'conversation' => $this->presentConversation($conversation, $user, true),
        ]);
    }

    public function sendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ((int) $conversation->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Percakapan tidak ditemukan.'], 404);
        }

        if (! $conversation->isOpen()) {
            return response()->json(['message' => 'Percakapan sudah ditutup.'], 422);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
        ]);

        $conversation->update(['last_message_at' => now(), 'user_typing_at' => null]);

        if ($conversation->cs_user_id !== null) {
            $conversation->cs?->notify(new ChatMessageNotification($message, $conversation));
        }

        return response()->json([
            'message' => 'Pesan terkirim.',
            'chatMessage' => $this->presentMessage($message->load('sender')),
        ], 201);
    }

    public function close(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ((int) $conversation->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Percakapan tidak ditemukan.'], 404);
        }

        $conversation->update(['status' => 'closed']);

        return response()->json(['message' => 'Percakapan ditutup.']);
    }

    public function poll(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ((int) $conversation->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Percakapan tidak ditemukan.'], 404);
        }

        $after = $request->integer('after', 0);

        $messages = $conversation->messages()
            ->when($after > 0, fn ($q) => $q->where('id', '>', $after))
            ->get()
            ->map(fn (ChatMessage $m): array => $this->presentMessage($m))
            ->all();

        $this->markRead($conversation, $user);

        return response()->json([
            'messages' => $messages,
            'status' => $conversation->status,
            'typing' => [
                'cs' => $conversation->isCSTyping(),
                'user' => $conversation->isUserTyping(),
            ],
        ]);
    }

    private function markRead(ChatConversation $conversation, User $user): void
    {
        ChatMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
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
            'read' => $message->isRead(),
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

    private function presentConversation(ChatConversation $conversation, User $user, bool $withMessages = false): array
    {
        $last = $conversation->messages->last() ?? $conversation->latestMessage->first();

        $unread = ChatMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();

        $data = [
            'id' => $conversation->id,
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
