<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VendorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
          'company_name' => ['required', 'string', 'max:255'],
            'registration_number' => ['required','string','max:255', 'unique:vendors,registration_number',],
            'email' => ['required','email','max:255','unique:vendors,email',],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'tax_number' => ['required','string','max:255','unique:vendors,tax_number', ],
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_account' => ['required', 'integer'],
            'status' => ['sometimes','in:pending,active,suspended,blacklisted', ],
        ];
    }
}
