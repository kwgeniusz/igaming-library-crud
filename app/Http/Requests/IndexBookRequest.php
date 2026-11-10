<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['category' => ['nullable', 'integer', 'exists:categories,id']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'category.integer' => 'Selecciona una categoría válida.',
            'category.exists' => 'La categoría seleccionada ya no existe. Selecciona otra.',
        ];
    }
}
