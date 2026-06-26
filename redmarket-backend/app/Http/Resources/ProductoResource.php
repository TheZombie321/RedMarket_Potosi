<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio_venta' => $this->precio_venta,
            'precio_oferta' => $this->precio_oferta,
            'en_descuento' => $this->en_descuento,
            'stock_actual' => $this->stock_actual,
            'unidad_medida' => $this->unidad_medida,
            'imagen_url' => $this->imagen_url,
            'es_perecedero' => $this->es_perecedero,
            'categoria_id' => $this->categoria_id,
            'categoria' => $this->whenLoaded('categoria', fn() => [
                'id' => $this->categoria->id,
                'nombre' => $this->categoria->nombre,
            ]),
        ];

        // Staff (Admin/Encargado) pueden ver campos internos
        if ($request->user()?->hasAnyRole(['Administrador', 'Encargado'])) {
            $data['codigo_barras'] = $this->codigo_barras;
            $data['precio_compra'] = $this->precio_compra;
            $data['pasillo'] = $this->pasillo;
            $data['nivel'] = $this->nivel;
            $data['stock_minimo'] = $this->stock_minimo;
        }

        return $data;
    }
}
