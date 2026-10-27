<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('shuffle_questions')->default(true)->after('passing_score');
            $table->boolean('shuffle_options')->default(true)->after('shuffle_questions');
            $table->unsignedSmallInteger('max_attempts')->nullable()->after('shuffle_options');
            $table->unsignedSmallInteger('time_limit_minutes')->nullable()->after('max_attempts');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->json('question_order')->nullable()->after('answers');
            $table->json('option_order')->nullable()->after('question_order');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['question_order', 'option_order']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['shuffle_questions', 'shuffle_options', 'max_attempts', 'time_limit_minutes']);
        });
    }
};
