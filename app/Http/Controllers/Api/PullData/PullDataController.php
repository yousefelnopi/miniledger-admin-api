<?php

namespace App\Http\Controllers\Api\PullData;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CarModel;

class PullDataController extends Controller
{
    public function pull()
    {
        try {
            // Categories (active only)
            $categories = Category::select('id', 'name', 'image')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            // Products (active only) + car_models pivot
            $products = Product::active()
                ->with([
                    'category:id,name',
                    'carModels:id', // الكاشير محتاج id بس للـ pivot
                ])
                ->select(
                    'id',
                    'name',
                    'sku',
                    'part_number',
                    'sell_price',
                    'stock_qty',
                    'alert_stock',
                    'location_code',
                    'category_id',
                    'image',
                    'is_active',
                )
                ->get();

            // Customers
            $customers = Customer::select('id', 'name', 'phone')
                ->orderBy('name')
                ->get();


            // Car Models (كل البيانات عشان الكاشير يحفظها محلياً)
            $carModels = CarModel::select('id', 'brand', 'model', 'year', 'engine', 'trim')
                ->orderBy('brand')
                ->get();

            return $this->successResponse([
                'categories' => $categories,
                'products'   => $products,
                'customers'  => $customers,
                'car_models' => $carModels,
            ], 'Data pulled successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to pull data', $e->getMessage(), 500);
        }
    }
}
