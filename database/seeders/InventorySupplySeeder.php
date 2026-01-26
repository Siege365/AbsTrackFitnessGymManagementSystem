<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventorySupply;

class InventorySupplySeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'product_number' => 'PROD001',
                'product_name' => 'Dumbbell 10kg',
                'category' => 'Equipment',
                'unit_price' => 45.99,
                'stock_qty' => 25,
                'low_stock_threshold' => 5,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD002',
                'product_name' => 'Yoga Mat',
                'category' => 'Accessories',
                'unit_price' => 19.99,
                'stock_qty' => 50,
                'low_stock_threshold' => 10,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD003',
                'product_name' => 'Protein Powder 2kg',
                'category' => 'Supplements',
                'unit_price' => 59.99,
                'stock_qty' => 3,
                'low_stock_threshold' => 5,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD004',
                'product_name' => 'Resistance Band Set',
                'category' => 'Equipment',
                'unit_price' => 29.99,
                'stock_qty' => 15,
                'low_stock_threshold' => 5,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD005',
                'product_name' => 'Water Bottle 1L',
                'category' => 'Accessories',
                'unit_price' => 12.99,
                'stock_qty' => 0,
                'low_stock_threshold' => 10,
                'last_restocked' => now()->subDays(30)
            ],
            [
                'product_number' => 'PROD006',
                'product_name' => 'Gym Towel',
                'category' => 'Accessories',
                'unit_price' => 8.99,
                'stock_qty' => 100,
                'low_stock_threshold' => 20,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD007',
                'product_name' => 'Kettlebell 15kg',
                'category' => 'Equipment',
                'unit_price' => 55.00,
                'stock_qty' => 12,
                'low_stock_threshold' => 5,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD008',
                'product_name' => 'Jump Rope',
                'category' => 'Equipment',
                'unit_price' => 15.99,
                'stock_qty' => 30,
                'low_stock_threshold' => 10,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD009',
                'product_name' => 'Pre-Workout Mix',
                'category' => 'Supplements',
                'unit_price' => 39.99,
                'stock_qty' => 8,
                'low_stock_threshold' => 10,
                'last_restocked' => now()->subDays(15)
            ],
            [
                'product_number' => 'PROD010',
                'product_name' => 'Foam Roller',
                'category' => 'Accessories',
                'unit_price' => 24.99,
                'stock_qty' => 20,
                'low_stock_threshold' => 8,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD011',
                'product_name' => 'Barbell 20kg',
                'category' => 'Equipment',
                'unit_price' => 120.00,
                'stock_qty' => 5,
                'low_stock_threshold' => 3,
                'last_restocked' => now()
            ],
        ];

        foreach ($items as $item) {
            InventorySupply::updateOrCreate(
                ['product_number' => $item['product_number']],
                $item
            );
        }
    }
}