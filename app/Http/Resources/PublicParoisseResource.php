<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicParoisseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Expose uniquement les données publiques de la paroisse.
     * Pas d'emails internes, pas d'IDs non nécessaires.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'nom' => $this->nom,
            'diocese' => $this->diocese,
            'adresse' => $this->adresse,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'whatsapp_channel_url' => $this->whatsapp_channel_url,
        ];
    }
}
