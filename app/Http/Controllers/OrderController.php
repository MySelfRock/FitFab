<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Order::query();

        if ($user->isProfessional()) {
            // Professional: orders they're fulfilling
            $query->where('professional_id', $user->professional->id);
        } else {
            // User: orders they've created
            $query->where('user_id', $user->id);
        }

        $orders = $query->with([
            'project.template',
            'project.material',
            'professional.user',
            'user',
            'offer',
        ])
            ->latest()
            ->paginate(15);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'isProfessional' => $user->isProfessional(),
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load([
            'project.user',
            'project.template',
            'project.material',
            'project.pieces',
            'project.sheets',
            'professional.user',
            'user',
            'offer',
        ]);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Initiate payment for an order.
     */
    public function pay(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if ($order->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Este pedido não pode ser pago no momento.');
        }

        // TODO: Integrate with payment gateway (Stripe/PagSeguro)
        // For now, just mark as paid (placeholder)

        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pagamento realizado com sucesso! O profissional foi notificado.');
    }

    /**
     * Mark order as completed.
     */
    public function complete(Request $request, Order $order): RedirectResponse
    {
        // Only professional or user can complete
        $user = $request->user();

        if ($user->id !== $order->user_id && (!$user->isProfessional() || $user->professional->id !== $order->professional_id)) {
            abort(403, 'Não autorizado.');
        }

        if ($order->status !== 'paid' && $order->status !== 'in_progress') {
            return redirect()->back()
                ->with('error', 'Este pedido não pode ser marcado como concluído.');
        }

        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Update professional stats
        $professional = $order->professional;
        $professional->increment('total_jobs');

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pedido marcado como concluído!');
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if (!in_array($order->status, ['pending', 'paid'])) {
            return redirect()->back()
                ->with('error', 'Este pedido não pode ser cancelado.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $order->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('orders.index')
            ->with('success', 'Pedido cancelado.');
    }

    /**
     * Get order statistics for the user.
     */
    public function stats(Request $request): Response
    {
        $user = $request->user();

        if ($user->isProfessional()) {
            // Professional stats
            $stats = [
                'total' => Order::where('professional_id', $user->professional->id)->count(),
                'pending' => Order::where('professional_id', $user->professional->id)->where('status', 'pending')->count(),
                'paid' => Order::where('professional_id', $user->professional->id)->where('status', 'paid')->count(),
                'in_progress' => Order::where('professional_id', $user->professional->id)->where('status', 'in_progress')->count(),
                'completed' => Order::where('professional_id', $user->professional->id)->where('status', 'completed')->count(),
                'total_earned' => Order::where('professional_id', $user->professional->id)
                    ->where('status', 'completed')
                    ->sum('amount'),
            ];
        } else {
            // User stats
            $stats = [
                'total' => Order::where('user_id', $user->id)->count(),
                'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
                'paid' => Order::where('user_id', $user->id)->where('status', 'paid')->count(),
                'in_progress' => Order::where('user_id', $user->id)->where('status', 'in_progress')->count(),
                'completed' => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
                'total_spent' => Order::where('user_id', $user->id)
                    ->whereIn('status', ['paid', 'in_progress', 'completed'])
                    ->sum('amount'),
            ];
        }

        return Inertia::render('Orders/Stats', [
            'stats' => $stats,
            'isProfessional' => $user->isProfessional(),
        ]);
    }
}
