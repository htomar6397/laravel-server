<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create System Administrator
        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@kmc.go.tz',
            'password' => Hash::make('password'),
            'full_name' => 'System Administrator',
            'org_unit_id' => 2, // Kibaha Municipal Council
            'department' => 'ICT',
            'position' => 'System Administrator',
            'phone' => '+255712345678',
            'is_active' => true,
        ]);

        // Assign System Administrator role
        $adminRole = Role::where('name', 'System Administrator')->first();
        if ($adminRole) {
            $admin->roles()->attach($adminRole->id);
        }

        $this->command->info('✅ Created System Administrator (admin@kmc.go.tz / password)');
    }
}
