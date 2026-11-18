<?php

namespace App\Models;

use App\Models\Concerns\BelongsToParoisse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntentionMesse extends Model
{
    use BelongsToParoisse, HasFactory;

    public const TYPES = [
        'defunt' => 'Défunt',
        'action_de_grace' => 'Action de grâce',
        'sante' => 'Santé',
        'anniversaire_deces' => 'Anniversaire de décès',
        'autre' => 'Autre',
    ];

    public const STATUTS = [
        'recue' => 'Reçue',
        'confirmee' => 'Confirmée',
        'programmee' => 'Programmée',
        'celebree' => 'Célébrée',
        'refusee' => 'Refusée',
        'annulee' => 'Annulée',
    ];

    /**
     * Transitions autorisées entre statuts.
     *
     * @var array<string, list<string>>
     */
    public const TRANSITIONS = [
        'recue' => ['confirmee', 'programmee', 'refusee', 'annulee'],
        'confirmee' => ['programmee', 'refusee', 'annulee'],
        'programmee' => ['celebree', 'annulee'],
        'celebree' => [],
        'refusee' => [],
        'annulee' => [],
    ];

    public const NOM_ANONYME = 'Demandeur anonymisé';

    protected $table = 'intentions_messe';

    protected $fillable = [
        'paroisse_id',
        'code_suivi',
        'nom_demandeur',
        'telephone',
        'type',
        'intention',
        'date_souhaitee',
        'horaire_id',
        'statut',
        'date_programmee',
        'horaire_programme_id',
        'motif_refus',
        'traitee_par',
    ];

    protected function casts(): array
    {
        return [
            'date_souhaitee' => 'date',
            'date_programmee' => 'date',
        ];
    }

    public function horaire(): BelongsTo
    {
        return $this->belongsTo(Horaire::class);
    }

    public function horaireProgramme(): BelongsTo
    {
        return $this->belongsTo(Horaire::class, 'horaire_programme_id');
    }

    public function traiteePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traitee_par');
    }

    /**
     * @return list<string>
     */
    public function transitionsDisponibles(): array
    {
        return self::TRANSITIONS[$this->statut] ?? [];
    }

    public function peutPasserA(string $statut): bool
    {
        return in_array($statut, $this->transitionsDisponibles(), true);
    }
}
