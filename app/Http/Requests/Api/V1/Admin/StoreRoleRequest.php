<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Support\StaffPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:64',
                'alpha_dash',
                Rule::unique('roles', 'slug')->ignore($roleId),
                Rule::notIn(['super_admin', 'admin']),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'permission_keys' => ['required', 'array', 'min:1'],
            'permission_keys.*' => ['string', Rule::in(StaffPermissions::keys())],
        ];
    }
}
