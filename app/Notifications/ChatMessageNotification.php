<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ChatMessageNotification extends Notification
{
    public function __construct(
        private readonly ChatMessage $message,
        private readonly ChatConversation $conversation,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->message->loadMissing('sender');

        return [
            'type' => 'chat_message',
            'conversation_id' => $this->conversation->id,
            'message_id' => $this->message->id,
            'sender_name' => $this->message->sender?->name,
            'excerpt' => Str::limit($this->message->body, 140),
            'url' => '/bantuan/chat/'.$this->conversation->id,
        ];
    }
}
