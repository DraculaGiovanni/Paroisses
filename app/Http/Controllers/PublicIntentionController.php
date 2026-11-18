<?php

namespace App\Http\Controllers;

use App\Http\Requests\Public\StoreIntentionRequest;
use App\Http\Requests\Public\SuivreIntentionRequest;
use App\Http\Resources\PublicParoisseResource;
use App\Models\IntentionMesse;
use App\Models\Paroisse;
use App\Support\HoraireOption;
use App\Support\IntentionCode;
use App\Support\PublicFormToken;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PublicIntentionController extends Controller
{
    public function create(Paroisse $paroisse): Response
    {
        return Inertia::render('Public/Intentions/Nouvelle', [
            'paroisse' => $this->paroisseData($paroisse),
            'types' => IntentionMesse::TYPES,
            'horaires' => HoraireOption::pourParoisse($paroisse->id),
            'date_min' => Carbon::today('Africa/Porto-Novo')->toDateString(),
            'form_token' => PublicFormToken::issue(),
        ]);
    }

    public function store(StoreIntentionRequest $request, Paroisse $paroisse): RedirectResponse
    {
        $data = $request->validated();

        $code = IntentionCode::generateUnique(
            fn (string $candidate): bool => IntentionMesse::withoutGlobalScopes()
                ->where('code_suivi', $candidate)
                ->exists()
        );

        try {
            IntentionMesse::create([
                'paroisse_id' => $paroisse->id,
                'code_suivi' => $code,
                'nom_demandeur' => $data['nom_demandeur'],
                'telephone' => $data['telephone'] ?? null,
                'type' => $data['type'],
                'intention' => $data['intention'],
                'date_souhaitee' => $data['date_souhaitee'] ?? null,
                'horaire_id' => $data['horaire_id'] ?? null,
                'statut' => 'recue',
            ]);
        } catch (QueryException $exception) {
            Log::error('Échec de l\'enregistrement d\'une intention de messe.', [
                'sql_state' => $exception->getCode(),
            ]);

            return back()
                ->withErrors(['intention' => 'La demande n\'a pas pu être enregistrée. Veuillez réessayer.'])
                ->withInput();
        }

        return redirect()
            ->route('paroisse.intentions.confirmation', $paroisse)
            ->with('intention_code_suivi', $code);
    }

    public function confirmation(Request $request, Paroisse $paroisse): Response|RedirectResponse
    {
        $code = $request->session()->pull('intention_code_suivi');

        if (! is_string($code) || $code === '') {
            return redirect()->route('paroisse.intentions.nouvelle', $paroisse);
        }

        return Inertia::render('Public/Intentions/Confirmation', [
            'paroisse' => $this->paroisseData($paroisse),
            'code' => $code,
        ]);
    }

    public function suiviForm(Paroisse $paroisse): Response
    {
        return Inertia::render('Public/Intentions/Suivi', [
            'paroisse' => $this->paroisseData($paroisse),
            'form_token' => PublicFormToken::issue(),
            'resultat' => null,
            'message' => null,
        ]);
    }

    public function suivi(SuivreIntentionRequest $request, Paroisse $paroisse): Response
    {
        $code = IntentionCode::normalize((string) $request->validated('code_suivi'));

        $intention = IntentionMesse::forParoisse($paroisse->id)
            ->with('horaireProgramme')
            ->where('code_suivi', $code)
            ->first();

        $resultat = null;

        if ($intention !== null) {
            $resultat = [
                'statut' => $intention->statut,
                'date_programmee' => $intention->date_programmee?->toDateString(),
                'heure_programmee' => $intention->horaireProgramme?->heure?->format('H:i'),
                'motif_refus' => $intention->motif_refus,
            ];
        }

        return Inertia::render('Public/Intentions/Suivi', [
            'paroisse' => $this->paroisseData($paroisse),
            'form_token' => PublicFormToken::issue(),
            'resultat' => $resultat,
            'message' => $resultat === null
                ? 'Aucune demande ne correspond à ce code pour cette paroisse.'
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function paroisseData(Paroisse $paroisse): array
    {
        return PublicParoisseResource::make($paroisse)->resolve(request());
    }
}
