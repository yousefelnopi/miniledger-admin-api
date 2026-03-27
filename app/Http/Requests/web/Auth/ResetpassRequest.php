<?php

namespace  App\Http\Requests\Web\Auth;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth ;
use Illuminate\Validation\Rules\Password;


class ResetpassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            'old_password' => ['required', 'current_password'],
            'new_password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols()
            ]
        ];
    }


    public function messages()
    {
        return [
            'old_password.required' => 'Old Password field is required',
            'new_password.required' => 'New Password field is required',
            'new_password.confirmed' => 'Password confirmation does not match',
            'new_password.min' => 'Password must be at least 8 characters long',
            'new_password.mixed_case' => 'Password must contain at least one uppercase and one lowercase letter',
            'new_password.numbers' => 'Password must contain at least one number',
            'new_password.symbols' => 'Password must contain at least one symbol',
        ];
    }
}
