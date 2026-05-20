<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Feedback;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Modules a regular admin can be assigned to
    public const ASSIGNABLE_MODULES = [
        'users'     => 'Manage Users',
        'feedbacks' => 'Manage Feedbacks',
        'reports'   => 'Manage Reports',
    ];

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $stats = [
            'total_users'      => User::whereNotIn('role', ['admin', 'super_admin'])->count(),
            'banned_users'     => User::where('artist_status', 'banned')->count(),
            'total_artists'    => User::whereNotIn('role', ['admin', 'super_admin'])->where('is_artist', true)->count(),
            'total_buyers'     => User::whereNotIn('role', ['admin', 'super_admin'])->where('is_artist', false)->count(),
            'total_feedbacks'  => Feedback::count(),
            'unread_feedbacks' => Feedback::where('is_read', false)->count(),
            'total_reports'    => Report::count(),
            'pending_reports'  => Report::where('status', 'pending')->count(),
            'total_admins'     => User::whereIn('role', ['admin', 'super_admin'])->count(),
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
        abort_unless(auth()->user()->canAccessAdminModule('users'), 403);

        $query = User::whereNotIn('role', ['admin', 'super_admin']);

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
        abort_unless(auth()->user()->canAccessAdminModule('users'), 403);

        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot ban an admin account.');
        }
        $user->update(['artist_status' => 'banned']);
        return back()->with('success', "User \"{$user->fullname}\" has been banned.");
    }

    public function unbanUser(User $user)
    {
        abort_unless(auth()->user()->canAccessAdminModule('users'), 403);

        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot modify an admin account.');
        }
        $user->update(['artist_status' => null]);
        return back()->with('success', "User \"{$user->fullname}\" has been unbanned.");
    }

    // ── Feedbacks ─────────────────────────────────────────────────────────────

    public function feedbacks(Request $request)
    {
        abort_unless(auth()->user()->canAccessAdminModule('feedbacks'), 403);

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
        abort_unless(auth()->user()->canAccessAdminModule('feedbacks'), 403);

        $feedback->update(['is_read' => true]);
        return back()->with('success', 'Feedback marked as read.');
    }

    public function deleteFeedback(Feedback $feedback)
    {
        abort_unless(auth()->user()->canAccessAdminModule('feedbacks'), 403);

        $feedback->delete();
        return back()->with('success', 'Feedback deleted successfully.');
    }

    // ── Reports ───────────────────────────────────────────────────────────────

    public function reports(Request $request)
    {
        abort_unless(auth()->user()->canAccessAdminModule('reports'), 403);

        $query = Report::with(['reporter', 'reportedUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(15);

        return view('adminReports', compact('reports'));
    }

    public function updateReportStatus(Request $request, Report $report)
    {
        abort_unless(auth()->user()->canAccessAdminModule('reports'), 403);

        $request->validate([
            'status' => 'required|in:pending,reviewed,dismissed',
        ]);
        $report->update(['status' => $request->status]);
        return back()->with('success', 'Report status updated to "' . ucfirst($request->status) . '".');
    }

    // ── Admins ────────────────────────────────────────────────────────────────

    public function admins()
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $admins = User::whereIn('role', ['admin', 'super_admin'])
            ->orderByRaw("FIELD(role, 'super_admin', 'admin')")
            ->orderBy('created_at')
            ->get();

        return view('adminAdmins', [
            'admins'  => $admins,
            'modules' => self::ASSIGNABLE_MODULES,
        ]);
    }

    public function addAdmin(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $request->validate([
            'email'         => 'required|email',
            'fullname'      => 'nullable|string|max:255',
            'password'      => 'nullable|string|min:8',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'in:users,feedbacks,reports',
        ]);

        $permissions = array_values($request->input('permissions', []));
        $user        = User::where('email', $request->email)->first();

        if ($user) {
            if ($user->isSuperAdmin()) {
                return back()->with('error', 'This account is already a super admin.');
            }
            if ($user->role === 'admin') {
                return back()->with('error', 'This email is already an admin account.');
            }
            $user->update([
                'role'               => 'admin',
                'admin_permissions'  => $permissions,
                'email_verified_at'  => $user->email_verified_at ?? now(),
            ]);
            return back()->with('success', "\"{$user->fullname}\" has been promoted to admin.");
        }

        // New account — fullname + password become required
        $request->validate([
            'fullname' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ], [
            'fullname.required' => 'Full name is required when creating a new account.',
            'password.required' => 'Password is required when creating a new account.',
        ]);

        User::create([
            'fullname'          => $request->fullname,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'role'              => 'admin',
            'admin_permissions' => $permissions,
            'email_verified_at' => now(),
        ]);

        return back()->with('success', 'New admin account created successfully.');
    }

    public function removeAdmin(User $user)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot remove yourself as admin.');
        }
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super admin accounts cannot be demoted from this panel.');
        }

        $user->update([
            'role'               => 'user',
            'admin_permissions'  => null,
        ]);

        return back()->with('success', "\"{$user->fullname}\" has been removed from admin.");
    }

    public function updateAdminPermissions(Request $request, User $user)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super admin permissions cannot be changed here.');
        }

        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'in:users,feedbacks,reports',
        ]);

        $user->update([
            'admin_permissions' => array_values($request->input('permissions', [])),
        ]);

        return back()->with('success', "Permissions updated for \"{$user->fullname}\".");
    }
}