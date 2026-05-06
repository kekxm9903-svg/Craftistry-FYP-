<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Route;

class NotificationService
{
    // ════════════════════════════════════════
    // BUYER NOTIFICATIONS
    // ════════════════════════════════════════

    /**
     * Notify buyer when their regular order status changes.
     * Triggers: OrderController@complete, ArtistOrderController@accept/ship
     */
    public static function orderStatusChanged(int $buyerId, int $orderId, string $newStatus): void
    {
        $labels = [
            'processing' => 'Order Confirmed',
            'preparing'  => 'Order Being Prepared',
            'shipped'    => 'Order Shipped',
            'completed'  => 'Order Completed',
            'cancelled'  => 'Order Cancelled',
        ];

        $messages = [
            'processing' => 'Your order has been confirmed and is being processed.',
            'preparing'  => 'Great news! Your order is now being prepared by the artist.',
            'shipped'    => 'Your order is on the way! Check your delivery details.',
            'completed'  => 'Your order has been marked as completed. Enjoy your artwork!',
            'cancelled'  => 'Your order has been cancelled.',
        ];

        $title   = $labels[$newStatus]   ?? 'Order Update';
        $message = $messages[$newStatus] ?? 'Your order status has been updated to: ' . ucfirst($newStatus);

        Notification::send(
            $buyerId,
            'order_status',
            $title,
            $message,
            route('orders.show', $orderId)
        );
    }

    /**
     * Notify buyer when their custom order request status changes.
     * Triggers: ArtistCustomOrderController@accept/refuse
     */
    public static function customOrderStatusChanged(int $buyerId, int $customOrderId, string $newStatus, ?float $counterPrice = null): void
    {
        if ($newStatus === 'accepted') {
            $title   = 'Custom Order Accepted!';
            $message = 'Your custom order request has been accepted. Proceed to payment to confirm.';
        } elseif ($newStatus === 'refused' && $counterPrice) {
            $title   = 'Counter Offer Received';
            $message = 'The artist has sent a counter offer of RM ' . number_format($counterPrice, 2) . '. Review and respond.';
        } elseif ($newStatus === 'refused') {
            $title   = 'Custom Order Declined';
            $message = 'Unfortunately your custom order request has been declined by the artist.';
        } else {
            $title   = 'Custom Order Update';
            $message = 'Your custom order status has been updated.';
        }

        Notification::send(
            $buyerId,
            'request_status',
            $title,
            $message,
            route('custom-orders.show', $customOrderId)
        );
    }

    /**
     * Notify buyer when their bulk order request status changes.
     * Triggers: BulkOrderController@accept/refuse
     */
    public static function bulkOrderStatusChanged(int $buyerId, int $bulkOrderId, string $newStatus): void
    {
        if ($newStatus === 'accepted') {
            $title   = 'Bulk Order Accepted!';
            $message = 'Your bulk order request has been accepted. Proceed to payment to confirm.';
        } elseif ($newStatus === 'refused') {
            $title   = 'Bulk Order Declined';
            $message = 'Unfortunately your bulk order request has been declined by the artist.';
        } else {
            $title   = 'Bulk Order Update';
            $message = 'Your bulk order status has been updated.';
        }

        Notification::send(
            $buyerId,
            'request_status',
            $title,
            $message,
            route('bulk-orders.show', $bulkOrderId)
        );
    }

    /**
     * Notify buyer 3 days before their enrolled class starts.
     * Triggers: ClassReminderCommand (scheduled command)
     */
    public static function classReminder(int $buyerId, int $classId, string $classTitle, string $startDate): void
    {
        Notification::send(
            $buyerId,
            'class_reminder',
            'Class Starting Soon!',
            "Reminder: \"{$classTitle}\" starts on {$startDate}. Don't forget to join!",
            route('class.event.show', $classId)
        );
    }

    // ════════════════════════════════════════
    // SELLER NOTIFICATIONS
    // ════════════════════════════════════════

    /**
     * Notify seller when they receive a new regular order.
     * Triggers: OrderCheckoutController@success
     */
    public static function newOrder(int $sellerId, int $orderId, string $buyerName, string $productName): void
    {
        Notification::send(
            $sellerId,
            'new_order',
            'New Order Received!',
            "{$buyerName} placed an order for \"{$productName}\".",
            route('artist.orders')
        );
    }

    /**
     * Notify seller when they receive a new custom order request.
     * Triggers: CustomOrderController@store
     */
    public static function newCustomOrderRequest(int $sellerId, int $customOrderId, string $buyerName, string $title): void
    {
        Notification::send(
            $sellerId,
            'new_request',
            'New Custom Order Request',
            "{$buyerName} sent a custom order request: \"{$title}\".",
            route('artist.custom-orders.show', $customOrderId)
        );
    }

    /**
     * Notify seller when they receive a new bulk order request.
     * Triggers: BulkOrderController@store
     */
    public static function newBulkOrderRequest(int $sellerId, int $bulkOrderId, string $buyerName, string $productName, int $qty): void
    {
        Notification::send(
            $sellerId,
            'new_request',
            'New Bulk Order Request',
            "{$buyerName} requested a bulk order of {$qty} pcs for \"{$productName}\".",
            route('artist.bulk-orders.show', $bulkOrderId)
        );
    }

    /**
     * Notify seller/organiser when someone enrolls in their class.
     * Triggers: ClassEventController@enroll
     */
    public static function newClassEnrollment(int $sellerId, int $classId, string $buyerName, string $classTitle): void
    {
        Notification::send(
            $sellerId,
            'new_enrollment',
            'New Class Enrollment',
            "{$buyerName} has enrolled in your class \"{$classTitle}\".",
            route('class.event.show', $classId)
        );
    }
}