<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternshipGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'semester' => ['nullable', 'string', 'max:20'],
            'subject' => ['nullable', 'string', 'max:100'],
            'college_name' => ['nullable', 'string', 'max:255'],
            'internship_mode' => ['required', 'in:online,offline'],
            'whatsapp_group_link' => ['nullable', 'url', 'max:500'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:active,completed,inactive'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('internship_type') && ! $this->has('internship_mode')) {
            $this->merge(['internship_mode' => $this->internship_type]);
        }
    }
}
