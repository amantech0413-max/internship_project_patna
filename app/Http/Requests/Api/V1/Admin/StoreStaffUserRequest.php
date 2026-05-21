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
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['boolean'],
        ];
    }
}
