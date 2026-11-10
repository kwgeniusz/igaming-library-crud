<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'published_year' => ['required', 'integer', 'between:1000,'.now()->year],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.string' => 'El título debe ser un texto.',
            'title.min' => 'El título debe tener al menos 3 caracteres.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'author.required' => 'El autor es obligatorio.',
            'author.string' => 'El autor debe ser un texto.',
            'author.max' => 'El autor no puede superar los 255 caracteres.',
            'description.string' => 'La descripción debe ser un texto.',
            'published_year.required' => 'El año de publicación es obligatorio.',
            'published_year.integer' => 'El año de publicación debe ser un número entero.',
            'published_year.between' => 'El año de publicación debe estar entre 1000 y '.now()->year.'.',
            'category_id.required' => 'Selecciona una categoría.',
            'category_id.integer' => 'Selecciona una categoría válida.',
            'category_id.exists' => 'La categoría seleccionada ya no existe. Selecciona otra.',
        ];
    }
}
