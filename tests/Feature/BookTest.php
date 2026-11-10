<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 11, 10));
});

test('guests cannot access any book endpoint', function (string $method, string $uri) {
    $this->call(strtoupper($method), $uri)->assertRedirect(route('login'));
    $jsonMethod = $method.'Json';
    $this->{$jsonMethod}($uri)->assertUnauthorized();
})->with([
    ['get', '/books'], ['get', '/books/create'], ['get', '/books/1/edit'],
    ['post', '/books'], ['put', '/books/1'], ['delete', '/books/1'],
]);

test('books are listed in title order with their categories', function () {
    $category = Category::factory()->create(['name' => 'Novela']);
    $last = Book::factory()->for($category)->create(['title' => 'Zorro']);
    $first = Book::factory()->for($category)->create(['title' => 'América']);

    $this->actingAs(User::factory()->create())->getJson(route('books.index'))
        ->assertOk()->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $first->id)
        ->assertJsonPath('data.1.id', $last->id)
        ->assertJsonPath('data.0.category.name', 'Novela')
        ->assertJsonStructure(['data' => [['id', 'title', 'author', 'description', 'published_year', 'category_id', 'category' => ['id', 'name']]]]);
});

test('the list and forms are available through Inertia', function () {
    $book = Book::factory()->create();
    $this->actingAs(User::factory()->create())
        ->get(route('books.index'), [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(Request::create('/books')),
            'Accept' => 'application/json',
        ])
        ->assertOk()->assertHeader('X-Inertia', 'true')
        ->assertJsonPath('component', 'books/index')
        ->assertJsonCount(1, 'props.books')
        ->assertJsonCount(1, 'props.categories')
        ->assertJsonPath('props.books.0.category.id', $book->category_id)
        ->assertJsonPath('props.books.0.category.name', $book->category->name);
    $this->get(route('books.index'))->assertInertia(fn (Assert $page) => $page->component('books/index')->has('books', 1));
    $this->get(route('books.create'))->assertInertia(fn (Assert $page) => $page->component('books/create')->where('currentYear', 2026));
    $this->get(route('books.edit', $book))->assertInertia(fn (Assert $page) => $page->component('books/edit')->where('book.id', $book->id)->where('book.category.id', $book->category_id));
});

test('books can be filtered by category and an empty filter lists everything', function () {
    $book = Book::factory()->create();
    Book::factory()->create();
    $this->actingAs(User::factory()->create())
        ->getJson(route('books.index', ['category' => $book->category_id]))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $book->id);
    $this->get(route('books.index', ['category' => $book->category_id]))
        ->assertInertia(fn (Assert $page) => $page->component('books/index')->has('books', 1)->where('filters.category', $book->category_id));
    $this->getJson(route('books.index', ['category' => '']))->assertOk()->assertJsonCount(2, 'data');
});

test('empty categories and an empty library return empty collections', function () {
    $category = Category::factory()->create();
    $this->actingAs(User::factory()->create())->getJson(route('books.index'))->assertOk()->assertExactJson(['data' => []]);
    $this->getJson(route('books.index', ['category' => $category->id]))->assertOk()->assertExactJson(['data' => []]);
});

test('invalid category filters are rejected', function (mixed $category) {
    $this->actingAs(User::factory()->create())->getJson(route('books.index', ['category' => $category]))
        ->assertUnprocessable()->assertJsonValidationErrors('category');
})->with(['missing category' => [9999], 'text' => ['abc'], 'array' => [[1]]]);

test('a book is created from validated and trimmed fields', function () {
    $category = Category::factory()->create();
    $payload = ['title' => '  Un libro nuevo  ', 'author' => '  Una autora  ', 'description' => '  Una descripción  ', 'published_year' => 2026, 'category_id' => $category->id, 'id' => 9000];
    $response = $this->actingAs(User::factory()->create())->postJson(route('books.store'), $payload)
        ->assertCreated()->assertJsonPath('data.title', 'Un libro nuevo')->assertJsonPath('data.category.id', $category->id);
    $this->assertDatabaseHas('books', ['id' => $response->json('data.id'), 'title' => 'Un libro nuevo', 'author' => 'Una autora', 'description' => 'Una descripción', 'published_year' => 2026]);
    $this->assertDatabaseMissing('books', ['id' => 9000]);
});

test('description is optional and both year boundaries are accepted', function (int $year) {
    $payload = Book::factory()->make(['published_year' => $year])->toArray();
    unset($payload['description']);
    $this->actingAs(User::factory()->create())->postJson(route('books.store'), $payload)
        ->assertCreated()->assertJsonPath('data.description', null)->assertJsonPath('data.published_year', $year);
})->with([1000, 2026]);

