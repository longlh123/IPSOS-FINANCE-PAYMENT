<?php

namespace Database\Seeders;

use App\Models;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Employee;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            // AdministrativeDivisions::class,
            // DepartmentDBSeeder::class,
            // CreateNewUserDBSeeder::class,
            //CreateNewProjectDBSeeder::class
            ProjectDBSeeder::class
        ]);
    }
}
