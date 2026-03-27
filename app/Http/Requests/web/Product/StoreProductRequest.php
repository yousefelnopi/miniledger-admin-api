<?php

namespace App\Http\Requests\Web\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // A built-in Laravel class used to create advanced and complex validation rules (like unique constraints with extra conditions)

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->isAdmin() ?? false;
    }

    /**
     * Prepare the data before validation.
     * If the admin leaves the barcode (SKU) empty, the system generates a random one.
     */
    protected function prepareForValidation(): void
    {
        if (empty($this->sku)) {
            $this->merge([
                'sku' => 'PRD-' . strtoupper(substr(uniqid(), -6)) . rand(10, 99)
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Relationships
            'category_id'   => 'required|integer|exists:categories,id',
            'supplier_id'   => 'nullable|integer|exists:suppliers,id',

            // Basic Data
            'name'          => 'required|string|max:255',
            'brand'         => 'nullable|string|max:255',
            'location_code' => 'nullable|string|max:255',
            'description'   => 'nullable|string',

            // Image
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',

            // Identifiers
            'sku'           => 'required|string|unique:products,sku|max:100',

            // Part Number Logic
            'part_number' => [
                'required',
                'string',
                'max:100',
                // 1. Prevent duplicate part number only if it has the same brand
                Rule::unique('products', 'part_number')->where(function ($query) {
                    return $query->where('brand', $this->brand);
                })
            ],

            // Prices
            'buy_price'     => 'required|numeric|min:0',
            // 2. Ensure selling price is not lower than purchase price
            'sell_price'    => 'required|numeric|min:0|gte:buy_price',

            // Stock details
            'stock_qty'     => 'required|integer|min:0',
            'alert_stock'   => 'required|integer|min:0',

            // Activation Status
            'is_active'     => 'required|boolean',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'sell_price.gte' => 'The selling price cannot be less than the purchase price.',
            // 3. Custom message for the composite unique constraint (Part Number + Brand)
            'part_number.unique' => 'This exact product (same part number and brand) is already registered in the system.',
        ];
    }
}
