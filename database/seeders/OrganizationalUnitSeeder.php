<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrganizationalUnit;
use Illuminate\Support\Facades\DB;

/**
 * OrganizationalUnitSeeder
 * 
 * Seeds the complete hierarchical organizational structure for
 * Kibaha Municipal Council in Pwani Region, Tanzania
 * 
 * Structure:
 * - Pwani Region
 *   - Kibaha Municipal Council (District)
 *     - 12 Wards
 *       - Villages (per ward)
 *       - Health Facilities (per ward)
 */
class OrganizationalUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('organizational_units')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // =================================================================
        // LEVEL 1: REGION
        // =================================================================
        
        $region = OrganizationalUnit::create([
            'code' => 'PWN',
            'name' => 'Pwani Region',
            'level' => 'REGION',
            'parent_id' => null,
            'latitude' => -6.7833,
            'longitude' => 38.9167,
            'population' => 1098668, // 2022 Census
            'description' => 'Pwani Region (Swahili: Mkoa wa Pwani) is one of Tanzania\'s 31 administrative regions. The regional capital is Kibaha.',
            'is_active' => true,
        ]);

        $this->command->info('✅ Created Region: Pwani Region');

        // =================================================================
        // LEVEL 2: DISTRICT (Kibaha Municipal Council)
        // =================================================================
        
        $district = OrganizationalUnit::create([
            'code' => 'KBH',
            'name' => 'Kibaha Municipal Council',
            'level' => 'DISTRICT',
            'parent_id' => $region->id,
            'latitude' => -6.7667,
            'longitude' => 38.9167,
            'population' => 175000, // Estimated 2024
            'description' => 'Kibaha Municipal Council (Swahili: Halmashauri ya Manispaa ya Kibaha) is a municipal council in Pwani Region. It was elevated to municipal status in 2015.',
            'is_active' => true,
        ]);

        $this->command->info('✅ Created District: Kibaha Municipal Council');

        // =================================================================
        // LEVEL 3: WARDS (12 Wards of Kibaha)
        // =================================================================
        
        $wards = [
            [
                'code' => 'KBH-01',
                'name' => 'Kibaha Mjini',
                'latitude' => -6.7667,
                'longitude' => 38.9167,
                'population' => 25000,
                'description' => 'Kibaha town center ward, serving as the administrative and commercial hub of the municipality.',
            ],
            [
                'code' => 'KBH-02',
                'name' => 'Mbwawa',
                'latitude' => -6.7500,
                'longitude' => 38.9000,
                'population' => 18000,
                'description' => 'Residential ward located northwest of Kibaha town center.',
            ],
            [
                'code' => 'KBH-03',
                'name' => 'Kongowe',
                'latitude' => -6.7800,
                'longitude' => 38.9300,
                'population' => 15000,
                'description' => 'Eastern ward with mixed residential and agricultural activities.',
            ],
            [
                'code' => 'KBH-04',
                'name' => 'Mlandizi',
                'latitude' => -6.7400,
                'longitude' => 38.8800,
                'population' => 12000,
                'description' => 'Northern ward along the main highway to Morogoro.',
            ],
            [
                'code' => 'KBH-05',
                'name' => 'Soga',
                'latitude' => -6.8000,
                'longitude' => 38.9000,
                'population' => 14000,
                'description' => 'Southern ward with agriculture and livestock activities.',
            ],
            [
                'code' => 'KBH-06',
                'name' => 'Mailimoja',
                'latitude' => -6.7600,
                'longitude' => 38.9400,
                'population' => 16000,
                'description' => 'Eastern residential ward with growing population.',
            ],
            [
                'code' => 'KBH-07',
                'name' => 'Picha ya Ndege',
                'latitude' => -6.7300,
                'longitude' => 38.9100,
                'population' => 13000,
                'description' => 'Northern ward known for educational institutions.',
            ],
            [
                'code' => 'KBH-08',
                'name' => 'Visiga',
                'latitude' => -6.7900,
                'longitude' => 38.9200,
                'population' => 11000,
                'description' => 'South-eastern ward with agricultural focus.',
            ],
            [
                'code' => 'KBH-09',
                'name' => 'Kwala',
                'latitude' => -6.7700,
                'longitude' => 38.8900,
                'population' => 10000,
                'description' => 'Western ward with emerging residential developments.',
            ],
            [
                'code' => 'KBH-10',
                'name' => 'Tumbi',
                'latitude' => -6.8100,
                'longitude' => 38.9100,
                'population' => 15000,
                'description' => 'Southern ward with commercial and residential areas.',
            ],
            [
                'code' => 'KBH-11',
                'name' => 'Mkuza',
                'latitude' => -6.7500,
                'longitude' => 38.9500,
                'population' => 13000,
                'description' => 'Eastern ward bordering neighboring districts.',
            ],
            [
                'code' => 'KBH-12',
                'name' => 'Misugusugu',
                'latitude' => -6.8000,
                'longitude' => 38.8800,
                'population' => 13000,
                'description' => 'South-western ward with agricultural activities.',
            ],
        ];

        $createdWards = [];
        foreach ($wards as $wardData) {
            $ward = OrganizationalUnit::create(array_merge($wardData, [
                'level' => 'WARD',
                'parent_id' => $district->id,
                'is_active' => true,
            ]));
            
            $createdWards[] = $ward;
            $this->command->info("   ✅ Created Ward: {$ward->name} (Pop: {$ward->population})");
        }

        $this->command->info('✅ Created 12 Wards in Kibaha Municipal Council');

        // =================================================================
        // LEVEL 4: VILLAGES (Sample villages for each ward)
        // =================================================================
        
        $villagesData = [
            'Kibaha Mjini' => ['Kibaha A', 'Kibaha B', 'Kibaha C', 'Msangani', 'Mabibo'],
            'Mbwawa' => ['Mbwawa A', 'Mbwawa B', 'Kigugu', 'Nyangala'],
            'Kongowe' => ['Kongowe A', 'Kongowe B', 'Mtoni'],
            'Mlandizi' => ['Mlandizi A', 'Mlandizi B', 'Gwata'],
            'Soga' => ['Soga A', 'Soga B', 'Manzese'],
            'Mailimoja' => ['Mailimoja A', 'Mailimoja B'],
            'Picha ya Ndege' => ['Picha A', 'Picha B', 'Ndege'],
            'Visiga' => ['Visiga A', 'Visiga B'],
            'Kwala' => ['Kwala A', 'Kwala B'],
            'Tumbi' => ['Tumbi A', 'Tumbi B', 'Tumbi C'],
            'Mkuza' => ['Mkuza A', 'Mkuza B'],
            'Misugusugu' => ['Misugusugu A', 'Misugusugu B'],
        ];

        $villageCount = 0;
        foreach ($createdWards as $ward) {
            if (isset($villagesData[$ward->name])) {
                foreach ($villagesData[$ward->name] as $villageName) {
                    OrganizationalUnit::create([
                        'code' => $ward->code . '-V' . str_pad($villageCount + 1, 2, '0', STR_PAD_LEFT),
                        'name' => $villageName,
                        'level' => 'VILLAGE',
                        'parent_id' => $ward->id,
                        'latitude' => $ward->latitude + (rand(-50, 50) / 10000),
                        'longitude' => $ward->longitude + (rand(-50, 50) / 10000),
                        'population' => rand(800, 3000),
                        'description' => "Village in {$ward->name} ward",
                        'is_active' => true,
                    ]);
                    $villageCount++;
                }
            }
        }

        $this->command->info("✅ Created {$villageCount} Villages across all wards");

        // =================================================================
        // LEVEL 5: HEALTH FACILITIES (Sample facilities)
        // =================================================================
        
        $facilities = [
            ['ward' => 'Kibaha Mjini', 'name' => 'Kibaha District Hospital', 'type' => 'Hospital'],
            ['ward' => 'Kibaha Mjini', 'name' => 'Kibaha Health Centre', 'type' => 'Health Centre'],
            ['ward' => 'Mbwawa', 'name' => 'Mbwawa Dispensary', 'type' => 'Dispensary'],
            ['ward' => 'Kongowe', 'name' => 'Kongowe Dispensary', 'type' => 'Dispensary'],
            ['ward' => 'Mlandizi', 'name' => 'Mlandizi Health Centre', 'type' => 'Health Centre'],
            ['ward' => 'Soga', 'name' => 'Soga Dispensary', 'type' => 'Dispensary'],
            ['ward' => 'Mailimoja', 'name' => 'Mailimoja Dispensary', 'type' => 'Dispensary'],
            ['ward' => 'Tumbi', 'name' => 'Tumbi Health Centre', 'type' => 'Health Centre'],
        ];

        $facilityCount = 0;
        foreach ($facilities as $facilityData) {
            $ward = collect($createdWards)->firstWhere('name', $facilityData['ward']);
            
            if ($ward) {
                OrganizationalUnit::create([
                    'code' => $ward->code . '-F' . str_pad($facilityCount + 1, 2, '0', STR_PAD_LEFT),
                    'name' => $facilityData['name'],
                    'level' => 'FACILITY',
                    'parent_id' => $ward->id,
                    'latitude' => $ward->latitude + (rand(-30, 30) / 10000),
                    'longitude' => $ward->longitude + (rand(-30, 30) / 10000),
                    'description' => "{$facilityData['type']} serving {$ward->name} ward and surrounding areas",
                    'is_active' => true,
                ]);
                $facilityCount++;
            }
        }

        $this->command->info("✅ Created {$facilityCount} Health Facilities");

        // =================================================================
        // SUMMARY
        // =================================================================
        
        $totalUnits = OrganizationalUnit::count();
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('ORGANIZATIONAL STRUCTURE SUMMARY');
        $this->command->info('========================================');
        $this->command->info("✅ Total Organizational Units: {$totalUnits}");
        $this->command->info('   - 1 Region (Pwani)');
        $this->command->info('   - 1 District (Kibaha Municipal Council)');
        $this->command->info("   - 12 Wards");
        $this->command->info("   - {$villageCount} Villages");
        $this->command->info("   - {$facilityCount} Health Facilities");
        $this->command->info('========================================');
        $this->command->info('Total Population: ' . number_format($district->population));
        $this->command->info('========================================');
    }
}
