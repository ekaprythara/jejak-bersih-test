<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
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
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric'],
            'estimated_days' => ['required', 'numeric']
        ];
    }

    public function messages(): array
    {
        return [
            'name' => 'Nama layanan harus diisi.',
            'price' => 'Harga harus diisi.',
            'estimated_days' => 'Estimasi waktu layanan harus diisi.'
        ];
    }
}
