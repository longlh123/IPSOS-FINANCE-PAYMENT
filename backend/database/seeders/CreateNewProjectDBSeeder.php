<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectDetail;
use App\Models\ProjectType;
use App\Models\ProjectCode;

class CreateNewProjectDBSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projectPilot = Project::create([
            'internal_code' => '2024-079',
            'project_name' => 'GALLEON_092024',
        ]);

        $projectPilot->projectDetails()->create([
            'symphony' => '24018941',
            'status' => Project::STATUS_ON_GOING,
            'platform' => 'ifield',
            'created_user_id' => 1,
            'planned_field_start' => date('Y-m-d', strtotime('2024-09-13')),
            'planned_field_end' => date('Y-m-d', strtotime('2024-09-19')),
        ]);

        $projectPilot->projectPermissions()->create([
            'user_id' => 1
        ]);

        $provinces = [
            [
                'province_id' => 1,
                'sample_size_main' => 75,
                'price_main' => 30000
            ],
            [
                'province_id' => 2,
                'sample_size_main' => 75,
                'price_main' => 30000
            ],
            [
                'province_id' => 3,
                'sample_size_main' => 50,
                'price_main' => 30000
            ],
            [
                'province_id' => 4,
                'sample_size_main' => 50,
                'price_main' => 30000
            ],
            [
                'province_id' => 5,
                'sample_size_main' => 50,
                'price_main' => 30000
            ]
        ];

        foreach($provinces as $province){
            $projectPilot->projectProvinces()->create($province);
        };

        $projectPilot = Project::create([
            'internal_code' => '2024-206',
            'project_name' => 'SNOM',
        ]);

        $projectPilot->projectDetails()->create([
            'symphony' => '24055099',
            'status' => Project::STATUS_ON_GOING,
            'platform' => 'ifield',
            'created_user_id' => 1,
            'planned_field_start' => date('Y-m-d', strtotime('2024-09-17')),
            'planned_field_end' => date('Y-m-d', strtotime('2024-09-26')),
        ]);

        $projectPilot->projectPermissions()->create([
            'user_id' => 1
        ]);

        $provinces = [
            [
                'province_id' => 1,
                'sample_size_main' => 105,
                'price_main' => 30000, 
                'price_main_1' => 10000, //Non
                'sample_size_booters' => 45,
                'price_boosters' => 30000
            ],
            [
                'province_id' => 2,
                'sample_size_main' => 105,
                'price_main' => 30000, 
                'price_main_1' => 10000, //Non
                'sample_size_booters' => 45,
                'price_boosters' => 30000
            ],
            [
                'province_id' => 3,
                'sample_size_main' => 66,
                'price_main' => 30000, 
                'price_main_1' => 10000, //Non
                'sample_size_booters' => 24,
                'price_boosters' => 30000
            ],
            [
                'province_id' => 12,
                'sample_size_main' => 66,
                'price_main' => 30000, 
                'price_main_1' => 10000, //Non
                'sample_size_booters' => 24,
                'price_boosters' => 30000
            ]
        ];

        foreach($provinces as $province){
            $projectPilot->projectProvinces()->create($province);
        };
    }
}
