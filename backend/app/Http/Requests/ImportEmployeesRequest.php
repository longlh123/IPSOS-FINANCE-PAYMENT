<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportEmployeesRequest extends FormRequest
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
            "employee_ids" => "required|string"
        ];
    }

    public function message()
    {
        return [
            "employee_ids.required" => "The employee IDs is required.",
            "employee_ids.string" => "The employee IDs must be a string"
        ];
    }
}
