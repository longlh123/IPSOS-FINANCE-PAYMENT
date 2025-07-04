<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectStatusRequest extends FormRequest
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
            'status' => 'required|string|in:planned,in coming,on going,completed,on hold,cancelled'
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'The status of project is required.',
            'status.in' => 'The status of project is invalid provided.'
        ];
    }
}
