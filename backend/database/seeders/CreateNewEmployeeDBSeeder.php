<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Employee;

class CreateNewEmployeeDBSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstname = "Long";
        $lastname = "Phạm";
        $province_id = 1;
        $role_id = 12;
        $team_id = 9;
        
        $faker = Faker::create();

        $employee = Employee::create([
            'employee_id' => 'SG99999',
            'first_name' => $firstname,
            'last_name' => $lastname,
            'gender' => 'Nam',
            'card_id' => $faker->unique()->numerify('CARD########'),
            'date_of_issuance' => $faker->date(),
            'place_of_residence' => $faker->city,
            'place_of_issuance' => $faker->city,
            'citizen_identity_card' => $faker->unique()->numerify('CIC##########'),
            'date_of_birth' => $faker->date(),
            'address' => $faker->address,
            'province_id' => $province_id,
            'phone_number' => $faker->phoneNumber,
            'profile_picture' => $faker->imageUrl(640, 480, 'people'),
            'tax_code' => $faker->unique()->numerify('TAX##########'),
            'tax_deduction_at' => $faker->date(),
            'role_id' => $role_id,
            'team_id' => $team_id
        ]);
        
        //Create full-time employees 
        $faker = Faker::create();

        for ($i = 1; $i <= 20; $i++) {
            $firstname = $faker->firstName;
            $lastname = $faker->lastName;

            $role_id = 9;
            $department_id = null;

            switch(true){
                case ($i >= 1 && $i <= 3):
                    $role_id = 2; //Finance 
                    $department_id = 1;
                    break;
                case ($i >= 4 && $i <= 6):
                    $role_id = 3; //Researcher
                    $department_id = 3;
                    break;
                case ($i >= 7 && $i <= 9):
                    $role_id = 4; //Project Manager
                    $department_id = 8;
                    break;
                case ($i >= 10 && $i <= 12):
                    $role_id = 7; //"Field Manager"
                    $department_id = 6;
                    break;
                case ($i >= 13 && $i <= 15):
                    $role_id = 8; //"Field Executive" 
                    $department_id = 6;
                    break;
                case ($i >= 16 && $i <= 18):
                    $role_id = 9; //"Field Administrator"
                    $department_id = 6;
                    break;
                case ($i >= 19 && $i <= 20):
                    $role_id = 10; //"Quality Control Manager"
                    $department_id = 7;
                    break;
            }

            $user = User::factory()->create([
                'name' => $firstname . $lastname,
                'email' => $firstname.'.'.$lastname.'@ipsos.com',
                'password' => Hash::make('password'),
            ]);
    
            // Create user details
            $user->userDetails()->create([
                'user_id' => $user->id,
                'first_name' => $firstname,
                'last_name' => $lastname,
                'date_of_birth' => $faker->date(),
                'address' => $faker->address,
                'phone_number' => $faker->phoneNumber,
                'profile_picture' => $faker->imageUrl(640, 480, 'people'),
                'role_id' => $role_id,
                'department_id' => $department_id,
            ]);
        }
        
        //Create part-time employees 
        $faker = Faker::create();
        
        for ($i = 1; $i <= 20; $i++) {
            $firstname = $faker->firstName;
            $lastname = $faker->lastName;
            
            $role_id = 12; //"Interviewer"

            switch($i){
                case ($i >= 1 && $i <= 4):
                    $role_id = 13; //"Back-checker"

                    if($i >= 1 && $i <= 2){
                        $str_id = "SG";
                        $province_id = 1;
                        $team_id = 13;
                    } else {
                        $str_id = "HN";
                        $province_id = 2;
                        $team_id = 13;
                    }
                    break;
                case ($i >= 5 && $i <= 6):
                    $role_id = 14; //"Coder"
                    
                    $str_id = "SG";
                    $province_id = 1;
                    $team_id = 15; //"CODING"
                    break;
                default:
                    if($i >= 7 && $i <= 10){
                        $str_id = "SG";
                        $province_id = 1;
                        $team_id = 9;
                    } else if($i >= 11 && $i <= 14) {
                        $str_id = "HN";
                        $province_id = 2;
                        $team_id = 10;
                    } else if($i >= 15 && $i <= 17) {
                        $str_id = "DN";
                        $province_id = 3;
                        $team_id = 11;
                    } else if($i >= 18 && $i <= 20) {
                        $str_id = "CT";
                        $province_id = 4;
                        $team_id = 12;
                    }
                    break;
            }

            $employee_id = $faker->unique()->numerify($str_id . '######');

            $employee = Employee::create([
                'employee_id' => $employee_id,
                'first_name' => $firstname,
                'last_name' => $lastname,
                'card_id' => $faker->unique()->numerify('CARD########'),
                'date_of_issuance' => $faker->date(),
                'place_of_issuance' => $faker->city,
                'citizen_identity_card' => $faker->unique()->numerify('CIC##########'),
                'date_of_birth' => $faker->date(),
                'address' => $faker->address,
                'province_id' => $province_id,
                'phone_number' => $faker->phoneNumber,
                'profile_picture' => $faker->imageUrl(640, 480, 'people'),
                'tax_code' => $faker->unique()->numerify('TAX##########'),
                'tax_deduction_at' => $faker->date(),
                'role_id' => $role_id,
                'team_id' => $team_id
            ]);
        }
    }
}
