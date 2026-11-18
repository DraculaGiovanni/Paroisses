<?php

namespace App\Models;

use App\Models\Concerns\BelongsToParoisse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horaire extends Model
{
    use BelongsToParoisse, HasFactory;

    public const TYPES = [
        'messe' => 'Messe',
        'confession' => 'Confession',
        'adoration' => 'Adoration',
        'autre' => 'Autre',
    ];

    public const JOURS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche',
    ];

    protected $fillable = [
        'paroisse_id',
        'jour_semaine',
        'type',
        'heure',
        'lieu',
        'note',
        'actif',
        'heure_fin',
        'valide_du',
        'valide_au',
    ];

    protected $casts = [
        'heure' => 'datetime:H:i',
        'actif' => 'boolean',
        'valide_du' => 'date',
        'valide_au' => 'date',
    ];

    /**
     * Scope pour ne récupérer que les horaires actifs.
     */
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    public function scopeValidesA($query, $date): Builder
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date, 'Africa/Porto-Novo');
        $formatted = $date->toDateString();

        return $query
            ->where(function (Builder $query) use ($formatted): void {
                $query->whereNull('valide_du')->orWhere('valide_du', '<=', $formatted);
            })
            ->where(function (Builder $query) use ($formatted): void {
                $query->whereNull('valide_au')->orWhere('valide_au', '>=', $formatted);
            });
    }

    /**
     * Calcule le prochain horaire de messe à partir de maintenant.
     *
     * @param  Builder  $query
     * @param  Carbon  $now  Moment de référence (fuseau Africa/Porto-Novo)
     * @return Horaire|null
     */
    public function scopeProchaineMesseDe($query, $now)
    {
        // Jour actuel (1-7, lundi-dimanche)
        $currentDay = $now->dayOfWeekIso;
        $currentTime = $now->format('H:i');

        // Cherche d'abord dans le jour actuel, après l'heure actuelle
        $horaire = $query->where('type', 'messe')
            ->where('actif', true)
            ->where('jour_semaine', $currentDay)
            ->where('heure', '>', $currentTime)
            ->orderBy('heure')
            ->first();

        if ($horaire) {
            return $horaire;
        }

        // Sinon cherche dans les jours suivants
        $horaire = $query->where('type', 'messe')
            ->where('actif', true)
            ->where('jour_semaine', '>', $currentDay)
            ->orderBy('jour_semaine')
            ->orderBy('heure')
            ->first();

        if ($horaire) {
            return $horaire;
        }

        // Sinon cherche en début de semaine (retour au lundi)
        return $query->where('type', 'messe')
            ->where('actif', true)
            ->where('jour_semaine', '<=', $currentDay)
            ->orderBy('jour_semaine')
            ->orderBy('heure')
            ->first();
    }
}
