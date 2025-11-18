<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): Response
    {
        $query = User::query();

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->withCount(['projects', 'orders'])
            ->with('professional')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['role', 'search']),
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        $user->load([
            'professional',
            'projects.template',
            'projects.material',
            'orders.project',
        ]);

        $stats = [
            'projects_count' => $user->projects()->count(),
            'orders_count' => $user->orders()->count(),
            'total_spent' => $user->orders()
                ->whereIn('status', ['paid', 'in_progress', 'completed'])
                ->sum('amount'),
        ];

        if ($user->isProfessional()) {
            $professional = $user->professional;
            $stats['professional_orders'] = $professional->orders()->count();
            $stats['professional_revenue'] = $professional->orders()
                ->where('status', 'completed')
                ->sum('amount');
            $stats['pending_offers'] = $professional->offers()->where('status', 'pending')->count();
        }

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|in:user,professional,admin',
        ]);

        // Prevent self-demotion from admin
        if ($request->user()->id === $user->id && $validated['role'] !== 'admin') {
            return redirect()->back()
                ->with('error', 'Você não pode alterar seu próprio nível de acesso.');
        }

        $user->update(['role' => $validated['role']]);

        return redirect()->back()
            ->with('success', 'Nível de acesso atualizado com sucesso!');
    }

    /**
     * Suspend or activate user.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        // Prevent self-suspension
        if (request()->user()->id === $user->id) {
            return redirect()->back()
                ->with('error', 'Você não pode suspender sua própria conta.');
        }

        // Toggle email_verified_at to simulate suspension
        // In production, you'd want a dedicated 'suspended' column
        $suspended = $user->email_verified_at === null;
        $user->update([
            'email_verified_at' => $suspended ? now() : null,
        ]);

        $status = $suspended ? 'ativado' : 'suspenso';

        return redirect()->back()
            ->with('success', "Usuário {$status} com sucesso!");
    }

    /**
     * Delete user account.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent self-deletion
        if (request()->user()->id === $user->id) {
            return redirect()->back()
                ->with('error', 'Você não pode excluir sua própria conta.');
        }

        // Check for active orders
        if ($user->orders()->whereIn('status', ['pending', 'paid', 'in_progress'])->exists()) {
            return redirect()->back()
                ->with('error', 'Não é possível excluir usuário com pedidos ativos.');
        }

        if ($user->isProfessional()) {
            $professional = $user->professional;
            if ($professional->orders()->whereIn('status', ['pending', 'paid', 'in_progress'])->exists()) {
                return redirect()->back()
                    ->with('error', 'Não é possível excluir profissional com pedidos ativos.');
            }
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }
}
