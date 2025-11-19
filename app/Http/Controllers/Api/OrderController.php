<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Order::with(['project', 'professional.user', 'offer'])
            ->where('user_id', $user->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($orders);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['project', 'professional.user', 'offer']);

        return response()->json([
            'order' => $order,
        ]);
    }

    /**
     * Initiate payment for order.
     */
    public function pay(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Este pedido não pode ser pago',
            ], 400);
        }

        // TODO: Integrate with payment provider (Stripe, PagSeguro, etc)
        // For now, return placeholder checkout URL

        $checkoutUrl = route('orders.checkout', ['order' => $order->id]);

        return response()->json([
            'message' => 'Redirecionando para pagamento',
            'checkout_url' => $checkoutUrl,
            'order' => $order,
        ]);
    }

    /**
     * Mark order as completed.
     */
    public function complete(Request $request, Order $order): JsonResponse
    {
        // Only professional or admin can complete
        $user = $request->user();

        if (!$user->isAdmin() && (!$user->isProfessional() || $order->professional_id !== $user->professional->id)) {
            return response()->json([
                'message' => 'Não autorizado',
            ], 403);
        }

        if ($order->status !== 'in_progress') {
            return response()->json([
                'message' => 'Pedido não está em andamento',
            ], 400);
        }

        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Pedido marcado como concluído',
            'order' => $order,
        ]);
    }

    /**
     * Cancel order.
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        if ($order->isPaid()) {
            return response()->json([
                'message' => 'Pedidos pagos não podem ser cancelados. Solicite reembolso.',
            ], 400);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Pedido cancelado com sucesso',
        ]);
    }

    /**
     * Get order statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'total' => Order::where('user_id', $user->id)->count(),
            'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'paid' => Order::where('user_id', $user->id)->paid()->count(),
            'completed' => Order::where('user_id', $user->id)->completed()->count(),
            'total_spent' => Order::where('user_id', $user->id)->paid()->sum('amount'),
        ];

        return response()->json($stats);
    }
}
