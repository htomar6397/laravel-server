<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Theme;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('themes')->delete();

        $themes = [
            [
                'code' => 'EDU',
                'name' => 'Education Development',
                'description' => 'Improving educational infrastructure, quality of education, and access to learning opportunities for all residents.',
                'color' => '#3498db',
                'icon' => 'fas fa-graduation-cap',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'HEA',
                'name' => 'Healthcare Services',
                'description' => 'Enhancing healthcare facilities, improving medical services, and promoting public health initiatives.',
                'color' => '#e74c3c',
                'icon' => 'fas fa-heartbeat',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'INF',
                'name' => 'Infrastructure Development',
                'description' => 'Building and maintaining roads, water systems, electricity, and other essential infrastructure.',
                'color' => '#f39c12',
                'icon' => 'fas fa-road',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'AGR',
                'name' => 'Agricultural Development',
                'description' => 'Supporting farmers, improving agricultural practices, and ensuring food security.',
                'color' => '#27ae60',
                'icon' => 'fas fa-seedling',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'code' => 'ENV',
                'name' => 'Environmental Protection',
                'description' => 'Conserving natural resources, promoting sustainability, and addressing climate change impacts.',
                'color' => '#16a085',
                'icon' => 'fas fa-leaf',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'code' => 'ECO',
                'name' => 'Economic Development',
                'description' => 'Promoting business growth, creating employment opportunities, and supporting local enterprises.',
                'color' => '#9b59b6',
                'icon' => 'fas fa-chart-line',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'code' => 'SOC',
                'name' => 'Social Services',
                'description' => 'Providing social welfare, supporting vulnerable groups, and enhancing community well-being.',
                'color' => '#e67e22',
                'icon' => 'fas fa-hands-helping',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'GOV',
                'name' => 'Governance & Administration',
                'description' => 'Improving public service delivery, enhancing transparency, and strengthening local governance.',
                'color' => '#34495e',
                'icon' => 'fas fa-landmark',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'code' => 'YTH',
                'name' => 'Youth & Sports Development',
                'description' => 'Empowering youth through education, training, sports programs, and entrepreneurship support.',
                'color' => '#f1c40f',
                'icon' => 'fas fa-running',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'code' => 'TEC',
                'name' => 'Technology & Innovation',
                'description' => 'Promoting digital transformation, supporting innovation, and improving access to technology.',
                'color' => '#2c3e50',
                'icon' => 'fas fa-microchip',
                'sort_order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($themes as $theme) {
            Theme::create($theme);
        }

        $this->command->info('✓ Themes seeded successfully!');
    }
}
