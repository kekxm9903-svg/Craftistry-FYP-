<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserProfileController extends Controller
{
    /**
     * Display the user profile.
     */
    public function show()
    {
        $user = Auth::user();
        
        $states = [
            'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan',
            'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah',
            'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur',
            'Labuan', 'Putrajaya'
        ];

        return view('userProfileShow', compact('user', 'states'));
    }

    public function edit()
    {
        // Get fresh user data from database
        $user = \App\Models\User::find(Auth::id());
        
        $states = [
            'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan',
            'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah',
            'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur',
            'Labuan', 'Putrajaya'
        ];
        
        // Add cache busting to force fresh render
        $timestamp = time();
        
        return view('userProfileUpdate', compact('user', 'states', 'timestamp'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // DEBUG: Log incoming data
        Log::info('=== Profile Update Debug ===');
        Log::info('User ID: ' . $user->id);
        
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            Log::info('File Details:', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);
        }
        
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'remove_profile_picture' => 'nullable|in:0,1',
        ]);

        // 1. HANDLE PROFILE PICTURE REMOVAL
        if ($request->input('remove_profile_picture') == '1') {
            Log::info('Removing profile image');
            
            // Get current image safely
            $currentImage = $user->profile_image ?? $user->profile_picture ?? null;
            
            if ($currentImage && $currentImage !== 'images/Profile.png') {
                try {
                    if (Storage::disk('public')->exists($currentImage)) {
                        Storage::disk('public')->delete($currentImage);
                        Log::info('Successfully deleted: ' . $currentImage);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to delete image: ' . $e->getMessage());
                }
            }
            
            // FIX: Only set null if column actually exists
            if (Schema::hasColumn('users', 'profile_picture')) {
                $user->profile_picture = null;
            }
            if (Schema::hasColumn('users', 'profile_image')) {
                $user->profile_image = null;
            }
            
            $user->save();
        }
        // 2. HANDLE NEW PROFILE PICTURE UPLOAD
        elseif ($request->hasFile('profile_picture')) {
            Log::info('Processing new profile image upload');
            
            try {
                $file = $request->file('profile_picture');
                
                // Validate the file
                if (!$file->isValid()) {
                    throw new \Exception('Uploaded file is not valid');
                }
                
                // Delete old profile picture if exists
                $oldImage = $user->profile_image ?? $user->profile_picture ?? null;
                if ($oldImage && $oldImage !== 'images/Profile.png') {
                    if (Storage::disk('public')->exists($oldImage)) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }
                
                // Store new image
                $imageName = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs('profile_pictures', $imageName, 'public');
                
                if (!$imagePath) {
                    throw new \Exception('Failed to store file');
                }
                
                // FIX: Only save to columns that actually exist
                if (Schema::hasColumn('users', 'profile_picture')) {
                    $user->profile_picture = $imagePath;
                }
                if (Schema::hasColumn('users', 'profile_image')) {
                    $user->profile_image = $imagePath;
                }
                
                if (!$user->save()) {
                    throw new \Exception('Failed to save user profile picture to database');
                }
                
                Log::info('Profile picture updated successfully');
                
            } catch (\Exception $e) {
                Log::error('Profile image upload failed: ' . $e->getMessage());
                return back()->withInput()->with('error', 'Failed to upload profile image: ' . $e->getMessage());
            }
        }

        // Build location field
        $city = $validated['city'] ?? $user->city;
        $state = $validated['state'] ?? $user->state;
        
        $locationParts = array_filter([$city, $state]);
        if (!empty($locationParts)) {
            $validated['location'] = implode(', ', $locationParts);
        }

        // Clean up validated array
        unset($validated['profile_picture']);
        unset($validated['remove_profile_picture']);

        // Update other fields
        $user->update($validated);

        return redirect()->route('user.profile.show')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Update profile image via AJAX.
     */
    public function updateProfileImage(Request $request)
    {
        try {
            $request->validate([
                'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
            ]);

            $user = Auth::user();

            // Ensure directory exists
            $uploadPath = storage_path('app/public/profile_pictures');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }

            // Upload new image
            if ($request->hasFile('profile_picture')) {
                // Delete old image
                $currentImage = $user->profile_image ?? $user->profile_picture ?? null;
                if ($currentImage && $currentImage !== 'images/Profile.png') {
                    if (Storage::disk('public')->exists($currentImage)) {
                        Storage::disk('public')->delete($currentImage);
                    }
                }

                $imagePath = $request->file('profile_picture')->store('profile_pictures', 'public');
                
                // FIX: Check columns before saving
                if (Schema::hasColumn('users', 'profile_image')) {
                    $user->profile_image = $imagePath;
                }
                if (Schema::hasColumn('users', 'profile_picture')) {
                    $user->profile_picture = $imagePath;
                }
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Profile image updated successfully',
                    'image_url' => asset('storage/' . $imagePath)
                ]);
            }

            return response()->json([
                'success' => false, 
                'message' => 'No image file provided'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Profile image update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to update profile image'
            ], 500);
        }
    }

    /**
     * Show the form to change password.
     */
    public function showChangePasswordForm()
    {
        // If your file is at: resources/views/changePassword.blade.php
        return view('changePassword');
    }

    /**
     * Handle the password change request.
     */
    public function updatePassword(Request $request)
    {
        // FIX: Use 'different' rule for cleaner validation
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required', 
                'confirmed', 
                Password::min(8),
                'different:current_password' // Ensures new pass != old pass
            ],
        ], [
            'current_password.current_password' => 'The provided password does not match your current password.',
            'password.different' => 'The new password cannot be the same as your current password.'
        ]);

        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('user.profile.show')
            ->with('success', 'Password updated successfully!');
    }
}