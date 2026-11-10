<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexBookRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\CategoryResource;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function index(IndexBookRequest $request): Response|AnonymousResourceCollection
    {
        $categoryId = $request->filled('category') ? $request->integer('category') : null;
        $books = Book::query()->with('category')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderBy('title')->orderBy('id')->get();

        if (! $request->header('X-Inertia') && $request->expectsJson()) {
            return BookResource::collection($books);
        }

        return Inertia::render('books/index', [
            'books' => BookResource::collection($books)->resolve($request),
            'categories' => CategoryResource::collection(Category::query()->orderBy('name')->get())->resolve($request),
            'filters' => ['category' => $categoryId],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('books/create', [
            'categories' => CategoryResource::collection(Category::query()->orderBy('name')->get())->resolve($request),
            'currentYear' => now()->year,
        ]);
    }

    public function store(StoreBookRequest $request): BookResource|RedirectResponse
    {
        $book = Book::create($request->validated());

        if (! $request->header('X-Inertia') && $request->expectsJson()) {
            return BookResource::make($book->load('category'));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Libro creado.']);

        return to_route('books.index');
    }

    public function edit(Request $request, Book $book): Response
    {
        return Inertia::render('books/edit', [
            'book' => BookResource::make($book->load('category'))->resolve($request),
            'categories' => CategoryResource::collection(Category::query()->orderBy('name')->get())->resolve($request),
            'currentYear' => now()->year,
        ]);
    }

    public function update(UpdateBookRequest $request, Book $book): BookResource|RedirectResponse
    {
        $book->update($request->validated());

        if (! $request->header('X-Inertia') && $request->expectsJson()) {
            return BookResource::make($book->load('category'));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Libro actualizado.']);

        return to_route('books.index');
    }

    public function destroy(Request $request, Book $book): HttpResponse|RedirectResponse
    {
        $book->delete();

        if (! $request->header('X-Inertia') && $request->expectsJson()) {
            return response()->noContent();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Libro eliminado.']);

        return to_route('books.index');
    }
}
