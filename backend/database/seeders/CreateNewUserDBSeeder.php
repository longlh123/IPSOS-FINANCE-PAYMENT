<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Role;

class CreateNewUserDBSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $department = Department::firstOrCreate(
            ['name' => 'ADMIN'],
            ['title' => 'Administrator']
        );

        $role = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['department_id' => $department->id]
        );

        $user = User::firstOrCreate(
            ['email' => 'nhan.ho@ipsos.com'],
            [
                'name' => 'Nhan.Ho',
                'password' => Hash::make('password'),
            ]
        );

        // Create user details
        $user->userDetails()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'first_name' => 'Nhân',
            'last_name' => 'Hồ',
            'date_of_birth' => '1999-07-27',
            'address' => '123 Main St',
            'phone_number' => '1234567890',
            'profile_picture' => 'path/to/profile_picture.jpg',
            'role_id' => $role->id,
            'department_id' => $department->id,
        ]);
    }
}
