<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'college_id' => ['required', 'integer', 'exists:colleges,id'],
            'student_name' => ['required', 'string', 'min:2', 'max:255'],
            'mobile_number' => ['required', 'regex:/^\d{10}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'college_id.required' => 'Please select a college.',
            'college_id.exists' => 'Selected college is invalid.',
            'student_name.required' => 'Student name is required.',
            'student_name.min' => 'Student name must be at least 2 characters.',
            'mobile_number.required' => 'Mobile number is required.',
            'mobile_number.regex' => 'Mobile number must be exactly 10 digits.',
        ];
    }
}
