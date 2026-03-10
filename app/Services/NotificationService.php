<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    /**
     * Create a notification record.
     */
    public static function create(string $type, string $title, string $message, array $options = []): Notification
    {
        return Notification::create([
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'icon'    => $options['icon'] ?? self::getDefaultIcon($type),
            'color'   => $options['color'] ?? self::getDefaultColor($type),
            'link'    => $options['link'] ?? null,
        ]);
    }

    /**
     * New membership or PT client registered.
     */
    public static function newMembership(string $name, string $planType, string $customerType = 'member'): Notification
    {
        $label = $customerType === 'client' ? 'PT Client' : 'Member';
        return self::create(
            'new_membership',
            "New {$label} Registration",
            "{$name} registered as a new {$label} ({$planType})",
            ['link' => $customerType === 'client' ? route('clients.index') : route('memberships.index')]
        );
    }

    /**
     * Payment received (product or membership).
     */
    public static function paymentReceived(string $customerName, float $amount, string $paymentType = 'product'): Notification
    {
        $formattedAmount = '₱' . number_format($amount, 2);
        $label = match ($paymentType) {
            'membership' => 'Membership payment',
            'pt'         => 'PT payment',
            default      => 'Product payment',
        };
        return self::create(
            'payment_received',
            'Payment Received',
            "{$label} of {$formattedAmount} from {$customerName}",
            ['link' => route('payments.history')]
        );
    }

    /**
     * Low stock alert for an inventory item.
     */
    public static function lowStock(string $productName, int $currentQty, int $threshold): Notification
    {
        return self::create(
            'low_stock',
            'Low Inventory Alert',
            "{$productName} stock is low ({$currentQty} remaining, threshold: {$threshold})",
            ['link' => route('inventory.index')]
        );
    }

    /**
     * Membership/client expiring soon.
     */
    public static function membershipExpiring(int $count, string $period = 'this week'): Notification
    {
        $label = $count === 1 ? 'subscription is' : 'subscriptions are';
        return self::create(
            'membership_expiring',
            'Subscriptions Expiring Soon',
            "{$count} {$label} expiring {$period}",
            ['link' => route('memberships.index')]
        );
    }

    /**
     * Refund processed notification.
     */
    public static function refundProcessed(string $receiptNumber, float $amount, string $type = 'product'): Notification
    {
        $formattedAmount = '₱' . number_format($amount, 2);
        $label = $type === 'membership' ? 'Membership' : 'Product';
        return self::create(
            'refund_processed',
            'Refund Processed',
            "{$label} refund of {$formattedAmount} processed (Receipt #{$receiptNumber})",
            ['link' => route('payments.history')]
        );
    }

    /**
     * New PT session scheduled.
     */
    public static function newPTSession(string $customerName, string $date, string $time): Notification
    {
        return self::create(
            'new_pt_session',
            'PT Session Scheduled',
            "New session for {$customerName} on {$date} at {$time}",
            ['link' => route('sessions.pt.index')]
        );
    }

    /**
     * Out of stock alert.
     */
    public static function outOfStock(string $productName): Notification
    {
        return self::create(
            'low_stock',
            'Out of Stock',
            "{$productName} is now out of stock",
            ['link' => route('inventory.index'), 'color' => 'danger']
        );
    }

    /**
     * Default icon mapping by notification type.
     */
    private static function getDefaultIcon(string $type): string
    {
        return match ($type) {
            'new_membership'      => 'mdi-account-plus',
            'payment_received'    => 'mdi-credit-card',
            'low_stock'           => 'mdi-package-variant',
            'membership_expiring' => 'mdi-clock-alert',
            'refund_processed'    => 'mdi-cash-refund',
            'new_pt_session'      => 'mdi-calendar-plus',
            default               => 'mdi-bell',
        };
    }

    /**
     * Default color mapping by notification type.
     */
    private static function getDefaultColor(string $type): string
    {
        return match ($type) {
            'new_membership'      => 'success',
            'payment_received'    => 'info',
            'low_stock'           => 'danger',
            'membership_expiring' => 'warning',
            'refund_processed'    => 'warning',
            'new_pt_session'      => 'info',
            default               => 'info',
        };
    }
}
