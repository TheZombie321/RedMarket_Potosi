<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use Illuminate\Support\Facades\DB;
use App\Traits\RecordsStockMovements;

class StripeWebhookController extends Controller
{
    use RecordsStockMovements;

    public function handle(Request $request)
    {
        Stripe::setApiKey(config('stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            \Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $session = $event->data->object;

        if ($event->type === 'checkout.session.completed') {
            $this->handleCompleted($session);
        } elseif (in_array($event->type, [
            'checkout.session.expired',
            'checkout.session.async_payment_failed',
        ])) {
            $this->handleFailed($session);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function handleCompleted($session)
    {
        $pedidoId = $session->metadata->pedido_id ?? null;
        if (!$pedidoId) return;

        $pedido = Pedido::find($pedidoId);
        if (!$pedido || $pedido->payment_status === 'paid') return;

        $pedido->update([
            'payment_status' => 'paid',
            'stripe_session_id' => $session->id,
        ]);
    }

    protected function handleFailed($session)
    {
        $pedidoId = $session->metadata->pedido_id ?? null;
        if (!$pedidoId) return;

        DB::transaction(function () use ($pedidoId) {
            $pedido = Pedido::find($pedidoId);
            if (!$pedido || $pedido->payment_status === 'paid') return;

            $pedido->load('items.producto');
            foreach ($pedido->items as $item) {
                $this->recordMovement(
                    $item->producto, 'ingreso', $item->cantidad,
                    $pedido, null,
                    "Pago fallido - Pedido {$pedido->codigo}"
                );
            }

            $pedido->update([
                'payment_status' => 'failed',
                'estado' => 'cancelado',
            ]);
        });
    }
}
