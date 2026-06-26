<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orden de Compra {{ $orden->codigo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; margin: 20px; }
        h1 { text-align: center; font-size: 18px; margin: 0 0 4px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 10px; }
        .header p { margin: 2px 0; }
        .datos { margin-bottom: 12px; }
        .datos table { width: 100%; }
        .datos td { padding: 2px 5px; font-size: 11px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items th { background: #eee; padding: 6px 4px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.items td { padding: 5px 4px; border-bottom: 1px solid #ddd; font-size: 11px; }
        table.items .right { text-align: right; }
        .totales { margin-top: 10px; text-align: right; }
        .totales p { margin: 2px 0; }
        .total-final { font-size: 16px; font-weight: bold; border-top: 2px solid #333; padding-top: 6px; margin-top: 6px; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #666; }
        .badge { display: inline-block; padding: 2px 8px; border: 1px solid #333; font-size: 10px; border-radius: 3px; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REDMARKET POTOSÍ</h1>
        <p><strong>ORDEN DE COMPRA</strong> Nro. {{ $orden->codigo }}</p>
        <p>Fecha: {{ $orden->created_at->format('d/m/Y H:i') }}</p>
        <span class="badge">{{ ucfirst($orden->estado) }}</span>
    </div>

    <div class="datos">
        <table>
            <tr><td><strong>Proveedor:</strong></td><td>{{ $orden->proveedor->nombre }}</td></tr>
            <tr><td><strong>Contacto:</strong></td><td>{{ $orden->proveedor->contacto ?? '—' }}</td></tr>
            <tr><td><strong>Creado por:</strong></td><td>{{ $orden->user->name }}</td></tr>
            @if($orden->notas)<tr><td><strong>Notas:</strong></td><td>{{ $orden->notas }}</td></tr>@endif
        </table>
    </div>

    <table class="items">
        <thead>
            <tr><th>Cant.</th><th>Producto</th><th class="right">P. Unit.</th><th class="right">Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach($orden->items as $item)
            <tr>
                <td>{{ $item->cantidad }}</td>
                <td>{{ $item->producto->nombre ?? 'Producto' }}</td>
                <td class="right">Bs. {{ number_format($item->precio_unitario, 2) }}</td>
                <td class="right">Bs. {{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <p class="total-final">Total: Bs. {{ number_format($orden->total, 2) }}</p>
    </div>

    <div class="footer">
        <p>RedMarket Potosí — Tu tienda virtual de confianza</p>
    </div>
</body>
</html>
