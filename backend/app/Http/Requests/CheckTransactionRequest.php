<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckTransactionRequest extends FormRequest
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
            'token' => 'required|string',
            'transaction_id' => 'required|string'
        ];
    }

    public function messages() {
        return [
            'token.required' => 'The token is required', 
            'token.string' => 'The token must be a string.',
            'transaction_id.required' => 'Service Type is required',
            'transaction_id.string' => 'The transaction id must be a string.',
        ];
    }
}
