<?php

namespace App\Models;

use App\Models\Concerns\BelongsToParoisse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouleurExceptionnelle extends Model
{
    use BelongsToParoisse, HasFactory;

    protected $table = 'couleurs_exceptionnelles';

    protected $fillable = [
        'paroisse_id',
        'date',
        'couleur',
        'libelle',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
