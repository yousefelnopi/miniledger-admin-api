<?php

namespace App\Http\Controllers\Web\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global Smart Search
     * Returns lightweight suggestions for POS
     */
    public function search(Request $request)
    {
        $q = trim($request->input('q'));

        if (!$q) {
            return  $this->errorResponse('Search query required', null, 422);
        }

        /*
        |--------------------------------
        | Search Products
        |--------------------------------
        */

        $products = Product::query()
            ->active()
            ->select('id', 'name', 'sku')
            ->where(function ($query) use ($q) {

                $query->where('sku', $q)

                    ->orWhere('part_number', $q)

                    ->orWhereFullText(
                        ['name', 'brand', 'description', 'part_number'],
                        $q
                    )

                    ->orWhere('name', 'like', "%{$q}%")

                    ->orWhere('brand', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($product) {

                return [
                    'type' => 'product',
                    'id' => $product->id,
                    'title' => $product->name,
                    'sku' => $product->sku,
                    'url' => url("api/products/{$product->id}")
                ];
            });

        /*
        |--------------------------------
        | Search Categories
        |--------------------------------
        */

        $categories = Category::query()
            ->select('id', 'name')
            ->where('name', 'like', "%{$q}%")
            ->limit(5)
            ->get()
            ->map(function ($category) {

                return [
                    'type' => 'category',
                    'id' => $category->id,
                    'title' => $category->name,
                    'url' => url("api/categories/{$category->id}")
                ];
            });

        /*
        |--------------------------------
        | Merge Results
        |--------------------------------
        */

        $results = $products->concat($categories)->values();

        return $this->successResponse($results, 'Search results retrieved');
    }
}
