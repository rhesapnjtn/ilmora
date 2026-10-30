<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'app_name', 'value' => 'Ilmora', 'type' => 'string', 'group' => 'Umum'],
            ['key' => 'app_description', 'value' => 'Platform pembelajaran digital Ilmora', 'type' => 'string', 'group' => 'Umum'],
            ['key' => 'support_email', 'value' => 'support@ilmora.test', 'type' => 'string', 'group' => 'Umum'],
            ['key' => 'support_whatsapp', 'value' => '', 'type' => 'string', 'group' => 'Umum'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'group' => 'Operasional'],
            ['key' => 'registration_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'Operasional'],
            ['key' => 'email_verification_required', 'value' => 'true', 'type' => 'boolean', 'group' => 'Operasional'],
            ['key' => 'max_upload_size_mb', 'value' => '50', 'type' => 'integer', 'group' => 'Operasional'],
            ['key' => 'default_currency', 'value' => 'IDR', 'type' => 'string', 'group' => 'Pembayaran'],
            ['key' => 'service_fee', 'value' => '0', 'type' => 'integer', 'group' => 'Pembayaran'],
            ['key' => 'payment_instructions', 'value' => '', 'type' => 'string', 'group' => 'Pembayaran'],
            ['key' => 'notify_payment_email', 'value' => 'true', 'type' => 'boolean', 'group' => 'Notifikasi'],
            ['key' => 'notify_chat_email', 'value' => 'true', 'type' => 'boolean', 'group' => 'Notifikasi'],
            ['key' => 'notify_subscription_email', 'value' => 'true', 'type' => 'boolean', 'group' => 'Notifikasi'],
            ['key' => 'courses_per_page', 'value' => '12', 'type' => 'integer', 'group' => 'Konten'],
            ['key' => 'auto_approve_reviews', 'value' => 'false', 'type' => 'boolean', 'group' => 'Konten'],
            ['key' => 'subscription_duration_days', 'value' => '30', 'type' => 'integer', 'group' => 'Konten'],
        ];

        foreach ($defaults as $item) {
            Setting::firstOrCreate(['key' => $item['key']], $item);
        }
    }
}
