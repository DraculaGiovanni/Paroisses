<?php

namespace App\Http\Requests\Public;

use App\Models\IntentionMesse;
use App\Models\Paroisse;
use App\Support\PublicFormToken;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIntentionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $paroisseId = $this->paroisseId();

        return [
            'nom_demandeur' => ['required', 'string', 'max:100', 'not_regex:/<[^>]*>/'],
            'telephone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s().-]{6,28}[0-9]$/'],
            'type' => ['required', 'string', Rule::in(array_keys(IntentionMesse::TYPES))],
            'intention' => ['required', 'string', 'max:300', 'not_regex:/<[^>]*>/'],
            'date_souhaitee' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:'.CarbonImmutable::today('Africa/Porto-Novo')->toDateString(),
            ],
            'horaire_id' => [
                'nullable',
                'integer',
                Rule::exists('horaires', 'id')->where(function ($query) use ($paroisseId): void {
                    if ($paroisseId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('paroisse_id', $paroisseId)->where('type', 'messe');
                }),
            ],
            'reference_colis' => ['prohibited'],
            'form_token' => [
                'required',
                'string',
                'max:100',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! PublicFormToken::isValid((string) $value)) {
                        $fail('Le formulaire a expiré. Rechargez la page puis réessayez.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nom_demandeur' => 'nom du demandeur',
            'telephone' => 'téléphone',
            'type' => 'type de demande',
            'intention' => 'intention',
            'date_souhaitee' => 'date souhaitée',
            'horaire_id' => 'horaire',
            'form_token' => 'jeton du formulaire',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nom_demandeur.required' => 'Le nom du demandeur est obligatoire.',
            'nom_demandeur.max' => 'Le nom du demandeur ne peut pas dépasser 100 caractères.',
            'nom_demandeur.not_regex' => 'Le nom du demandeur ne doit pas contenir de balises HTML.',
            'telephone.max' => 'Le numéro de téléphone ne peut pas dépasser 30 caractères.',
            'telephone.regex' => 'Le numéro de téléphone n\'est pas valide.',
            'type.required' => 'Le type de demande est obligatoire.',
            'type.in' => 'Le type de demande sélectionné n\'est pas valide.',
            'intention.required' => 'L\'intention est obligatoire.',
            'intention.max' => 'L\'intention ne peut pas dépasser 300 caractères.',
            'intention.not_regex' => 'L\'intention ne doit pas contenir de balises HTML.',
            'date_souhaitee.date_format' => 'La date souhaitée doit être au format AAAA-MM-JJ.',
            'date_souhaitee.after_or_equal' => 'La date souhaitée doit être aujourd\'hui ou dans le futur.',
            'horaire_id.integer' => 'L\'horaire sélectionné n\'est pas valide.',
            'horaire_id.exists' => 'L\'horaire sélectionné n\'est pas une messe de cette paroisse.',
            'reference_colis.prohibited' => 'Ce champ doit rester vide.',
            'form_token.required' => 'Le formulaire a expiré. Rechargez la page puis réessayez.',
            'form_token.max' => 'Le formulaire a expiré. Rechargez la page puis réessayez.',
        ];
    }

    private function paroisseId(): ?int
    {
        $paroisse = $this->route('paroisse');

        if ($paroisse instanceof Paroisse) {
            return $paroisse->id;
        }

        if (is_numeric($paroisse)) {
            return (int) $paroisse;
        }

        if (is_string($paroisse) && $paroisse !== '') {
            return Paroisse::query()->where('slug', $paroisse)->value('id');
        }

        return null;
    }
}
