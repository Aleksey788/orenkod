<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;

class EndCoachLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:2000']];
    }
}
