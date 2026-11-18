<?php

namespace App\Support;

use App\Models\Horaire;

class HoraireOption
{
    /**
     * Libellés des messes actives d'une paroisse, pour les listes déroulantes.
     *
     * @return array<int, array{id: int, label: string}>
     */
    public static function pourParoisse(int $paroisseId): array
    {
        return Horaire::forParoisse($paroisseId)
            ->where('type', 'messe')
            ->where('actif', true)
            ->orderBy('jour_semaine')
            ->orderBy('heure')
            ->get()
            ->map(function (Horaire $horaire): array {
                $jour = Horaire::JOURS[$horaire->jour_semaine] ?? '';
                $heure = $horaire->heure?->format('H:i') ?? '';
                $lieu = $horaire->lieu !== null && $horaire->lieu !== '' ? ' — '.$horaire->lieu : '';

                return [
                    'id' => $horaire->id,
                    'label' => trim($jour.' — '.$heure.$lieu),
                ];
            })
            ->values()
            ->all();
    }
}
