<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $pedido->codigo }}</title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 12px; width: 280px; margin: 0 auto; padding: 10px; }
        h1 { text-align: center; font-size: 16px; margin: 0 0 5px; }
        .header { text-align: center; border-bottom: 1px dashed #333; padding-bottom: 8px; margin-bottom: 8px; }
        .header p { margin: 2px 0; font-size: 11px; }
        .item { border-bottom: 1px dotted #ccc; padding: 4px 0; }
        .item p { margin: 1px 0; }
        .item-nombre { font-weight: bold; font-size: 11px; }
        .item-detalle { font-size: 10px; color: #555; }
        .totales { margin-top: 8px; padding-top: 8px; border-top: 1px dashed #333; }
        .total-line { display: flex; justify-content: space-between; font-size: 11px; padding: 2px 0; }
        .total-final { font-size: 14px; font-weight: bold; border-top: 1px solid #333; padding-top: 4px; margin-top: 4px; }
        .footer { text-align: center; margin-top: 12px; font-size: 10px; color: #888; border-top: 1px dashed #333; padding-top: 8px; }
        .badge { display: inline-block; padding: 2px 6px; border: 1px solid #333; font-size: 10px; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REDMARKET POTOSÍ</h1>
        <p>Tu tienda virtual de confianza</p>
        <p style="font-size:10px">Potosí - Bolivia</p>
        <p style="margin-top:6px"><strong>{{ $pedido->codigo }}</strong></p>
        <p>{{ now()->format('d/m/Y H:i') }}</p>
        <p style="margin-top:4px">
            @if($pedido->payment_method === 'stripe')
                💳 Pagado con tarjeta
            @else
                💵 Efectivo contra entrega
            @endif
        </p>
        @if($pedido->payment_status === 'paid')
            <span class="badge">PAGADO</span>
        @endif
    </div>

    <p><strong>Cliente:</strong> {{ $cliente->name }}</p>
    <p><strong>Dirección:</strong> {{ $pedido->direccion_texto }}</p>

    <div style="border-bottom:1px dashed #333; margin: 8px 0;"></div>

    @foreach($items as $item)
    <div class="item">
        <p class="item-nombre">{{ $item->producto->nombre ?? 'Producto' }}</p>
        <p class="item-detalle">
            Bs. {{ number_format($item->precio_unitario, 2) }} x {{ $item->cantidad }}
            = <strong>Bs. {{ number_format($item->precio_unitario * $item->cantidad, 2) }}</strong>
        </p>
    </div>
    @endforeach

    <div class="totales">
        <div class="total-line"><span>Subtotal</span><span>Bs. {{ number_format($pedido->total_productos, 2) }}</span></div>
        <div class="total-line"><span>Delivery</span><span>Bs. {{ number_format($pedido->delivery_fee, 2) }}</span></div>
        <div class="total-line total-final"><span>TOTAL</span><span>Bs. {{ number_format($pedido->total_final, 2) }}</span></div>
    </div>

    <div class="footer">
        <p>¡Gracias por tu compra!</p>
        <p>RedMarket Potosí</p>
    </div>
</body>
</html>
