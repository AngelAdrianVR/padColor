<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketSolutionResource extends JsonResource
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
            'media' => $this->getMedia()->all(),
            'created_at' => [
                'isoFormat' => $this->created_at?->isoFormat('DD MMM YYYY, h:mm a'),
                'diffForHumans' => $this->created_at?->diffForHumans(),
            ],
            'updated_at' => $this->updated_at?->isoFormat('DD MMM YYYY'),
        ];
    }
}
