<?php

namespace App\Console\Commands;

use App\Models\Membership;
use App\Models\Client;
use App\Models\InventorySupply;
use App\Models\Notification;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateNotifications extends Command
{
    protected $signature = 'notifications:generate';
    protected $description = 'Generate notifications for expiring memberships and low stock items';

    public function handle()
    {
        $this->checkExpiringSubscriptions();
        $this->checkLowStock();
        $this->cleanupOldNotifications();

        $this->info('Notifications generated successfully.');
    }

    private function checkExpiringSubscriptions()
    {
        $today = Carbon::today();
        $weekFromNow = Carbon::today()->addDays(7);

        // Check if we already sent an expiring notification today
        $alreadySent = Notification::where('type', 'membership_expiring')
            ->whereDate('created_at', $today)
            ->exists();

        if ($alreadySent) {
            return;
        }

        $expiringMemberships = Membership::whereBetween('due_date', [$today, $weekFromNow])->count();
        $expiringClients = Client::whereBetween('due_date', [$today, $weekFromNow])->count();
        $total = $expiringMemberships + $expiringClients;

        if ($total > 0) {
            NotificationService::membershipExpiring($total, 'this week');
        }
    }

    private function checkLowStock()
    {
        $today = Carbon::today();

        // Check if we already sent low stock notifications today
        $alreadySent = Notification::where('type', 'low_stock')
            ->whereDate('created_at', $today)
            ->where('title', 'Daily Low Stock Summary')
            ->exists();

        if ($alreadySent) {
            return;
        }

        $lowStockItems = InventorySupply::whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->where('stock_qty', '>', 0)
            ->get();

        $outOfStockItems = InventorySupply::where('stock_qty', 0)->get();

        if ($lowStockItems->count() > 0 || $outOfStockItems->count() > 0) {
            $totalAlerts = $lowStockItems->count() + $outOfStockItems->count();
            $names = $lowStockItems->merge($outOfStockItems)
                ->take(3)
                ->pluck('product_name')
                ->implode(', ');

            $suffix = $totalAlerts > 3 ? " and " . ($totalAlerts - 3) . " more" : "";

            NotificationService::create(
                'low_stock',
                'Daily Low Stock Summary',
                "{$totalAlerts} product(s) need attention: {$names}{$suffix}",
                ['link' => route('inventory.index')]
            );
        }
    }

    private function cleanupOldNotifications()
    {
        // Delete notifications older than 30 days
        Notification::where('created_at', '<', now()->subDays(30))->delete();
    }
}
