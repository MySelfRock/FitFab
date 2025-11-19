<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OfferController extends Controller
{
    /**
     * Display a listing of offers.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // If user is professional, show offers they made
        if ($user->isProfessional() && $user->professional) {
            $offers = Offer::where('professional_id', $user->professional->id)
                ->with(['project', 'project.user'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            // If regular user, show offers for their projects
            $projectIds = $user->projects()->pluck('id');
            $offers = Offer::whereIn('project_id', $projectIds)
                ->with(['professional.user', 'project'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return response()->json($offers);
    }

    /**
     * Store a newly created offer.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isProfessional() || !$user->professional) {
            return response()->json([
                'message' => 'Apenas profissionais podem criar orçamentos',
            ], 403);
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'price' => 'required|numeric|min:0',
            'estimated_days' => 'nullable|integer|min:1',
            'message' => 'nullable|string|max:1000',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $offer = Offer::create([
            'project_id' => $validated['project_id'],
            'professional_id' => $user->professional->id,
            'price' => $validated['price'],
            'estimated_days' => $validated['estimated_days'] ?? null,
            'message' => $validated['message'] ?? null,
            'expires_at' => $validated['expires_at'] ?? now()->addDays(7),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Orçamento enviado com sucesso',
            'offer' => $offer->load(['professional.user', 'project']),
        ], 201);
    }

    /**
     * Display the specified offer.
     */
    public function show(Offer $offer): JsonResponse
    {
        $this->authorize('view', $offer);

        $offer->load(['professional.user', 'project']);

        return response()->json([
            'offer' => $offer,
        ]);
    }

    /**
     * Update the specified offer.
     */
    public function update(Request $request, Offer $offer): JsonResponse
    {
        $this->authorize('update', $offer);

        $validated = $request->validate([
            'price' => 'sometimes|numeric|min:0',
            'estimated_days' => 'nullable|integer|min:1',
            'message' => 'nullable|string|max:1000',
        ]);

        $offer->update($validated);

        return response()->json([
            'message' => 'Orçamento atualizado com sucesso',
            'offer' => $offer,
        ]);
    }

    /**
     * Accept an offer and create order.
     */
    public function accept(Request $request, Offer $offer): JsonResponse
    {
        $this->authorize('accept', $offer);

        if ($offer->status !== 'pending') {
            return response()->json([
                'message' => 'Este orçamento não pode mais ser aceito',
            ], 400);
        }

        if ($offer->isExpired()) {
            return response()->json([
                'message' => 'Este orçamento expirou',
            ], 400);
        }

        // Update offer status
        $offer->update(['status' => 'accepted']);

        // Reject other offers for the same project
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

        return response()->json([
            'message' => 'Orçamento aceito com sucesso. Pedido criado.',
            'offer' => $offer,
            'order' => $order->load(['project', 'professional.user']),
        ]);
    }

    /**
     * Reject an offer.
     */
    public function reject(Request $request, Offer $offer): JsonResponse
    {
        $this->authorize('accept', $offer); // Same policy as accept

        $offer->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Orçamento rejeitado',
        ]);
    }
}
