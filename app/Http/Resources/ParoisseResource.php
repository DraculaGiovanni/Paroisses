<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParoisseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Resource pour l'admin - expose toutes les données nécessaires.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'slug' => $this->slug,
            'diocese' => $this->diocese,
            'adresse' => $this->adresse,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'whatsapp_channel_url' => $this->whatsapp_channel_url,
            'logo_path' => $this->logo_path,
            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }
}
