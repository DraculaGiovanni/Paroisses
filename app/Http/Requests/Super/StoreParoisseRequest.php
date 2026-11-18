<?php

namespace App\Http\Requests\Super;

use App\Support\ColorContrast;
use Illuminate\Foundation\Http\FormRequest;

class StoreParoisseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'super_admin';
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:150'],
            'diocese' => ['nullable', 'string', 'max:150'],
            'couleur_principale' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
                function ($attribute, $value, $fail) {
                    if ($value && ! $this->hasGoodContrast($value)) {
                        $fail('La couleur choisie est trop claire. Veuillez choisir une couleur plus foncée pour assurer la lisibilité du texte blanc.');
                    }
                },
            ],
            'admin_name' => ['required', 'string', 'max:100'],
            'admin_email' => ['required', 'email', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'couleur_principale.regex' => 'La couleur doit être au format hexadécimal (#RRGGBB). Exemple : #1E40AF',
            'admin_name.required' => 'Le nom de l\'administrateur est requis.',
            'admin_email.required' => 'L\'email de l\'administrateur est requis.',
            'admin_email.email' => 'L\'email doit être une adresse valide.',
            'admin_email.unique' => 'Cet email est déjà utilisé.',
            'admin_password.required' => 'Le mot de passe est requis.',
            'admin_password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
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
