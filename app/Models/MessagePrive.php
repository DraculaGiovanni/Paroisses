<?php

namespace App\Models;

use App\Models\Concerns\BelongsToParoisse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessagePrive extends Model
{
    use BelongsToParoisse, HasFactory;

    public const SUJETS = [
        'suggestion' => 'Suggestion',
        'remerciement' => 'Remerciement',
        'difficulte' => 'Difficulté',
        'autre' => 'Autre',
    ];

    public const STATUTS = [
        'nouveau' => 'Nouveau',
        'lu' => 'Lu',
        'traite' => 'Traité',
        'archive' => 'Archivé',
    ];

    protected $table = 'messages_prives';

    protected $fillable = [
        'paroisse_id',
        'sujet',
        'message',
        'nom',
        'contact',
        'souhaite_reponse',
        'statut',
        'lu_par',
    ];

    protected function casts(): array
    {
        return [
            'souhaite_reponse' => 'boolean',
        ];
    }

    public function luPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lu_par');
    }
}
