<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Professional;
use App\Models\Project;
use App\Models\Template;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Display admin dashboard with statistics.
     */
    public function index(): Response
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'regular' => User::where('role', 'user')->count(),
                'professionals' => User::where('role', 'professional')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'recent' => User::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'projects' => [
                'total' => Project::count(),
                'pending' => Project::where('status', 'pending')->count(),
                'processing' => Project::where('status', 'processing')->count(),
                'completed' => Project::where('status', 'completed')->count(),
                'failed' => Project::where('status', 'failed')->count(),
                'recent' => Project::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'professionals' => [
                'total' => Professional::count(),
                'active' => Professional::where('active', true)->count(),
                'verified' => Professional::where('verified', true)->count(),
                'pending_verification' => Professional::where('verified', false)->count(),
            ],
            'offers' => [
                'total' => Offer::count(),
                'pending' => Offer::where('status', 'pending')->count(),
                'accepted' => Offer::where('status', 'accepted')->count(),
                'rejected' => Offer::where('status', 'rejected')->count(),
            ],
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'paid' => Order::where('status', 'paid')->count(),
                'in_progress' => Order::where('status', 'in_progress')->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
                'total_revenue' => Order::whereIn('status', ['paid', 'in_progress', 'completed'])->sum('platform_fee'),
            ],
            'templates' => [
                'total' => Template::count(),
                'active' => Template::where('active', true)->count(),
            ],
        ];

        // Recent activity
        $recentUsers = User::latest()->take(5)->get();
        $recentProjects = Project::with(['user', 'template'])->latest()->take(5)->get();
        $recentOrders = Order::with(['user', 'professional.user'])->latest()->take(5)->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentProjects' => $recentProjects,
            'recentOrders' => $recentOrders,
        ]);
    }
}
