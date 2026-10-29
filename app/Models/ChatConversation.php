<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cs_user_id',
        'topic',
        'subject',
        'status',
        'last_message_at',
        'user_typing_at',
        'cs_typing_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'user_typing_at' => 'datetime',
            'cs_typing_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cs(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cs_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')->orderBy('created_at');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')->latest('created_at')->limit(1);
    }

    public function isUserTyping(): bool
    {
        return $this->user_typing_at !== null && $this->user_typing_at->diffInSeconds(now()) < 5;
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isCSTyping(): bool
    {
        return $this->cs_typing_at !== null && $this->cs_typing_at->diffInSeconds(now()) < 5;
    }
}
