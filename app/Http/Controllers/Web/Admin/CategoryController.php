<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\Web\Category\StoreCategoryRequest;
use App\Http\Requests\Web\Category\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Get all categories with pagination (6 items per page).
     */
    public function index()
    {
        // 1. Fetch only ACTIVE categories, ordered by sort_order
        $categories = Category::with('parent:id,name')
            ->where('is_active', 1) // Only show active categories
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc') // secondary sort if Category Not Sorted  sort_order = 0 -> default
            ->paginate(6);

        // 2. Check if the collection is empty
        if ($categories->isEmpty()) {
            return $this->errorResponse('No active categories found', null, 404);
        }

        return $this->successResponse($categories, 'Active categories retrieved successfully');
    }

    /**
     * Create a new category.
     */
    public function store(StoreCategoryRequest $request)
    {
        // Variable to store image path in case we need to delete it later
        $imagePath = null;

        try {
            $data = $request->validated();

            // 1. Upload new image if provided
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('categories', 'public');
                $data['image'] = $imagePath;
            }

            // 2. Safely handle the boolean value for is_active
            if ($request->has('is_active')) {
                $data['is_active'] = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            }

            // 3. Create the category in the database
            $category = Category::create($data);

            // Refresh the model to get default database values (like sort_order)
            $category->refresh();

            // Load parent data to return a complete response
            $category->load('parent');

            return $this->successResponse($category, 'Category created successfully', 201);
        } catch (\Exception $e) {
            // 4. If database fails, delete the newly uploaded image
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            return $this->errorResponse('Failed to create category: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Show a specific category.
     */
    public function show(Category $category)
    {
        // 1. Prevent viewing deactivated categories
        if (!$category->is_active) {
            return $this->errorResponse('This category is deactivated and cannot be viewed', null, 403); // 403 Forbidden
        }

        // 2. Load the category along with its parent and children (useful for frontend tree views)
        $category->load(['parent', 'children']);

        return $this->successResponse($category, 'Category retrieved successfully');
    }

    /**
     * Update an existing category.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        // Variable to store new image path
        $newImagePath = null;

        try {
            $data = $request->validated();

            // Safely handle the boolean value for is_active
            if ($request->has('is_active')) {
                $data['is_active'] = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            }

            // 1. Upload new image if provided
            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')->store('categories', 'public');
                $data['image'] = $newImagePath;
            }

            // 2. Save the old image path before updating
            $oldImage = $category->image;

            // 3. Update the database
            $category->update($data);

            // 4. If update is successful and a new image was uploaded, delete the old image
            if ($newImagePath && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            // Load parent data after update
            $category->load('parent');

            return $this->successResponse($category, 'Category updated successfully');
        } catch (\Exception $e) {
            // 5. If database update fails, delete the newly uploaded image
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }
            return $this->errorResponse('Failed to update category: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Deactivate a category (Soft Delete logic).
     */
    public function destroy(Category $category)
    {
        try {
            // 1. Check if it's already deactivated
            if (!$category->is_active) {
                return $this->errorResponse('This category is already deactivated.', null, 400);
            }

            // 2. Deactivate the category instead of hard deleting it
            $category->update(['is_active' => 0]);

            return $this->successResponse(null, 'Category deactivated successfully. Historical data is preserved.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to deactivate category: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Reorder categories based on frontend drag-and-drop.
     */
    public function reorder(Request $request)
    {
        try {
            // 1. Validate that we received an array of IDs and they exist in the DB
            $request->validate([
                'category_ids'   => ['required', 'array'],
                'category_ids.*' => ['integer', 'exists:categories,id'],
            ]);

            /*
            *@ Loop through the array and update the sort_order based on the index
            *@ Use a transaction to ensure atomicity
            */

            DB::transaction(function () use ($request) {

                foreach ($request->category_ids as $index => $id) {

                    Category::where('id', $id)->update(['sort_order' => $index]);
                }
            });

            return $this->successResponse(null, 'Categories reordered successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to reorder categories: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get related categories (categories with the same parent).
     */
    public function related(Category $category)
    {
        // 1. Prevent interacting with deactivated categories
        if (!$category->is_active) {
            return $this->errorResponse('This category is deactivated', null, 403);
        }

        // 2. Fetch active categories that share the same parent_id, excluding the current category
        $relatedCategories = Category::where('parent_id', $category->parent_id)
            ->where('id', '!=', $category->id)
            ->where('is_active', 1) // Only show active categories
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc') // secondary sort if Category Not Sorted  sort_order = 0 -> default
            ->limit(4)
            ->get();

        return $this->successResponse($relatedCategories, 'Related categories retrieved successfully');
    }
}
