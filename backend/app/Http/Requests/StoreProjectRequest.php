<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
        return [
            'internal_code' => 'required|string',
            'symphony' => 'required|string',
            'project_name' => 'required|string',
            'platform' => 'required|string|in:ifield,dimensions,other,iField,Dimensions,Other',
            'planned_field_start' => 'required|date',
            'planned_field_end' => 'required|date',
            'project_types' => 'required|array|min:1',
            'project_types.*' => 'required|numeric|min:1',
            'teams' => 'required|array|min:1',
            'teams.*' => 'numeric|exists:teams,id',
        ];
    }

    public function messages(){
        return [
            'internal_code.required' => 'The internal code is required.',
            'internal_code.string' => 'The internal code must be a string.',
            
            'symphony.required' => 'The symphony is required.',
            'symphony.string' => 'The symphony must be a string.',

            'project_name.required' => 'The project name is required.',
            'project_name.string' => 'The project name must be a string.',

            'platform.required' => 'The platform is required.',
            'platform.string' => 'The platform must be a string',
            'platform.in' => 'The platform must be one of the following: ifield, dimension.',

            'planned_field_start.required' => 'The planned field start is required.',
            'planned_field_start.date' => 'The planned field start must be a valid date.',

            'planned_field_end.required' => 'The planned end end is required.',
            'planned_field_end.date' => 'The planned field end must be a valid date.',
            
            'project_types.required' => 'At least one project type is required.',
            'project_types.array' => 'The project types must be an array.',
            'project_types.min' => 'You must select at least one project type.',
            'project_types.*.required' => 'Each project type is required.',
            'project_types.*.numeric' => 'Each project type must be a numeric.',
            'project_types.*.min' => 'The selected project type is invalid',
            
            'teams.required' => 'At least one team is required.',
            'teams.array' => 'The teams must be an array.',
            'teams.min' => 'You must select at least one team.',
            'teams.*.numeric' => 'Each team name must be a numeric.',
            'teams.*.exists' => 'The selected team does not exist.'
        ];
    }
}
