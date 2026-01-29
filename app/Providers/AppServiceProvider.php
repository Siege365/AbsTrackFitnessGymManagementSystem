<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register formatContact blade directive
        Blade::directive('formatContact', function ($expression) {
            return "<?php echo \App\Providers\AppServiceProvider::formatContactNumber($expression); ?>";
        });
    }

    /**
     * Format contact number to 0912-345-6789 format
     * 
     * @param string $contact
     * @return string
     */
    public static function formatContactNumber($contact)
    {
        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $contact);
        
        // Handle different lengths
        $length = strlen($digits);
        
        if ($length === 11 && substr($digits, 0, 1) === '0') {
            // Format: 0912-345-6789
            return substr($digits, 0, 4) . '-' . substr($digits, 4, 3) . '-' . substr($digits, 7, 4);
        } elseif ($length === 10) {
            // Format: 0912-345-6789 (add leading 0)
            return '0' . substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
        } elseif ($length === 12 && substr($digits, 0, 2) === '63') {
            // Philippine format with country code: +63 912-345-6789
            return '+63 ' . substr($digits, 2, 3) . '-' . substr($digits, 5, 3) . '-' . substr($digits, 8, 4);
        }
        
        // Return as-is if format doesn't match expected patterns
        return $contact;
    }
}
