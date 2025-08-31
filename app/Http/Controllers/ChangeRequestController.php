<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ChangeRequestController extends Controller
{
    /**
     * Muestra una lista de todas las solicitudes de cambio.
     */
    public function index()
    {
        // Cargar las solicitudes con sus relaciones para mostrar información útil en el frontend.
        $changeRequests = ChangeRequest::with(['product', 'requester'])
            ->latest()
            ->paginate(20);

        return Inertia::render('ChangeRequest/Index', [
            'changeRequests' => $changeRequests,
        ]);
    }

    /**
     * Aprueba una solicitud de cambio y aplica los datos al producto.
     */
    public function approve(ChangeRequest $changeRequest)
    {
        // Iniciar una transacción para asegurar la integridad de los datos.
        // Si algo falla, se revierte todo.
        DB::transaction(function () use ($changeRequest) {
            $product = $changeRequest->product;

            // 1. Actualizar la ficha técnica del producto con los datos de la solicitud.
            $product->sheet_data = $changeRequest->data;
            $product->save();

            // 2. Mover los archivos multimedia pendientes de la solicitud al producto.
            $pendingMedia = $changeRequest->getMedia('pending_documents');
            foreach ($pendingMedia as $mediaItem) {
                // Mueve el archivo a la colección 'documents' del producto.
                $mediaItem->move($product, 'documents');
            }

            // 3. Actualizar el estado de la solicitud de cambio.
            $changeRequest->status = 'approved';
            $changeRequest->approved_by = Auth::id();
            $changeRequest->decided_at = now();
            $changeRequest->save();
        });

        return to_route('change-requests.index')->with('success', 'Solicitud aprobada y cambios aplicados.');
    }

    /**
     * Rechaza una solicitud de cambio.
     */
    public function reject(Request $request, ChangeRequest $changeRequest)
    {
        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($changeRequest, $request) {
            // 1. Eliminar los archivos multimedia asociados a esta solicitud, ya que no se usarán.
            $changeRequest->clearMediaCollection('pending_documents');

            // 2. Actualizar el estado de la solicitud.
            $changeRequest->status = 'rejected';
            $changeRequest->approved_by = Auth::id(); // Guardamos quién tomó la decisión.
            $changeRequest->decided_at = now();
            $changeRequest->comments = $request->input('comments'); // Guardar comentarios del rechazo
            $changeRequest->save();
        });

        return to_route('change-requests.index')->with('info', 'La solicitud ha sido rechazada.');
    }
}