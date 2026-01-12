<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            'phone_number' => 'required|string|digits_between:10,11',
            'service_type' => 'required|string|in:topup,card,voucher',
            'service_code' => 'required|string|max:10',
            'provider' => 'required|string|in:vinnet,gotit',
            'delivery_method' => 'required|string|in:qr,sms'
        ];
    }

    public function messages() {
        return [
            'token.required' => 'The token is required.', 
            'phone_number.required' => 'The phone number is required.',
            'phone_number.digits_between' => 'The phone number must be between 10 and 11 digits.',
            
            'service_type.required' => 'Service Type is required',
            'service_type.in' => 'The service type must be one of the following: topup, card.',

            'service_code.required' => 'The service code is required.',

            'provider.required' => 'Provider is required',
            'provider.in' => 'The provider must be one of the following: vinnet, gotit.',

            'delivery_method.required' => 'Delivery Method is required.',
            'delivery_method.string' => 'Delivery Method must be a string.',
            'delivery_method.in' => 'The Delivery Method must be one of the following: qr, sms.'
        ];
    }
}
