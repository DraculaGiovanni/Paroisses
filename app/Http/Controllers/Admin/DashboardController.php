<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\HoraireResource;
use App\Models\Annonce;
use App\Models\Horaire;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $now = Carbon::now('Africa/Porto-Novo');

        // Compteurs en UNE requête agrégée par table
        $annoncesStats = Annonce::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN publie_le IS NULL THEN 1 ELSE 0 END) as brouillons,
            SUM(CASE WHEN publie_le IS NOT NULL AND publie_le <= ? THEN 1 ELSE 0 END) as publiees,
            SUM(CASE WHEN publie_le IS NOT NULL AND publie_le > ? THEN 1 ELSE 0 END) as programmees
        ', [$now, $now])->first();

        $horairesActifs = Horaire::where('actif', true)->count();

        // Prochaine messe
        $prochaineMesse = Horaire::where('type', 'messe')
            ->where('actif', true)
            ->where(function ($query) use ($now) {
                $jourActuel = $now->dayOfWeekIso; // 1 = lundi, 7 = dimanche
                $heureActuelle = $now->format('H:i:s');

                // Aujourd'hui après l'heure actuelle
                $query->where(function ($q) use ($jourActuel, $heureActuelle) {
                    $q->where('jour_semaine', $jourActuel)
                        ->where('heure', '>', $heureActuelle);
                })
                // OU jours suivants
                    ->orWhere('jour_semaine', '>', $jourActuel);
            })
            ->orderBy(DB::raw("
                CASE 
                    WHEN jour_semaine = {$now->dayOfWeekIso} THEN 0
                    ELSE 1
                END
            "))
            ->orderBy('jour_semaine')
            ->orderBy('heure')
            ->first();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'annonces_publiees' => $annoncesStats->publiees ?? 0,
                'annonces_brouillons' => $annoncesStats->brouillons ?? 0,
                'annonces_programmees' => $annoncesStats->programmees ?? 0,
                'horaires_actifs' => $horairesActifs,
            ],
            'prochaine_messe' => $prochaineMesse ? HoraireResource::make($prochaineMesse) : null,
        ]);
    }
}
