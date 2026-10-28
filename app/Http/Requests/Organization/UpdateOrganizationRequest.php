<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'municipality' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'address' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}