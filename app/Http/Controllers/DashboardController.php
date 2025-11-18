<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $stats = [
            'total_projects' => $user->projects()->count(),
            'ready_projects' => $user->projects()->where('status', 'ready')->count(),
            'processing_projects' => $user->projects()->where('status', 'processing')->count(),
        ];

        $recent_projects = $user->projects()
            ->latest()
            ->limit(5)
            ->with(['template', 'material'])
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recent_projects' => $recent_projects,
        ]);
    }
}
