<?php

namespace App\Http\Requests\Web\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            // 🔥 شلنا sometimes لأن في الإنشاء لازم الحقول الإجبارية تتبعت
            'name'      => ['required', 'string', 'max:255'],

            // 🔥 شلنا الـ ID من الـ unique لأن ده مورد جديد ملوش ID لسه
            'email'     => ['nullable', 'email', 'max:255', 'unique:suppliers,email'],

            'phone'     => ['required', 'string', 'max:20', 'regex:/^\+[0-9]+$/', 'unique:suppliers,phone'],

            'address'   => ['nullable', 'string', 'max:255'],
            'notes'     => ['nullable', 'string', 'max:1000'],
            'image'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            // Name Messages
            'name.required' => 'The Supplier Name is required.',
            'name.string'   => 'The Name must be a valid string.',
            'name.max'      => 'The Name must not exceed 255 characters.',

            // Email Messages
            'email.email'   => 'The Email format is invalid (e.g., example@gmail.com).',
            'email.unique'  => 'This Email already exists for another supplier.',
            'email.max'     => 'The Email must not exceed 255 characters.',

            // Phone Messages
            'phone.required' => 'The Phone Number is required.',
            'phone.string'   => 'The Phone Number must be a valid string.',
            'phone.max'      => 'The Phone Number must not exceed 20 characters.',
            'phone.regex'    => 'The Phone Number must start with + followed by numbers (e.g., +20123456789).',
            'phone.unique'   => 'This Phone Number is already registered for another supplier.',

            // Address & Notes Messages
            'address.string' => 'The Address must be a valid string.',
            'address.max'    => 'The Address must not exceed 255 characters.',
            'notes.string'   => 'The Notes must be a valid string.',
            'notes.max'      => 'The Notes must not exceed 1000 characters.',

            // Image Messages
            'image.image' => 'The uploaded file must be a valid image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'image.max'   => 'The image size must not exceed 2MB.',

            // Boolean Messages
            'is_active.boolean' => 'The active status must be true or false.',
        ];
    }
}
