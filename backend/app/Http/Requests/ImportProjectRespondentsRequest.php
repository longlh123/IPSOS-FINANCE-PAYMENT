<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportProjectRespondentsRequest extends FormRequest
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
            'project_respondents' => 'required|array',
            'project_respondents.*.instance_id' => 'required|string',
            'project_respondents.*.shell_chainid' => 'required|string',
            'project_respondents.*.location_id' => 'required|string',
            'project_respondents.*.province_name' => 'required|string',
            'project_respondents.*.employee_id' => 'required|string',
            'project_respondents.*.respondent_phone_number' => 'required|string',
            'project_respondents.*.phone_number' => 'required|string',
        ];
    }
}