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
            'registration_no' => ['required', 'string', 'max:50', 'unique:students,registration_no'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'university_roll_no' => ['required', 'string', 'max:50'],
            'college_roll_no' => ['required', 'string', 'max:50'],
            'college_name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'digits:10'],
            'internship_mode' => ['nullable', Rule::in(['online', 'offline'])],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_no.required' => 'Registration number is required.',
            'registration_no.unique' => 'This registration number is already registered.',
            'name.required' => 'Student name is required.',
            'father_name.required' => "Father's name is required.",
            'university_roll_no.required' => 'University roll number is required.',
            'college_roll_no.required' => 'College roll number is required.',
            'college_name.required' => 'College name is required.',
            'subject.required' => 'Subject is required.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.digits' => 'Mobile number must be exactly 10 digits.',
        ];
    }
}
