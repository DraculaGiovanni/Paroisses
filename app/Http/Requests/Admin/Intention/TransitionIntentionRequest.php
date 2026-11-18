<?php

namespace App\Http\Requests\Admin\Intention;

use App\Models\IntentionMesse;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionIntentionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $intention = $this->route('intention');

        return $intention instanceof IntentionMesse
            && $this->user() !== null
            && $this->user()->can('update', $intention);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $intention = $this->route('intention');
        $transitions = $intention instanceof IntentionMesse
            ? $intention->transitionsDisponibles()
            : [];
        $paroisseId = $intention instanceof IntentionMesse ? $intention->paroisse_id : null;
        $programme = $this->input('statut') === 'programmee';
        $refuse = $this->input('statut') === 'refusee';

        return [
            'statut' => ['required', 'string', Rule::in($transitions)],
            'date_programmee' => [
                Rule::requiredIf($programme),
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:'.CarbonImmutable::today('Africa/Porto-Novo')->toDateString(),
            ],
            'horaire_programme_id' => [
                Rule::requiredIf($programme),
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
            'motif_refus' => [
                Rule::requiredIf($refuse),
                'nullable',
                'string',
                'max:200',
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
            'statut' => 'statut',
            'date_programmee' => 'date programmée',
            'horaire_programme_id' => 'messe',
            'motif_refus' => 'motif du refus',
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
            'statut.required' => 'Le nouveau statut est obligatoire.',
            'statut.in' => 'Cette transition de statut n\'est pas autorisée.',
            'date_programmee.required' => 'La date programmée est obligatoire pour programmer une demande.',
            'date_programmee.date_format' => 'La date programmée doit être au format AAAA-MM-JJ.',
            'date_programmee.after_or_equal' => 'La date programmée doit être aujourd\'hui ou dans le futur.',
            'horaire_programme_id.required' => 'La messe est obligatoire pour programmer une demande.',
            'horaire_programme_id.integer' => 'La messe sélectionnée n\'est pas valide.',
            'horaire_programme_id.exists' => 'La messe sélectionnée n\'est pas une messe de cette paroisse.',
            'motif_refus.required' => 'Le motif du refus est obligatoire.',
            'motif_refus.max' => 'Le motif du refus ne peut pas dépasser 200 caractères.',
        ];
    }
}
