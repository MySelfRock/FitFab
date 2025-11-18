<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyProsJob;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    /**
     * Display a listing of offers.
     * Shows different views for users (received offers) and professionals (sent offers).
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->isProfessional()) {
            // Professional: show offers they've sent
            $offers = Offer::where('professional_id', $user->professional->id)
                ->with(['project.user', 'project.template', 'project.material'])
                ->latest()
                ->paginate(15);
        } else {
            // User: show offers received on their projects
            $offers = Offer::whereHas('project', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->with(['professional.user', 'project'])
                ->latest()
                ->paginate(15);
        }

        return Inertia::render('Offers/Index', [
            'offers' => $offers,
            'isProfessional' => $user->isProfessional(),
        ]);
    }

    /**
     * Show the form for creating a new offer (professionals only).
     */
    public function create(Request $request): Response
    {
        $user = $request->user();

        if (!$user->isProfessional()) {
            return redirect()->route('dashboard')
                ->with('error', 'Apenas profissionais podem criar ofertas.');
        }

        $projectId = $request->query('project_id');
        $project = null;

        if ($projectId) {
            $project = Project::with(['user', 'template', 'material', 'pieces'])
                ->findOrFail($projectId);

            // Check if professional already made an offer
            $existingOffer = Offer::where('project_id', $project->id)
                ->where('professional_id', $user->professional->id)
                ->first();

            if ($existingOffer) {
                return redirect()->route('offers.index')
                    ->with('info', 'Você já enviou uma proposta para este projeto.');
            }
        }

        return Inertia::render('Offers/Create', [
            'project' => $project,
        ]);
    }

    /**
     * Store a newly created offer.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isProfessional()) {
            return redirect()->route('dashboard')
                ->with('error', 'Apenas profissionais podem criar ofertas.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'price' => 'required|numeric|min:0',
            'delivery_days' => 'required|integer|min:1|max:365',
            'message' => 'required|string|max:1000',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        // Check if already offered
        $existingOffer = Offer::where('project_id', $project->id)
            ->where('professional_id', $user->professional->id)
            ->first();

        if ($existingOffer) {
            return redirect()->route('offers.index')
                ->with('error', 'Você já enviou uma proposta para este projeto.');
        }

        $offer = Offer::create([
            'project_id' => $validated['project_id'],
            'professional_id' => $user->professional->id,
            'price' => $validated['price'],
            'delivery_days' => $validated['delivery_days'],
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        return redirect()->route('offers.index')
            ->with('success', 'Proposta enviada com sucesso!');
    }

    /**
     * Display the specified offer.
     */
    public function show(Offer $offer): Response
    {
        $this->authorize('view', $offer);

        $offer->load(['project.user', 'project.template', 'project.material', 'project.pieces', 'professional.user']);

        return Inertia::render('Offers/Show', [
            'offer' => $offer,
        ]);
    }

    /**
     * Accept an offer (creates an order).
     */
    public function accept(Request $request, Offer $offer): RedirectResponse
    {
        $this->authorize('accept', $offer);

        if ($offer->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Esta proposta não pode mais ser aceita.');
        }

        // Update offer status
        $offer->update(['status' => 'accepted']);

        // Reject other pending offers for this project
        Offer::where('project_id', $offer->project_id)
            ->where('id', '!=', $offer->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        // Create order
        $order = Order::create([
            'project_id' => $offer->project_id,
            'user_id' => $request->user()->id,
            'professional_id' => $offer->professional_id,
            'offer_id' => $offer->id,
            'amount' => $offer->price,
            'platform_fee' => $offer->price * 0.15, // 15% platform fee
            'status' => 'pending',
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Proposta aceita! Prossiga para o pagamento.');
    }

    /**
     * Reject an offer.
     */
    public function reject(Request $request, Offer $offer): RedirectResponse
    {
        $this->authorize('accept', $offer); // Same permission as accept

        if ($offer->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Esta proposta não pode ser rejeitada.');
        }

        $offer->update(['status' => 'rejected']);

        return redirect()->route('offers.index')
            ->with('success', 'Proposta rejeitada.');
    }

    /**
     * Request quotes from professionals for a project.
     */
    public function requestQuotes(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius_km' => 'nullable|integer|min:1|max:200',
        ]);

        $radiusKm = $validated['radius_km'] ?? 50;

        // Dispatch job to notify professionals
        NotifyProsJob::dispatch($project, $validated['lat'], $validated['lng'], $radiusKm);

        return redirect()->route('projects.show', $project)
            ->with('success', "Notificação enviada para profissionais em um raio de {$radiusKm}km!");
    }
}
