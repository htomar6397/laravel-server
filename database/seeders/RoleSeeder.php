<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

/**
 * RoleSeeder
 * 
 * Seeds the roles table with all system roles and their permissions
 * for the KMC M&E System
 * 
 * Roles Created:
 * - System Administrator (Full access)
 * - M&E Officer (Data entry + Reporting)
 * - Field Officer (Data collection)
 * - Manager (View + Approve)
 * - Finance Officer (Financial management)
 * - Data Entry Clerk (Basic data entry)
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing roles
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $roles = [
            [
                'name' => 'System Administrator',
                'description' => 'Full system access with all permissions. Can manage users, roles, system settings, and all data.',
                'permissions' => [
                    '*', // Wildcard permission (grants everything)
                ],
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'M&E Officer',
                'description' => 'Monitoring & Evaluation Officer. Can enter data, manage projects, view reports, and track indicators.',
                'permissions' => [
                    // Project Management
                    'view_projects',
                    'create_projects',
                    'edit_projects',
                    'delete_projects',
                    'view_project_details',
                    
                    // Indicator Management
                    'view_indicators',
                    'create_indicators',
                    'edit_indicators',
                    'delete_indicators',
                    'manage_project_indicators',
                    
                    // Data Entry
                    'enter_data',
                    'edit_data',
                    'delete_data',
                    'view_data_history',
                    
                    // Expenditure Management
                    'view_expenditures',
                    'create_expenditures',
                    'edit_expenditures',
                    'view_expenditure_details',
                    
                    // Photo Capture
                    'capture_photos',
                    'view_photos',
                    'delete_photos',
                    
                    // Reports
                    'view_reports',
                    'generate_reports',
                    'export_reports',
                    'view_ai_reports',
                    'generate_ai_reports',
                    'view_quarterly_packs',
                    'generate_quarterly_packs',
                    
                    // Dashboard
                    'view_dashboard',
                    'view_statistics',
                    'view_charts',
                    
                    // Themes
                    'view_themes',
                ],
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Field Officer',
                'description' => 'Field data collection officer. Can enter data, capture GPS photos, and record expenditures in the field.',
                'permissions' => [
                    // Data Entry
                    'enter_data',
                    'view_data_history',
                    
                    // Photo Capture
                    'capture_photos',
                    'view_photos',
                    
                    // Expenditure Recording
                    'create_expenditures',
                    'view_expenditures',
                    
                    // Project Viewing
                    'view_projects',
                    'view_project_details',
                    
                    // Indicator Viewing
                    'view_indicators',
                    
                    // Dashboard
                    'view_dashboard',
                    
                    // Mobile App Access
                    'use_mobile_app',
                    'sync_offline_data',
                ],
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Manager',
                'description' => 'Management level access. Can view all reports, approve expenditures, and monitor project performance.',
                'permissions' => [
                    // Project Viewing
                    'view_projects',
                    'view_project_details',
                    'view_project_reports',
                    
                    // Indicator Viewing
                    'view_indicators',
                    'view_indicator_performance',
                    
                    // Data Viewing
                    'view_data_history',
                    
                    // Expenditure Approval
                    'view_expenditures',
                    'view_expenditure_details',
                    'approve_expenditures',
                    'reject_expenditures',
                    'verify_expenditures',
                    
                    // Reports
                    'view_reports',
                    'generate_reports',
                    'export_reports',
                    'view_ai_reports',
                    'generate_ai_reports',
                    'view_quarterly_packs',
                    'generate_quarterly_packs',
                    'view_financial_reports',
                    
                    // Dashboard
                    'view_dashboard',
                    'view_statistics',
                    'view_charts',
                    'view_kpis',
                    
                    // Photo Viewing
                    'view_photos',
                    
                    // Notifications
                    'view_notifications',
                    'receive_alerts',
                ],
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Finance Officer',
                'description' => 'Financial management officer. Can manage expenditures, approve transactions, and view financial reports.',
                'permissions' => [
                    // Expenditure Management
                    'view_expenditures',
                    'create_expenditures',
                    'edit_expenditures',
                    'delete_expenditures',
                    'approve_expenditures',
                    'reject_expenditures',
                    'verify_expenditures',
                    'view_expenditure_details',
                    
                    // Financial Reports
                    'view_reports',
                    'view_financial_reports',
                    'generate_financial_reports',
                    'export_reports',
                    
                    // Project Viewing (for budget tracking)
                    'view_projects',
                    'view_project_details',
                    'view_project_budgets',
                    
                    // Dashboard
                    'view_dashboard',
                    'view_statistics',
                    'view_financial_charts',
                    
                    // Budget Monitoring
                    'view_budget_utilization',
                    'track_financial_performance',
                ],
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Data Entry Clerk',
                'description' => 'Basic data entry clerk. Can enter and view data for assigned projects.',
                'permissions' => [
                    // Data Entry
                    'enter_data',
                    'view_data_history',
                    
                    // Project Viewing
                    'view_projects',
                    'view_project_details',
                    
                    // Indicator Viewing
                    'view_indicators',
                    
                    // Dashboard
                    'view_dashboard',
                ],
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert all roles
        foreach ($roles as $role) {
            Role::create($role);
        }

        $this->command->info('✅ Created ' . count($roles) . ' roles with complete permissions');
        $this->command->info('   - System Administrator (Full access)');
        $this->command->info('   - M&E Officer (Data entry + Reports)');
        $this->command->info('   - Field Officer (Field data collection)');
        $this->command->info('   - Manager (Approval + Reports)');
        $this->command->info('   - Finance Officer (Financial management)');
        $this->command->info('   - Data Entry Clerk (Basic entry)');
    }
}
