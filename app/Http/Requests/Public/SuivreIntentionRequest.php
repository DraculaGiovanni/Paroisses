<?php

namespace App\Http\Requests\Public;

use App\Support\PublicFormToken;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SuivreIntentionRequest extends FormRequest
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
        return [
            'code_suivi' => [
                'required',
                'string',
                'size:10',
                'regex:/^[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{10}$/i',
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
            'code_suivi' => 'code de suivi',
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
            'code_suivi.required' => 'Le code de suivi est obligatoire.',
            'code_suivi.size' => 'Le code de suivi doit contenir exactement 10 caractères.',
            'code_suivi.regex' => 'Le code de suivi contient des caractères non autorisés.',
            'reference_colis.prohibited' => 'Ce champ doit rester vide.',
            'form_token.required' => 'Le formulaire a expiré. Rechargez la page puis réessayez.',
            'form_token.max' => 'Le formulaire a expiré. Rechargez la page puis réessayez.',
        ];
    }
}
