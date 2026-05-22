<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Support\IndianMobile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreStaffStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = IndianMobile::normalize($this->input('mobile_number'));

        if ($normalized !== null) {
            $this->merge(['mobile_number' => $normalized]);
        }
    }

    public function rules(): array
    {
        return [
            'college_id' => ['required', 'integer', 'exists:colleges,id'],
            'student_name' => ['required', 'string', 'min:2', 'max:255'],
            'mobile_number' => ['required', 'regex:/^\d{10}$/'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (IndianMobile::normalize($this->input('mobile_number')) === null) {
                $v->errors()->add(
                    'mobile_number',
                    'Invalid mobile. Use 10 digits, or 91/+91 prefix (last 10 digits will be used).'
                );
            }
        });
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
