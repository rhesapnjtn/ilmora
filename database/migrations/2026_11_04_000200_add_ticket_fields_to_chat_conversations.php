<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table): void {
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('status');
            $table->foreignId('assigned_admin_id')->nullable()->after('cs_user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table): void {
            $table->dropColumn(['priority', 'resolved_at']);
            $table->dropConstrainedForeignId('assigned_admin_id');
        });
    }
};
