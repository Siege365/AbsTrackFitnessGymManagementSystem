#!/usr/bin/env php
<?php

/**
 * Contact Number Cleanup Script
 * 
 * This script removes minus signs and dashes from existing contact numbers
 * in the memberships table.
 * 
 * Usage: php fix_contact_numbers.php
 */

// Autoload Laravel
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Membership;

echo "\n========================================\n";
echo "Contact Number Cleanup Script\n";
echo "========================================\n\n";

// Find all memberships with problematic contact numbers
echo "Searching for problematic contact numbers...\n\n";

$problematic = Membership::where('contact', 'LIKE', '-%')
    ->orWhere('contact', 'LIKE', '%-%')
    ->get();

if ($problematic->isEmpty()) {
    echo "✅ No problematic contact numbers found!\n";
    echo "All contact numbers are clean.\n\n";
    exit(0);
}

echo "Found " . $problematic->count() . " record(s) with issues:\n\n";

// Display problematic records
echo "ID    | Name                  | Current Contact\n";
echo "------|----------------------|------------------\n";

foreach ($problematic as $member) {
    printf("%-5d | %-20s | %s\n", $member->id, $member->name, $member->contact);
}

echo "\n";
echo "Do you want to fix these contact numbers? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    echo "\n❌ Operation cancelled.\n\n";
    exit(0);
}

echo "\nFixing contact numbers...\n\n";

$fixed = 0;
$errors = 0;

foreach ($problematic as $member) {
    $oldContact = $member->contact;
    
    // Remove minus signs and dashes
    $newContact = str_replace(['-', '−', '–', '—'], '', $member->contact);
    
    // Also remove any leading minus/negative signs
    $newContact = ltrim($newContact, '-−–—');
    
    try {
        $member->contact = $newContact;
        $member->save();
        
        echo "✅ Fixed: {$member->name}\n";
        echo "   Old: {$oldContact}\n";
        echo "   New: {$newContact}\n\n";
        
        $fixed++;
    } catch (\Exception $e) {
        echo "❌ Error fixing {$member->name}: {$e->getMessage()}\n\n";
        $errors++;
    }
}

echo "\n========================================\n";
echo "Summary:\n";
echo "========================================\n";
echo "✅ Fixed: {$fixed} record(s)\n";
echo "❌ Errors: {$errors} record(s)\n";
echo "========================================\n\n";

if ($fixed > 0) {
    echo "🎉 Contact numbers have been successfully cleaned!\n\n";
}
