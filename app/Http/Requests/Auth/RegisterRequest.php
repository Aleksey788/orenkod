<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => mb_strtolower(
                    trim((string) $this->input('email'))
                ),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required', 'string', 'max:100'
            ],

            'last_name' => [
                'required', 'string', 'max:100'
            ],

            'middle_name' => [
                'nullable', 'string', 'max:100'
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'phone' => [
                'nullable', 'string', 'max:20'
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],

            'birth_date' => [
                'required',
                'date',
                'before_or_equal:' .
                    now()->subYears(18)->toDateString(),
            ],

            'municipality' => [
                'required', 'string', 'max:150'
            ],

            'start_year' => [
                'required',
                'integer',
                'min:1900',
                'max:' . now()->year,
            ],

            'personal_data_consent' => [
                'required', 'accepted'
            ],
        ];
    }
}