test('a book can be updated and reassigned to another category', function () {
    $book = Book::factory()->create();
    $category = Category::factory()->create();
    $payload = ['title' => 'Título actualizado', 'author' => 'Otra autora', 'description' => null, 'published_year' => 2001, 'category_id' => $category->id];
    $this->actingAs(User::factory()->create())->putJson(route('books.update', $book), $payload)
        ->assertOk()->assertJsonPath('data.category.id', $category->id)->assertJsonPath('data.description', null);
    $this->assertDatabaseHas('books', ['id' => $book->id, ...$payload]);
});

test('all required book fields are validated on creation and update', function (string $operation) {
    $book = Book::factory()->create();
    $method = $operation === 'store' ? 'postJson' : 'putJson';
    $uri = $operation === 'store' ? route('books.store') : route('books.update', $book);
    $this->actingAs(User::factory()->create())->{$method}($uri, [])
        ->assertUnprocessable()->assertJsonValidationErrors(['title', 'author', 'published_year', 'category_id'])
        ->assertJsonPath('errors.title.0', 'El título es obligatorio.');
    $this->assertModelExists($book);
    $this->assertDatabaseCount('books', 1);
})->with(['store', 'update']);

test('invalid book values cannot be stored or updated', function (string $field, mixed $value, string $operation) {
    $book = Book::factory()->create();
    $payload = Book::factory()->make()->toArray();
    $payload[$field] = $value;
    $method = $operation === 'store' ? 'postJson' : 'putJson';
    $uri = $operation === 'store' ? route('books.store') : route('books.update', $book);
    $this->actingAs(User::factory()->create())->{$method}($uri, $payload)
        ->assertUnprocessable()->assertJsonValidationErrors($field);
    $this->assertDatabaseCount('books', 1);
    $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => $book->title, 'author' => $book->author, 'published_year' => $book->published_year, 'category_id' => $book->category_id]);
})->with([
    'short title' => ['title', 'ab'], 'long title' => ['title', str_repeat('a', 256)],
    'blank title' => ['title', '   '], 'title type' => ['title', ['invalid']],
    'blank author' => ['author', '  '], 'long author' => ['author', str_repeat('a', 256)],
    'author type' => ['author', 123], 'description type' => ['description', ['invalid']],
    'year too old' => ['published_year', 999], 'future year' => ['published_year', 2027],
    'decimal year' => ['published_year', 2000.5], 'year text' => ['published_year', 'ayer'],
    'missing category' => ['category_id', 9999], 'category type' => ['category_id', 'texto'],
])->with(['store', 'update']);

test('the current year limit follows the server clock', function () {
    $this->travelTo(now()->setDate(2027, 1, 1));
    $payload = Book::factory()->make(['published_year' => 2027])->toArray();
    $this->actingAs(User::factory()->create())->postJson(route('books.store'), $payload)->assertCreated();
});

test('web writes redirect and persist the changes', function () {
    $this->actingAs(User::factory()->create());
    $payload = Book::factory()->make()->toArray();
    $this->post(route('books.store'), $payload, ['X-Inertia' => 'true'])
        ->assertRedirect(route('books.index'))->assertSessionHasNoErrors();
    $book = Book::firstOrFail();
    $this->put(route('books.update', $book), [...$payload, 'title' => 'Libro editado'], ['X-Inertia' => 'true'])
        ->assertRedirect(route('books.index'))->assertSessionHasNoErrors();
    $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Libro editado']);
    $this->delete(route('books.destroy', $book), [], ['X-Inertia' => 'true'])->assertRedirect(route('books.index'));
    $this->assertModelMissing($book);
});

test('web validation redirects with errors and preserves the input', function () {
    $this->actingAs(User::factory()->create())->from(route('books.create'))
        ->post(route('books.store'), ['title' => 'ab'], ['X-Inertia' => 'true', 'Accept' => 'application/json'])
        ->assertRedirect(route('books.create'))->assertSessionHasErrors(['title', 'author', 'published_year', 'category_id'])->assertSessionHasInput('title', 'ab');
    $this->assertDatabaseCount('books', 0);
});

test('a book can be deleted without deleting its category', function () {
    $book = Book::factory()->create();
    $this->actingAs(User::factory()->create())->deleteJson(route('books.destroy', $book))->assertNoContent();
    $this->assertModelMissing($book);
    $this->assertDatabaseHas('categories', ['id' => $book->category_id]);
});

test('missing books produce not found responses', function () {
    $this->actingAs(User::factory()->create());
    $this->get(route('books.edit', 9999))->assertNotFound();
    $this->putJson(route('books.update', 9999), [])->assertNotFound();
    $this->deleteJson(route('books.destroy', 9999))->assertNotFound();
});

test('authenticated users manage the same shared catalog', function () {
    $book = Book::factory()->create();
    $this->actingAs(User::factory()->create())->getJson(route('books.index'))->assertJsonPath('data.0.id', $book->id);
    $this->actingAs(User::factory()->create())->deleteJson(route('books.destroy', $book))->assertNoContent();
    $this->assertModelMissing($book);
});
