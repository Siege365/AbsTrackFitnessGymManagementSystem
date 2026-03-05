<?php

namespace App\Helpers;

use App\Models\InventorySupply;

class CategoryHelper
{
    /**
     * Comprehensive keyword-to-icon mapping for smart category icon resolution.
     * Each group maps a set of keywords to an MDI icon class.
     * Order matters — first match wins.
     */
    private static array $iconMap = [
        // Supplements & Vitamins
        'mdi-pill' => [
            'supplement', 'supplements', 'vitamin', 'vitamins', 'protein', 'powder',
            'capsule', 'capsules', 'tablet', 'tablets', 'pill', 'pills', 'whey',
            'creatine', 'bcaa', 'amino', 'pre-workout', 'preworkout', 'post-workout',
            'postworkout', 'multivitamin', 'omega', 'fish oil', 'collagen', 'biotin',
            'zinc', 'magnesium', 'calcium', 'iron', 'probiotics', 'electrolyte',
            'electrolytes', 'mass gainer', 'gainer', 'isolate', 'casein', 'glutamine',
            'l-carnitine', 'carnitine', 'fat burner', 'thermogenic', 'nitric oxide',
            'booster', 'enhancer', 'dietary', 'nutritional', 'nutraceutical',
            'herbal', 'mineral', 'minerals', 'nutrient', 'nutrients',
        ],

        // Equipment & Machines
        'mdi-dumbbell' => [
            'equipment', 'equipments', 'machine', 'machines', 'weight', 'weights',
            'dumbbell', 'dumbbells', 'barbell', 'barbells', 'kettlebell', 'kettlebells',
            'bench', 'rack', 'racks', 'cable', 'cables', 'treadmill', 'treadmills',
            'elliptical', 'bike', 'bikes', 'rower', 'rowing', 'smith machine',
            'leg press', 'pull-up bar', 'pullup bar', 'chin-up bar', 'squat rack',
            'power rack', 'weight plate', 'plates', 'olympic', 'trap bar',
            'ez bar', 'curl bar', 'ab roller', 'ab wheel', 'gym equipment',
            'fitness equipment', 'exercise machine', 'cardio', 'cardio machine',
            'strength', 'resistance machine', 'home gym', 'multi gym',
            'lat pulldown', 'pec deck', 'chest press', 'shoulder press',
            'leg curl', 'leg extension', 'hack squat', 'calf raise',
            'cable crossover', 'functional trainer', 'battle rope', 'battle ropes',
            'slam ball', 'medicine ball', 'medicine balls', 'plyo box', 'plyometric',
            'pull up', 'dip station', 'inversion table', 'punching bag',
            'heavy bag', 'speed bag', 'boxing', 'mma',
        ],

        // Apparel & Clothing
        'mdi-tshirt-crew' => [
            'apparel', 'clothing', 'clothes', 'shirt', 'shirts', 't-shirt', 'tshirt',
            'shorts', 'pants', 'leggings', 'jersey', 'jerseys', 'uniform', 'uniforms',
            'wear', 'sportswear', 'activewear', 'athleisure', 'outfit', 'outfits',
            'tank top', 'tank tops', 'singlet', 'singlets', 'hoodie', 'hoodies',
            'jacket', 'jackets', 'sweater', 'sweaters', 'sweatshirt', 'sweatpants',
            'joggers', 'tracksuit', 'tracksuits', 'compression', 'rashguard',
            'rash guard', 'sports bra', 'bra', 'crop top', 'vest', 'vests',
            'cap', 'caps', 'hat', 'hats', 'headband', 'headbands', 'wristband',
            'wristbands', 'gym wear', 'workout clothes', 'training wear',
            'garment', 'garments', 'textile', 'textiles', 'fabric',
        ],

        // Footwear
        'mdi-shoe-sneaker' => [
            'footwear', 'shoe', 'shoes', 'sneaker', 'sneakers', 'boot', 'boots',
            'sandal', 'sandals', 'slipper', 'slippers', 'flip flop', 'flip flops',
            'sock', 'socks', 'ankle sock', 'crew sock', 'running shoe',
            'training shoe', 'cross trainer', 'lifting shoe', 'weightlifting shoe',
            'deadlift shoe', 'squat shoe', 'athletic shoe', 'sport shoe',
            'sports shoes', 'gym shoe', 'gym shoes', 'insole', 'insoles',
        ],

        // Beverages & Drinks
        'mdi-cup' => [
            'beverage', 'beverages', 'drink', 'drinks', 'water', 'juice', 'juices',
            'shake', 'shakes', 'smoothie', 'smoothies', 'soda', 'coffee', 'tea',
            'energy drink', 'energy drinks', 'sports drink', 'sports drinks',
            'isotonic', 'coconut water', 'milk', 'almond milk', 'soy milk',
            'oat milk', 'kombucha', 'sparkling', 'mineral water', 'flavored water',
            'refreshment', 'refreshments', 'hydration', 'liquid', 'liquids',
            'bottle', 'bottles', 'can', 'cans', 'drinkable', 'infusion',
        ],

        // Food & Snacks
        'mdi-food-apple' => [
            'snack', 'snacks', 'food', 'foods', 'bar', 'bars', 'protein bar',
            'energy bar', 'granola', 'granola bar', 'chips', 'nuts', 'nut',
            'trail mix', 'dried fruit', 'fruit', 'fruits', 'meal', 'meals',
            'meal prep', 'meal replacement', 'oatmeal', 'cereal', 'cookie',
            'cookies', 'brownie', 'brownies', 'pancake', 'pancakes', 'waffle',
            'waffles', 'peanut butter', 'almond butter', 'spread', 'jam',
            'honey', 'syrup', 'sauce', 'seasoning', 'spice', 'rice cake',
            'rice cakes', 'popcorn', 'jerky', 'beef jerky', 'chicken',
            'tuna', 'salmon', 'egg', 'eggs', 'yogurt', 'greek yogurt',
            'cottage cheese', 'cheese', 'edible', 'eatables', 'munchies',
            'candy', 'chocolate', 'gummy', 'gummies', 'chew', 'chews',
        ],

        // Accessories & Gear
        'mdi-bag-personal' => [
            'accessory', 'accessories', 'bag', 'bags', 'belt', 'belts',
            'glove', 'gloves', 'strap', 'straps', 'wrap', 'wraps', 'wrist wrap',
            'knee wrap', 'elbow wrap', 'band', 'bands', 'resistance band',
            'resistance bands', 'loop band', 'pull-up band', 'mat', 'mats',
            'yoga mat', 'exercise mat', 'towel', 'towels', 'gym towel',
            'rope', 'ropes', 'jump rope', 'skipping rope', 'grip', 'grips',
            'chalk', 'gym chalk', 'lifting chalk', 'shaker', 'shaker bottle',
            'water bottle', 'gym bag', 'duffel', 'backpack', 'duffle',
            'locker', 'padlock', 'lock', 'clip', 'clips', 'barbell clip',
            'collar', 'barbell collar', 'carabiner', 'hook', 'hooks',
            'foam roller', 'roller', 'massage ball', 'lacrosse ball',
            'trigger point', 'stretching', 'mobility', 'recovery',
            'knee sleeve', 'elbow sleeve', 'sleeve', 'sleeves',
            'arm sleeve', 'shin guard', 'ankle support', 'brace',
            'support', 'supporter', 'protective gear', 'gear',
        ],

        // Electronics & Gadgets
        'mdi-watch-variant' => [
            'electronic', 'electronics', 'gadget', 'gadgets', 'watch', 'watches',
            'smartwatch', 'fitness tracker', 'tracker', 'trackers', 'monitor',
            'heart rate', 'heart rate monitor', 'timer', 'timers', 'stopwatch',
            'speaker', 'speakers', 'bluetooth', 'earphone', 'earphones',
            'earbud', 'earbuds', 'headphone', 'headphones', 'wireless',
            'charger', 'charging', 'power bank', 'battery', 'scale',
            'smart scale', 'body scale', 'digital', 'device', 'devices',
            'sensor', 'sensors', 'pedometer', 'gps', 'tech', 'technology',
            'wearable', 'wearables', 'display', 'screen', 'led', 'bluetooth speaker',
        ],

        // Hygiene & Cleaning
        'mdi-hand-wash' => [
            'hygiene', 'cleaning', 'clean', 'soap', 'sanitizer', 'sanitiser',
            'disinfectant', 'spray', 'sprays', 'wipe', 'wipes', 'tissue',
            'tissues', 'deodorant', 'antiperspirant', 'body wash', 'shampoo',
            'conditioner', 'lotion', 'moisturizer', 'sunscreen', 'sunblock',
            'hand wash', 'hand soap', 'alcohol', 'sanitizing', 'antibacterial',
            'detergent', 'bleach', 'mop', 'broom', 'dustpan', 'vacuum',
            'trash bag', 'garbage bag', 'bin liner', 'air freshener',
            'fragrance', 'perfume', 'cologne', 'body spray', 'dry shampoo',
            'face wash', 'facial', 'skincare', 'skin care', 'grooming',
            'personal care', 'toiletries', 'toiletry', 'bath', 'shower',
        ],

        // Medicine & First Aid
        'mdi-medical-bag' => [
            'medicine', 'medical', 'first aid', 'firstaid', 'health',
            'bandage', 'bandages', 'gauze', 'plaster', 'adhesive',
            'antiseptic', 'ointment', 'cream', 'pain relief', 'painkiller',
            'ibuprofen', 'aspirin', 'ice pack', 'cold pack', 'hot pack',
            'heat pad', 'kinesiology tape', 'kinesio tape', 'athletic tape',
            'sports tape', 'ankle brace', 'knee brace', 'wrist brace',
            'splint', 'sling', 'thermometer', 'blood pressure', 'stethoscope',
            'emergency', 'rescue', 'cpr', 'aed', 'defibrillator',
            'inhaler', 'epipen', 'pharmaceutical', 'pharma', 'drug', 'drugs',
            'prescription', 'otc', 'over the counter',
        ],

        // Tools & Maintenance
        'mdi-wrench' => [
            'tool', 'tools', 'repair', 'maintenance', 'wrench', 'screwdriver',
            'hammer', 'plier', 'pliers', 'drill', 'saw', 'cutter',
            'tape measure', 'level', 'bolt', 'bolts', 'nut', 'screw',
            'screws', 'nail', 'nails', 'washer', 'spare part', 'spare parts',
            'replacement', 'component', 'components', 'hardware', 'fixture',
            'fixtures', 'lubricant', 'oil', 'grease', 'wd-40', 'adhesive tape',
            'duct tape', 'zip tie', 'zip ties', 'cable tie', 'cable ties',
            'toolbox', 'toolkit', 'utility', 'utilities', 'mechanical',
        ],

        // Stationery & Office
        'mdi-pencil-box' => [
            'stationery', 'stationary', 'office', 'paper', 'papers', 'pen',
            'pens', 'pencil', 'pencils', 'marker', 'markers', 'eraser',
            'notebook', 'notepad', 'folder', 'file', 'files', 'binder',
            'clipboard', 'stapler', 'staples', 'tape', 'scotch tape',
            'envelope', 'envelopes', 'label', 'labels', 'sticker', 'stickers',
            'stamp', 'stamps', 'ink', 'toner', 'printer', 'printing',
            'laminate', 'laminating', 'whiteboard', 'board', 'calendar',
            'planner', 'diary', 'journal', 'memo', 'post-it', 'sticky note',
        ],

        // Furniture & Storage
        'mdi-sofa-single' => [
            'furniture', 'chair', 'chairs', 'table', 'tables', 'desk',
            'desks', 'shelf', 'shelves', 'shelving', 'cabinet', 'cabinets',
            'locker', 'lockers', 'storage', 'container', 'containers',
            'bin', 'bins', 'basket', 'baskets', 'organizer', 'organizers',
            'hook', 'hanger', 'hangers', 'mirror', 'mirrors', 'clock',
            'clocks', 'lamp', 'lamps', 'light', 'lighting', 'fan', 'fans',
            'air conditioner', 'heater', 'mat', 'rug', 'carpet', 'signage',
            'sign', 'banner', 'poster', 'frame', 'frames', 'decor',
            'decoration', 'interior', 'fixture',
        ],

        // Safety & Protection
        'mdi-shield-check' => [
            'safety', 'protection', 'protective', 'guard', 'guards',
            'helmet', 'helmets', 'pad', 'pads', 'knee pad', 'elbow pad',
            'shin pad', 'chest protector', 'mouthguard', 'mouth guard',
            'goggles', 'glasses', 'safety glasses', 'ear plug', 'ear plugs',
            'earplugs', 'mask', 'masks', 'face mask', 'face shield',
            'safety vest', 'reflective', 'cone', 'cones', 'barrier',
            'caution', 'warning', 'hazard', 'fire extinguisher',
            'smoke detector', 'alarm', 'security', 'cctv', 'camera',
        ],

        // Merchandise & Promotional
        'mdi-shopping' => [
            'merchandise', 'merch', 'brand', 'branded', 'logo', 'promotional',
            'promo', 'souvenir', 'souvenirs', 'gift', 'gifts', 'giveaway',
            'giveaways', 'keychain', 'keychains', 'mug', 'mugs', 'tumbler',
            'tumblers', 'lanyard', 'lanyards', 'pin', 'pins', 'badge',
            'badges', 'patch', 'patches', 'sticker', 'decal', 'decals',
            'collectible', 'collectibles', 'limited edition', 'exclusive',
            'custom', 'personalized', 'customized', 'embroidered',
        ],

        // Fitness & Training Programs
        'mdi-yoga' => [
            'fitness', 'yoga', 'pilates', 'exercise', 'workout', 'training',
            'program', 'programs', 'class', 'classes', 'session', 'sessions',
            'course', 'courses', 'certification', 'tutorial', 'guide',
            'book', 'books', 'dvd', 'video', 'videos', 'instruction',
            'manual', 'manuals', 'chart', 'charts', 'poster', 'educational',
            'stretching', 'flexibility', 'aerobic', 'aerobics', 'zumba',
            'spin', 'spinning', 'crossfit', 'hiit', 'circuit training',
            'bootcamp', 'boot camp', 'personal training', 'coaching',
        ],

        // Packaging & Containers
        'mdi-package-variant' => [
            'packaging', 'package', 'packages', 'box', 'boxes', 'carton',
            'cartons', 'wrap', 'wrapping', 'bubble wrap', 'packing',
            'shipping', 'delivery', 'parcel', 'parcels', 'crate', 'crates',
            'pallet', 'pallets', 'container', 'sealant', 'shrink wrap',
            'tape', 'packing tape', 'label', 'shipping label',
        ],
    ];

