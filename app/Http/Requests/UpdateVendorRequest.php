<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
          'company_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes','email','max:255','unique:vendors,email',],
            'phone' => ['sometimes', 'string', 'max:20'],
            'address' => ['sometimes', 'string'],
            'bank_name' => ['sometimes', 'string', 'max:255'],
            'bank_account' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
