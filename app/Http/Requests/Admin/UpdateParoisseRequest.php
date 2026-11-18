<?php

namespace App\Http\Requests\Admin;

use App\Support\ColorContrast;
use Illuminate\Foundation\Http\FormRequest;

class UpdateParoisseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->user()->paroisse);
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'diocese' => ['nullable', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'couleur_principale' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
                function ($attribute, $value, $fail) {
                    if ($value && ! $this->hasGoodContrast($value)) {
                        $fail('La couleur choisie est trop claire. Veuillez choisir une couleur plus foncée pour assurer la lisibilité du texte blanc.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'couleur_principale.regex' => 'La couleur doit être au format hexadécimal (#RRGGBB). Exemple : #1E40AF',
        ];
    }

    /**
     * Vérifie le contraste WCAG AA (ratio 4.5:1 minimum avec le blanc)
     */
    private function hasGoodContrast(string $hexColor): bool
    {
        return ColorContrast::meetsMinimumContrast($hexColor, 4.5);
    }
}
