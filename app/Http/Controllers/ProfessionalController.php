<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfessionalController extends Controller
{
    /**
     * Display a listing of professionals with optional geolocation search.
     */
    public function index(Request $request): Response
    {
        $query = Professional::query()
            ->where('active', true)
            ->with('user');

        // Geolocation search if coordinates provided
        if ($request->has(['lat', 'lng'])) {
            $lat = (float) $request->input('lat');
            $lng = (float) $request->input('lng');
            $radiusKm = $request->input('radius', 50);

            $query->withinRadius($lat, $lng, $radiusKm);
        } else {
            $query->orderBy('rating', 'desc');
        }

        // Filter by specialty
        if ($request->has('specialty') && $request->specialty) {
            $query->where('specialty', 'like', '%' . $request->specialty . '%');
        }

        // Filter by verified
        if ($request->has('verified') && $request->verified) {
            $query->where('verified', true);
        }

        $professionals = $query->paginate(12)->withQueryString();

        return Inertia::render('Professionals/Index', [
            'professionals' => $professionals,
            'filters' => [
                'lat' => $request->input('lat'),
                'lng' => $request->input('lng'),
                'radius' => $request->input('radius', 50),
                'specialty' => $request->input('specialty'),
                'verified' => $request->input('verified'),
            ],
        ]);
    }

    /**
     * Display the specified professional's public profile.
     */
    public function show(Professional $professional): Response
    {
        $professional->load(['user']);

        // Get recent reviews/ratings (placeholder - would need reviews table)
        // For now, just show the professional data

        return Inertia::render('Professionals/Show', [
            'professional' => $professional,
        ]);
    }

    /**
     * Show the form for becoming a professional.
     */
    public function create(Request $request): Response
    {
        $user = $request->user();

        // Check if user is already a professional
        if ($user->professional) {
            return redirect()->route('professionals.edit')
                ->with('info', 'Você já possui um perfil profissional. Edite suas informações abaixo.');
        }

        return Inertia::render('Professionals/Create');
    }

    /**
     * Store a newly created professional profile.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Check if user is already a professional
        if ($user->professional) {
            return redirect()->route('professionals.edit')
                ->with('error', 'Você já possui um perfil profissional.');
        }

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'portfolio_url' => 'nullable|url|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'service_radius_km' => 'required|integer|min:1|max:500',
            'certifications' => 'nullable|array',
            'equipment' => 'nullable|array',
        ]);

        $professional = Professional::create([
            'user_id' => $user->id,
            'business_name' => $validated['business_name'],
            'specialty' => $validated['specialty'],
            'description' => $validated['description'],
            'portfolio_url' => $validated['portfolio_url'] ?? null,
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'service_radius_km' => $validated['service_radius_km'],
            'certifications' => $validated['certifications'] ?? [],
            'equipment' => $validated['equipment'] ?? [],
            'active' => true,
            'verified' => false, // Needs admin verification
            'rating' => 0,
            'total_reviews' => 0,
            'total_jobs' => 0,
        ]);

        // Update user role to professional
        $user->update(['role' => 'professional']);

        return redirect()->route('professionals.show', $professional)
            ->with('success', 'Perfil profissional criado com sucesso! Aguarde a verificação para começar a receber propostas.');
    }

    /**
     * Show the form for editing the professional profile.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        if (!$user->professional) {
            return redirect()->route('professionals.create')
                ->with('error', 'Você ainda não possui um perfil profissional.');
        }

        return Inertia::render('Professionals/Edit', [
            'professional' => $user->professional,
        ]);
    }

    /**
     * Update the specified professional profile.
     */
    public function update(Request $request, Professional $professional): RedirectResponse
    {
        $this->authorize('update', $professional);

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'portfolio_url' => 'nullable|url|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'service_radius_km' => 'required|integer|min:1|max:500',
            'certifications' => 'nullable|array',
            'equipment' => 'nullable|array',
            'active' => 'nullable|boolean',
        ]);

        $professional->update($validated);

        return redirect()->route('professionals.show', $professional)
            ->with('success', 'Perfil atualizado com sucesso!');
    }
}
