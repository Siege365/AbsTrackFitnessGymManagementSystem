<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->bootstrapWith([
    Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    Illuminate\Foundation\Bootstrap\HandleExceptions::class,
    Illuminate\Foundation\Bootstrap\RegisterFacades::class,
    Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    Illuminate\Foundation\Bootstrap\BootProviders::class,
]);

use App\Models\PTSchedule;
use Illuminate\Support\Facades\DB;

try {
    // Get a test record
    $row = DB::table('pt_schedules')->first();
    if (!$row) { echo "No records\n"; exit; }
    echo "Testing Eloquent delete for ID: {$row->id}\n";

    DB::beginTransaction();

    // Step 1: load with relationships
    echo "Step 1: findOrFail...\n";
    $schedule = PTSchedule::with(['client', 'membership'])->findOrFail($row->id);
    echo "OK. customer_source={$schedule->customer_source}\n";

    // Step 2: access display_name
    echo "Step 2: display_name...\n";
    $displayName = $schedule->display_name;
    echo "OK. displayName=$displayName\n";

    // Step 3: delete
    echo "Step 3: delete...\n";
    $schedule->delete();
    echo "OK. Deleted.\n";

    DB::rollBack();
    echo "Rolled back.\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR (" . get_class($e) . "): " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
