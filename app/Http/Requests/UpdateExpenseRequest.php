<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
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
            'expense_date'        => ['required', 'date'],
            'description'         => ['required', 'string'],
            'amount'              => ['required', 'numeric', 'min:0'],
            'img_attachment'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // Validasi file gambar maksimal 2MB
            'expense_category_id' => ['required', 'exists:expense_categories,id'],

            // Catatan: user_id dan outlet_id biasanya diisi otomatis di controller 
            // berdasarkan user yang sedang login, jadi tidak wajib divalidasi dari request form.
        ];
    }
}
