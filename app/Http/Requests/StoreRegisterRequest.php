<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegisterRequest extends FormRequest
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
            "first_name" => ['required', 'regex:/^[A-Za-z ]+$/', 'min:3', 'max:100'],
            "last_name" => ['nullable', 'regex:/^[A-Za-z ]+$/', 'min:3', 'max:100'],
            "email" => 'required|email|unique:users,email',
            "password" => [
                'required',
                'string',
                'confirmed',
                'min:6',
                'max:30',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{6,30}$/'
            ],
            "phone_no" => 'required|digits:10|unique:users,phone_no',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.regex' => 'Password should contain one uppercase, one lowercase, one number, and one special character (@$!%*?&_) with 6-30 characters!',
            'email.unique' => 'This email id is already registered. Kindly register using another email id.',
            'phone_no.unique' => 'This phone number is already registered. Kindly register using another phone number.',
        ];
    }
}
