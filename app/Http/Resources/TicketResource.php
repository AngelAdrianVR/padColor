<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => str_pad($this->id, 2, "0", STR_PAD_LEFT),
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'expired_date' => $this->expired_date?->isoFormat('DD MMM YYYY'),
            'user' => $this->whenLoaded('user'),
            'responsible' => $this->whenLoaded('responsible'),
            'category' => $this->whenLoaded('category'),
            'solutions_count' => $this->whenCounted('ticketSolutions'),
            'media' => $this->getMedia()->all(),
            'created_at' => $this->created_at?->isoFormat('DD MMM YYYY'),
            'updated_at' => $this->updated_at?->isoFormat('DD MMM YYYY'),
        ];
    }
}
