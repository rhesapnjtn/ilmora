<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the default administrator account.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ilmora.test'],
            [
                'name' => 'Administrator Ilmora',
                'password' => 'password',
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        );
    }
}
