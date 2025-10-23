<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Employee;

class CreateNewUserDBSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Long Pham',
            'email' => 'long.pham@ipsos.com',
            'password' => Hash::make('password'),
        ]);

        // Create user details
        $user->userDetails()->create([
            'user_id' => $user->id,
            'first_name' => 'Long',
            'last_name' => 'Pham',
            'date_of_birth' => '1990-01-01',
            'address' => '123 Main St',
            'phone_number' => '1234567890',
            'profile_picture' => 'path/to/profile_picture.jpg',
            'role_id' => 1,
            'department_id' => 9,
        ]);

        $user = User::factory()->create([
            'name' => 'Nhan.Ho',
            'email' => 'nhan.ho@ipsos.com',
            'password' => Hash::make('password'),
        ]);

        // Create user details
        $user->userDetails()->create([
            'user_id' => $user->id,
            'first_name' => 'Nhân',
            'last_name' => 'Hồ',
            'date_of_birth' => '1999-07-27',
            'address' => '123 Main St',
            'phone_number' => '1234567890',
            'profile_picture' => 'path/to/profile_picture.jpg',
            'role_id' => 5,
            'department_id' => 5,
        ]);
    }
}
