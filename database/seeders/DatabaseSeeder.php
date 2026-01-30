<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting KMC M&E System Database Seeding...');
        
        // Seed organizational units first (required by other seeders)
        $this->command->info('📋 Seeding Organizational Units...');
        $this->call(OrganizationalUnitSeeder::class);
        
        // Seed roles and permissions
        $this->command->info('👥 Seeding Roles and Permissions...');
        $this->call(RoleSeeder::class);
        
        // Seed admin user
        $this->command->info('👤 Seeding Admin User...');
        $this->call(UserSeeder::class);
        
        // Seed themes
        $this->command->info('🎨 Seeding Themes...');
        $this->call(ThemeSeeder::class);
        
        // Seed indicators (depends on themes)
        $this->command->info('📊 Seeding Indicators...');
        $this->call(IndicatorSeeder::class);
        
        // Seed projects (depends on themes and org units)
        $this->command->info('🏗️ Seeding Projects...');
        $this->call(ProjectSeeder::class);
        
        // You can add more seeders here as needed
        // $this->call(UserSeeder::class);
        // $this->call(ExpenditureSeeder::class);
        // $this->call(DataEntrySeeder::class);
        // $this->call(PhotoCaptureSeeder::class);
        
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📈 Summary:');
        $this->command->info('   • Organizational Units: Hierarchical structure (Region → District → Wards → Villages → Facilities)');
        $this->command->info('   • Roles: 6 roles with comprehensive permissions (System Administrator, Project Manager, etc.)');
        $this->command->info('   • Themes: 10 development themes (Education, Healthcare, Infrastructure, etc.)');
        $this->command->info('   • Indicators: 24 performance indicators across all themes');
        $this->command->info('   • Projects: 10 sample projects with realistic data');
        $this->command->info('');
        $this->command->info('🚀 Your KMC M&E System is now ready for use!');
    }
}
