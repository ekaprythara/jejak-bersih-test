<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreCustomerRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'phone_number' => ['required', 'numeric', 'unique:customers,phone_number'],
        ];
    }

    public function messages(): array
    {
        return [
            'name' => 'Nama wajib diisi.',
            'phone_number' => 'Nomor telepon wajib diisi.',
            'phone_number.unique' => 'Nomor telepon ini sudah terdaftar.'
        ];
    }
}
