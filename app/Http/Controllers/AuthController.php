<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CartItem;
use App\Models\ArtworkSell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ─── Cart Helpers ─────────────────────────────────────────────────────────

    /**
     * Save session cart → DB (called before logout)
     */
    private function saveCartToDb(int $userId): void
    {
        $cart = session('cart', []);

        CartItem::where('user_id', $userId)->delete();

        foreach ($cart as $artworkId => $item) {
            CartItem::create([
                'user_id'    => $userId,
                'artwork_id' => $artworkId,
                'quantity'   => $item['quantity'],
            ]);
        }
    }

    /**
     * Restore cart from DB → session (called after login)
     */
    private function restoreCartFromDb(int $userId): void
    {
        $items = CartItem::where('user_id', $userId)
                    ->with(['artwork.artist.user'])
                    ->get();

        if ($items->isEmpty()) return;

        $cart = [];
        foreach ($items as $item) {
            $artwork = $item->artwork;
            if (!$artwork) continue;

            $cart[$item->artwork_id] = [
                'id'           => $item->artwork_id,
                'name'         => $artwork->product_name ?? 'Untitled Artwork',
                'price'        => (float) ($artwork->product_price ?? 0),
                'shipping_fee' => (float) ($artwork->shipping_fee ?? 0),
                'image_path'   => $artwork->image_path ?? null,
                'artwork_type' => $artwork->artwork_type ?? null,
                'artist_name'  => optional(optional($artwork->artist)->user)->fullname
                                  ?? optional(optional($artwork->artist)->user)->name
                                  ?? null,
                'quantity'     => $item->quantity,
            ];
        }

        session(['cart' => $cart]);
    }

    // ─── Register ─────────────────────────────────────────────────────────────

    public function showRegister()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20',
            'location' => 'required|string',
            'password' => 'required|min:8|confirmed',
            'terms'    => 'accepted',
        ], [
            'email.unique'        => 'This email is already registered. Please use a different email or try logging in.',
            'password.min'        => 'Password must be at least 8 characters long.',
            'password.confirmed'  => 'Password confirmation does not match.',
            'terms.accepted'      => 'You must accept the terms and conditions to continue.',
        ]);

        $user = User::create([
            'fullname' => $validated['fullname'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'],
            'location' => $validated['location'],
            'password' => Hash::make($validated['password']),
            'role'     => 'buyer',
        ]);

        Auth::login($user);

        // New user — no saved cart to restore, but restoreCartFromDb is safe to call anyway
        $this->restoreCartFromDb($user->id);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome! Your account has been created successfully.');
    }

    // ─── Login ────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Please enter your email address.',
            'email.email'       => 'Please enter a valid email address.',
            'password.required' => 'Please enter your password.',
        ]);

        // Check if user exists
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email address. Please check your email or sign up for a new account.',
            ])->onlyInput('email');
        }

        // Check if account is banned
        if ($user->artist_status === 'banned') {
            return back()->withErrors([
                'email' => 'Your account has been suspended. Please contact support for assistance.',
            ])->onlyInput('email');
        }

        // Attempt login
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // ← Restore saved cart from DB into session
            $this->restoreCartFromDb(Auth::id());

            // Redirect admin to admin dashboard
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Welcome, Admin ' . Auth::user()->fullname . '!');
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . Auth::user()->fullname . '!');
        }

        return back()->withErrors([
            'email' => 'Incorrect password. Please try again or use "Forgot Password" to reset it.',
        ])->onlyInput('email');
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        // ← Save session cart to DB before clearing session
        $this->saveCartToDb(Auth::id());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('logout.success')
            ->with('success', 'You have been logged out successfully.');
    }
}