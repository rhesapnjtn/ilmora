<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_reviews', function (Blueprint $table): void {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved')->after('body');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('course_reviews', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
