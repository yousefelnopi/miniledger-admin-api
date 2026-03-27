<?php

namespace App\Http\Requests\Web\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ensure the user is an admin
        return Auth::user()->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Name is required and must be unique across all categories
            'name'        => ['required', 'string', 'max:255', 'unique:categories,name'],

            'description' => ['nullable', 'string', 'max:1000'],

            // Parent ID must exist in the categories table if provided
            'parent_id'   => ['nullable', 'integer', 'exists:categories,id'],

            // Sort order must be a non-negative number
            'sort_order'  => ['nullable', 'integer', 'min:0'],

            // Image validation (Optional, max 2MB, specific types)
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],

            // Active status
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            // Name Messages
            'name.required' => 'The category name is required.',
            'name.string'   => 'The category name must be a valid string.',
            'name.max'      => 'The category name must not exceed 255 characters.',
            'name.unique'   => 'The category name has already been taken.',

            // Description Messages
            'description.string' => 'The description must be a valid string.',
            'description.max'    => 'The description must not exceed 1000 characters.',

            // Parent ID Messages
            'parent_id.integer' => 'The parent category ID must be a number.',
            'parent_id.exists'  => 'The selected parent category does not exist.',

            // Sort Order Messages
            'sort_order.integer' => 'The sort order must be a number.',
            'sort_order.min'     => 'The sort order cannot be a negative number.',

            // Image Messages
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'image.max'   => 'The image size must not exceed 2MB.',

            // Boolean Messages
            'is_active.boolean' => 'The active status must be true or false.',
        ];
    }
}
