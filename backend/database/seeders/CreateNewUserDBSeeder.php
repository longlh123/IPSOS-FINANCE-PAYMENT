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
        // $user = User::factory()->create([
        //     'name' => 'Long Pham',
        //     'email' => 'long.pham@ipsos.com',
        //     'password' => Hash::make('password'),
        // ]);

        // // Create user details
        // $user->userDetails()->create([
        //     'user_id' => $user->id,
        //     'first_name' => 'Long',
        //     'last_name' => 'Pham',
        //     'date_of_birth' => '1990-01-01',
        //     'address' => '123 Main St',
        //     'phone_number' => '1234567890',
        //     'profile_picture' => 'path/to/profile_picture.jpg',
        //     'role_id' => 1,
        //     'department_id' => 9,
        // ]);

        $user = User::factory()->create([
            'name' => 'Thua Nguyen',
            'email' => 'thua.nguyen@ipsos.com',
            'password' => Hash::make('thuanguyen!2024'),
        ]);

        // Create user details
        $user->userDetails()->create([
            'user_id' => $user->id,
            'first_name' => 'Thừa',
            'last_name' => 'Nguyễn',
            'date_of_birth' => '1990-01-01',
            'address' => 'Hồ Chí Minh',
            'phone_number' => '0909390088',
            'profile_picture' => 'path/to/profile_picture.jpg',
            'role_id' => 7,
            'department_id' => 6,
        ]);

        $user = User::factory()->create([
            'name' => 'Anh Tran',
            'email' => 'anh.tran@ipsos.com',
            'password' => Hash::make('anhtran!2024'),
        ]);

        // Create user details
        $user->userDetails()->create([
            'user_id' => $user->id,
            'first_name' => 'Anh',
            'last_name' => 'Trần',
            'date_of_birth' => '1990-01-01',
            'address' => 'Hồ Chí Minh',
            'phone_number' => '0945243123',
            'profile_picture' => 'path/to/profile_picture.jpg',
            'role_id' => 7,
            'department_id' => 6,
        ]);
    }
}
