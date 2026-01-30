<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\{Project, Theme, OrganizationalUnit, User};
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('projects')->delete();

        // Get reference data
        $themes = Theme::all()->keyBy('code');
        $orgUnits = OrganizationalUnit::where('level', 'WARD')->get();
        $adminUser = User::where('username', 'admin')->first();

        $projects = [
            [
                'code' => 'KMC-EDU-001',
                'name' => 'Primary School Infrastructure Improvement',
                'description' => 'Construction and renovation of primary school buildings, classrooms, and facilities across Kibaha Municipality to improve learning environment.',
                'theme_code' => 'EDU',
                'ward_names' => ['Kibaha Mjini', 'Mlandizi', 'Kongowe'],
                'sector' => 'Education',
                'donor' => 'World Bank',
                'budget' => 250000000.00,
                'currency' => 'TZS',
                'start_date' => '2024-01-15',
                'end_date' => '2025-12-31',
                'status' => 'ACTIVE',
                'completion_percentage' => 65.0,
                'latitude' => -6.7645,
                'longitude' => 38.9042,
                'location_description' => 'Multiple locations across Kibaha Municipality',
            ],
            [
                'code' => 'KMC-HEA-001',
                'name' => 'Rural Health Center Upgrade',
                'description' => 'Upgrading rural health facilities with modern equipment, staff training, and improved medical services.',
                'theme_code' => 'HEA',
                'ward_names' => ['Mbwawa', 'Kibaha Mjini', 'Mlandizi'],
                'sector' => 'Healthcare',
                'donor' => 'UNICEF',
                'budget' => 180000000.00,
                'currency' => 'TZS',
                'start_date' => '2024-03-01',
                'end_date' => '2025-06-30',
                'status' => 'ACTIVE',
                'completion_percentage' => 45.0,
                'latitude' => -6.7823,
                'longitude' => 38.9123,
                'location_description' => 'Rural health centers in target wards',
            ],
            [
                'code' => 'KMC-INF-001',
                'name' => 'Rural Road Construction Project',
                'description' => 'Construction of all-weather roads connecting rural communities to main transport networks.',
                'theme_code' => 'INF',
                'ward_names' => ['Kongowe', 'Soga', 'Mailimoja'],
                'sector' => 'Infrastructure',
                'donor' => 'African Development Bank',
                'budget' => 450000000.00,
                'currency' => 'TZS',
                'start_date' => '2023-09-01',
                'end_date' => '2025-08-31',
                'status' => 'ACTIVE',
                'completion_percentage' => 78.0,
                'latitude' => -6.7543,
                'longitude' => 38.8921,
                'location_description' => 'Rural road networks in target areas',
            ],
            [
                'code' => 'KMC-AGR-001',
                'name' => 'Smallholder Farmer Support Program',
                'description' => 'Providing agricultural inputs, training, and market access support to smallholder farmers.',
                'theme_code' => 'AGR',
                'ward_names' => ['Mlandizi', 'Kongowe', 'Kibaha Mjini'],
                'sector' => 'Agriculture',
                'donor' => 'FAO',
                'budget' => 120000000.00,
                'currency' => 'TZS',
                'start_date' => '2024-02-01',
                'end_date' => '2024-12-31',
                'status' => 'ACTIVE',
                'completion_percentage' => 82.0,
                'latitude' => -6.7712,
                'longitude' => 38.8987,
                'location_description' => 'Farming communities in target wards',
            ],
            [
                'code' => 'KMC-ENV-001',
                'name' => 'Community Tree Planting Initiative',
                'description' => 'Environmental conservation through community-based tree planting and forest management.',
                'theme_code' => 'ENV',
                'ward_names' => ['Picha ya Ndege', 'Visiga', 'Kwala'],
                'sector' => 'Environment',
                'donor' => 'UNDP',
                'budget' => 85000000.00,
                'currency' => 'TZS',
                'start_date' => '2024-04-01',
                'end_date' => '2025-03-31',
                'status' => 'ACTIVE',
                'completion_percentage' => 55.0,
                'latitude' => -6.7689,
                'longitude' => 38.9065,
                'location_description' => 'Community lands and forest areas',
            ],
            [
                'code' => 'KMC-ECO-001',
                'name' => 'Youth Entrepreneurship Development',
                'description' => 'Training and financial support for young entrepreneurs to start and grow businesses.',
                'theme_code' => 'ECO',
                'ward_names' => ['Kibaha Mjini', 'Mlandizi', 'Tumbi'],
                'sector' => 'Economic Development',
                'donor' => 'ILO',
                'budget' => 95000000.00,
                'currency' => 'TZS',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'status' => 'ACTIVE',
                'completion_percentage' => 70.0,
                'latitude' => -6.7656,
                'longitude' => 38.9023,
                'location_description' => 'Business development centers',
            ],
            [
                'code' => 'KMC-SOC-001',
                'name' => 'Community Water Supply Project',
                'description' => 'Installation of clean water supply systems in underserved communities.',
                'theme_code' => 'INF',
                'ward_names' => ['Kongowe', 'Soga', 'Mailimoja'],
                'sector' => 'Water & Sanitation',
                'donor' => 'WaterAid',
                'budget' => 320000000.00,
                'currency' => 'TZS',
                'start_date' => '2023-11-01',
                'end_date' => '2025-10-31',
                'status' => 'ACTIVE',
                'completion_percentage' => 60.0,
                'latitude' => -6.7734,
                'longitude' => 38.9101,
                'location_description' => 'Underserved communities',
            ],
            [
                'code' => 'KMC-YTH-001',
                'name' => 'Sports Facilities Development',
                'description' => 'Construction of sports facilities and youth centers to promote physical activity and youth engagement.',
                'theme_code' => 'YTH',
                'ward_names' => ['Kibaha Mjini', 'Mlandizi', 'Mkuza'],
                'sector' => 'Sports & Recreation',
                'donor' => 'Ministry of Sports',
                'budget' => 150000000.00,
                'currency' => 'TZS',
                'start_date' => '2024-05-01',
                'end_date' => '2025-04-30',
                'status' => 'ACTIVE',
                'completion_percentage' => 35.0,
                'latitude' => -6.7678,
                'longitude' => 38.9045,
                'location_description' => 'Community centers and schools',
            ],
            [
                'code' => 'KMC-TEC-001',
                'name' => 'Digital Literacy and ICT Training',
                'description' => 'Providing digital skills training and ICT infrastructure to bridge the digital divide.',
                'theme_code' => 'TEC',
                'ward_names' => ['Tumbi', 'Mkuza', 'Misugusugu'],
                'sector' => 'Technology',
                'donor' => 'ITU',
                'budget' => 110000000.00,
                'currency' => 'TZS',
                'start_date' => '2024-03-15',
                'end_date' => '2025-02-28',
                'status' => 'ACTIVE',
                'completion_percentage' => 58.0,
                'latitude' => -6.7701,
                'longitude' => 38.9078,
                'location_description' => 'Community ICT centers',
            ],
            [
                'code' => 'KMC-GOV-001',
                'name' => 'Public Service Improvement Initiative',
                'description' => 'Enhancing public service delivery through capacity building and system improvements.',
                'theme_code' => 'GOV',
                'ward_names' => ['Visiga', 'Kwala', 'Misugusugu'],
                'sector' => 'Governance',
                'donor' => 'UNDP',
                'budget' => 75000000.00,
                'currency' => 'TZS',
                'start_date' => '2024-02-15',
                'end_date' => '2024-12-15',
                'status' => 'COMPLETED',
                'completion_percentage' => 100.0,
                'latitude' => -6.7667,
                'longitude' => 38.9056,
                'location_description' => 'Municipal offices and service centers',
            ],
        ];

        foreach ($projects as $projectData) {
            // Get theme ID
            $themeId = $themes[$projectData['theme_code']]->id;
            
            // Get random org unit from specified wards
            $targetOrgUnits = $orgUnits->whereIn('name', $projectData['ward_names']);
            $orgUnit = $targetOrgUnits->random();
            
            // Prepare project data
            $project = [
                'code' => $projectData['code'],
                'name' => $projectData['name'],
                'description' => $projectData['description'],
                'theme_id' => $themeId,
                'org_unit_id' => $orgUnit->id,
                'sector' => $projectData['sector'],
                'donor' => $projectData['donor'],
                'budget' => $projectData['budget'],
                'currency' => $projectData['currency'],
                'start_date' => $projectData['start_date'],
                'end_date' => $projectData['end_date'],
                'status' => $projectData['status'],
                'completion_percentage' => $projectData['completion_percentage'],
                'latitude' => $projectData['latitude'],
                'longitude' => $projectData['longitude'],
                'location_description' => $projectData['location_description'],
                'created_by' => $adminUser ? $adminUser->id : 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Remove extra fields
            unset($project['theme_code'], $project['ward_names']);
            
            Project::create($project);
        }

        $this->command->info('✓ Projects seeded successfully!');
    }
}
