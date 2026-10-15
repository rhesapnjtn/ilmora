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
        Schema::table('courses', function (Blueprint $table) {
            $table->renameColumn('reviewCount', 'review_count');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->text('summary')->nullable()->after('title');
            $table->text('description')->nullable()->after('summary');
            $table->string('category_name')->nullable()->after('category');
            $table->string('instructor_name')->nullable()->after('rating');
            $table->string('instructor_role')->nullable()->after('instructor_name');
            $table->unsignedInteger('students')->default(0)->after('review_count');
            $table->unsignedInteger('duration_hours')->default(0)->after('students');
            $table->string('accent')->default('primary')->after('duration_hours');
            $table->boolean('popular')->default(false)->after('accent');
            $table->boolean('is_free')->default(false)->after('popular');
            $table->json('learn')->nullable()->after('is_free');
            $table->json('includes')->nullable()->after('learn');
        });

        Schema::table('course_lessons', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('course_id');
            $table->string('module')->nullable()->after('title');
            $table->string('duration')->nullable()->after('module');
            $table->text('description')->nullable()->after('duration');
            $table->text('code')->nullable()->after('description');
            $table->json('materials')->nullable()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'module',
                'duration',
                'description',
                'code',
                'materials',
            ]);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'summary',
                'description',
                'category_name',
                'instructor_name',
                'instructor_role',
                'students',
                'duration_hours',
                'accent',
                'popular',
                'is_free',
                'learn',
                'includes',
            ]);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->renameColumn('review_count', 'reviewCount');
        });
    }
};
