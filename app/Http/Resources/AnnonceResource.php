<?php

namespace App\Http\Resources;

use App\Support\AnnouncementCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnonceResource extends JsonResource
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
            'titre' => $this->titre,
            'contenu' => $this->contenu,
            'date_debut' => $this->date_debut?->format('Y-m-d'),
            'date_fin' => $this->date_fin?->format('Y-m-d'),
            'publie_le' => $this->publie_le?->format('Y-m-d H:i'),
            'epinglee' => $this->epinglee,
            'actif' => $this->actif,
            'est_publiee' => $this->publie_le !== null && $this->publie_le->isPast(),
            'categorie' => $this->categorie,
            'categorie_label' => AnnouncementCategory::tryFrom($this->categorie)?->label() ?? 'Administration',
            'expire_le' => $this->expire_le?->format('Y-m-d H:i'),
            'archivee_le' => $this->archivee_le?->format('Y-m-d H:i'),
            'responsable' => $this->responsable,
            'statut' => $this->status,
        ];
    }
}
