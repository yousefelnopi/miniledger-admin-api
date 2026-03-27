<?php

namespace App\Http\Requests\Web\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Get the current product from the route
        $product = $this->route('product');

        // Get the new brand from the request, or fallback to the existing one if not provided
        $brand = $this->input('brand', $product->brand ?? null);

        return [
            // 'sometimes' means the field is optional in the request (for partial updates),
            // but if provided, it must follow the validation rules.
            'category_id'   => 'sometimes|required|integer|exists:categories,id',
            'supplier_id'   => 'nullable|integer|exists:suppliers,id',

            'name'          => 'sometimes|required|string|max:255',
            'brand'         => 'nullable|string|max:255',
            'location_code' => 'nullable|string|max:255',
            'description'   => 'nullable|string',

            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',

            // 1. Ignore the current product's ID to prevent a false "duplicate SKU" error
            'sku'           => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($product->id)
            ],

            // 2. Same advanced logic as Store, but ignoring the current product
            'part_number' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'part_number')->where(function ($query) use ($brand) {
                    return $query->where('brand', $brand);
                })->ignore($product->id) // The magic part: allows saving without changing the part number
            ],

            'buy_price'     => 'sometimes|required|numeric|min:0',
            'sell_price'    => 'sometimes|required|numeric|min:0|gte:buy_price',

            // Stock quantities (usually updated via inventory operations, but kept here just in case)
            'stock_qty'     => 'sometimes|required|integer|min:0',
            'alert_stock'   => 'sometimes|required|integer|min:0',

            'is_active'     => 'sometimes|required|boolean',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'sell_price.gte' => 'The selling price cannot be less than the purchase price.',
            'part_number.unique' => 'This exact product (same part number and brand) is already registered in the system.',
        ];
    }
}
