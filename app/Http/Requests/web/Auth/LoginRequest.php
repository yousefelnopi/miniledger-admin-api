<?php

namespace App\Http\Requests\Web\Auth;

use Illuminate\Foundation\Http\FormRequest;


class LoginRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email field is required',
            'email.email' => 'Email format must be example@gmail.com',
            'password.required' => 'Password field is required',
        ];
    }
}
