<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Supplier\CreateSupplierRequest;
use App\Http\Requests\Web\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    /**
     * Get all suppliers with pagination.
     */
    public function index()
    {
        // Get suppliers ordered by the latest, paginated by 10
        $suppliers = Supplier::latest()->paginate(10);

        return $this->successResponse($suppliers, 'Suppliers retrieved successfully');
    }

    /**
     * Create a new supplier.
     */
    public function store(CreateSupplierRequest $request)
    {
        $imagePath = null;

        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('suppliers', 'public');
                $data['image'] = $imagePath;
            }

            if ($request->has('is_active')) {
                $data['is_active'] = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            }

            $supplier = Supplier::create($data);
            $supplier->refresh();

            return $this->successResponse($supplier, 'Supplier created successfully', 201);
        } catch (\Exception $e) {
            // If DB fails, delete the uploaded image
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            return $this->errorResponse('Failed to create supplier: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Show a specific supplier.
     */
    public function show(Supplier $supplier)
    {
        return $this->successResponse($supplier, 'Supplier retrieved successfully');
    }

    /**
     * Update an existing supplier.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $newImagePath = null;

        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')->store('suppliers', 'public');
                $data['image'] = $newImagePath;
            } else {
                unset($data['image']);
            }

            if ($request->has('is_active')) {
                $data['is_active'] = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            }

            $oldImage = $supplier->image;

            $supplier->update($data);

            // Delete old image only after DB succeeds
            if ($newImagePath && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            return $this->successResponse($supplier, 'Supplier updated successfully');
        } catch (\Exception $e) {
            // If DB fails, delete the newly uploaded image
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }
            return $this->errorResponse('Failed to update supplier: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Delete a supplier.
     */
    public function destroy(Supplier $supplier)
    {
        // Delete image from storage first
        if ($supplier->image && Storage::disk('public')->exists($supplier->image)) {
            Storage::disk('public')->delete($supplier->image);
        }

        // Delete the database record
        $supplier->delete();

        return $this->successResponse(null, 'Supplier deleted successfully');
    }
}
