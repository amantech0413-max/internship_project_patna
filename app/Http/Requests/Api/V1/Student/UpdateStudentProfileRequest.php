<?php

namespace App\Http\Requests\Api\V1\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'university_roll_no' => ['nullable', 'string', 'max:50'],
            'college_roll_no' => ['nullable', 'string', 'max:50'],
            'college_name' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:100'],
            'semester' => ['nullable', 'string', 'max:20'],
            'mobile' => ['sometimes', 'digits:10'],
            'email' => ['nullable', 'email', 'max:255'],
            'internship_mode' => ['sometimes', 'in:online,offline'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'id_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ];
    }
}
