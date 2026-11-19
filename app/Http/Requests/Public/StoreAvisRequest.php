<?php

namespace App\Http\Requests\Public;

use App\Support\PublicFormToken;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAvisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:1000', 'not_regex:/<[^>]*>/'],
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
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'message' => 'avis',
            'form_token' => 'jeton du formulaire',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Votre avis est obligatoire.',
            'message.max' => 'Votre avis ne peut pas dépasser 1 000 caractères.',
            'message.not_regex' => 'Votre avis ne doit pas contenir de balises HTML.',
            'reference_colis.prohibited' => 'Ce champ doit rester vide.',
            'form_token.required' => 'Le formulaire a expiré. Rechargez la page puis réessayez.',
            'form_token.max' => 'Le formulaire a expiré. Rechargez la page puis réessayez.',
        ];
    }
}
