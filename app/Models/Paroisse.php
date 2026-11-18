<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Paroisse extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'slug', 'diocese', 'adresse', 'telephone',
        'email', 'whatsapp_channel_url', 'logo_path', 'couleur_principale',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function horaires(): HasMany
    {
        return $this->hasMany(Horaire::class);
    }

    public function annonces(): HasMany
    {
        return $this->hasMany(Annonce::class);
    }

    /**
     * Scope pour charger uniquement les données publiques d'une paroisse.
     */
    public function scopeForPublic($query, string $slug)
    {
        return $query->where('slug', $slug)
            ->select(['id', 'nom', 'diocese', 'adresse', 'telephone', 'email', 'whatsapp_channel_url']);
    }

    public static function uniqueSlug(string $nom): string
    {
        $base = Str::slug($nom);   // "Notre Dame des Apôtres" devient "notre-dame-des-apotres"
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
