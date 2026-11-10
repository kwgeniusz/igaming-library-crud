<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot access category endpoints', function (string $method, string $uri) {
    $this->call(strtoupper($method), $uri)->assertRedirect(route('login'));
    $jsonMethod = $method.'Json';
    $this->{$jsonMethod}($uri)->assertUnauthorized();
})->with([['get', '/categories'], ['post', '/categories'], ['delete', '/categories/1']]);

test('categories are listed alphabetically with their book counts', function () {
    $category = Category::factory()->create(['name' => 'Ciencia']);
    Category::factory()->create(['name' => 'Novela']);
    Book::factory()->count(2)->for($category)->create();
    $this->actingAs(User::factory()->create())->getJson(route('categories.index'))
        ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('data.0.name', 'Ciencia')->assertJsonPath('data.0.books_count', 2)->assertJsonPath('data.1.books_count', 0);
    $this->get(route('categories.index'))->assertInertia(fn (Assert $page) => $page->component('categories/index')->has('categories', 2)->where('categories.0.books_count', 2));
});

test('a unique category can be created with a trimmed name', function () {
    $this->actingAs(User::factory()->create())->postJson(route('categories.store'), ['name' => '  Novela  '])
        ->assertCreated()->assertJsonPath('data.name', 'Novela')->assertJsonPath('data.books_count', 0);
    $this->assertDatabaseHas('categories', ['name' => 'Novela']);
});

test('category names must be valid', function (mixed $name) {
    $this->actingAs(User::factory()->create())->postJson(route('categories.store'), ['name' => $name])
        ->assertUnprocessable()->assertJsonValidationErrors('name');
    $this->assertDatabaseCount('categories', 0);
})->with(['null' => [null], 'empty' => [''], 'spaces' => ['  '], 'too long' => [str_repeat('a', 256)], 'array' => [['invalid']]]);

test('duplicate category names are rejected', function () {
    Category::factory()->create(['name' => 'Novela']);
    $this->actingAs(User::factory()->create())->postJson(route('categories.store'), ['name' => '  Novela '])
        ->assertUnprocessable()->assertJsonValidationErrors('name')->assertJsonPath('errors.name.0', 'Ya existe una categoría con este nombre.');
    $this->assertDatabaseCount('categories', 1);
});

test('an empty category can be deleted', function () {
    $category = Category::factory()->create();
    $this->actingAs(User::factory()->create())->deleteJson(route('categories.destroy', $category))->assertNoContent();
    $this->assertModelMissing($category);
});

test('a category with books cannot be deleted', function () {
    $book = Book::factory()->create();
    $this->actingAs(User::factory()->create())->deleteJson(route('categories.destroy', $book->category_id))
        ->assertConflict()->assertJsonPath('message', 'Esta categoría tiene libros asociados. Reasigna o elimina sus libros antes de eliminarla.');
    $this->assertDatabaseHas('categories', ['id' => $book->category_id]);
    $this->assertModelExists($book);
});

test('the foreign key also prevents deleting a category with books', function () {
    $book = Book::factory()->create();
    expect(fn () => $book->category->delete())->toThrow(QueryException::class);
    $this->assertModelExists($book);
    $this->assertDatabaseHas('categories', ['id' => $book->category_id]);
});

test('category deletion reports a conflict if a book appears after the initial check', function () {
    $category = Category::factory()->create();
    Category::deleting(function (Category $deletingCategory) {
        Book::factory()->for($deletingCategory)->create();
    });
    try {
        $this->actingAs(User::factory()->create())->deleteJson(route('categories.destroy', $category))->assertConflict();
        $this->assertModelExists($category);
        $this->assertDatabaseHas('books', ['category_id' => $category->id]);
    } finally {
        Category::flushEventListeners();
    }
});

test('web category writes redirect with useful errors', function () {
    $this->actingAs(User::factory()->create());
    $this->post(route('categories.store'), ['name' => 'Historia'], ['X-Inertia' => 'true'])
        ->assertRedirect(route('categories.index'))->assertSessionHasNoErrors();
    $category = Category::firstOrFail();
    $this->from(route('categories.index'))->post(route('categories.store'), ['name' => 'Historia'], ['X-Inertia' => 'true'])
        ->assertRedirect(route('categories.index'))->assertSessionHasErrors('name');
    $book = Book::factory()->for($category)->create();
    $this->delete(route('categories.destroy', $category), [], ['X-Inertia' => 'true'])
        ->assertRedirect(route('categories.index'))->assertSessionHasErrors('category');
    $book->delete();
    $this->delete(route('categories.destroy', $category), [], ['X-Inertia' => 'true'])->assertRedirect(route('categories.index'));
    $this->assertModelMissing($category);
});

test('a missing category returns not found', function () {
    $this->actingAs(User::factory()->create())->deleteJson(route('categories.destroy', 9999))->assertNotFound();
});

test('seeders are repeatable and preserve existing records', function () {
    $user = User::factory()->create(['email' => 'test@example.com', 'name' => 'Usuario existente']);
    $this->seed(DatabaseSeeder::class);
    $book = Book::where('title', 'Cien años de soledad')->firstOrFail();
    $book->update(['description' => 'Una nota personal']);
    $this->seed(DatabaseSeeder::class);
    $this->assertDatabaseCount('categories', 3);
    $this->assertDatabaseCount('books', 5);
    $this->assertDatabaseCount('users', 1);
    expect($user->refresh()->name)->toBe('Usuario existente');
    expect($book->refresh()->description)->toBe('Una nota personal');
    expect(Book::with('category')->get()->every(fn (Book $book) => $book->category !== null))->toBeTrue();
});
