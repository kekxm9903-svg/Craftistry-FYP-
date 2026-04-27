<?php

namespace App\Http\Controllers;

use App\Models\ClassEvent;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ClassEventController extends Controller
{
    /**
     * Display a listing of the resource (user's own classes/events)
     */
    public function index()
    {
        $classEvents = ClassEvent::where('user_id', Auth::id())
                                 ->withCount('bookings')
                                 ->latest()
                                 ->paginate(12);

        return view('classEvent', compact('classEvents'));
    }

    /**
     * Display all classes and events from all artists (public browse page)
     */
    public function browse(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $now   = Carbon::now();

        $query = ClassEvent::with('user')->withCount('bookings');

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('media_type', $request->type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            switch ($request->status) {
                case 'upcoming':
                    $query->where('start_date', '>', $today);
                    break;
                case 'active':
                    $query->where('start_date', '<=', $today)
                          ->where('end_date', '>=', $today)
                          ->where(function ($q) use ($today) {
                              $q->whereNull('enrollment_deadline')
                                ->orWhere('enrollment_deadline', '>=', $today);
                          });
                    break;
                case 'expired':
                    $query->where(function ($q) use ($today) {
                        $q->where('end_date', '<', $today)
                          ->orWhere(function ($q2) use ($today) {
                              $q2->whereNotNull('enrollment_deadline')
                                 ->where('enrollment_deadline', '<', $today);
                          });
                    });
                    break;
                case 'full':
                    $query->whereNotNull('max_participants');
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('fullname', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $classEvents = $query->latest()->paginate(12)->withQueryString();

        if ($request->filled('status') && $request->status === 'full') {
            $filtered = $classEvents->getCollection()->filter(function ($class) {
                return $class->max_participants &&
                       $class->bookings_count >= $class->max_participants;
            });
            $classEvents->setCollection($filtered);
        }

        return view('classEventBrowse', compact('classEvents'));
    }

    /**
     * Display the specified resource (Detail page)
     */
    public function show($id)
    {
        $classEvent = ClassEvent::with('user')->withCount('bookings')->findOrFail($id);

        $isEnrolled = false;
        if (Auth::check()) {
            $isEnrolled = Booking::where('class_event_id', $id)
                ->where('user_id', Auth::id())
                ->whereIn('payment_status', ['paid', 'free'])
                ->exists();
        }

        $isOwner = Auth::check() && $classEvent->user_id === Auth::id();

        return view('classEventShow', compact('classEvent', 'isEnrolled', 'isOwner'));
    }

    /**
     * Enroll the authenticated user into a class/event (AJAX)
     */
    public function enroll($id)
    {
        $classEvent = ClassEvent::findOrFail($id);

        if ($classEvent->user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot enroll in your own class/event.',
            ], 403);
        }

        if ($classEvent->enrollment_deadline && now()->toDateString() > $classEvent->enrollment_deadline->toDateString()) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment is closed. The deadline was ' . $classEvent->enrollment_deadline->format('M d, Y') . '.',
            ], 403);
        }

        if ($classEvent->max_participants) {
            $currentCount = Booking::where('class_event_id', $id)->count();
            if ($currentCount >= $classEvent->max_participants) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, this class/event is fully booked.',
                    'is_full' => true,
                ], 409);
            }
        }

        $existing = Booking::where('class_event_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            return response()->json([
                'success'     => false,
                'message'     => 'You are already enrolled in this class/event.',
                'is_enrolled' => true,
            ], 409);
        }

        // ── Paid class → redirect to checkout instead ──────────────
        if ($classEvent->is_paid && $classEvent->price > 0) {
            return response()->json([
                'success'          => false,
                'requires_payment' => true,
                'redirect'         => route('class.checkout.show', $classEvent->id),
                'message'          => 'This class requires payment to enroll.',
            ]);
        }

        try {
            Booking::create([
                'class_event_id' => $id,
                'user_id'        => Auth::id(),
                'booked_at'      => now(),
                'payment_status' => 'free',
            ]);

            $newCount = Booking::where('class_event_id', $id)->count();

            return response()->json([
                'success'           => true,
                'message'           => 'You have successfully enrolled in "' . $classEvent->title . '"!',
                'is_enrolled'       => true,
                'participant_count' => $newCount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unenroll the authenticated user from a class/event (AJAX)
     * — Auto-refunds via Stripe if booking was paid
     */
    public function unenroll($id)
    {
        $classEvent = ClassEvent::findOrFail($id);

        if (!$classEvent->is_cancellation_open) {
            return response()->json([
                'success'             => false,
                'message'             => 'Cancellation is no longer allowed. The cancellation deadline was ' . $classEvent->formatted_cancellation_deadline . '.',
                'cancellation_closed' => true,
            ], 403);
        }

        $booking = Booking::where('class_event_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$booking) {
            return response()->json([
                'success'     => false,
                'message'     => 'You are not enrolled in this class/event.',
                'is_enrolled' => false,
            ], 404);
        }

        // ── Paid booking → issue Stripe refund first ────────────────
        $wasRefunded = false;
        if ($booking->payment_status === 'paid' && $booking->stripe_session_id) {
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

                $session       = \Stripe\Checkout\Session::retrieve($booking->stripe_session_id);
                $paymentIntent = $session->payment_intent;

                if ($paymentIntent) {
                    \Stripe\Refund::create([
                        'payment_intent' => $paymentIntent,
                        'reason'         => 'requested_by_customer',
                    ]);
                }

                $booking->update(['payment_status' => 'refunded']);
                $wasRefunded = true;

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund failed: ' . $e->getMessage() . '. Please contact support.',
                ], 500);
            }
        }

        try {
            $booking->delete();

            $newCount = Booking::where('class_event_id', $id)->count();

            $message = $wasRefunded
                ? 'Your enrollment has been cancelled and a refund has been issued to your original payment method. It may take 5–10 business days to appear.'
                : 'Your enrollment has been cancelled.';

            return response()->json([
                'success'           => true,
                'message'           => $message,
                'is_enrolled'       => false,
                'was_refunded'      => $wasRefunded,
                'participant_count' => $newCount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel enrollment. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get participants list for a class/event (AJAX — owner only)
     */
    public function getParticipants($id)
    {
        $classEvent = ClassEvent::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $participants = Booking::with('user')
            ->where('class_event_id', $id)
            ->orderBy('booked_at', 'asc')
            ->get()
            ->map(function ($booking) {
                return [
                    'booking_id' => $booking->id,
                    'name'       => $booking->user->fullname ?? $booking->user->name ?? 'Unknown',
                    'email'      => $booking->user->email,
                    'booked_at'  => $booking->booked_at
                        ? $booking->booked_at->timezone('Asia/Kuala_Lumpur')->format('d M Y, h:i A')
                        : $booking->created_at->timezone('Asia/Kuala_Lumpur')->format('d M Y, h:i A'),
                ];
            });

        return response()->json([
            'success'           => true,
            'event_title'       => $classEvent->title,
            'participant_count' => $participants->count(),
            'participants'      => $participants,
        ]);
    }

    /**
     * Drop a participant from a class/event (AJAX — owner only)
     */
    public function dropParticipant($id, $bookingId)
    {
        $classEvent = ClassEvent::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $booking = Booking::where('id', $bookingId)
            ->where('class_event_id', $id)
            ->firstOrFail();

        try {
            $booking->delete();

            $newCount = Booking::where('class_event_id', $id)->count();

            return response()->json([
                'success'           => true,
                'message'           => 'Participant removed successfully.',
                'participant_count' => $newCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove participant.',
            ], 500);
        }
    }

    /**
     * Get class data as JSON (for edit modal)
     */
    public function getData($id)
    {
        $classEvent = ClassEvent::findOrFail($id);

        if ($classEvent->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $startDate          = Carbon::parse($classEvent->start_date)->format('Y-m-d');
        $endDate            = Carbon::parse($classEvent->end_date)->format('Y-m-d');
        $enrollmentDeadline = $classEvent->enrollment_deadline
            ? Carbon::parse($classEvent->enrollment_deadline)->format('Y-m-d')
            : null;

        $startTime = $classEvent->start_time;
        $endTime   = $classEvent->end_time;

        if ($startTime instanceof \DateTime) {
            $startTime = $startTime->format('H:i');
        } elseif (is_string($startTime) && strlen($startTime) > 5) {
            $startTime = Carbon::parse($startTime)->format('H:i');
        }

        if ($endTime instanceof \DateTime) {
            $endTime = $endTime->format('H:i');
        } elseif (is_string($endTime) && strlen($endTime) > 5) {
            $endTime = Carbon::parse($endTime)->format('H:i');
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                     => $classEvent->id,
                'title'                  => $classEvent->title,
                'description'            => $classEvent->description,
                'is_paid'                => (int) $classEvent->is_paid,
                'price'                  => $classEvent->price,
                'media_type'             => $classEvent->media_type,
                'platform'               => $classEvent->platform,
                'location'               => $classEvent->location,
                'start_date'             => $startDate,
                'end_date'               => $endDate,
                'enrollment_deadline'    => $enrollmentDeadline,
                'cancellation_deadline'  => $classEvent->cancellation_deadline
                    ? Carbon::parse($classEvent->cancellation_deadline)->format('Y-m-d')
                    : null,
                'require_form'           => (int) $classEvent->require_form,
                'enrollment_form_url'    => $classEvent->enrollment_form_url,
                'max_participants'       => $classEvent->max_participants,
                'start_time'             => $startTime,
                'end_time'               => $endTime,
                'poster_url'             => $classEvent->poster_url,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $classEvent = ClassEvent::findOrFail($id);

        if ($classEvent->user_id !== Auth::id()) {
            abort(403);
        }

        return view('classEventEdit', compact('classEvent'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string|max:1000',
            'poster_image'        => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'is_paid'             => 'required|boolean',
            'price'               => 'nullable|numeric|min:0.01|required_if:is_paid,1',
            'media_type'          => 'required|in:online,physical',
            'platform'            => 'required_if:media_type,online|nullable|string|max:255',
            'location'            => 'required_if:media_type,physical|nullable|string|max:255',
            'start_date'          => 'required|date|after_or_equal:today',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'enrollment_deadline' => 'nullable|date|before_or_equal:start_date',
            'cancellation_deadline' => 'nullable|date',
            'require_form'        => 'nullable|boolean',
            'enrollment_form_url' => 'nullable|url|max:2048',
            'max_participants'    => 'nullable|integer|min:1|max:99999',
            'start_time'          => 'required|date_format:H:i',
            'end_time'            => 'required|date_format:H:i',
            'duration_weeks'      => 'nullable|numeric',
            'duration_hours'      => 'nullable|integer',
            'duration_minutes'    => 'nullable|integer',
        ]);

        try {
            $posterPath = null;
            if ($request->hasFile('poster_image')) {
                $posterPath = $request->file('poster_image')->store('class-events', 'public');
            }

            if (!$request->duration_weeks) {
                $start                       = Carbon::parse($request->start_date);
                $end                         = Carbon::parse($request->end_date);
                $diffDays                    = $start->diffInDays($end);
                $validated['duration_weeks'] = round($diffDays / 7, 1);
            }

            if (!$request->duration_hours || !$request->duration_minutes) {
                $startTime                     = Carbon::parse($request->start_time);
                $endTime                       = Carbon::parse($request->end_time);
                $diffMinutes                   = $startTime->diffInMinutes($endTime);
                $validated['duration_hours']   = floor($diffMinutes / 60);
                $validated['duration_minutes'] = $diffMinutes % 60;
            }

            $classEvent = ClassEvent::create([
                'user_id'                => Auth::id(),
                'title'                  => $validated['title'],
                'description'            => $validated['description'] ?? null,
                'poster_image'           => $posterPath,
                'is_paid'                => $validated['is_paid'],
                'price'                  => $validated['is_paid'] ? $validated['price'] : null,
                'media_type'             => $validated['media_type'],
                'platform'               => $validated['platform'] ?? null,
                'location'               => $validated['location'] ?? null,
                'start_date'             => $validated['start_date'],
                'end_date'               => $validated['end_date'],
                'enrollment_deadline'    => $validated['enrollment_deadline'] ?? null,
                'cancellation_deadline'  => $validated['cancellation_deadline'] ?? null,
                'require_form'           => $validated['require_form'] ?? 0,
                'enrollment_form_url'    => $validated['enrollment_form_url'] ?? null,
                'max_participants'       => $validated['max_participants'] ?? null,
                'duration_weeks'         => $validated['duration_weeks'],
                'start_time'             => $validated['start_time'],
                'end_time'               => $validated['end_time'],
                'duration_hours'         => $validated['duration_hours'],
                'duration_minutes'       => $validated['duration_minutes'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Class/Event created successfully!',
                'data'    => $classEvent,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create class/event. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $classEvent = ClassEvent::findOrFail($id);

        if ($classEvent->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title'                 => 'required|string|max:255',
            'description'           => 'nullable|string|max:1000',
            'poster_image'          => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'is_paid'               => 'required|boolean',
            'price'                 => 'nullable|numeric|min:0.01|required_if:is_paid,1',
            'media_type'            => 'required|in:online,physical',
            'platform'              => 'required_if:media_type,online|nullable|string|max:255',
            'location'              => 'required_if:media_type,physical|nullable|string|max:255',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'enrollment_deadline'   => 'nullable|date|before_or_equal:start_date',
            'cancellation_deadline' => 'nullable|date',
            'require_form'          => 'nullable|boolean',
            'enrollment_form_url'   => 'nullable|url|max:2048',
            'max_participants'      => 'nullable|integer|min:1|max:99999',
            'start_time'            => 'required|date_format:H:i',
            'end_time'              => 'required|date_format:H:i',
        ]);

        try {
            $updateData = [
                'title'                  => $validated['title'],
                'description'            => $validated['description'] ?? null,
                'is_paid'                => $validated['is_paid'],
                'price'                  => $validated['is_paid'] ? $validated['price'] : null,
                'media_type'             => $validated['media_type'],
                'platform'               => $validated['platform'] ?? null,
                'location'               => $validated['location'] ?? null,
                'start_date'             => $validated['start_date'],
                'end_date'               => $validated['end_date'],
                'enrollment_deadline'    => $validated['enrollment_deadline'] ?? null,
                'cancellation_deadline'  => $request->input('cancellation_deadline') ?: null,
                'require_form'           => $request->input('require_form', 0),
                'enrollment_form_url'    => $request->input('enrollment_form_url') ?: null,
                'max_participants'       => $request->input('max_participants') ?: null,
                'start_time'             => $validated['start_time'],
                'end_time'               => $validated['end_time'],
            ];

            if ($request->hasFile('poster_image')) {
                if ($classEvent->poster_image) {
                    Storage::disk('public')->delete($classEvent->poster_image);
                }
                $updateData['poster_image'] = $request->file('poster_image')->store('class-events', 'public');
            }

            $start                          = Carbon::parse($validated['start_date']);
            $end                            = Carbon::parse($validated['end_date']);
            $updateData['duration_weeks']   = round($start->diffInDays($end) / 7, 1);

            $startTime                      = Carbon::parse($validated['start_time']);
            $endTime                        = Carbon::parse($validated['end_time']);
            $diffMinutes                    = $startTime->diffInMinutes($endTime);
            $updateData['duration_hours']   = floor($diffMinutes / 60);
            $updateData['duration_minutes'] = $diffMinutes % 60;

            $classEvent->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Class/Event updated successfully!',
                'data'    => $classEvent,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update class/event.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $classEvent = ClassEvent::findOrFail($id);

        if ($classEvent->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            if ($classEvent->poster_image) {
                Storage::disk('public')->delete($classEvent->poster_image);
            }

            $classEvent->delete();

            return response()->json([
                'success' => true,
                'message' => 'Class/Event deleted successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete class/event.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}