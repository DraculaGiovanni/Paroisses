<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', User::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $paroisseRules = $this->user()->isSuperAdmin()
            ? ['required', 'integer', Rule::exists('paroisses', 'id')]
            : ['prohibited'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['sometimes', 'required', Rule::in(['admin', 'secretariat'])],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [Rule::in(Permission::values())],
            'paroisse_id' => $paroisseRules,
        ];
    }
}
