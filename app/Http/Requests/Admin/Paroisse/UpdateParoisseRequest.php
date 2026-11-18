<?php

namespace App\Http\Requests\Admin\Paroisse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParoisseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $paroisse = $this->route('paroisse') ?? $this->user()->paroisse;

        return [
            'nom' => ['required', 'string', 'max:150'],
            'slug' => [
                'required',
                'string',
                'max:80',
                'alpha_dash',
                Rule::unique('paroisses', 'slug')->ignore($paroisse?->id),
            ],
            'diocese' => ['nullable', 'string', 'max:150'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'couleur_principale' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'whatsapp_channel_url' => [
                'nullable',
                'url',
                'starts_with:https://whatsapp.com/channel/,https://chat.whatsapp.com/',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'nom' => 'nom de la paroisse',
            'slug' => 'identifiant URL',
            'diocese' => 'diocèse',
            'adresse' => 'adresse',
            'telephone' => 'téléphone',
            'email' => 'email',
            'couleur_principale' => 'couleur d\'accent',
            'whatsapp_channel_url' => 'lien WhatsApp',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la paroisse est obligatoire.',
            'slug.required' => 'L\'identifiant URL est obligatoire.',
            'slug.unique' => 'Cet identifiant URL est déjà utilisé par une autre paroisse.',
            'slug.alpha_dash' => 'L\'identifiant ne peut contenir que des lettres, chiffres, tirets et underscores.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'couleur_principale.regex' => 'La couleur doit être au format hexadécimal (#000000).',
            'whatsapp_channel_url.starts_with' => 'Le lien WhatsApp doit commencer par https://whatsapp.com/channel/ ou https://chat.whatsapp.com/',
        ];
    }
}