    /**
     * Get the MDI icon class for a category name using smart keyword matching.
     *
     * @param string $categoryName
     * @return string MDI icon class (e.g., 'mdi-pill')
     */
    public static function getIcon(string $categoryName): string
    {
        $lower = strtolower(trim($categoryName));

        foreach (self::$iconMap as $icon => $keywords) {
            foreach ($keywords as $keyword) {
                // Check if category name contains the keyword or matches exactly
                if ($lower === $keyword || str_contains($lower, $keyword)) {
                    return $icon;
                }
            }
        }

        // Fallback: generic tag icon
        return 'mdi-tag-outline';
    }

    /**
     * Check for similar existing categories using multiple similarity algorithms.
     * Returns an array of similar category names with their similarity scores.
     *
     * @param string $input The user-entered category name
     * @param float $threshold Minimum similarity percentage (0-100) to be considered similar
     * @return array [['name' => string, 'score' => float], ...]
     */
    public static function checkSimilarCategories(string $input, float $threshold = 65.0): array
    {
        $input = trim($input);
        if (empty($input)) {
            return [];
        }

        $inputLower = strtolower($input);

        // Get all unique categories from the database
        $existingCategories = InventorySupply::distinct()
            ->pluck('category')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $similar = [];

        foreach ($existingCategories as $existing) {
            $existingLower = strtolower($existing);

            // Skip if exact match (case-insensitive)
            if ($inputLower === $existingLower) {
                $similar[] = [
                    'name' => $existing,
                    'score' => 100.0,
                    'type' => 'exact',
                ];
                continue;
            }

            // 1. Check if one contains the other (substring match)
            if (str_contains($inputLower, $existingLower) || str_contains($existingLower, $inputLower)) {
                $similar[] = [
                    'name' => $existing,
                    'score' => 90.0,
                    'type' => 'contains',
                ];
                continue;
            }

            // 2. Levenshtein distance (good for typos)
            $levenshtein = levenshtein($inputLower, $existingLower);
            $maxLen = max(strlen($inputLower), strlen($existingLower));
            $levenshteinScore = $maxLen > 0 ? (1 - $levenshtein / $maxLen) * 100 : 0;

            // 3. similar_text percentage
            similar_text($inputLower, $existingLower, $similarTextScore);

            // 4. Soundex comparison (phonetic similarity)
            $soundexMatch = soundex($inputLower) === soundex($existingLower);
            $soundexBonus = $soundexMatch ? 15 : 0;

            // 5. Metaphone comparison (another phonetic algorithm)
            $metaphoneMatch = metaphone($inputLower) === metaphone($existingLower);
            $metaphoneBonus = $metaphoneMatch ? 10 : 0;

            // 6. Check plural/singular forms
            $pluralScore = 0;
            if (
                $inputLower . 's' === $existingLower ||
                $existingLower . 's' === $inputLower ||
                $inputLower . 'es' === $existingLower ||
                $existingLower . 'es' === $inputLower ||
                rtrim($inputLower, 's') === rtrim($existingLower, 's')
            ) {
                $pluralScore = 95;
            }

            // Calculate weighted composite score
            $compositeScore = max(
                $levenshteinScore,
                $similarTextScore,
                $pluralScore
            ) + $soundexBonus + $metaphoneBonus;

            // Cap at 99 (100 is reserved for exact match)
            $compositeScore = min($compositeScore, 99);

            if ($compositeScore >= $threshold) {
                $similar[] = [
                    'name' => $existing,
                    'score' => round($compositeScore, 1),
                    'type' => $soundexMatch ? 'phonetic' : ($pluralScore > 0 ? 'plural' : 'similar'),
                ];
            }
        }

        // Sort by score descending
        usort($similar, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $similar;
    }

    /**
     * Get all existing categories from the database with their colors and icons.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllCategories(): \Illuminate\Support\Collection
    {
        return InventorySupply::select('category', 'category_color')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return (object) [
                    'name' => $item->category,
                    'slug' => strtolower(str_replace(' ', '-', $item->category)),
                    'color' => $item->category_color,
                    'icon' => self::getIcon($item->category),
                ];
            })
            ->sortBy('name')
            ->values();
    }
}
