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
            $table->boolean('is_answer')->default(false)->after('parent_id');
            $table->index(['lesson_id', 'parent_id', 'created_at'], 'lesson_discussions_listing_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_discussions', function (Blueprint $table) {
            $table->dropIndex('lesson_discussions_listing_index');
            $table->dropColumn('is_answer');
        });
    }
};
