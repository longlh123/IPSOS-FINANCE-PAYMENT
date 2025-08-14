<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectDetail;
use App\Models\ProjectType;
use App\Models\ProjectCode;
use Faker\Factory as Faker;

class ProjectDBSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $project_types = [
        //     ['name' => 'F2F', 'title' => ''],
        //     ['name' => 'CLT', 'title' => ''],
        //     ['name' => 'HUT', 'title' => ''],
        //     ['name' => 'CATI', 'title' => ''], 
        //     ['name' => 'FGD', 'title' => ''],
        //     ['name' => 'IDI', 'title' => ''],
        //     ['name' => 'IHV', 'title' => ''],
        // ];

        // foreach($project_types as $project_type){
        //     ProjectType::create($project_type);
        // }

        // $faker = Faker::create();

        // $status = ['planned', 'in coming', 'on going', 'completed', 'on hold', 'cancelled'];
        // $platforms = ['ifield', 'dimensions'];

        // for($i=0; $i <= 10; $i++){
        //     $year = ['2023', '2024'][array_rand(['2023', '2024'])];

        //     $project_name = $faker->unique()->numerify("PROJECT_NAME_###");
        //     $internal_code = $faker->unique()->numerify($year.'-###');

        //     $project = Project::create([
        //         'internal_code' => $internal_code,
        //         'project_name' => $project_name,
        //     ]);

        //     $created_user_id = rand(7, 15);

        //     $project->projectDetails()->create([
        //         'symphony' => $faker->unique()->numerify(substr($year, 2, -1).'######0102'),
        //         'status' => $status[array_rand($status)],
        //         'platform' => $platforms[array_rand($platforms)],
        //         'created_user_id' => $created_user_id,
        //         'planned_field_start' => now(),
        //         'planned_field_end' => now(),
        //     ]);

        //     $project->projectPermissions()->create([
        //         'user_id' => $created_user_id
        //     ]);
        // }

        // $projectPilot = Project::create([
        //     'internal_code' => '2024-999',
        //     'project_name' => 'F2F_TEST_2024',
        // ]);

        // $projectPilot->projectDetails()->create([
        //     'symphony' => '23089766',
        //     'status' => Project::STATUS_ON_GOING,
        //     'platform' => 'ifield',
        //     'created_user_id' => 1,
        //     'planned_field_start' => date('Y-m-d', strtotime('2025-08-01')),
        //     'planned_field_end' => date('Y-m-d', strtotime('2025-12-31')),
        // ]);

        // $projectPilot->projectPermissions()->create([
        //     'user_id' => 1
        // ]);

        // $provinces = [
        //     [
        //         'province_id' => 1,
        //         'sample_size_main' => 100,
        //         'price_main' => 10000
        //     ],
        //     [
        //         'province_id' => 2,
        //         'sample_size_main' => 100,
        //         'price_main' => 10000
        //     ],
        // ];

        // foreach($provinces as $province){
        //     $projectPilot->projectProvinces()->create($province);
        // };

        $projectPilot = Project::create([
            'internal_code' => '2025-079',
            'project_name' => 'GALLEON_082025',
        ]);

        $projectPilot->projectDetails()->create([
            'symphony' => '25022579',
            'status' => Project::STATUS_PLANNED,
            'platform' => 'ifield',
            'created_user_id' => 1,
            'planned_field_start' => date('Y-m-d', strtotime('2025-08-14')),
            'planned_field_end' => date('Y-m-d', strtotime('2025-08-20')),
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
    }
}
