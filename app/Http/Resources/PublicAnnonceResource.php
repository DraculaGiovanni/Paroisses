<?php

namespace App\Http\Resources;

use App\Support\AnnouncementCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicAnnonceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'contenu' => $this->contenu,
            'categorie' => $this->categorie,
            'categorie_label' => AnnouncementCategory::tryFrom($this->categorie)?->label() ?? 'Administration',
            'publie_le' => $this->publie_le?->format('Y-m-d H:i'),
            'epinglee' => $this->categorie === 'urgence' || $this->epinglee,
        ];
    }
}
