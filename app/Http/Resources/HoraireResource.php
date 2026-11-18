<?php

namespace App\Http\Resources;

use App\Models\Horaire;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HoraireResource extends JsonResource
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
            'type' => $this->type,
            'type_label' => Horaire::TYPES[$this->type] ?? $this->type,
            'jour_semaine' => $this->jour_semaine,
            'jour_label' => Horaire::JOURS[$this->jour_semaine] ?? '',
            'heure' => $this->heure ? $this->heure->format('H:i') : null,
            'heure_fin' => $this->heure_fin ? substr((string) $this->heure_fin, 0, 5) : null,
            'valide_du' => $this->valide_du?->format('Y-m-d'),
            'valide_au' => $this->valide_au?->format('Y-m-d'),
            'lieu' => $this->lieu,
            'note' => $this->note,
            'actif' => $this->actif,
        ];
    }
}
