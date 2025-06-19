<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectGotItRequest extends FormRequest
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
            'url' => 'required|string',
            'phone_number' => 'required|string|digits_between:10,11',
            'service_code' => 'required|string|max:10'
        ];
    }

    public function messages() {
        return [
            'url.required' => 'The url is required', 
            'phone_number.required' => 'The phone number is required.',
            'phone_number.digits_between' => 'The phone number must be between 10 and 11 digits.',
            'service_code.required' => 'The service code is required.'
        ];
    }
}
