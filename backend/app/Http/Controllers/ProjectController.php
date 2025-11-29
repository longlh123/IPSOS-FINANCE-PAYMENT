<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\EmployeeResource;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\Team;
use App\Models\Province;
use App\Models\ProjectVinnetToken;
use App\Models\ProjectUUID;
use App\Models\Employee;
use App\Models\ProjectEmployee;
use App\Http\Requests\StoreProjectVinnetTokenRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Requests\UpdateProjectStatusRequest;
use App\Http\Requests\UpdateProjectDisabledRequest;

class ProjectController extends Controller
{
    public function show($projectId)
    {
        try
        {
            try
            {
                $project = Project::with(['projectDetails','projectTypes'])->findOrFail($projectId);
            }
            catch(\Exception $e)
            {
                Log::error('The project not found: ' . $e->getMessage());
                return response()->json([
                    'status_code' => Response::HTTP_NOT_FOUND, //404
                    'message' => 'The project not found'
                ], Response::HTTP_NOT_FOUND);
            }
            
            return response()->json([
                'status_code' => Response::HTTP_OK, //400
                'message' => 'Projects displayed successfully upon request',
                'data' => new ProjectResource($project)
            ], Response::HTTP_OK);
        }
        catch(\Exception $e)
        {
            Log::error("Project display fails. " . $e->getMessage());
            return response()->json([
                'status_code' => 500,
                'message' => 'Project display fails. ' . $e->getMessage(),
            ]);
        }
        
    }

