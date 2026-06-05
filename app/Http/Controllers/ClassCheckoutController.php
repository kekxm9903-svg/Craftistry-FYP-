<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassEvent;
use App\Models\Booking;
use App\Services\NotificationService;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Refund;

class ClassCheckoutController extends Controller
{
    // Show checkout page
    public function show($classEventId)
    {
        $classEvent = ClassEvent::findOrFail($classEventId);
        $user       = auth()->user();

        // Check if already enrolled and paid
        $booking = Booking::where('class_event_id', $classEventId)
                          ->where('user_id', $user->id)
                          ->first();

        if ($booking && $booking->payment_status === 'paid') {
            return redirect()->route('class.event.show', $classEventId)
                             ->with('info', 'You are already enrolled in this class.');
        }

        // Free class — should not reach here, but guard anyway
        if (!$classEvent->is_paid || !$classEvent->price) {
            return redirect()->route('class.event.show', $classEventId);
        }

        return view('classCheckout', compact('classEvent'));
    }

    // Process payment — redirect to Stripe
    public function process(Request $request, $classEventId)
    {
        $classEvent = ClassEvent::findOrFail($classEventId);
        $user       = auth()->user();

        Stripe::setApiKey(config('services.stripe.secret'));

        // Create or update pending booking
        $booking = Booking::firstOrCreate(
            ['class_event_id' => $classEvent->id, 'user_id' => $user->id],
            ['payment_status' => 'pending', 'booked_at' => now()]
        );

        // If booking exists but was cancelled/pending, reset it
        if ($booking->payment_status !== 'paid') {
            $booking->update(['payment_status' => 'pending']);
        }

        // Create Stripe session
        // Note: do NOT include 'fpx' — it causes card to disappear in sandbox
        $session = Session::create([
            'payment_method_types' => ['card', 'fpx', 'grabpay'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => 'myr',
                    'unit_amount'  => (int)($classEvent->price * 100),
                    'product_data' => [
                        'name'        => $classEvent->title,
                        'description' => 'Craftistry Class Enrollment',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => route('class.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('class.checkout.cancel', $classEvent->id),
            'metadata'    => [
                'booking_id'     => $booking->id,
                'class_event_id' => $classEvent->id,
                'user_id'        => $user->id,
            ],
        ]);

        // Save session ID to booking for later refund use
        $booking->update(['stripe_session_id' => $session->id]);

        return redirect($session->url);
    }

    // Payment success
    public function success(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::retrieve($request->session_id);
        $booking = Booking::where('stripe_session_id', $session->id)
                          ->with('classEvent', 'user')
                          ->first();

        if ($booking && $session->payment_status === 'paid') {
            $booking->update([
                'payment_status' => 'paid',
                'amount_paid'    => $booking->classEvent->price ?? 0,
            ]);

            // Notify the instructor
            if ($booking->classEvent) {
                NotificationService::newClassEnrollment(
                    $booking->classEvent->user_id,
                    $booking->classEvent->id,
                    $booking->user->fullname ?? $booking->user->name ?? 'A user',
                    $booking->classEvent->title
                );
            }
        }

        return view('classCheckoutSuccessful', compact('booking'));
    }

    // Payment cancelled — user clicked "Back" on Stripe page
    public function cancel($classEventId)
    {
        // Delete the pending booking so they can try again
        Booking::where('class_event_id', $classEventId)
               ->where('user_id', auth()->id())
               ->where('payment_status', 'pending')
               ->delete();

        return redirect()->route('class.event.show', $classEventId)
                         ->with('error', 'Payment was cancelled. You have not been enrolled.');
    }
}