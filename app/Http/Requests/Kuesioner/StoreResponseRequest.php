<?php

// Form request untuk validasi pembuatan response kuesioner.

namespace App\Http\Requests\Kuesioner;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreResponseRequest extends FormRequest
{
    /**
     * Tentukan apakah user boleh menjalankan request ini.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Aturan validasi pembuatan response.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kuesioner_id' => ['required', 'integer', 'exists:kuesioners,id'],
            'target_id' => ['required', 'integer', 'exists:pegawais,id'],
        ];
    }

    /**
     * Format response validasi agar konsisten untuk API React.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'data' => [
                'errors' => $validator->errors(),
            ],
            'message' => $validator->errors()->first() ?: 'Validasi gagal.',
        ], 422));
    }
}
