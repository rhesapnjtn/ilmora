<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->timestamp('user_typing_at')->nullable()->after('last_message_at');
            $table->timestamp('cs_typing_at')->nullable()->after('user_typing_at');
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropColumn(['user_typing_at', 'cs_typing_at']);
        });
    }
};
