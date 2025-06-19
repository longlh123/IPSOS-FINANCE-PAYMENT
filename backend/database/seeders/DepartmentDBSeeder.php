<?php

namespace Database\Seeders;

use App\Models;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Team;
use App\Models\Role;

class DepartmentDBSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'HR', 'title' => 'Human Resources'],
            ['name' => 'FN', 'title' => 'Finance'],
            ['name' => 'CS', 'title' => 'Customer Service'],
            ['name' => 'IT', 'title' => 'Information Technology'], 
            ['name' => 'DP', 'title' => 'Data Processing' ],
            ['name' => 'FW', 'title' => 'Fieldwork'],
            ['name' => 'QC', 'title' => 'Quality Control'],
            ['name' => 'PM', 'title' => 'Project Management'],
            ['name' => 'ADMIN', 'title' => 'Administrator']
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        $teams = [
            ['name' => 'INNO', 'title' => 'Innovation', 'department_id' => 3],
            ['name' => 'MSU', 'title' => 'Market Strategy & Understanding', 'department_id' => 3],
            ['name' => 'CEX', 'title' => 'Customer Experience', 'department_id' => 3],
            ['name' => 'HEC', 'title' => 'Healthcare', 'department_id' => 3],
            ['name' => 'BHT', 'title' => 'Brand Health Tracking', 'department_id' => 3],
            ['name' => 'IUU', 'title' => '', 'department_id' => 3],
            ['name' => 'S3', 'title' => 'Strategy 3', 'department_id' => 3],
            ['name' => 'CHP', 'title' => 'Channel Performance', 'department_id' => 3],
            ['name' => 'FW-SG', 'title' => 'Fieldwork Hochiminh', 'department_id' => 6],
            ['name' => 'FW-HN', 'title' => 'Fieldwork Hanoi', 'department_id' => 6],
            ['name' => 'FW-DN', 'title' => 'Fieldwork Danang', 'department_id' => 6],
            ['name' => 'FW-CT', 'title' => 'Fieldwork Cantho', 'department_id' => 6],
            ['name' => 'QC-SG', 'title' => 'Quality Control Hochiminh', 'department_id' => 7],
            ['name' => 'QC-HN', 'title' => 'Quality Control Hanoi', 'department_id' => 7],
            ['name' => 'CODING', 'title' => 'Coding', 'department_id' => 5],
            ['name' => 'QC-DN', 'title' => 'Quality Control Danang', 'department_id' => 7],
            ['name' => 'QC-CT', 'title' => 'Quality Control Cantho', 'department_id' => 7],
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }

        $roles = [
            ['name' => 'Admin', 'department_id' => 9],
            ['name' => 'Finance', 'department_id' => 1],
            ['name' => 'Researcher', 'department_id' => 3],
            ['name' => 'Project Manager', 'department_id' => 8],
            ['name' => 'Scripter', 'department_id' => 5],
            ['name' => 'Data Processing', 'department_id' => 5],
            ['name' => 'Field Manager', 'department_id' => 6],
            ['name' => 'Field Executive', 'department_id' => 6],
            ['name' => 'Field Administrator', 'department_id' => 6],
            ['name' => 'Quality Control Manager', 'department_id' => 7],
            ['name' => 'Quality Control Executive', 'department_id' => 7],
            ['name' => 'Interviewer', 'department_id' => 6],
            ['name' => 'Back-checker', 'department_id' => 7],
            ['name' => 'Coder', 'department_id' => 5],
        ];

        foreach($roles as $role){
            Role::create($role);
        }
    }
}
