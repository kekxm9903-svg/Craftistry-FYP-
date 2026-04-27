<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Feedback;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $stats = [
            'total_users'      => User::where('role', '!=', 'admin')->count(),
            'banned_users'     => User::where('artist_status', 'banned')->count(),
            'total_artists'    => User::where('role', '!=', 'admin')->where('is_artist', true)->count(),
            'total_buyers'     => User::where('role', '!=', 'admin')->where('is_artist', false)->count(),
            'total_feedbacks'  => Feedback::count(),
            'unread_feedbacks' => Feedback::where('is_read', false)->count(),
            'total_reports'    => Report::count(),
            'pending_reports'  => Report::where('status', 'pending')->count(),
            'total_admins'     => User::where('role', 'admin')->count(),
        ];

        $recentFeedbacks = Feedback::with('user')
            ->latest()->take(5)->get();

        $recentReports = Report::with(['reporter', 'reportedUser'])
            ->where('status', 'pending')
            ->latest()->take(5)->get();

        return view('adminDashboard', compact('stats', 'recentFeedbacks', 'recentReports'));
    }

    // ── Users ─────────────────────────────────────────────────────────────────

    public function users(Request $request)
    {
        $query = User::where('role', '!=', 'admin');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('fullname', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'banned') {
                $query->where('artist_status', 'banned');
            } elseif ($request->status === 'artist') {
                $query->where('is_artist', true);
            } elseif ($request->status === 'buyer') {
                $query->where('is_artist', false);
            }
        }

        $users = $query->latest()->paginate(15);

        return view('adminUserList', compact('users'));
    }

    public function banUser(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot ban an admin account.');
        }
        $user->update(['artist_status' => 'banned']);
        return back()->with('success', "User \"{$user->fullname}\" has been banned.");
    }

    public function unbanUser(User $user)
    {
        $user->update(['artist_status' => null]);
        return back()->with('success', "User \"{$user->fullname}\" has been unbanned.");
    }

    // ── Feedbacks ─────────────────────────────────────────────────────────────

    public function feedbacks(Request $request)
    {
        $query = Feedback::with('user');

        if ($request->filled('status')) {
            $query->where('is_read', $request->status === 'read');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $feedbacks = $query->latest()->paginate(15);

        return view('adminFeedbacks', compact('feedbacks'));
    }

    public function markFeedbackRead(Feedback $feedback)
    {
        $feedback->update(['is_read' => true]);
        return back()->with('success', 'Feedback marked as read.');
    }

    public function deleteFeedback(Feedback $feedback)
    {
        $feedback->delete();
        return back()->with('success', 'Feedback deleted successfully.');
    }

    // ── Reports ───────────────────────────────────────────────────────────────

    public function reports(Request $request)
    {
        $query = Report::with(['reporter', 'reportedUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(15);

        return view('adminReports', compact('reports'));
    }

    public function updateReportStatus(Request $request, Report $report)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,dismissed',
        ]);
        $report->update(['status' => $request->status]);
        return back()->with('success', 'Report status updated to "' . ucfirst($request->status) . '".');
    }

    // ── Admins ────────────────────────────────────────────────────────────────

    public function admins()
    {
        $admins = User::where('role', 'admin')->latest()->get();
        return view('adminAdmins', compact('admins'));
    }

    public function addAdmin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'fullname' => 'required|string|max:255',
            'password' => 'required|min:8',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if ($user->role === 'admin') {
                return back()->with('error', 'This email is already an admin account.');
            }
            $user->update(['role' => 'admin']);
            return back()->with('success', "\"{$user->fullname}\" has been promoted to admin.");
        }

        User::create([
            'fullname' => $request->fullname,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin',
        ]);

        return back()->with('success', 'New admin account created successfully.');
    }

    public function removeAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot remove yourself as admin.');
        }
        $user->update(['role' => 'buyer']);
        return back()->with('success', "\"{$user->fullname}\" has been removed from admin.");
    }
}