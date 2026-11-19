<?php

namespace App\Models;

use App\Models\Concerns\BelongsToParoisse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    use BelongsToParoisse, HasFactory;

    protected $fillable = [
        'paroisse_id',
        'message',
        'statut',
        'modere_le',
    ];

    protected $casts = [
        'modere_le' => 'datetime',
    ];

    /**
     * @var array<string, string>
     */
    public const STATUTS = [
        'en_attente' => 'En attente',
        'publie' => 'Publié',
        'rejete' => 'Rejeté',
    ];

    public function scopePublies(Builder $query): Builder
    {
        return $query->where('statut', 'publie');
    }

    public function statutLabel(): string
    {
        return self::STATUTS[$this->statut] ?? 'Statut inconnu';
    }
}
