<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lesson_discussions', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('user_id')->constrained('lesson_discussions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_discussions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
