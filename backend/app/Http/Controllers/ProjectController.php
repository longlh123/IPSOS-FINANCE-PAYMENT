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
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\Team;
use App\Models\Province;
use App\Models\ProjectVinnetToken;
use App\Models\ProjectUUID;
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
            
            //Log::info(Auth::user()->userDetails->role->name);

            if(in_array(Auth::user()->userDetails->role->name, ['Admin', 'Finance'])){
                $query = Project::with(['projectDetails.createdBy']);
            } else {
                $query = Project::with(['projectDetails.createdBy'])
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
            
            $projects = $query->get();
            
            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'List of projects requested successfully',
                'data' => ProjectResource::collection($projects) 
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
                throw new \Exception($validator->errors()->first());
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
                    'created_user_id' => $user->id,
                    'platform' => strtolower($validatedRequest['platform']),
                    'planned_field_start' => $validatedRequest['planned_field_start'],
                    'planned_field_end' => $validatedRequest['planned_field_end']
                ]);

                $project->projectPermissions()->create([
                    'user_id' => $user->id,
                ]);

                $projectTypes = $validatedRequest['project_types'];
                $projectTypeIds = ProjectType::whereIn('name', $projectTypes)->pluck('id');

                if($projectTypeIds->isEmpty())
                {
                    throw new \Exception('Invalid project types provided');
                }

                // Attach the project types to the project (assuming a many-to-many relationship)
                $project->projectTypes()->attach($projectTypeIds);

                $teams = $validatedRequest['teams'];
                $teamIds = Team::whereIn('name', $teams)->pluck('id');

                if($teamIds->isEmpty())
                {
                    throw new Exception('Invalid teams provided');
                }

                //Attach the teams to the project (assuming a many-to-many relationship)
                $project->teams()->attach($teamIds);
                
                DB::commit();
                
                Log::info('The project stored successfully.');
                return response()->json([
                    'status_code' => Response::HTTP_OK,
                    'message' => 'The project stored successfully.',
                    'data' => new ProjectResource($project)
                ]);
            } 
            catch (\Exception $e)
            {
                DB::rollBack();
            
                // Log the error for debugging
                Log::error('Project creation failed: ' . $e->getMessage());
                throw new \Exception('Project '.$validatedRequest['internal_code'].' already exists.');
            }
        } catch(\Exception $e)
        {
            Log::error('Storage failed: ' . $e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => 'Storage failed: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
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

}
