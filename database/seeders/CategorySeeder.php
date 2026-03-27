<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // ================= Main Categories =================
        $engine = Category::firstOrCreate(
            ['name' => 'Engine Parts'],
            [
                'image' => 'engine_parts.jpg',
                'description' => 'Cylinders, pistons, camshafts, and all engine related components',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $brakes = Category::firstOrCreate(
            ['name' => 'Brake System'],
            [
                'image' => 'brake_system.jpg',
                'description' => 'Brake pads, discs, calipers, and all braking components',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        $suspension = Category::firstOrCreate(
            ['name' => 'Suspension & Steering'],
            [
                'image' => 'suspension_steering.jpg',
                'description' => 'Shock absorbers, springs, steering racks, and related parts',
                'sort_order' => 3,
                'is_active' => true,
            ]
        );

        $electrical = Category::firstOrCreate(
            ['name' => 'Electrical & Lighting'],
            [
                'image' => 'electrical_lighting.jpg',
                'description' => 'Batteries, alternators, headlights, sensors, and electronic components',
                'sort_order' => 4,
                'is_active' => true,
            ]
        );

        $filters = Category::firstOrCreate(
            ['name' => 'Filters & Fluids'],
            [
                'image' => 'filters_fluids.jpg',
                'description' => 'Air filters, oil filters, fuel filters, engine oils, and coolants',
                'sort_order' => 5,
                'is_active' => true,
            ]
        );

        // ================= Subcategories Example =================
        Category::firstOrCreate(
            ['name' => 'Air Filters'],
            [
                'image' => 'air_filters.jpg',
                'description' => 'Engine air filters for various car models',
                'parent_id' => $filters->id,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'Oil Filters'],
            [
                'image' => 'oil_filters.jpg',
                'description' => 'High quality oil filters to keep engine clean',
                'parent_id' => $filters->id,
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'Brake Pads'],
            [
                'image' => 'brake_pads.jpg',
                'description' => 'Durable brake pads for all car models',
                'parent_id' => $brakes->id,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'Shock Absorbers'],
            [
                'image' => 'shock_absorbers.jpg',
                'description' => 'Front and rear shock absorbers for smooth ride',
                'parent_id' => $suspension->id,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );
    }
}
