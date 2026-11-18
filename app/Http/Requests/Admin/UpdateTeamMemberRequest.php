<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateTeamMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $member = $this->route('user');

        return $member instanceof User && Gate::allows('update', $member);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['sometimes', 'required', Rule::in(['admin', 'secretariat'])],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [Rule::in(Permission::values())],
            'paroisse_id' => ['prohibited'],
            'actif' => ['prohibited'],
        ];
    }
}
