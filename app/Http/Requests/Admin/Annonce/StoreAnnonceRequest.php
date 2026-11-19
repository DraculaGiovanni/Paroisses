<?php

namespace App\Http\Requests\Admin\Annonce;

use App\Support\AnnouncementCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnonceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // L'autorisation est gérée par la Policy
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
            'titre' => ['required', 'string', 'max:150'],
            'contenu' => ['required', 'string', 'max:5000'],
            'date_debut' => ['nullable', 'date', 'after_or_equal:today'],
            'date_fin' => ['nullable', 'date', 'after:date_debut'],
            'actif' => ['boolean'],
            'epinglee' => ['boolean'],
            'publier' => ['boolean'], // Si true, publie immédiatement
            'categorie' => ['sometimes', Rule::in(AnnouncementCategory::values())],
            'publie_le' => ['nullable', 'date'],
            'expire_le' => ['nullable', 'date', 'after:publie_le'],
            'responsable' => ['nullable', 'string', 'max:100'],
            'notifier' => ['boolean'],
            'image' => ['nullable', 'file', 'mimes:jpeg,png,webp', 'max:5120'],
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
            'titre' => 'titre',
            'contenu' => 'contenu',
            'date_debut' => 'date de début',
            'date_fin' => 'date de fin',
            'actif' => 'statut actif',
            'epinglee' => 'épinglée',
            'publier' => 'publier',
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
            'titre.required' => 'Le titre est obligatoire.',
            'titre.max' => 'Le titre ne peut pas dépasser 150 caractères.',
            'contenu.required' => 'Le contenu est obligatoire.',
            'contenu.max' => 'Le contenu ne peut pas dépasser 5000 caractères.',
            'date_debut.after_or_equal' => 'La date de début doit être aujourd\'hui ou dans le futur.',
            'date_fin.after' => 'La date de fin doit être après la date de début.',
        ];
    }
}
