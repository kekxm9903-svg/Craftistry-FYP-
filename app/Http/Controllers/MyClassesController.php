<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class MyClassesController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['classEvent.user'])
            ->where('user_id', Auth::id())
            ->orderBy('booked_at', 'desc')
            ->paginate(12);

        return view('myClasses', compact('bookings'));
    }
}