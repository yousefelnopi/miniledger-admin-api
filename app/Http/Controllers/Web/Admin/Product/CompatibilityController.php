<?php

namespace App\Http\Controllers\Web\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\CarModel;
use App\Models\Category;
use App\Models\Product;
use App\Services\Contracts\AICompatibilityServiceInterface;
use Illuminate\Http\Request;
use Nette\Utils\Json;

class CompatibilityController extends Controller
{
    protected $aiService;

    public function __construct(AICompatibilityServiceInterface $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * 🔹 Private helper: generate AI models for a single product
     * 🔹 ترجع array من الموديلات فقط بدون response كامل
     */
    private function generateModelsData(Product $product): array
    {
        $searchQuery = $product->part_number ?? $product->name;

        if (!$searchQuery) {

            return [];
        }


        return $this->aiService->generate($searchQuery);
    }

    /**
     * 🔹 Generate AI suggestions for a single product (public)
     */
    public function generateSingleProductModels(Product $product)
    {
        $aiModels = $this->generateModelsData($product);

        if (empty($aiModels)) {
            return $this->errorResponse('No AI suggestions generated for product: ' . $product->name, null, 404);
        }
        // return json_decode(dd($aiModels));
        return $this->successResponse([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'ai_models' => $aiModels,
        ], "AI suggestions generated for product: {$product->name}");
    }

    /**
     * 🔹 Generate AI suggestions for all products in a category
     * 🔹 Skip products that already have compatibilities
     */
    public function generateCategoryModels(Category $category)
    {
        $products = $category->products()
            ->where('is_active', true)
            ->get();

        $result = [];

        foreach ($products as $product) {
            // تجاهل المنتجات اللي عندها compatibilities محفوظة بالفعل
            if ($product->carModels()->exists()) {
                continue;
            }

            $aiModels = $this->generateModelsData($product);

            $result[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'ai_models' => $aiModels,
            ];
        }

        return $this->successResponse(
            $result,
            "AI suggestions generated for " . count($result) . " products in this category (new only)"
        );
    }

    // ================= Store, Index, Destroy (باقي الكونترولر كما هو) =================
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'car_models' => 'required|array',
            'car_models.*.brand' => 'required|string',
            'car_models.*.model' => 'required|string',
            'car_models.*.year'  => 'required|digits:4',
            'car_models.*.engine' => 'nullable|string',
        ]);

        try {
            $carModelIds = [];

            foreach ($request->car_models as $car) {
                $carModel = CarModel::firstOrCreate([
                    'brand' => $car['brand'],
                    'model' => $car['model'],
                    'year'  => $car['year'],
                    'engine' => $car['engine'] ?? null,
                ]);

                $carModelIds[] = $carModel->id;
            }
            // syncWithoutDetaching() → Add the models to the pivot table without Deleteing the old ones Other than (sync)
            // sync() → Delete the old models and add the new ones
            $product->carModels()->syncWithoutDetaching($carModelIds);

            return $this->successResponse(
                null,
                'Compatibilities stored successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    public function index(Product $product)
    {
        $product->load('carModels:id,brand,model,year,engine');

        return $this->successResponse(
            $product->carModels,
            'Product compatibilities retrieved'
        );
    }

    public function destroy(Product $product, CarModel $carModel)
    {
        $product->carModels()->detach($carModel->id);

        return $this->successResponse(
            null,
            'Compatibility removed successfully'
        );
    }
}
