<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@craftistry.com'],
            [
                'fullname' => 'System Admin',
                'password' => bcrypt('Admin@1234'),
                'role'     => 'admin',
            ]
        );
    }
}