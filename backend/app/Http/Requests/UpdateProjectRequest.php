<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectId = $this->route('project');

        return [
            'internal_code' => 'required|string',
            'project_name' => 'required|string',
            'symphony' => 'required|string',
            'job_number' => 'required|string',
            'planned_field_start' => 'required|date',
            'planned_field_end' => 'required|date',
            'project_types' => 'required|array',
            'project_types.*' => 'required|exists:project_types,name',
            'teams' => 'required|array',
            'teams.*' => 'required|exists:teams,name',
            'permissions' => 'required|array',
            'permissions.*' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'internal_code.required' => 'The internal code is required.',
            'internal_code.string' => 'The internal code must be a string.',
            'symphony.required' => 'The symphony is required.',
            'symphony.string' => 'The symphony must be a string.',
            'job_number.required' => 'The job number is required.',
            'job_number.string' => 'The job number must be a string.',
            'planned_field_start.required' => 'The planned field start is required.',
            'planned_field_start.date' => 'The planned field start must be a date',
            'planned_field_end.required' => 'The planned field end is required.',
            'planned_field_end.date' => 'The planned field end must be a date',
            'project_name.required' => 'The project name is required.',
            'project_name.string' => 'The project name must be a string.',
            'project_types.required' => 'The project types are required.',
            'project_types.array' => 'The project types must be an array.',
            'project_types.*.required' => 'Each project type is required.',
            'project_types.*.exists' => 'The selected project type is invalid.',
            'teams.required' => 'The teams are required.',
            'teams.array' => 'The teams must be an array.',
            'teams.*.required' => 'Each team is required.',
            'teams.*.exists' => 'The selected team is invalid.',
            'permissions.required' => 'The permissions are required.',
            'permissions.array' => 'The permissions must be an array.',
            'permissions.*.required' => 'Each permission is required.',
        ];
    }
}
