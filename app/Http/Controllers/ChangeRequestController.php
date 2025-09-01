<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChangeRequestController extends Controller
{
    /**
     * Registra la decisión de un revisor (aprobar/rechazar) para una solicitud.
     * Si es el último voto, finaliza la solicitud basándose en la mayoría.
     */
    public function decide(Request $request, ChangeRequest $changeRequest)
    {
        // 1. Validar la entrada (la decisión y los comentarios opcionales).
        $validated = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'comments' => 'nullable|string|max:1000',
        ]);

        // 2. Actualizar el voto del revisor actual en la tabla pivote.
        $changeRequest->reviewers()->updateExistingPivot(Auth::id(), [
            'status' => $validated['decision'],
            'comments' => $validated['comments'],
        ]);

        // 3. Comprobar si todos los revisores asignados ya han votado.
        $totalReviewers = $changeRequest->reviewers()->count();
        $votedReviewers = $changeRequest->reviewers()->wherePivot('status', '!=', 'pending')->count();

        // Si el número de votos es igual al total de revisores, es hora de decidir.
        if ($totalReviewers > 0 && $totalReviewers === $votedReviewers) {
            $approvals = $changeRequest->reviewers()->wherePivot('status', 'approved')->count();
            $rejections = $totalReviewers - $approvals;

            if ($approvals > $rejections) {
                // Ganan las aprobaciones.
                $this->finalizeApproval($changeRequest);
                return back()->with('success', 'Voto registrado. ¡La solicitud ha sido APROBADA por mayoría y los cambios han sido aplicados!');
            } else {
                // Ganan los rechazos o hay un empate.
                $this->finalizeRejection($changeRequest, 'Rechazada por mayoría de votos.');
                return back()->with('info', 'Voto registrado. La solicitud ha sido RECHAZADA por mayoría.');
            }
        }

        // Si aún no han votado todos, simplemente se registra el voto.
        return back()->with('success', 'Tu voto ha sido registrado. Esperando a los demás revisores.');
    }

    /**
     * Lógica interna para aplicar los cambios de una solicitud aprobada.
     */
    private function finalizeApproval(ChangeRequest $changeRequest)
    {
        DB::transaction(function () use ($changeRequest) {
            $product = $changeRequest->product;

            $product->sheet_data = $changeRequest->data;
            $product->save();

            $pendingMedia = $changeRequest->getMedia('pending_documents');
            foreach ($pendingMedia as $mediaItem) {
                $mediaItem->move($product, 'documents');
            }

            $changeRequest->status = 'approved';
            $changeRequest->approved_by = Auth::id(); // Registra al último votante como el que cerró la solicitud.
            $changeRequest->decided_at = now();
            $changeRequest->save();
        });
    }

    /**
     * Lógica interna para descartar los cambios de una solicitud rechazada.
     */
    private function finalizeRejection(ChangeRequest $changeRequest, string $comments = '')
    {
        DB::transaction(function () use ($changeRequest, $comments) {
            $changeRequest->clearMediaCollection('pending_documents');

            $changeRequest->status = 'rejected';
            $changeRequest->approved_by = Auth::id();
            $changeRequest->decided_at = now();
            $changeRequest->comments = $comments;
            $changeRequest->save();
        });
    }
}