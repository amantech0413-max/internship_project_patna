<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCollegeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'college_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'mobile_number' => ['nullable', 'regex:/^\d{10}$/'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
