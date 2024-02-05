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
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'expired_date' => $this->expired_date?->isoFormat('DD MMM YYYY'),
            'user' => $this->whenLoaded('user'),
            'responsible' => $this->whenLoaded('responsible'),
            'category' => $this->whenLoaded('category'),
            'media' => $this->getMedia()->all(),
            'created_at' => $this->created_at?->isoFormat('DD MMM YYYY h:mm'),
            'updated_at' => $this->created_at?->isoFormat('DD MMM YYYY h:mm'),
        ];
    }
}
