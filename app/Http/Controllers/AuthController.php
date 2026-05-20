<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CartItem;
use App\Models\ArtworkSell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    // ─── Cart Helpers ─────────────────────────────────────────────────────────

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
            'password' => 'required|min:8|confirmed',
            'terms'    => 'accepted',
        ], [
            'email.unique'       => 'This email is already registered.',
            'password.min'       => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'terms.accepted'     => 'You must accept the terms and conditions.',
        ]);

        $user = User::create([
            'fullname'         => $validated['fullname'],
            'email'            => $validated['email'],
            'phone'            => $validated['phone'],
            'password'         => Hash::make($validated['password']),
            'role'             => 'buyer',
            'preference_shown' => false,
        ]);

        Auth::login($user);
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')
            ->with('success', 'Account created! Please check your email to verify your address before continuing.');
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

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email address.',
            ])->onlyInput('email');
        }

        if ($user->artist_status === 'banned') {
            return back()->withErrors([
                'email' => 'Your account has been suspended. Please contact support.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $authUser = Auth::user();

            // Admins (both admin and super_admin) bypass email verification
            if (!$authUser->hasVerifiedEmail() && !$authUser->isAdmin()) {
                return redirect()->route('verification.notice')
                    ->with('warning', 'Please verify your email address before logging in.');
            }

            $this->restoreCartFromDb($authUser->id);

            // Redirect admins and super_admins to admin panel
            if ($authUser->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Welcome, ' . $authUser->fullname . '!');
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . $authUser->fullname . '!');
        }

        return back()->withErrors([
            'email' => 'Incorrect password. Please try again.',
        ])->onlyInput('email');
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        $this->saveCartToDb(Auth::id());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('logout.success')
            ->with('success', 'You have been logged out successfully.');
    }

    // ─── Forgot Password ──────────────────────────────────────────────────────

    public function showForgotPassword()
    {
        return view('forgotPassword');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    // ─── Reset Password ───────────────────────────────────────────────────────

    public function showResetPassword(Request $request, string $token)
    {
        return view('resetPassword', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ], [
            'password.min'       => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}