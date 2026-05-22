<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('staff_user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['nullable', 'regex:/^\d{10}$/'],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:6'],
            'is_active' => ['boolean'],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(fn ($q) => $q->where('is_assignable', true)),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('assigned_role_id') && ! $this->has('role_id')) {
            $this->merge(['role_id' => $this->input('assigned_role_id')]);
        }
    }
}
