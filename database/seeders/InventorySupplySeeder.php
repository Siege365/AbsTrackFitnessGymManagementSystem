<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventorySupply;

class InventorySupplySeeder extends Seeder
{
    /**
     * Get default color for a category.
     */
    private function getCategoryColor($category)
    {
        $colorMap = [
            'Supplements' => '#42A5F5',
            'Equipment' => '#4CAF50',
            'Apparel' => '#AB47BC',
            'Beverages' => '#FFA726',
            'Snacks' => '#EC407A',
            'Accessories' => '#26C6DA',
        ];

        return $colorMap[$category] ?? '#9E9E9E';
    }

    public function run()
    {
        $items = [
            // ========== EQUIPMENT ==========
            [
                'product_number' => 'PROD-0001',
                'product_name' => 'Dumbbell Set 5kg',
                'category' => 'Equipment',
                'unit_price' => 899.00,
                'stock_qty' => 25,
                'low_stock_threshold' => 10,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0002',
                'product_name' => 'Dumbbell Set 10kg',
                'category' => 'Equipment',
                'unit_price' => 1499.00,
                'stock_qty' => 18,
                'low_stock_threshold' => 10,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0003',
                'product_name' => 'Kettlebell 12kg',
                'category' => 'Equipment',
                'unit_price' => 1299.00,
                'stock_qty' => 15,
                'low_stock_threshold' => 8,
                'last_restocked' => now()->subDays(5)
            ],
            [
                'product_number' => 'PROD-0004',
                'product_name' => 'Kettlebell 16kg',
                'category' => 'Equipment',
                'unit_price' => 1599.00,
                'stock_qty' => 10,
                'low_stock_threshold' => 8,
                'last_restocked' => now()->subDays(5)
            ],
            [
                'product_number' => 'PROD-0005',
                'product_name' => 'Barbell Olympic 20kg',
                'category' => 'Equipment',
                'unit_price' => 3500.00,
                'stock_qty' => 8,
                'low_stock_threshold' => 5,
                'last_restocked' => now()->subDays(10)
            ],
            [
                'product_number' => 'PROD-0006',
                'product_name' => 'Weight Plates 5kg (Pair)',
                'category' => 'Equipment',
                'unit_price' => 899.00,
                'stock_qty' => 30,
                'low_stock_threshold' => 15,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0007',
                'product_name' => 'Weight Plates 10kg (Pair)',
                'category' => 'Equipment',
                'unit_price' => 1699.00,
                'stock_qty' => 20,
                'low_stock_threshold' => 10,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0008',
                'product_name' => 'Resistance Band Set',
                'category' => 'Equipment',
                'unit_price' => 599.00,
                'stock_qty' => 40,
                'low_stock_threshold' => 15,
                'last_restocked' => now()->subDays(3)
            ],
            [
                'product_number' => 'PROD-0009',
                'product_name' => 'Pull-Up Bar',
                'category' => 'Equipment',
                'unit_price' => 1299.00,
                'stock_qty' => 12,
                'low_stock_threshold' => 8,
                'last_restocked' => now()->subDays(7)
            ],
            [
                'product_number' => 'PROD-0010',
                'product_name' => 'Ab Wheel Roller',
                'category' => 'Equipment',
                'unit_price' => 399.00,
                'stock_qty' => 35,
                'low_stock_threshold' => 15,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0011',
                'product_name' => 'Jump Rope Speed',
                'category' => 'Equipment',
                'unit_price' => 299.00,
                'stock_qty' => 50,
                'low_stock_threshold' => 20,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0012',
                'product_name' => 'Yoga Mat Premium',
                'category' => 'Equipment',
                'unit_price' => 799.00,
                'stock_qty' => 45,
                'low_stock_threshold' => 20,
                'last_restocked' => now()->subDays(2)
            ],
            [
                'product_number' => 'PROD-0013',
                'product_name' => 'Foam Roller',
                'category' => 'Equipment',
                'unit_price' => 599.00,
                'stock_qty' => 28,
                'low_stock_threshold' => 12,
                'last_restocked' => now()->subDays(4)
            ],
            [
                'product_number' => 'PROD-0014',
                'product_name' => 'Medicine Ball 5kg',
                'category' => 'Equipment',
                'unit_price' => 899.00,
                'stock_qty' => 15,
                'low_stock_threshold' => 10,
                'last_restocked' => now()->subDays(6)
            ],
            [
                'product_number' => 'PROD-0015',
                'product_name' => 'Bench Press Flat',
                'category' => 'Equipment',
                'unit_price' => 4500.00,
                'stock_qty' => 5,
                'low_stock_threshold' => 3,
                'last_restocked' => now()->subDays(15)
            ],

            // ========== SUPPLEMENTS ==========
            [
                'product_number' => 'PROD-0016',
                'product_name' => 'Whey Protein Isolate 2kg',
                'category' => 'Supplements',
                'unit_price' => 2499.00,
                'stock_qty' => 35,
                'low_stock_threshold' => 15,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0017',
                'product_name' => 'Whey Protein Concentrate 1kg',
                'category' => 'Supplements',
                'unit_price' => 1299.00,
                'stock_qty' => 50,
                'low_stock_threshold' => 20,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0018',
                'product_name' => 'Pre-Workout Energy Boost',
                'category' => 'Supplements',
                'unit_price' => 1599.00,
                'stock_qty' => 22,
                'low_stock_threshold' => 15,
                'last_restocked' => now()->subDays(5)
            ],
            [
                'product_number' => 'PROD-0019',
                'product_name' => 'BCAA Powder 300g',
                'category' => 'Supplements',
                'unit_price' => 999.00,
                'stock_qty' => 30,
                'low_stock_threshold' => 15,
                'last_restocked' => now()->subDays(3)
            ],
            [
                'product_number' => 'PROD-0020',
                'product_name' => 'Creatine Monohydrate 500g',
                'category' => 'Supplements',
                'unit_price' => 899.00,
                'stock_qty' => 40,
                'low_stock_threshold' => 20,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0021',
                'product_name' => 'L-Glutamine 300g',
                'category' => 'Supplements',
                'unit_price' => 799.00,
                'stock_qty' => 25,
                'low_stock_threshold' => 12,
                'last_restocked' => now()->subDays(7)
            ],
            [
                'product_number' => 'PROD-0022',
                'product_name' => 'Mass Gainer 3kg',
                'category' => 'Supplements',
                'unit_price' => 2799.00,
                'stock_qty' => 18,
                'low_stock_threshold' => 10,
                'last_restocked' => now()->subDays(4)
            ],
            [
                'product_number' => 'PROD-0023',
                'product_name' => 'Multivitamin Tablets (60pcs)',
                'category' => 'Supplements',
                'unit_price' => 599.00,
                'stock_qty' => 55,
                'low_stock_threshold' => 25,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0024',
                'product_name' => 'Fish Oil Omega-3 (100 caps)',
                'category' => 'Supplements',
                'unit_price' => 799.00,
                'stock_qty' => 42,
                'low_stock_threshold' => 20,
                'last_restocked' => now()->subDays(2)
            ],
            [
                'product_number' => 'PROD-0025',
                'product_name' => 'Fat Burner Thermogenic',
                'category' => 'Supplements',
                'unit_price' => 1299.00,
                'stock_qty' => 20,
                'low_stock_threshold' => 10,
                'last_restocked' => now()->subDays(8)
            ],
            [
                'product_number' => 'PROD-0026',
                'product_name' => 'Post-Workout Recovery',
                'category' => 'Supplements',
                'unit_price' => 1199.00,
                'stock_qty' => 28,
                'low_stock_threshold' => 15,
                'last_restocked' => now()->subDays(5)
            ],
            [
                'product_number' => 'PROD-0027',
                'product_name' => 'Protein Bar Chocolate (12pcs)',
                'category' => 'Supplements',
                'unit_price' => 899.00,
                'stock_qty' => 60,
                'low_stock_threshold' => 30,
                'last_restocked' => now()
            ],

            // ========== ACCESSORIES ==========
            [
                'product_number' => 'PROD-0028',
                'product_name' => 'Gym Towel Microfiber',
                'category' => 'Accessories',
                'unit_price' => 249.00,
                'stock_qty' => 100,
                'low_stock_threshold' => 40,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0029',
                'product_name' => 'Water Bottle 1L',
                'category' => 'Accessories',
                'unit_price' => 199.00,
                'stock_qty' => 80,
                'low_stock_threshold' => 35,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0030',
                'product_name' => 'Shaker Bottle 700ml',
                'category' => 'Accessories',
                'unit_price' => 299.00,
                'stock_qty' => 65,
                'low_stock_threshold' => 30,
                'last_restocked' => now()->subDays(1)
            ],
            [
                'product_number' => 'PROD-0031',
                'product_name' => 'Gym Bag Large',
                'category' => 'Accessories',
                'unit_price' => 899.00,
                'stock_qty' => 25,
                'low_stock_threshold' => 12,
                'last_restocked' => now()->subDays(10)
            ],
            [
                'product_number' => 'PROD-0032',
                'product_name' => 'Workout Gloves',
                'category' => 'Accessories',
                'unit_price' => 599.00,
                'stock_qty' => 40,
                'low_stock_threshold' => 18,
                'last_restocked' => now()->subDays(5)
            ],
            [
                'product_number' => 'PROD-0033',
                'product_name' => 'Lifting Straps',
                'category' => 'Accessories',
                'unit_price' => 399.00,
                'stock_qty' => 35,
                'low_stock_threshold' => 15,
                'last_restocked' => now()->subDays(6)
            ],
            [
                'product_number' => 'PROD-0034',
                'product_name' => 'Wrist Wraps',
                'category' => 'Accessories',
                'unit_price' => 349.00,
                'stock_qty' => 45,
                'low_stock_threshold' => 20,
                'last_restocked' => now()->subDays(3)
            ],
            [
                'product_number' => 'PROD-0035',
                'product_name' => 'Knee Sleeves',
                'category' => 'Accessories',
                'unit_price' => 799.00,
                'stock_qty' => 22,
                'low_stock_threshold' => 10,
                'last_restocked' => now()->subDays(12)
            ],
            [
                'product_number' => 'PROD-0036',
                'product_name' => 'Weightlifting Belt',
                'category' => 'Accessories',
                'unit_price' => 1299.00,
                'stock_qty' => 15,
                'low_stock_threshold' => 8,
                'last_restocked' => now()->subDays(15)
            ],
            [
                'product_number' => 'PROD-0037',
                'product_name' => 'Headband Sweatband',
                'category' => 'Accessories',
                'unit_price' => 149.00,
                'stock_qty' => 70,
                'low_stock_threshold' => 30,
                'last_restocked' => now()
            ],

            // ========== BEVERAGES ==========
            [
                'product_number' => 'PROD-0038',
                'product_name' => 'Sports Drink Lemon 500ml',
                'category' => 'Beverages',
                'unit_price' => 45.00,
                'stock_qty' => 120,
                'low_stock_threshold' => 50,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0039',
                'product_name' => 'Sports Drink Orange 500ml',
                'category' => 'Beverages',
                'unit_price' => 45.00,
                'stock_qty' => 110,
                'low_stock_threshold' => 50,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0040',
                'product_name' => 'Energy Drink 250ml',
                'category' => 'Beverages',
                'unit_price' => 55.00,
                'stock_qty' => 95,
                'low_stock_threshold' => 40,
                'last_restocked' => now()->subDays(1)
            ],
            [
                'product_number' => 'PROD-0041',
                'product_name' => 'Bottled Water 500ml',
                'category' => 'Beverages',
                'unit_price' => 20.00,
                'stock_qty' => 200,
                'low_stock_threshold' => 80,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0042',
                'product_name' => 'Coconut Water 350ml',
                'category' => 'Beverages',
                'unit_price' => 65.00,
                'stock_qty' => 75,
                'low_stock_threshold' => 35,
                'last_restocked' => now()->subDays(2)
            ],
            [
                'product_number' => 'PROD-0043',
                'product_name' => 'Iced Coffee 250ml',
                'category' => 'Beverages',
                'unit_price' => 85.00,
                'stock_qty' => 50,
                'low_stock_threshold' => 25,
                'last_restocked' => now()->subDays(3)
            ],

            // ========== SNACKS ==========
            [
                'product_number' => 'PROD-0044',
                'product_name' => 'Energy Bar Peanut Butter',
                'category' => 'Snacks',
                'unit_price' => 75.00,
                'stock_qty' => 90,
                'low_stock_threshold' => 40,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0045',
                'product_name' => 'Energy Bar Chocolate',
                'category' => 'Snacks',
                'unit_price' => 75.00,
                'stock_qty' => 85,
                'low_stock_threshold' => 40,
                'last_restocked' => now()
            ],
            [
                'product_number' => 'PROD-0046',
                'product_name' => 'Protein Cookie Double Chocolate',
                'category' => 'Snacks',
                'unit_price' => 95.00,
                'stock_qty' => 60,
                'low_stock_threshold' => 30,
                'last_restocked' => now()->subDays(2)
            ],
            [
                'product_number' => 'PROD-0047',
                'product_name' => 'Trail Mix 200g',
                'category' => 'Snacks',
                'unit_price' => 120.00,
                'stock_qty' => 45,
                'low_stock_threshold' => 20,
                'last_restocked' => now()->subDays(5)
            ],
            [
                'product_number' => 'PROD-0048',
                'product_name' => 'Banana Chips 150g',
                'category' => 'Snacks',
                'unit_price' => 85.00,
                'stock_qty' => 55,
                'low_stock_threshold' => 25,
                'last_restocked' => now()->subDays(4)
            ],
            [
                'product_number' => 'PROD-0049',
                'product_name' => 'Granola Bar Honey Oat',
                'category' => 'Snacks',
                'unit_price' => 65.00,
                'stock_qty' => 70,
                'low_stock_threshold' => 35,
                'last_restocked' => now()->subDays(1)
            ],

            // ========== APPAREL ==========
            [
                'product_number' => 'PROD-0050',
                'product_name' => 'Gym T-Shirt Black (M)',
                'category' => 'Apparel',
                'unit_price' => 399.00,
                'stock_qty' => 30,
                'low_stock_threshold' => 15,
                'last_restocked' => now()->subDays(7)
            ],
            [
                'product_number' => 'PROD-0051',
                'product_name' => 'Gym T-Shirt Black (L)',
                'category' => 'Apparel',
                'unit_price' => 399.00,
                'stock_qty' => 25,
                'low_stock_threshold' => 15,
                'last_restocked' => now()->subDays(7)
            ],
            [
                'product_number' => 'PROD-0052',
                'product_name' => 'Training Shorts Black (M)',
                'category' => 'Apparel',
                'unit_price' => 499.00,
                'stock_qty' => 28,
                'low_stock_threshold' => 12,
                'last_restocked' => now()->subDays(10)
            ],
            [
                'product_number' => 'PROD-0053',
                'product_name' => 'Training Shorts Black (L)',
                'category' => 'Apparel',
                'unit_price' => 499.00,
                'stock_qty' => 22,
                'low_stock_threshold' => 12,
                'last_restocked' => now()->subDays(10)
            ],
            [
                'product_number' => 'PROD-0054',
                'product_name' => 'Compression Shirt (M)',
                'category' => 'Apparel',
                'unit_price' => 599.00,
                'stock_qty' => 18,
                'low_stock_threshold' => 10,
                'last_restocked' => now()->subDays(15)
            ],
            [
                'product_number' => 'PROD-0055',
                'product_name' => 'Tank Top Gray (L)',
                'category' => 'Apparel',
                'unit_price' => 349.00,
                'stock_qty' => 35,
                'low_stock_threshold' => 15,
                'last_restocked' => now()->subDays(5)
            ],
            [
                'product_number' => 'PROD-0056',
                'product_name' => 'Gym Socks (3 Pairs)',
                'category' => 'Apparel',
                'unit_price' => 299.00,
                'stock_qty' => 50,
                'low_stock_threshold' => 25,
                'last_restocked' => now()->subDays(3)
            ],
        ];

        foreach ($items as $item) {
            // Add category color
            if (!isset($item['category_color']) && isset($item['category'])) {
                $item['category_color'] = $this->getCategoryColor($item['category']);
            }
            
            InventorySupply::create($item);
        }
    }
}