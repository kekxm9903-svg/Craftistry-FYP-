<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassEvent;
use App\Models\Booking;
use Stripe\Stripe;
use Stripe\Checkout\Session;

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

        if ($booking && $booking->isPaid()) {
            return redirect()->route('my.classes')
                             ->with('info', 'You are already enrolled in this class.');
        }

        // Free class — enroll directly, skip checkout
        if (!$classEvent->is_paid || !$classEvent->price) {
            Booking::firstOrCreate(
                ['class_event_id' => $classEvent->id, 'user_id' => $user->id],
                ['payment_status' => 'free', 'booked_at' => now()]
            );
            return redirect()->route('my.classes')
                             ->with('success', 'You have successfully enrolled in ' . $classEvent->title . '!');
        }

        return view('classCheckout', compact('classEvent'));
    }

    // Process payment — redirect to Stripe
    public function process(Request $request, $classEventId)
    {
        $classEvent = ClassEvent::findOrFail($classEventId);
        $user       = auth()->user();

        Stripe::setApiKey(config('services.stripe.secret'));

        // Create pending booking
        $booking = Booking::firstOrCreate(
            ['class_event_id' => $classEvent->id, 'user_id' => $user->id],
            ['payment_status' => 'pending', 'booked_at' => now()]
        );

        // Create Stripe session
        $session = Session::create([
            'payment_method_types' => ['card', 'fpx', 'grabpay'],
            'line_items' => [[
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

        // Save session ID
        $booking->update(['stripe_session_id' => $session->id]);

        return redirect($session->url);
    }

    // Payment success
    public function success(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::retrieve($request->session_id);
        $booking = Booking::where('stripe_session_id', $session->id)->first();

        if ($booking && $session->payment_status === 'paid') {
            $booking->update([
                'payment_status' => 'paid',
                'amount_paid'    => $booking->classEvent->price,
            ]);
        }

        return view('classCheckoutSuccessful', compact('booking'));
    }

    // Payment cancelled
    public function cancel($classEventId)
    {
        Booking::where('class_event_id', $classEventId)
               ->where('user_id', auth()->id())
               ->where('payment_status', 'pending')
               ->delete();

        return redirect()->route('class.event.show', $classEventId)
                         ->with('error', 'Payment was cancelled. You have not been enrolled.');
    }
}