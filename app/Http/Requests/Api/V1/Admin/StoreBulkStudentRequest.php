<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Support\IndianMobile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBulkStudentRequest extends FormRequest
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
                $v->errors()->add('mobile_number', 'Invalid mobile. Use 10 digits, or 91/+91 prefix.');
            }
        });
    }
}
