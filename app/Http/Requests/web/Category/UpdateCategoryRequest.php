<?php

namespace App\Http\Requests\Web\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if the authenticated user is an admin
        return Auth::user()->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // 1. Get the category parameter from the route
        $categoryRouteParam = $this->route('category');

        // 2. Extract the ID safely (Works whether it's an Object or a String ID)
        $categoryId = is_object($categoryRouteParam) ? $categoryRouteParam->id : $categoryRouteParam;

        return [
            // Using 'sometimes' to allow partial updates
            'name'        => ['sometimes', 'required', 'string', 'max:255', 'unique:categories,name,' . $categoryId],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],

            // A category can be a sub-category, but CANNOT be its own parent (not_in:id)
            'parent_id'   => ['sometimes', 'nullable', 'integer', 'exists:categories,id', 'not_in:' . $categoryId],

            'sort_order'  => ['sometimes', 'nullable', 'integer', 'min:0'],
            'image'       => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_active'   => ['sometimes', 'nullable', 'boolean'],
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
            'parent_id.not_in'  => 'A category cannot be its own parent.',

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
