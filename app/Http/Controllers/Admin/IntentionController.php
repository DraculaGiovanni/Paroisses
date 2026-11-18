<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Intention\TransitionIntentionRequest;
use App\Models\Horaire;
use App\Models\IntentionMesse;
use App\Support\HoraireOption;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class IntentionController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', IntentionMesse::class);

        $statut = $request->string('statut')->toString();
        $date = $request->string('date')->toString();

        $query = IntentionMesse::query()
            ->with(['horaire', 'horaireProgramme']);

        if (array_key_exists($statut, IntentionMesse::STATUTS)) {
            $query->where('statut', $statut);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
            $query->whereDate('date_souhaitee', $date);
        }

        return Inertia::render('Admin/Intentions/Index', [
            'intentions' => $query->orderByDesc('created_at')
                ->paginate(20)
                ->withQueryString()
                ->through(fn (IntentionMesse $intention): array => $this->resume($intention)),
            'statuts' => $this->statuts(),
            'filters' => ['statut' => $statut, 'date' => $date],
            'nouvelles' => IntentionMesse::query()->where('statut', 'recue')->count(),
            'aujourdhui' => Carbon::today('Africa/Porto-Novo')->toDateString(),
        ]);
    }

    public function show(IntentionMesse $intention): Response
    {
        Gate::authorize('view', $intention);

        $intention->load(['horaire', 'horaireProgramme', 'traiteePar']);

        return Inertia::render('Admin/Intentions/Show', [
            'intention' => [
                'id' => $intention->id,
                'code_suivi' => $intention->code_suivi,
                'nom_demandeur' => $intention->nom_demandeur,
                'telephone' => $intention->telephone,
                'type' => $intention->type,
                'type_label' => IntentionMesse::TYPES[$intention->type] ?? $intention->type,
                'intention' => $intention->intention,
                'date_souhaitee' => $intention->date_souhaitee?->toDateString(),
                'horaire_souhaite' => $this->horaireLabel($intention->horaire),
                'statut' => $intention->statut,
                'statut_label' => IntentionMesse::STATUTS[$intention->statut] ?? $intention->statut,
                'date_programmee' => $intention->date_programmee?->toDateString(),
                'horaire_programme' => $this->horaireLabel($intention->horaireProgramme),
                'motif_refus' => $intention->motif_refus,
                'traitee_par' => $intention->traiteePar?->name,
                'created_at' => $intention->created_at?->toIso8601String(),
            ],
            'transitions' => collect($intention->transitionsDisponibles())
                ->map(fn (string $statut): array => [
                    'value' => $statut,
                    'label' => IntentionMesse::STATUTS[$statut] ?? $statut,
                ])
                ->values()
                ->all(),
            'horaires' => HoraireOption::pourParoisse($intention->paroisse_id),
            'aujourdhui' => Carbon::today('Africa/Porto-Novo')->toDateString(),
            'whatsapp_url' => $this->whatsappUrl($intention),
        ]);
    }

    public function updateStatut(TransitionIntentionRequest $request, IntentionMesse $intention): RedirectResponse
    {
        Gate::authorize('update', $intention);

        $data = $request->validated();
        $statut = $data['statut'];

        $attributs = [
            'statut' => $statut,
            'traitee_par' => $request->user()?->id,
        ];

        if ($statut === 'programmee') {
            $attributs['date_programmee'] = $data['date_programmee'];
            $attributs['horaire_programme_id'] = $data['horaire_programme_id'];
            $attributs['motif_refus'] = null;
        }

        if ($statut === 'refusee') {
            $attributs['motif_refus'] = $data['motif_refus'];
        }

        if (in_array($statut, ['confirmee', 'celebree', 'annulee'], true)) {
            $attributs['motif_refus'] = null;
        }

        $intention->update($attributs);

        return back()->with('success', 'Statut de la demande mis à jour.');
    }

    public function feuille(Request $request): Response
    {
        Gate::authorize('viewAny', IntentionMesse::class);

        $date = $request->string('date')->toString();

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            $date = Carbon::today('Africa/Porto-Novo')->toDateString();
        }

        $intentions = IntentionMesse::query()
            ->where('statut', 'programmee')
            ->whereDate('date_programmee', $date)
            ->with('horaireProgramme')
            ->orderBy('created_at')
            ->get();

        $groupes = $intentions
            ->groupBy(fn (IntentionMesse $intention): string => (string) ($intention->horaire_programme_id ?? 'sans'))
            ->sortBy(fn ($groupe): string => $groupe->first()->horaireProgramme?->heure?->format('H:i') ?? '99:99')
            ->map(function ($groupe): array {
                $horaire = $groupe->first()->horaireProgramme;

                return [
                    'horaire' => $this->horaireLabel($horaire) ?? 'Messe non précisée',
                    'heure' => $horaire?->heure?->format('H:i'),
                    'lieu' => $horaire?->lieu,
                    'intentions' => $groupe->map(fn (IntentionMesse $intention): array => [
                        'id' => $intention->id,
                        'code_suivi' => $intention->code_suivi,
                        'nom_demandeur' => $intention->nom_demandeur,
                        'type_label' => IntentionMesse::TYPES[$intention->type] ?? $intention->type,
                        'intention' => $intention->intention,
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Admin/Intentions/Feuille', [
            'date' => $date,
            'groupes' => $groupes,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function resume(IntentionMesse $intention): array
    {
        return [
            'id' => $intention->id,
            'code_suivi' => $intention->code_suivi,
            'nom_demandeur' => $intention->nom_demandeur,
            'telephone' => $intention->telephone,
            'type_label' => IntentionMesse::TYPES[$intention->type] ?? $intention->type,
            'date_souhaitee' => $intention->date_souhaitee?->toDateString(),
            'statut' => $intention->statut,
            'statut_label' => IntentionMesse::STATUTS[$intention->statut] ?? $intention->statut,
            'date_programmee' => $intention->date_programmee?->toDateString(),
            'horaire_programme' => $this->horaireLabel($intention->horaireProgramme),
            'created_at' => $intention->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function statuts(): array
    {
        return collect(IntentionMesse::STATUTS)
            ->map(fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
            ])
            ->values()
            ->all();
    }

    private function horaireLabel(?Horaire $horaire): ?string
    {
        if ($horaire === null) {
            return null;
        }

        $jour = Horaire::JOURS[$horaire->jour_semaine] ?? '';
        $heure = $horaire->heure?->format('H:i') ?? '';
        $lieu = $horaire->lieu !== null && $horaire->lieu !== '' ? ' — '.$horaire->lieu : '';

        return trim($jour.' — '.$heure.$lieu);
    }

    private function whatsappUrl(IntentionMesse $intention): ?string
    {
        if ($intention->telephone === null || $intention->telephone === '') {
            return null;
        }

        $numero = preg_replace('/\D+/', '', $intention->telephone);

        if ($numero === null || $numero === '') {
            return null;
        }

        $message = sprintf(
            'Bonjour %s, je vous contacte au sujet de votre demande de messe %s déposée auprès de la paroisse %s.',
            $intention->nom_demandeur,
            $intention->code_suivi,
            $intention->paroisse?->nom ?? ''
        );

        return 'https://wa.me/'.$numero.'?text='.rawurlencode($message);
    }
}
