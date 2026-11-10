<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response|AnonymousResourceCollection
    {
        $categories = Category::query()->withCount('books')->orderBy('name')->get();

        if (! $request->header('X-Inertia') && $request->expectsJson()) {
            return CategoryResource::collection($categories);
        }

        return Inertia::render('categories/index', [
            'categories' => CategoryResource::collection($categories)->resolve($request),
        ]);
    }

    public function store(StoreCategoryRequest $request): CategoryResource|RedirectResponse
    {
        try {
            $category = Category::create($request->validated());
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['name' => 'Ya existe una categoría con este nombre.']);
        }

        if (! $request->header('X-Inertia') && $request->expectsJson()) {
            return CategoryResource::make($category->loadCount('books'));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Categoría creada.']);

        return to_route('categories.index');
    }

    public function destroy(Request $request, Category $category): HttpResponse|JsonResponse|RedirectResponse
    {
        if ($category->books()->exists()) {
            return $this->categoryInUse($request);
        }

        try {
            /** @throws QueryException */
            $category->delete();
        } catch (QueryException $exception) {
            if (Book::query()->whereBelongsTo($category)->exists()) {
                return $this->categoryInUse($request);
            }

            throw $exception;
        }

        if (! $request->header('X-Inertia') && $request->expectsJson()) {
            return response()->noContent();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Categoría eliminada.']);

        return to_route('categories.index');
    }

    private function categoryInUse(Request $request): JsonResponse|RedirectResponse
    {
        $message = 'Esta categoría tiene libros asociados. Reasigna o elimina sus libros antes de eliminarla.';

        if (! $request->header('X-Inertia') && $request->expectsJson()) {
            return response()->json(['message' => $message], 409);
        }

        return to_route('categories.index')->withErrors(['category' => $message]);
    }
}
