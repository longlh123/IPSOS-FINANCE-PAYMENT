<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Region;
use App\Models\Province;
use App\Models\Department;
use App\Models\Role;
use App\Models\Team;
use App\Models\ProjectType;
use App\Models\User;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectPermissions;
use App\Models\Quotation;

class FullDummyDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $provinceIds = $this->seedAdministrativeDivisions();
            [$adminDeptId, $financeDeptId, $dpDeptId, $fwDeptId, $qcDeptId, $researchDeptId] = $this->seedDepartments();
            $roleIds = $this->seedRoles($adminDeptId, $financeDeptId, $dpDeptId, $fwDeptId, $qcDeptId, $researchDeptId);
            $teamIds = $this->seedTeams($researchDeptId, $fwDeptId, $qcDeptId, $dpDeptId);
            $projectTypeIds = $this->seedProjectTypes();

            $users = $this->seedUsers($roleIds, $adminDeptId, $financeDeptId, $dpDeptId);
            $employees = $this->seedEmployees($roleIds, $teamIds, $provinceIds);

            $this->seedProjects(
                $users,
                $employees,
                $projectTypeIds,
                $teamIds,
                $provinceIds
            );
        });
    }

    private function seedAdministrativeDivisions(): array
    {
        $region = Region::firstOrCreate(
            ['name' => 'Đông Nam Bộ'],
            ['eng_name' => 'Southeast']
        );

        $provinces = [
            ['name' => 'Hồ Chí Minh', 'abbreviation' => '01_HCM', 'old_area_code' => 8, 'area_code' => 28, 'codename' => 'ho_chi_minh', 'short_codename' => 'hcm'],
            ['name' => 'Hà Nội', 'abbreviation' => '02_HN', 'old_area_code' => 4, 'area_code' => 24, 'codename' => 'ha_noi', 'short_codename' => 'hn'],
            ['name' => 'Đà Nẵng', 'abbreviation' => '03_DN', 'old_area_code' => 511, 'area_code' => 236, 'codename' => 'da_nang', 'short_codename' => 'dn'],
            ['name' => 'Cần Thơ', 'abbreviation' => '04_CT', 'old_area_code' => 710, 'area_code' => 292, 'codename' => 'can_tho', 'short_codename' => 'ct'],
            ['name' => 'Khánh Hòa', 'abbreviation' => '05_KH', 'old_area_code' => 58, 'area_code' => 258, 'codename' => 'khanh_hoa', 'short_codename' => 'kh'],
        ];

        $ids = [];
        foreach ($provinces as $p) {
            $province = Province::updateOrCreate(
                ['name' => $p['name']],
                [
                    'abbreviation' => $p['abbreviation'],
                    'old_area_code' => $p['old_area_code'],
                    'area_code' => $p['area_code'],
                    'codename' => $p['codename'],
                    'short_codename' => $p['short_codename'],
                    'region_id' => $region->id,
                ]
            );
            $ids[] = $province->id;
        }

        return $ids;
    }

    private function seedDepartments(): array
    {
        $departmentMap = [
            'ADMIN' => 'Administrator',
            'FN' => 'Finance',
            'DP' => 'Data Processing',
            'FW' => 'Fieldwork',
            'QC' => 'Quality Control',
            'CS' => 'Customer Service',
        ];

        $ids = [];
        foreach ($departmentMap as $name => $title) {
            $dept = Department::updateOrCreate(['name' => $name], ['title' => $title]);
            $ids[$name] = $dept->id;
        }

        return [
            $ids['ADMIN'],
            $ids['FN'],
            $ids['DP'],
            $ids['FW'],
            $ids['QC'],
            $ids['CS'],
        ];
    }

    private function seedRoles(int $adminDeptId, int $financeDeptId, int $dpDeptId, int $fwDeptId, int $qcDeptId, int $researchDeptId): array
    {
        $roles = [
            'Admin' => $adminDeptId,
            'Finance' => $financeDeptId,
            'Scripter' => $dpDeptId,
            'Field Manager' => $fwDeptId,
            'Field Executive' => $fwDeptId,
            'Field Administrator' => $fwDeptId,
            'Interviewer' => $fwDeptId,
            'Back-checker' => $qcDeptId,
            'Coder' => $dpDeptId,
            'Researcher' => $researchDeptId,
        ];

        $ids = [];
        foreach ($roles as $name => $departmentId) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                ['department_id' => $departmentId]
            );
            $ids[$name] = $role->id;
        }

        return $ids;
    }

    private function seedTeams(int $researchDeptId, int $fwDeptId, int $qcDeptId, int $dpDeptId): array
    {
        $teams = [
            ['name' => 'INNO', 'title' => 'Innovation', 'department_id' => $researchDeptId],
            ['name' => 'MSU', 'title' => 'Market Strategy & Understanding', 'department_id' => $researchDeptId],
            ['name' => 'FW-SG', 'title' => 'Fieldwork Hochiminh', 'department_id' => $fwDeptId],
            ['name' => 'FW-HN', 'title' => 'Fieldwork Hanoi', 'department_id' => $fwDeptId],
            ['name' => 'FW-DN', 'title' => 'Fieldwork Danang', 'department_id' => $fwDeptId],
            ['name' => 'QC-SG', 'title' => 'Quality Control Hochiminh', 'department_id' => $qcDeptId],
            ['name' => 'CODING', 'title' => 'Coding', 'department_id' => $dpDeptId],
        ];

        $ids = [];
        foreach ($teams as $teamData) {
            $team = Team::updateOrCreate(
                ['name' => $teamData['name']],
                [
                    'title' => $teamData['title'],
                    'department_id' => $teamData['department_id'],
                ]
            );
            $ids[$teamData['name']] = $team->id;
        }

        return $ids;
    }

    private function seedProjectTypes(): array
    {
        $types = ['F2F', 'CLT', 'HUT', 'CATI', 'FGD', 'IDI', 'IHV'];

        $ids = [];
        foreach ($types as $type) {
            $row = ProjectType::updateOrCreate(['name' => $type], ['title' => '']);
            $ids[] = $row->id;
        }

        return $ids;
    }

    private function seedUsers(array $roleIds, int $adminDeptId, int $financeDeptId, int $dpDeptId): array
    {
        $userSeeds = [
            [
                'name' => 'System Admin',
                'email' => 'admin.demo@ipsos.com',
                'password' => Hash::make('password'),
                'first_name' => 'System',
                'last_name' => 'Admin',
                'role_id' => $roleIds['Admin'],
                'department_id' => $adminDeptId,
            ],
            [
                'name' => 'Finance Demo',
                'email' => 'finance.demo@ipsos.com',
                'password' => Hash::make('password'),
                'first_name' => 'Finance',
                'last_name' => 'Demo',
                'role_id' => $roleIds['Finance'],
                'department_id' => $financeDeptId,
            ],
            [
                'name' => 'Scripter Demo',
                'email' => 'scripter.demo@ipsos.com',
                'password' => Hash::make('password'),
                'first_name' => 'Scripter',
                'last_name' => 'Demo',
                'role_id' => $roleIds['Scripter'],
                'department_id' => $dpDeptId,
            ],
        ];

        $users = [];

        foreach ($userSeeds as $seed) {
            $user = User::updateOrCreate(
                ['email' => $seed['email']],
                [
                    'name' => $seed['name'],
                    'password' => $seed['password'],
                ]
            );

            $user->userDetails()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $seed['first_name'],
                    'last_name' => $seed['last_name'],
                    'date_of_birth' => '1995-01-01',
                    'address' => 'Demo address',
                    'phone_number' => '0900000000',
                    'profile_picture' => 'https://picsum.photos/200',
                    'role_id' => $seed['role_id'],
                    'department_id' => $seed['department_id'],
                ]
            );

            $users[] = $user;
        }

        return $users;
    }

    private function seedEmployees(array $roleIds, array $teamIds, array $provinceIds): array
    {
        $employees = [];

        $prefixes = ['SG', 'HN', 'DN', 'CT'];
        $teamCycle = ['FW-SG', 'FW-HN', 'FW-DN', 'QC-SG', 'CODING'];
        $roleCycle = ['Interviewer', 'Interviewer', 'Field Executive', 'Back-checker', 'Coder'];

        for ($i = 1; $i <= 60; $i++) {
            $prefix = $prefixes[$i % count($prefixes)];
            $employeeCode = sprintf('%s%06d', $prefix, $i);

            $teamName = $teamCycle[$i % count($teamCycle)];
            $roleName = $roleCycle[$i % count($roleCycle)];

            $employee = Employee::updateOrCreate(
                ['employee_id' => $employeeCode],
                [
                    'first_name' => 'Emp' . $i,
                    'last_name' => 'Demo',
                    'gender' => $i % 2 === 0 ? 'Nam' : 'Nữ',
                    'date_of_birth' => Carbon::now()->subYears(20 + ($i % 15))->toDateString(),
                    'address' => 'Demo employee address #' . $i,
                    'province_id' => $provinceIds[$i % count($provinceIds)],
                    'phone_number' => '09' . str_pad((string) (10000000 + $i), 8, '0', STR_PAD_LEFT),
                    'profile_picture' => 'https://picsum.photos/seed/emp' . $i . '/200',
                    'tax_code' => 'TAX' . str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                    'tax_deduction_at' => Carbon::now()->toDateString(),
                    'card_id' => 'CARD' . str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                    'citizen_identity_card' => 'CID' . str_pad((string) (1000000000 + $i), 10, '0', STR_PAD_LEFT),
                    'place_of_residence' => 'VN',
                    'date_of_issuance' => Carbon::now()->subYears(2)->toDateString(),
                    'place_of_issuance' => 'Cục CSQLHC',
                    'role_id' => $roleIds[$roleName],
                    'team_id' => $teamIds[$teamName],
                ]
            );

            $employees[] = $employee;
        }

        return $employees;
    }

    private function seedProjects(array $users, array $employees, array $projectTypeIds, array $teamIds, array $provinceIds): void
    {
        $statuses = ['planned', 'in coming', 'on going', 'completed', 'on hold'];

        for ($i = 1; $i <= 12; $i++) {
            $projectName = sprintf('DUMMY_PROJECT_%02d', $i);
            $internalCode = sprintf('%s-%04d', date('Y'), 700 + $i);

            $project = Project::updateOrCreate(
                ['project_name' => $projectName],
                [
                    'internal_code' => $internalCode,
                    'disabled' => false,
                ]
            );

            $project->projectDetails()->updateOrCreate(
                ['project_id' => $project->id],
                [
                    'symphony' => '26' . str_pad((string) (100000 + $i), 6, '0', STR_PAD_LEFT),
                    'job_number' => 'JOB-' . $i,
                    'status' => $statuses[$i % count($statuses)],
                    'created_user_id' => $users[0]->id,
                    'platform' => $i % 3 === 0 ? 'dimensions' : 'ifield',
                    'planned_field_start' => Carbon::now()->subDays(20 - $i)->toDateString(),
                    'planned_field_end' => Carbon::now()->addDays($i + 7)->toDateString(),
                    'actual_field_start' => Carbon::now()->subDays(5)->toDateString(),
                    'actual_field_end' => Carbon::now()->addDays(5)->toDateString(),
                ]
            );

            $project->projectTypes()->syncWithoutDetaching([
                $projectTypeIds[$i % count($projectTypeIds)],
                $projectTypeIds[($i + 1) % count($projectTypeIds)],
            ]);

            $project->teams()->syncWithoutDetaching([
                $teamIds['INNO'],
                $teamIds['MSU'],
            ]);

            foreach ($users as $user) {
                ProjectPermissions::firstOrCreate([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                ]);
            }

            foreach ([$provinceIds[0], $provinceIds[1], $provinceIds[2]] as $idx => $provinceId) {
                DB::table('project_provinces')->updateOrInsert(
                    [
                        'project_id' => $project->id,
                        'province_id' => $provinceId,
                    ],
                    [
                        'sample_size_main' => 80 + ($i * 3) + ($idx * 10),
                        'price_main' => 50000,
                        'sample_size_boosters' => 20 + ($idx * 5),
                        'price_boosters' => 70000,
                        'sample_size_non' => 10,
                        'price_non' => 30000,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $employeeIds = collect($employees)->shuffle()->take(12)->pluck('id')->all();
            $project->employees()->syncWithoutDetaching($employeeIds);

            if ($i <= 6) {
                $this->seedRespondentsAndTransactions($project->id, $employeeIds, $provinceIds);
                $this->seedQuotations($project->id, $users[0]->id, $users[1]->id);
            }
        }
    }

    private function seedRespondentsAndTransactions(int $projectId, array $employeeIds, array $provinceIds): void
    {
        for ($j = 1; $j <= 24; $j++) {
            $shell = sprintf('SC%03d', $j);
            $respondentId = $shell . '-' . sprintf('R%04d', $j);
            $channel = $j % 2 === 0 ? 'gotit' : 'vinnet';

            $respondentPk = DB::table('project_respondents')->updateOrInsert(
                [
                    'project_id' => $projectId,
                    'respondent_id' => $respondentId,
                ],
                [
                    'shell_chainid' => $shell,
                    'employee_id' => $employeeIds[$j % count($employeeIds)],
                    'province_id' => $provinceIds[$j % count($provinceIds)],
                    'interview_start' => Carbon::now()->subDays(rand(1, 20))->subMinutes(30),
                    'interview_end' => Carbon::now()->subDays(rand(1, 20)),
                    'respondent_phone_number' => '09' . str_pad((string) (20000000 + $j + ($projectId * 100)), 8, '0', STR_PAD_LEFT),
                    'phone_number' => '09' . str_pad((string) (30000000 + $j + ($projectId * 100)), 8, '0', STR_PAD_LEFT),
                    'service_type' => 'voucher',
                    'service_code' => 'S0002',
                    'reject_message' => null,
                    'channel' => $channel,
                    'price_level' => 'main',
                    'status' => $j % 5 === 0 ? 'success' : 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $respondent = DB::table('project_respondents')
                ->where('project_id', $projectId)
                ->where('respondent_id', $respondentId)
                ->first();

            if (!$respondent) {
                continue;
            }

            if ($channel === 'gotit') {
                DB::table('project_gotit_voucher_transactions')->updateOrInsert(
                    [
                        'project_respondent_id' => $respondent->id,
                        'transaction_ref_id' => 'GOTIT-' . $projectId . '-' . $j,
                        'transaction_ref_id_order' => 1,
                    ],
                    [
                        'amount' => 50000,
                        'voucher_value' => 50000,
                        'voucher_status' => $j % 5 === 0 ? 'SUCCESS' : 'PENDING',
                        'voucher_code' => strtoupper(Str::random(10)),
                        'voucher_serial' => strtoupper(Str::random(8)),
                        'invoice_date' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            } else {
                DB::table('project_vinnet_transactions')->updateOrInsert(
                    [
                        'project_respondent_id' => $respondent->id,
                        'vinnet_token_order' => 1,
                    ],
                    [
                        'vinnet_token_requuid' => (string) Str::uuid(),
                        'vinnet_serviceitems_requuid' => (string) Str::uuid(),
                        'vinnet_payservice_requuid' => (string) Str::uuid(),
                        'vinnet_token' => 'VTT-' . strtoupper(Str::random(12)),
                        'vinnet_token_status' => $j % 5 === 0 ? 'SUCCESS' : 'PENDING',
                        'vinnet_token_message' => 'Dummy seeded transaction',
                        'total_amt' => 50000,
                        'commission' => 0,
                        'discount' => 0,
                        'payment_amt' => 50000,
                        'recipient_type' => 'NT',
                        'vinnet_invoice_date' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function seedQuotations(int $projectId, int $creatorId, int $approverId): void
    {
        Quotation::updateOrCreate(
            [
                'project_id' => $projectId,
                'version' => 1,
            ],
            [
                'data' => [
                    'project_information' => [
                        'objective' => 'Dummy objective for project ' . $projectId,
                        'sample_size' => 300,
                    ],
                    'costing' => [
                        'fieldwork_cost' => 15000000,
                        'incentive_cost' => 8000000,
                    ],
                ],
                'status' => 'approved',
                'created_user_id' => $creatorId,
                'updated_user_id' => $creatorId,
                'approved_user_id' => $approverId,
                'approved_at' => now(),
            ]
        );

        Quotation::updateOrCreate(
            [
                'project_id' => $projectId,
                'version' => 2,
            ],
            [
                'data' => [
                    'project_information' => [
                        'objective' => 'Draft revision for project ' . $projectId,
                        'sample_size' => 320,
                    ],
                    'costing' => [
                        'fieldwork_cost' => 16500000,
                        'incentive_cost' => 9000000,
                    ],
                ],
                'status' => 'draft',
                'created_user_id' => $creatorId,
                'updated_user_id' => $creatorId,
                'approved_user_id' => null,
                'approved_at' => null,
            ]
        );
    }
}
