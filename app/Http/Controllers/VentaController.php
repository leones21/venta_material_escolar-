<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index()
    {
        return Venta::with('detalles.producto')->get();
    }

    public function show($id)
    {
        $venta = Venta::with('detalles.producto')->find($id);
        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }
        return $venta;
    }

    public function store(Request $request)
    {
        $venta = new Venta();
        $venta->fecha = now();
        $venta->total = 0; 
        $venta->save();

        $total = 0;
        foreach ($request->detalles as $detalle) {
            $producto = Producto::find($detalle['producto_id']);
            if ($producto) {
                $detalleVenta = new DetalleVenta();
                $detalleVenta->venta_id = $venta->id;
                $detalleVenta->producto_id = $producto->id;
                $detalleVenta->cantidad = $detalle['cantidad'];
                $detalleVenta->precio = $producto->precio * $detalle['cantidad'];
                $detalleVenta->save();

                $total += $detalleVenta->precio;
            }
        }

        $venta->total = $total;
        $venta->save();

        return response()->json($venta->load('detalles.producto'), 201);
    }

    public function update(Request $request, $id)
    {
        $venta = Venta::find($id);
        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }

        return response()->json($venta, 200);
    }

    public function destroy($id)
    {
        $venta = Venta::find($id);
        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }

        $venta->delete();
        return response()->json(['message' => 'Venta eliminada'], 200);
    }
}