    public function index(Request $request): JsonResponse
    {
        try{
            //Retrieve a value from the headers
            $showOnlyEnabled = $request->header('Show-Only-Enabled');

            //Get the values from the request
            $platform = $request->input('platform');
            $status = $request->input('status');
            
            $logged_in_user = Auth::user()->id;
            
            //page = số trang | per_page = số dòng mỗi trang
            $perPage = $request->input('per_page', 5);

            if(in_array(Auth::user()->userDetails->role->name, ['Admin', 'Finance'])){
                $query = Project::with([
                    'projectDetails.createdBy'
                ])
                ->withCount('projectRespondents as count_respondents')
                ->withCount('projectEmployees as count_employees');
            } else {
                $query = Project::with(['projectDetails.createdBy'])
                    ->withCount('projectRespondents as total_respondents')
                    ->whereHas('projectPermissions', function($q) use ($logged_in_user) {
                        $q->where('user_id', $logged_in_user);
                    });
            }
            
            if($showOnlyEnabled)
            {
                $query->when($showOnlyEnabled, function($query, $showOnlyEnabled){
                    return $query->where('disabled', !$showOnlyEnabled);
                });
            }

            $query->when($platform, function($query, $platform){
                return $query->whereHas('projectDetails', function(Builder $query) use ($platform){
                    $query->where('platform', $platform);
                });
            })->when($status, function($query, $status){
                return $query->whereHas('projectDetails', function(Builder $query) use ($status){
                    $query->where('platform', $status);
                });
            });
            
            //$projects = $query->get();
            // Laravel paginator
            $projects = $query->paginate($perPage);

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'List of projects requested successfully',
                'data' => ProjectResource::collection($projects),
                'meta' => [
                    'current_page' => $projects->currentPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                    'last_page' => $projects->lastPage(),
                ] 
            ], Response::HTTP_OK)
            ->header('Content-Type', 'application/json');
        } catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST)
            ->header('Content-Type', 'application/json');
        }
    }

    public function store(Request $request)
    {
        try
        {
            //Decode JSON data
            $decodedRequest = $request->json()->all();

            Log::info('Decoded data: ' . json_encode($decodedRequest));

            // Validate the decoded data
            $validator = Validator::make($decodedRequest, (new StoreProjectRequest())->rules(), (new StoreProjectRequest())->messages());

            if ($validator->fails()) {
                Log::error($validator->errors());
                return response->json([
                    'message' => $validator->errors()->first(),
                ], 422); // Unprocessable Entity
            }
            
            $validatedRequest = $validator->validated();
            
            try
            {
                DB::beginTransaction();

                $user = Auth::user();

                $project = Project::create([
                    'internal_code' => $validatedRequest['internal_code'],
                    'project_name' => $validatedRequest['project_name'],
                ]);

                $project->projectDetails()->create([
                    'symphony' => $validatedRequest['symphony'],
                    'created_user_id' => $user->id,
                    'platform' => strtolower($validatedRequest['platform']),
                    'planned_field_start' => $validatedRequest['planned_field_start'],
                    'planned_field_end' => $validatedRequest['planned_field_end']
                ]);

                $project->projectPermissions()->create([
                    'user_id' => $user->id,
                ]);

                $projectTypes = $validatedRequest['project_types'];
                
                // Attach the project types to the project (assuming a many-to-many relationship)
                $project->projectTypes()->attach($projectTypes);

                $teams = $validatedRequest['teams'];
                
                //Attach the teams to the project (assuming a many-to-many relationship)
                $project->teams()->attach($teams);
                
                DB::commit();
                
                Log::info('The project stored successfully.');

                return response()->json([
                    'message' => 'The project stored successfully.',
                    'data' => new ProjectResource($project)
                ], 201);
            } 
            catch (\Exception $e)
            {
                DB::rollBack();
                
                Log::error('SQL Error: ' . $e->getMessage());

                if($e->getCode() === '23000' && str_contains($e->getMessage(), 'Duplicate entry')){
                    return response()->json([
                        'message' => 'The project already exists with the same internal code or a similar name. Please check again!',
                        'error' => $e->getMessage()
                    ], 409); // 409 Conflict
                }
                
                return response()->json([
                    'message' => 'Database error occurred while creating project.',
                    'error' => $e->getMessage()
                ], 500);
            }
        } catch(\Exception $e)
        {
            DB::rollBack();
            Log::error('Unexpected error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unexpected error occurred while creating project.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateProjectRequest $request, $projectId): JsonResponse
    {
        try
        {
            $user = Auth::user();
            
            //Retrieve the project by its id
            try
            {
                $project = Project::findOrFail($projectId);

            } catch(\Exception $e){
                Log::error('The project not found: ' . $e->getMessage());
                return response()->json([
                    'status_code' => Response::HTTP_NOT_FOUND, //404
                    'message' => 'The project not found'
                ], Response::HTTP_NOT_FOUND);
            }

            if($user->id !== $project->projectDetails->created_user_id)
            {
                return response()->json([
                    'status_code' => 403, 
                    'message' => 'You do not have permission on this project. Please contact the admin for assistance.'
                ], 403);
            }

            $validatedRequest = $request->validated();
            
            $query = Project::where('internal_code', $validatedRequest['internal_code'])
                            ->where('project_name', $validatedRequest['project_name'])
                            ->where('id', '!=', $projectId);

            if($query->exists())
            {
                return response()->json(['status_code' => 401, 'message' => 'The combination of internal code and project name has already been taken.'], 401);
            }

            DB::beginTransaction();

            try
            {
                //Update project basic information
                $project->update($request->only([
                    'internal_code',
                    'project_name'
                ]));

                $project->projectDetails()->updateOrCreate([
                    'project_id' => $project->id,
                ], $request->only([
                    'symphony',
                    'job_number',
                    'planned_field_start',
                    'planned_field_end'
                ]));

                //Update project types (assuming a many-to-many relationship)
                $projectTypes = $validatedRequest['project_types'];
                $projectTypeIDs = ProjectType::whereIn('name', $projectTypes)->pluck('id');

                $project->projectTypes()->sync($projectTypeIDs);

                //Update teams (assuming a many-to-many relationship)
                $teams = $validatedRequest['teams'];
                $teamIds = Team::whereIn('name', $teams)->pluck('id');

                $project->teams()->sync($teamIds);

                //Update permissions
                $permissions = $validatedRequest['permissions'];
                $userIds = User::whereIn('email', $permissions)->pluck('id');

                foreach($userIds as $user_id) 
                {
                    $projectPermission = $project->projectPermissions()->where('user_id', $user_id)->first();

                    if(!$projectPermission)
                    {
                        $project->projectPermissions()->create([
                            'project_id' => $projectId,
                            'user_id' => $user_id
                        ]);
                    }
                }

                //Update provinces (assuming a many-to-many relationship)
                $provinceData = $request->input('provinces', []);

                foreach($provinceData as $provinceId => $provinceAttributes)
                {
                    $projectProvince = $project->projectProvinces()->where('province_id', $provinceId)->first();

                    if($projectProvince)
                    {
                        $projectProvince->update($provinceAttributes);
                    }
                    else
                    {
                        $project->projectProvinces()->create(array_merge(['province_id' => $provinceId], $provinceAttributes));
                    }
                }

                DB::commit();
                
                Log::info('The project editted successfully.');
                return response()->json([
                    'status_code' => Response::HTTP_OK,
                    'message' => 'The project editted successfully.',
                    'data' => new ProjectResource($project) 
                ]);
            }
            catch(\Exception $e)
            {
                DB::rollBack();
                Log::error('An error occurred while updating the project: ' . $e->getMessage());
                throw new \Exception('An error occurred while updating the project');
            }
        }
        catch(\Exception $e)
        {
            Log::error('The project editting failed: ' . $e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => 'The project editting failed: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateStatus(UpdateProjectStatusRequest $request, $projectId)
    {
        try
        {
            //Retrieve the project by its id
            try
            {
                $project = Project::findOrFail($projectId);

            } catch(\Exception $e){
                Log::error('The project not found: ' . $e->getMessage());
                return response()->json([
                    'status_code' => Response::HTTP_NOT_FOUND, //404
                    'message' => 'The project not found'
                ], Response::HTTP_NOT_FOUND);
            }

            //Validate and get the validated data from the request
            $validatedRequest = $request->validated();

            //Update the status in the project details
            $projectDetails = $project->projectDetails;

            if($projectDetails)
            {
                $projectDetails->update([
                    'status' => $validatedRequest['status']
                ]);
            }

            Log::info('The project is updated successfully.');
            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'The project is updated successfully.',
                'data' => new ProjectResource($project)
            ]);
        }
        catch (Exception $e)
        {
            Log::error('Updating failed: ' . $e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Updating failed: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateDisabled(UpdateProjectDisabledRequest $request, $projectId)
    {
        try
        {
            //Retrieve the project by its id
            try
            {
                $project = Project::findOrFail($projectId);

            } catch(\Exception $e){
                Log::error('The project not found: ' . $e->getMessage());
                return response()->json([
                    'status_code' => Response::HTTP_NOT_FOUND, //404
                    'message' => 'The project not found'
                ], Response::HTTP_NOT_FOUND);
            }
            
            //Validate and get the validated data from the request
            $validatedRequest = $request->validated();

            $project->update([
                'disabled' => $validatedRequest['disabled'],
            ]);

            Log::info('The project is disabled successfully.');
            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'The project is disabled successfully.',
                'data' => $project
            ]);
        }
        catch(\Exception $e)
        {
            Log::error('The project disabling failed: ' . $e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST,
                'message' => 'The project disabling failed: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function removeProvinceFromProject(Request $request, $projectId, $provinceId)
    {
        //Retrieve the project by its id
        try
        {
            $project = Project::findOrFail($projectId);

        } 
        catch(\Exception $e)
        {
            Log::error('The project not found: ' . $e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND, //404
                'message' => 'The project not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $user = Auth::user();

        if($user->id !== $project->projectDetails->created_user_id)
        {
            return response()->json(['status_code' => 403, 'message' => 'You do not have permission on this project. Please contact the admin for assistance.'], 403);
        }

        try
        {
            DB::beginTransaction();

            $projectProvince = $project->projectProvinces()->where('province_id', $provinceId)->first();

            if($projectProvince){
                //Delete the specific province
                $projectProvince->delete();
            } else {
                return response()->json(['status_code' => 404, 'message' => 'Province not found in this project.'], 404);
            }

            DB::commit();

            return response()->json([
                'status_code' => 404, 
                'message' => 'Province removed from this project successfully.',
                'data' => new ProjectResource($project)
            ], 404);
        }
        catch(\Exception $e)
        {
            DB::rollBack();

            Log::error('An error occurred while removing province from the project: ' . $e->getMessage());
            return response()->json([
                'status_code' => 500,
                'message' => 'An error occurred while removing province from the project: ' . $e->getMessage()
            ]);
        }
    }
    
    public function showTransactions($project_id)
    {
        try
        {
            $results = DB::table('project_respondents as pr')
            ->select([
                'p.internal_code',
                'p.project_name',
                'pr.shell_chainid',
                'ep.employee_id',
                'ep.first_name',
                'ep.last_name',
                'provinces.name AS province_name',
                'pr.interview_start',
                'pr.interview_end',
                'pr.phone_number',
                DB::raw('COALESCE(pvt.vinnet_token_requuid, pgt.transaction_ref_id) AS transaction_id'),
                DB::raw('COALESCE(pvt.total_amt, pgt.voucher_value) AS amount'),
                'pvt.discount',
                'pvt.payment_amt',
                DB::raw('COALESCE(pvt.created_at, pgt.created_at) AS created_at'),
                DB::raw('COALESCE(pvt.updated_at, pgt.updated_at) AS updated_at'),
                'pr.channel',
                'pr.service_code',
                DB::raw('COALESCE(pvt.vinnet_invoice_date, pgt.invoice_date) AS invoice_date'),
            ])
            ->leftJoin('project_vinnet_transactions as pvt', 'pr.id', '=', 'pvt.project_respondent_id')
            ->leftJoin('project_gotit_voucher_transactions as pgt', 'pr.id', '=', 'pgt.project_respondent_id')
            ->join('projects as p', 'p.id', '=', 'pr.project_id')
            ->join('provinces', 'provinces.id', '=', 'pr.province_id')
            ->join('employees as ep', 'ep.id', '=', 'pr.employee_id')
            ->where('pr.status', 'LIKE', 'Đã nhận quà.')
            ->where('p.id', $project_id)
            ->get();

            return response()->json([
                'status_code' => Response::HTTP_OK, //200
                'message' => 'Successful request.',
                'data' => $results
            ]);
        }
        catch(\Exception $e)
        {
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function showEmployees($project_id)
    {
        try
        {
            $sql = "
                SELECT
                    ep.id,
                    ep.employee_id,
                    ep.first_name,
                    ep.last_name,
                    concat(ep.last_name, ' ', ep.first_name) AS full_name,
                    COALESCE(gotit.total_gotit, 0) AS gotit_count,
                    COALESCE(vinnet.total_vinnet, 0) AS vinnet_count,
                    COALESCE(gotit.total_gotit, 0) + COALESCE(vinnet.total_vinnet, 0) AS transaction_count
                FROM project_employees AS epj
                INNER JOIN employees AS ep
                    ON ep.id = epj.employee_id
                LEFT JOIN (
                    SELECT pr.employee_id, COUNT(*) AS total_gotit
                    FROM project_respondents AS pr
                    INNER JOIN project_gotit_voucher_transactions AS pgt
                        ON pr.id = pgt.project_respondent_id
                    WHERE pr.project_id = :projectIdGotit
                        AND pr.status IN (
                            'Đã nhận quà.'
                        )
                    GROUP BY pr.employee_id
                ) AS gotit
                    ON ep.id = gotit.employee_id
                LEFT JOIN (
                    SELECT pr.employee_id, COUNT(*) AS total_vinnet
                    FROM project_respondents AS pr
                    INNER JOIN project_vinnet_transactions AS pvt
                        ON pr.id = pvt.project_respondent_id
                    WHERE pr.project_id = :projectIdVinnet
                        AND pr.status IN (
                            'Đã nhận quà.'
                        )
                    GROUP BY pr.employee_id
                ) AS vinnet
                    ON ep.id = vinnet.employee_id
                WHERE epj.project_id = :projectIdMain
                ORDER BY ep.employee_id;
            ";

            $employees = DB::select($sql, [
                'projectIdGotit' => $project_id,
                'projectIdVinnet' => $project_id,
                'projectIdMain' => $project_id    
            ]);

            return response()->json([
                'status_code' => Response::HTTP_OK, //200
                'data' => $employees
            ]);
        }
        catch(\Exception $e)
        {
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    public function bulkAddEmployees(Request $request, $projectId){
        try{
            $logged_in_user = Auth::user()->id;

            try{
                if(in_array(Auth::user()->userDetails->role->name, ['Admin', 'Scripter'])){
                
                    $project = Project::findOrFail($projectId);
                }
            } catch(\Exception $e){
                Log::error('The project not found: ' . $e->getMessage());

                return response()->json([
                    'status_code' => Response::HTTP_NOT_FOUND, //404
                    'message' => Project::STATUS_PROJECT_NOT_FOUND
                ]);
            }

            if($project){
                $employee_ids = explode(',', $request->employee_ids);

                $employeeIds = Employee::whereIn('employee_id', $employee_ids)->pluck('id');

                $employees = $project->projectEmployees()
                                        ->whereIn('employee_id', $employeeIds)
                                        ->get();

                Log::info('Request Data A: ' . json_encode( $employees));

                $employees = Employee::whereIn('employee_id', $employee_ids)->get();

                Log::info('Request Data: ' . json_encode( $employees));
                
                if($employees->isEmpty()){
                    return response()->json([
                        'status_code' => 400,
                        'message' => 'No employees matched'
                    ]);
                }

                DB::beginTransaction();
                
                $countSuccess = 0;

                foreach($employees as $employee){
                    ProjectEmployee::create([
                        'project_id' => $projectId,
                        'employee_id' => $employee->id
                    ]);

                    $countSuccess++;
                }

                DB::commit();

                return response()->json([
                    'status_code' => 200,
                    'success_count' => $countSuccess,
                    'message' => 'The employees stored successfully.'
                ]);
            }

        } catch(\Exception $e)
        {
            DB::rollBack();
            Log::error('Unexpected error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unexpected error occurred while adding employees into project.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
