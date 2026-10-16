<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Let a subscriber cancel on their own. An active subscription keeps its
     * benefits until `expires_at` while `cancel_at_period_end` records that it
     * will not be renewed, and `canceled_at`/`cancellation_reason` keep an audit
     * trail. A still-pending order is canceled immediately instead.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('cancel_at_period_end')->default(false)->after('expires_at');
            $table->timestamp('canceled_at')->nullable()->after('cancel_at_period_end');
            $table->string('cancellation_reason', 500)->nullable()->after('canceled_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['cancel_at_period_end', 'canceled_at', 'cancellation_reason']);
        });
    }
};
