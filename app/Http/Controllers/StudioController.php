<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\ArtworkType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudioController extends Controller
{
    /**
     * Display studio landing page or dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $view = 'landing';
        $artworkTypes = [];
        $artist = null;

        // Check if user already has artist profile
        if ($user->artist) {
            return redirect()->route('artist.profile');
        }

        return view('studio', compact('view', 'artworkTypes', 'artist', 'user'));
    }

    /**
     * Show artist registration form
     */
    public function showRegisterForm()
    {
        $user = Auth::user();

        // Check if user is already an artist
        if ($user && $user->artist) {
            return redirect()->route('artist.profile')->with('info', 'You already have an artist profile');
        }

        $artworkTypes = ArtworkType::all();

        return view('studio', [
            'view'         => 'register',
            'artworkTypes' => $artworkTypes,
            'artist'       => null,
            'user'         => $user
        ]);
    }

    /**
     * Process artist registration - AUTO APPROVED
     */
    public function register(Request $request)
    {
        $user = Auth::user();

        // Check if user is already an artist
        if ($user->artist) {
            return redirect()->route('artist.profile')->with('info', 'You already have an artist profile');
        }

        // Validate the form data
        $validated = $request->validate([
            'bio'                 => 'required|string|min:50|max:1000',
            'specialization'      => 'nullable|string|max:255',
            'artwork_types'       => 'required|array|min:1',
            'artwork_types.*'     => 'exists:artwork_types,id',
            'allow_customization' => 'boolean'
        ], [
            'bio.required'           => 'Please provide your artist bio.',
            'bio.min'                => 'Your bio must be at least 50 characters.',
            'bio.max'                => 'Your bio cannot exceed 1000 characters.',
            'artwork_types.required' => 'Please select at least one artwork type.',
            'artwork_types.min'      => 'Please select at least one artwork type.',
            'artwork_types.*.exists' => 'Invalid artwork type selected.'
        ]);

        try {
            // Create artist profile - AUTO APPROVED (verified status)
            $artist = Artist::create([
                'user_id'             => $user->id,
                'bio'                 => $validated['bio'],
                'specialization'      => $validated['specialization'] ?? null,
                'allow_customization' => $request->has('allow_customization'),
                'verification_status' => 'verified'
            ]);

            // Mark user as artist (keeps role as buyer — user can be both)
            $user->update(['is_artist' => true]);

            // Attach selected artwork types
            $artist->artworkTypes()->attach($validated['artwork_types']);

            // Force reload the user with the artist relationship
            $user->load('artist');

            // Regenerate session to ensure fresh data
            $request->session()->regenerate();

            // Explicitly refresh the auth user instance
            Auth::setUser($user->fresh(['artist']));

            return redirect()->route('artist.profile')
                ->with('success', 'Welcome to Craftistry! Your artist profile has been created successfully.');

        } catch (\Exception $e) {
            \Log::error('Artist Registration Error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred during registration. Please try again.');
        }
    }
}