<?php

namespace App\Models;

use App\Models\Concerns\BelongsToParoisse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activite extends Model
{
    use BelongsToParoisse, HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'paroisse_id',
        'user_id',
        'action',
        'cible_type',
        'cible_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * @var array<string, string>
     */
    public const ACTIONS = [
        'annonce.cree' => 'Annonce créée',
        'annonce.modifiee' => 'Annonce modifiée',
        'annonce.supprimee' => 'Annonce supprimée',
        'horaire.cree' => 'Horaire créé',
        'horaire.modifie' => 'Horaire modifié',
        'horaire.supprime' => 'Horaire supprimé',
        'intention.cree' => 'Intention créée',
        'intention.modifiee' => 'Intention modifiée',
        'couleur.ajoutee' => 'Couleur liturgique ajoutée',
        'couleur.supprimee' => 'Couleur liturgique supprimée',
        'paroisse.modifiee' => 'Paroisse modifiée',
        'utilisateur.cree' => 'Membre de l’équipe créé',
        'utilisateur.modifie' => 'Membre de l’équipe modifié',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return self::ACTIONS[$this->action] ?? 'Action enregistrée';
    }
}
