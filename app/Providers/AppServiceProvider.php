<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\ProductObserver;
use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\AICompatibilityServiceInterface;
use App\Services\GeminiCompatibilityService;
use App\Services\GroqCompatibilityService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AICompatibilityServiceInterface::class, function ($app) {
            $driver = env('AI_DRIVER', 'gemini');

            if ($driver === 'groq') {
                return new GroqCompatibilityService();
            }

            // Default to Gemini
            return new GeminiCompatibilityService();
        });
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Product::observe(ProductObserver::class);
    }
}
