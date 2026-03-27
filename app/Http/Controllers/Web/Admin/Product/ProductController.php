<?php

namespace App\Http\Controllers\Web\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Product\StoreProductRequest;
use App\Http\Requests\Web\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Category $category)
    {
        // Get active products for this category with supplier data
        $products = $category->products()
            ->active()
            ->with(['supplier:id,name'])
            ->latest()
            ->paginate(10);

        // Check if no products found (paginate is not a normal collection)
        if ($products->count() === 0) {
            return $this->errorResponse('No active products found for this category', null, 404);
        }

        return $this->successResponse($products, 'Active products retrieved successfully');
    }

    public function store(StoreProductRequest $request)
    {
        // Store image path to delete it if an error happens
        $imagePath = null;

        try {
            // Get validated data
            $data = $request->validated();

            // Set product as active by default
            $data['is_active'] = true;

            // Store image if uploaded
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
                $data['image'] = $imagePath;
            }

            // Create product
            $product = Product::create($data);

            // Return product with relations
            return $this->successResponse(
                $product->load(['category:id,name', 'supplier:id,name']),
                'Product created successfully',
                201
            );

        } catch (\Exception $e) {

            // Delete uploaded image if something fails
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            return $this->errorResponse('Failed to create product', $e->getMessage(), 500);
        }
    }

    public function show(Product $product)
    {
        // Prevent showing inactive product
        if (!$product->is_active) {
            return $this->errorResponse('This product is deactivated and cannot be viewed', null, 403);
        }

        // Load related data
        $product->load([
            'category:id,name',
            'supplier:id,name',
            'carModels:id,brand,model,year'
        ]);

        return $this->successResponse($product, 'Product retrieved successfully');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        // Store new image path to delete it if an error happens
        $newImagePath = null;

        try {
            // Get validated data
            $data = $request->validated();

            // Store new image if uploaded
            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')->store('products', 'public');
                $data['image'] = $newImagePath;
            }

            // Save old image path
            $oldImage = $product->image;

            // Update product
            $product->update($data);

            // Delete old image after successful update
            if ($newImagePath && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            return $this->successResponse(
                $product->fresh()->load(['category:id,name', 'supplier:id,name']),
                'Product updated successfully'
            );

        } catch (\Exception $e) {

            // Delete new image if update fails
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            return $this->errorResponse('Failed to update product', $e->getMessage(), 500);
        }
    }

    public function destroy(Product $product)
    {
        try {
            // Check if product is already inactive
            if (!$product->is_active) {
                return $this->errorResponse('This product is already deactivated.', null, 400);
            }

            // Soft delete by setting is_active to false
            $product->update(['is_active' => false]);

            return $this->successResponse(
                null,
                'Product deactivated successfully. Historical data is preserved.'
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to deactivate product', $e->getMessage(), 500);
        }
    }


}
