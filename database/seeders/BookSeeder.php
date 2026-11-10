<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            ['title' => 'Cien años de soledad', 'author' => 'Gabriel García Márquez', 'published_year' => 1967, 'category' => 'Novela', 'description' => 'La historia de la familia Buendía en el pueblo de Macondo.'],
            ['title' => 'Don Quijote de la Mancha', 'author' => 'Miguel de Cervantes', 'published_year' => 1605, 'category' => 'Novela', 'description' => 'Las aventuras de un caballero y su escudero por tierras de La Mancha.'],
            ['title' => 'Breve historia del tiempo', 'author' => 'Stephen Hawking', 'published_year' => 1988, 'category' => 'Ciencia', 'description' => 'Una introducción a las grandes preguntas sobre el universo.'],
            ['title' => 'El origen de las especies', 'author' => 'Charles Darwin', 'published_year' => 1859, 'category' => 'Ciencia', 'description' => null],
            ['title' => 'Historia de la guerra del Peloponeso', 'author' => 'Tucídides', 'published_year' => 1502, 'category' => 'Historia', 'description' => 'Edición impresa de la crónica histórica del conflicto entre Atenas y Esparta.'],
        ];

        foreach ($books as $data) {
            $category = Category::firstOrCreate(['name' => $data['category']]);
            unset($data['category']);
            Book::firstOrCreate(
                ['title' => $data['title'], 'author' => $data['author']],
                [...$data, 'category_id' => $category->id],
            );
        }
    }
}
