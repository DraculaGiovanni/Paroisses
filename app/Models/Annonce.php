<?php

namespace App\Models;

use App\Models\Concerns\BelongsToParoisse;
use App\Support\AnnouncementCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Annonce extends Model
{
    use BelongsToParoisse, HasFactory;

    protected $appends = ['image_url'];

    protected $fillable = [
        'paroisse_id',
        'titre',
        'contenu',
        'date_debut',
        'date_fin',
        'actif',
        'epinglee',
        'publie_le',
        'notifier',
        'categorie',
        'expire_le',
        'archivee_le',
        'responsable',
        'image_path',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'actif' => 'boolean',
        'epinglee' => 'boolean',
        'publie_le' => 'datetime',
        'notifier' => 'boolean',
        'expire_le' => 'datetime',
        'archivee_le' => 'datetime',
    ];

    /**
     * Scope pour récupérer uniquement les annonces publiées
     */
    public function scopePubliees(Builder $query): Builder
    {
        return $query->whereNotNull('publie_le')
            ->where('publie_le', '<=', now());
    }

    /**
     * Scope pour ne récupérer que les annonces actives.
     */
    public function scopeActives($query)
    {
        return $query->where('actif', true);
    }

    public function scopeNonArchivees(Builder $query): Builder
    {
        return $query->whereNull('archivee_le');
    }

    public function scopeCategorie(Builder $query, string $categorie): Builder
    {
        return $query->where('categorie', $categorie);
    }

    public function category(): AnnouncementCategory
    {
        return AnnouncementCategory::tryFrom($this->categorie)
            ?? AnnouncementCategory::Administration;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    public function getStatusAttribute(): string
    {
        if ($this->archivee_le !== null) {
            return 'archivee';
        }

        if ($this->publie_le === null) {
            return 'brouillon';
        }

        if ($this->publie_le->isFuture()) {
            return 'programmee';
        }

        if ($this->expire_le !== null && $this->expire_le->isPast()) {
            return 'expiree';
        }

        return 'publiee';
    }
}
