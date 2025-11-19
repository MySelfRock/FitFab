<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Professional;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfessionalVerificationController extends Controller
{
    /**
     * Display professionals pending verification.
     */
    public function index(Request $request): Response
    {
        $query = Professional::with('user');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->where('verified', true);
            } elseif ($request->status === 'pending') {
                $query->where('verified', false);
            }
        }

        // Filter by active
        if ($request->filled('active')) {
            $query->where('active', $request->active === 'true');
        }

        $professionals = $query->withCount(['offers', 'orders'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Professionals/Index', [
            'professionals' => $professionals,
            'filters' => $request->only(['status', 'active']),
        ]);
    }

    /**
     * Display the specified professional for verification.
     */
    public function show(Professional $professional): Response
    {
        $professional->load([
            'user',
            'offers.project',
            'orders.project',
        ]);

        $stats = [
            'total_offers' => $professional->offers()->count(),
            'accepted_offers' => $professional->offers()->where('status', 'accepted')->count(),
            'total_orders' => $professional->orders()->count(),
            'completed_orders' => $professional->orders()->where('status', 'completed')->count(),
            'total_earned' => $professional->orders()->where('status', 'completed')->sum('amount'),
            'acceptance_rate' => $professional->offers()->count() > 0
                ? round(($professional->offers()->where('status', 'accepted')->count() / $professional->offers()->count()) * 100, 2)
                : 0,
        ];

        return Inertia::render('Admin/Professionals/Show', [
            'professional' => $professional,
            'stats' => $stats,
        ]);
    }

    /**
     * Verify a professional.
     */
    public function verify(Professional $professional): RedirectResponse
    {
        if ($professional->verified) {
            return redirect()->back()
                ->with('error', 'Este profissional já está verificado.');
        }

        $professional->update([
            'verified' => true,
            'verified_at' => now(),
        ]);

        // Send notification email to professional
        $professional->load('user');
        $professional->user->notify(new \App\Notifications\ProfessionalVerifiedNotification($professional));

        return redirect()->back()
            ->with('success', 'Profissional verificado com sucesso!');
    }

    /**
     * Unverify a professional.
     */
    public function unverify(Professional $professional): RedirectResponse
    {
        if (!$professional->verified) {
            return redirect()->back()
                ->with('error', 'Este profissional não está verificado.');
        }

        $professional->update([
            'verified' => false,
            'verified_at' => null,
        ]);

        return redirect()->back()
            ->with('success', 'Verificação removida com sucesso!');
    }

    /**
     * Toggle professional active status.
     */
    public function toggleActive(Professional $professional): RedirectResponse
    {
        $professional->update([
            'active' => !$professional->active,
        ]);

        $status = $professional->active ? 'ativado' : 'desativado';

        return redirect()->back()
            ->with('success', "Profissional {$status} com sucesso!");
    }

    /**
     * Update professional verification notes.
     */
    public function updateNotes(Request $request, Professional $professional): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $professional->update($validated);

        return redirect()->back()
            ->with('success', 'Observações atualizadas com sucesso!');
    }
}
