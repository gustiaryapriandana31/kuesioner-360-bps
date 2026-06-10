<?php

// Form request untuk validasi penyimpanan jawaban response kuesioner.

namespace App\Http\Requests\Kuesioner;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateResponseRequest extends FormRequest
{
    /**
     * Tentukan apakah user boleh menjalankan request ini.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Aturan validasi update jawaban response.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'jawabans' => ['required', 'array', 'min:1'],
            'jawabans.*.pertanyaan_id' => ['required', 'integer', 'exists:pertanyaans,id'],
            'jawabans.*.nilai' => ['required', 'integer'],
            'submit' => ['sometimes', 'boolean'],
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
