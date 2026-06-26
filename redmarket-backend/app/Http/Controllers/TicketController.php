<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketController extends Controller
{
    public function ticket(Request $request, Pedido $pedido)
    {
        $user = $this->resolveUser($request);
        if (!$user || !$user->can('view', $pedido)) {
            abort(403);
        }

        Auth::onceUsingId($user->id);

        $pedido->load(['items.producto', 'user']);

        $pdf = Pdf::loadView('pdfs.ticket', [
            'pedido' => $pedido,
            'items' => $pedido->items,
            'cliente' => $pedido->user,
        ]);

        return $pdf->download("ticket-{$pedido->codigo}.pdf");
    }

    private function resolveUser(Request $request)
    {
        if ($token = $request->query('token')) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                return $accessToken->tokenable;
            }
        }

        return $request->user();
    }
}
