<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('id');

        return [
            'registration_no' => ['sometimes', 'string', 'max:50', Rule::unique('students', 'registration_no')->ignore($studentId)],
            'name' => ['sometimes', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'university_roll_no' => ['nullable', 'string', 'max:50'],
            'college_roll_no' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:100'],
            'semester' => ['nullable', 'string', 'max:20'],
            'mobile' => ['sometimes', 'digits:10'],
            'email' => ['nullable', 'email', 'max:255'],
            'internship_mode' => ['sometimes', Rule::in(['online', 'offline'])],
            'address' => ['nullable', 'string'],
            'college_id' => ['nullable', 'exists:colleges,id'],
            'status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected', 'completed'])],
            'rejection_reason' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'id_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ];
    }
}
