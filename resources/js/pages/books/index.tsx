import { Head, Link, router, usePage } from '@inertiajs/react';
import { BookOpen, Plus } from 'lucide-react';
import DeleteLibraryItem from '@/components/delete-library-item';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { create, destroy, edit, index } from '@/routes/books';
import type { Book, Category } from '@/types/library';

export default function Books({
    books,
    categories,
    filters,
}: {
    books: Book[];
    categories: Category[];
    filters: { category: number | null };
}) {
    const { errors } = usePage().props;

    return (
        <>
            <Head title="Libros" />
            <div className="mx-auto w-full max-w-7xl space-y-8 p-4 sm:p-8">
                <header className="flex flex-wrap items-start justify-between gap-4">
                    <div className="space-y-2">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Libros
                        </h1>
                        <p className="text-muted-foreground">
                            Un catálogo compartido para organizar tus lecturas.
                        </p>
                    </div>
                    <Button asChild className="min-h-11">
                        <Link href={create()}>
                            <Plus aria-hidden="true" />
                            Nuevo libro
                        </Link>
                    </Button>
                </header>
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div className="grid w-full gap-2 sm:max-w-xs">
                        <Label htmlFor="category-filter">
                            Filtrar por categoría
                        </Label>
                        <select
                            id="category-filter"
                            value={filters.category ?? ''}
                            onChange={(event) =>
                                router.visit(
                                    index({
                                        query: {
                                            category:
                                                event.target.value || undefined,
                                        },
                                    }),
                                    {
                                        preserveState: true,
                                        preserveScroll: true,
                                    },
                                )
                            }
                            className="h-11 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            aria-describedby="filter-error"
                        >
                            <option value="">Todas las categorías</option>
                            {categories.map((category) => (
                                <option key={category.id} value={category.id}>
                                    {category.name}
                                </option>
                            ))}
                        </select>
                        <InputError
                            id="filter-error"
                            message={errors.category}
                            role="alert"
                        />
                    </div>
                    <p
                        className="text-sm text-muted-foreground"
                        aria-live="polite"
                    >
                        {books.length} {books.length === 1 ? 'libro' : 'libros'}
                    </p>
                </div>
                {books.length === 0 ? (
                    <div className="flex flex-col items-center gap-4 rounded-xl border border-dashed px-6 py-16 text-center">
                        <BookOpen
                            className="size-8 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <h2 className="text-lg font-medium">
                            {filters.category
                                ? 'No hay libros en esta categoría'
                                : 'Tu biblioteca está por comenzar'}
                        </h2>
                        <p className="max-w-md text-sm text-muted-foreground">
                            {filters.category
                                ? 'Elige otra categoría o añade un libro a esta colección.'
                                : 'Añade tu primer libro y asígnale una categoría para encontrarlo fácilmente.'}
                        </p>
                        {filters.category ? (
                            <Button variant="outline" asChild>
                                <Link href={index()}>Ver todos los libros</Link>
                            </Button>
                        ) : (
                            <Button asChild>
                                <Link href={create()}>Añadir primer libro</Link>
                            </Button>
                        )}
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {books.map((book) => (
                            <li
                                key={book.id}
                                className="grid gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_10rem_auto] lg:items-center"
                            >
                                <div className="min-w-0 space-y-2">
                                    <h2 className="text-lg font-medium break-words">
                                        {book.title}
                                    </h2>
                                    <p className="text-sm break-words text-muted-foreground">
                                        {book.author}{' '}
                                        <span aria-hidden="true">·</span>{' '}
                                        <span className="tabular-nums">
                                            {book.published_year}
                                        </span>
                                    </p>
                                    {book.description && (
                                        <p className="max-w-prose text-sm leading-relaxed break-words text-muted-foreground">
                                            {book.description}
                                        </p>
                                    )}
                                </div>
                                <p className="text-sm break-words">
                                    <span className="sr-only">Categoría: </span>
                                    {book.category.name}
                                </p>
                                <div className="flex flex-wrap gap-2">
                                    <Button
                                        asChild
                                        variant="outline"
                                        className="min-h-11"
                                    >
                                        <Link
                                            href={edit(book.id)}
                                            aria-label={`Editar ${book.title}`}
                                        >
                                            Editar
                                        </Link>
                                    </Button>
                                    <DeleteLibraryItem
                                        action={destroy(book.id)}
                                        name={book.title}
                                        kind="libro"
                                    />
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}

Books.layout = { breadcrumbs: [{ title: 'Libros', href: index() }] };
