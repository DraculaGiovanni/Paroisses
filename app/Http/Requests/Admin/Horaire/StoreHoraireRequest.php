<?php

namespace App\Http\Requests\Admin\Horaire;

use App\Models\Horaire;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHoraireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(array_keys(Horaire::TYPES))],
            'jour_semaine' => ['required', 'integer', 'between:1,7'],
            'heure' => ['required', 'date_format:H:i'],
            'lieu' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
            'actif' => ['boolean'],
            'heure_fin' => ['nullable', 'date_format:H:i', 'after:heure'],
            'valide_du' => ['nullable', 'date'],
            'valide_au' => ['nullable', 'date', 'after_or_equal:valide_du'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'type de célébration',
            'jour_semaine' => 'jour de la semaine',
            'heure' => 'heure',
            'lieu' => 'lieu',
            'note' => 'note',
            'actif' => 'statut actif',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de célébration est obligatoire.',
            'type.in' => 'Le type de célébration n\'est pas valide.',
            'jour_semaine.required' => 'Le jour de la semaine est obligatoire.',
            'jour_semaine.between' => 'Le jour doit être entre 1 (lundi) et 7 (dimanche).',
            'heure.required' => 'L\'heure est obligatoire.',
            'heure.date_format' => 'L\'heure doit être au format HH:MM (ex: 18:00).',
        ];
    }
}
