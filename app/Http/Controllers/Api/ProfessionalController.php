<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Professional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    /**
     * Search professionals by location.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:200',
        ]);

        $query = Professional::approved()->with('user');

        // Filter by location if provided
        if (isset($validated['lat']) && isset($validated['lng'])) {
            $radius = $validated['radius'] ?? 50;
            $query->withinRadius($validated['lat'], $validated['lng'], $radius);
        }

        $professionals = $query->paginate(20);

        return response()->json($professionals);
    }

    /**
     * Display the specified professional.
     */
    public function show(Professional $professional): JsonResponse
    {
        $professional->load(['user', 'offers' => function ($query) {
            $query->where('status', 'accepted')->count();
        }]);

        return response()->json([
            'professional' => $professional,
        ]);
    }

    /**
     * Create or update professional profile.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:2',
            'postal_code' => 'required|string|max:10',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'service_radius_km' => 'nullable|integer|min:1|max:200',
            'services' => 'required|array',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();

        // Update user role to professional
        $user->update(['role' => 'professional']);

        $professional = Professional::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($validated, ['approved' => false])
        );

        return response()->json([
            'message' => 'Perfil profissional criado/atualizado com sucesso',
            'professional' => $professional,
        ], 201);
    }

    /**
     * Update the professional profile.
     */
    public function update(Request $request, Professional $professional): JsonResponse
    {
        $this->authorize('update', $professional);

        $validated = $request->validate([
            'business_name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:2',
            'postal_code' => 'sometimes|string|max:10',
            'lat' => 'sometimes|numeric|between:-90,90',
            'lng' => 'sometimes|numeric|between:-180,180',
            'service_radius_km' => 'nullable|integer|min:1|max:200',
            'services' => 'sometimes|array',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $professional->update($validated);

        return response()->json([
            'message' => 'Perfil atualizado com sucesso',
            'professional' => $professional,
        ]);
    }
}
