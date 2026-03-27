<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // ==============================
    // قائمة العملاء (Admin)
    // ==============================
    public function index(Request $request)
    {
        try {
            $customers = Customer::query()
                ->when(
                    $request->search,
                    fn($q) =>
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%')
                )
                ->orderBy('name')
                ->paginate(20);

            return $this->successResponse($customers, 'Customers retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve customers', $e->getMessage(), 500);
        }
    }

    // ==============================
    // إنشاء عميل (Admin + Cashier)
    // ==============================
    public function store(Request $request)
    {
        $request->validate([
            'uuid'  => 'nullable|string|unique:customers,uuid',
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers,phone',
        ]);

        try {


            $customer = Customer::create([
                'uuid'  => $request->uuid ?? null,
                'name'  => $request->name,
                'phone' => $request->phone ?? null,
            ]);

            return $this->successResponse($customer, 'Customer created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create customer', $e->getMessage(), 500);
        }
    }

    // ==============================
    // تفاصيل عميل (Admin)
    // ==============================
    public function show(Customer $customer)
    {
        try {
            return $this->successResponse($customer, 'Customer retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve customer', $e->getMessage(), 500);
        }
    }

    // ==============================
    // تعديل عميل (Admin)
    // ==============================
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name'    => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers,phone,' . $customer->id,
            'address' => 'nullable|string|max:500',
            'notes'   => 'nullable|string|max:1000',
        ]);

        try {
            $customer->update($request->only(['name', 'phone', 'address', 'notes']));

            return $this->successResponse($customer, 'Customer updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update customer', $e->getMessage(), 500);
        }
    }

    // ==============================
    // حذف عميل (Admin)
    // ==============================
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();

            return $this->successResponse(null, 'Customer deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete customer', $e->getMessage(), 500);
        }
    }
}
