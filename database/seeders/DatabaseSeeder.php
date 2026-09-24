<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',

        ]);

        // បង្កើត Super Admin ដំបូងគេ
        User::firstOrCreate(
            ['email' => 'admin@ecommerce.test'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('Admin@123456'), // ឬ password សុវត្ថិភាពរបស់អ្នក
                'role'     => 'admin',
            ]
        );
    }
}
