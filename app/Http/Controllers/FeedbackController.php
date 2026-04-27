<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Show the feedback form.
     */
    public function create()
    {
        return view('feedback');
    }

    /**
     * Store the submitted feedback.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|in:general,bug,suggestion',
            'subject'  => 'required|string|max:255',
            'message'  => 'required|string|min:10|max:2000',
        ], [
            'category.required' => 'Please select a category.',
            'category.in'       => 'Invalid category selected.',
            'subject.required'  => 'Please enter a subject.',
            'subject.max'       => 'Subject cannot exceed 255 characters.',
            'message.required'  => 'Please enter your message.',
            'message.min'       => 'Message must be at least 10 characters.',
            'message.max'       => 'Message cannot exceed 2000 characters.',
        ]);

        Feedback::create([
            'user_id'  => Auth::id(),
            'category' => $request->category,
            'subject'  => $request->subject,
            'message'  => $request->message,
            'is_read'  => false,
        ]);

        return redirect()->route('feedback.create')
            ->with('success', 'Your feedback has been submitted. We appreciate you taking the time!');
    }
}