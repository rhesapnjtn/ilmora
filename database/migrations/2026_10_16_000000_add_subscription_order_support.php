<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Introduce subscription "orders": a subscription can now be created in a
     * pending state (not yet paid), linked to the plan and billing cycle that
     * was chosen, while the matching row in `payments` records the pending
     * transaction an administrator verifies later.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('user_id')->constrained('plans')->nullOnDelete();
            $table->string('billing_cycle')->nullable()->after('plan');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'active', 'expired', 'canceled'])->default('active')->change();
            $table->date('starts_at')->nullable()->change();
            $table->date('expires_at')->nullable()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable()->change();
            $table->foreignId('subscription_id')->nullable()->after('course_id')->constrained('subscriptions')->nullOnDelete();
            $table->string('reference')->nullable()->unique()->after('payment_id');
            $table->text('notes')->nullable()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->dropConstrainedForeignId('subscription_id');
            $table->dropColumn('notes');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable(false)->change();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('status', ['active', 'expired', 'canceled'])->default('active')->change();
            $table->date('starts_at')->nullable(false)->change();
            $table->date('expires_at')->nullable(false)->change();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn('billing_cycle');
        });
    }
};
