<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'university_roll_no' => ['nullable', 'string', 'max:50'],
            'college_roll_no' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:100'],
            'semester' => ['nullable', 'string', 'max:20'],
            'mobile' => ['required', 'digits:10'],
            'email' => ['nullable', 'email', 'max:255'],
            'internship_mode' => ['required', Rule::in(['online', 'offline'])],
            'address' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'id_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Student name is required.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.digits' => 'Mobile number must be exactly 10 digits.',
            'internship_mode.required' => 'Please select internship mode.',
        ];
    }
}
