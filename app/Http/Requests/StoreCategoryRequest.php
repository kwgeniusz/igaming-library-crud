<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255', 'unique:categories,name']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.string' => 'El nombre de la categoría debe ser un texto.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'name.unique' => 'Ya existe una categoría con este nombre.',
        ];
    }
}
