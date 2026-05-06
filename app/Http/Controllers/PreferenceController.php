<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PreferenceController extends Controller
{
    // No constructor — auth is handled by the middleware group in web.php

    /**
     * Save the user's preferred artwork type and permanently mark modal as shown.
     * POST /user/preference
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'preferred_artwork_type' => ['required', 'string', 'max:100'],
            ]);

            DB::table('users')->where('id', Auth::id())->update([
                'preferred_artwork_type' => $request->preferred_artwork_type,
                'preference_shown'       => true,
                'updated_at'             => now(),
            ]);

            return response()->json([
                'success'    => true,
                'message'    => 'Preference saved!',
                'preference' => $request->preferred_artwork_type,
            ]);

        } catch (\Exception $e) {
            Log::error('PreferenceController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Skip — permanently mark as shown so it never appears again.
     * POST /user/preference/skip
     */
    public function skip(Request $request)
    {
        try {
            DB::table('users')->where('id', Auth::id())->update([
                'preference_shown' => true,
                'updated_at'       => now(),
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('PreferenceController@skip: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update preference from profile page.
     * POST /user/preference/update
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'preferred_artwork_type' => ['nullable', 'string', 'max:100'],
            ]);

            $pref = $request->preferred_artwork_type ?: null;

            DB::table('users')->where('id', Auth::id())->update([
                'preferred_artwork_type' => $pref,
                'updated_at'             => now(),
            ]);

            return response()->json([
                'success'    => true,
                'message'    => $pref ? 'Preference updated!' : 'Preference cleared.',
                'preference' => $pref,
            ]);

        } catch (\Exception $e) {
            Log::error('PreferenceController@update: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}