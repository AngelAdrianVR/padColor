<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\BasicNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ChangeRequestController extends Controller
{
    public function decide(Request $request, ChangeRequest $changeRequest)
    {
        $validated = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'comments' => 'nullable|string|max:1000',
        ]);

        $changeRequest->reviewers()->updateExistingPivot(Auth::id(), [
            'status' => $validated['decision'],
            'comments' => $validated['comments'],
        ]);

        $product = $changeRequest->product;
        $reviewer = Auth::user();

        // --- NOTIFICACIÓN 2: Notificar que un revisor ha votado ---
        $decisionText = $validated['decision'] === 'approved' ? 'aprobado' : 'rechazado';
        $subject = "Un revisor ha votado en la solicitud para \"{$product->name}\"";
        $description = "ha {$decisionText} los cambios propuestos.";
        $url = route('products.show', $product);

        // Notificar al solicitante
        $changeRequest->requester->notify(new BasicNotification($subject, $description, $reviewer->name, $reviewer->profile_photo_url, $url));
        
        // Notificar a los OTROS revisores
        $otherReviewers = $changeRequest->reviewers()->where('user_id', '!=', $reviewer->id)->get();
        if ($otherReviewers->isNotEmpty()) {
            Notification::send($otherReviewers, new BasicNotification($subject, $description, $reviewer->name, $reviewer->profile_photo_url, $url));
        }

        // --- LÓGICA DE DECISIÓN FINAL ---
        $totalReviewers = $changeRequest->reviewers()->count();
        $votedReviewers = $changeRequest->reviewers()->wherePivot('status', '!=', 'pending')->count();

        if ($totalReviewers > 0 && $totalReviewers === $votedReviewers) {
            $approvals = $changeRequest->reviewers()->wherePivot('status', 'approved')->count();
            $rejections = $totalReviewers - $approvals;

            if ($approvals > $rejections) {
                $this->finalizeApproval($changeRequest);
                return back()->with('success', 'Voto registrado. ¡La solicitud ha sido APROBADA por mayoría y los cambios han sido aplicados!');
            } else {
                $this->finalizeRejection($changeRequest, 'Rechazada por mayoría de votos.');
                return back()->with('info', 'Voto registrado. La solicitud ha sido RECHAZADA por mayoría.');
            }
        }

        return back()->with('success', 'Tu voto ha sido registrado. Esperando a los demás revisores.');
    }

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
            $changeRequest->approved_by = Auth::id();
            $changeRequest->decided_at = now();
            $changeRequest->save();
            
            // --- NOTIFICACIÓN 3.1: Notificar al solicitante que se APROBARON los cambios ---
            $finalizer = Auth::user();
            $subject = "¡Solicitud Aprobada! Cambios aplicados para \"{$product->name}\"";
            $description = "ha emitido el voto final: Aprobando por mayoría. Los cambios ya están visibles en la ficha técnica.";
            $url = route('products.show', $product);
            $changeRequest->requester->notify(new BasicNotification($subject, $description, $finalizer->name, $finalizer->profile_photo_url, $url));
        });
    }

    private function finalizeRejection(ChangeRequest $changeRequest, string $comments = '')
    {
        DB::transaction(function () use ($changeRequest, $comments) {
            $changeRequest->clearMediaCollection('pending_documents');

            $changeRequest->status = 'rejected';
            $changeRequest->approved_by = Auth::id();
            $changeRequest->decided_at = now();
            $changeRequest->comments = $comments;
            $changeRequest->save();

            // --- NOTIFICACIÓN 3.2: Notificar al solicitante que se RECHAZARON los cambios ---
            $product = $changeRequest->product;
            $finalizer = Auth::user();
            $subject = "Solicitud Rechazada para \"{$product->name}\"";
            $description = "ha emitido el voto final: Rechazando por mayoría. No se ha aplicado ninguna modificación.";
            $url = route('products.show', $product);
            $changeRequest->requester->notify(new BasicNotification($subject, $description, $finalizer->name, $finalizer->profile_photo_url, $url));
        });
    }
}