<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectType;

class ProjectTypeDBSeeder extends Seeder
{
    /**
     * Seed default project types.
     */
    public function run(): void
    {
        $defaultProjectTypes = [
            ['name' => 'F2F', 'title' => ''],
            ['name' => 'CLT', 'title' => ''],
            ['name' => 'HUT', 'title' => ''],
            ['name' => 'CATI', 'title' => ''],
            ['name' => 'FGD', 'title' => ''],
            ['name' => 'IDI', 'title' => ''],
            ['name' => 'IHV', 'title' => ''],
        ];

        foreach ($defaultProjectTypes as $projectType) {
            ProjectType::updateOrCreate(
                ['name' => $projectType['name']],
                ['title' => $projectType['title']]
            );
        }
    }
}
