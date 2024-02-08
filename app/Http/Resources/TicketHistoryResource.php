<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketHistoryResource extends JsonResource
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
            'description' => $this->description,
            'user' => $this->whenLoaded('user'),
            'ticket' => $this->whenLoaded('ticket'),
            'created_at' => $this->created_at?->isoFormat('DD MMM YYYY, h:mm a'),
            'updated_at' => $this->updated_at?->isoFormat('DD MMM YYYY, h:mm a'),
        ];
    }
}
