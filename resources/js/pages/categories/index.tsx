import { Form, Head, Link, usePage } from '@inertiajs/react';
import DeleteLibraryItem from '@/components/delete-library-item';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as booksIndex } from '@/routes/books';
import { destroy, index, store } from '@/routes/categories';
import type { Category } from '@/types/library';

export default function Categories({ categories }: { categories: Category[] }) {
    const { errors } = usePage().props;

    return (
        <>
            <Head title="Categorías" />
            <div className="mx-auto w-full max-w-6xl space-y-8 p-4 sm:p-8">
                <header className="space-y-2">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Categorías
                    </h1>
                    <p className="max-w-prose text-muted-foreground">
                        Organiza el catálogo por temas. Solo puedes eliminar
                        categorías que no tengan libros.
                    </p>
                </header>
                <div className="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
                    <section
                        className="min-w-0 space-y-4"
                        aria-label="Categorías registradas"
                    >
                        <InputError message={errors.category} role="alert" />
                        {categories.length === 0 ? (
                            <div className="space-y-2 rounded-xl border border-dashed p-8">
                                <h2 className="text-lg font-medium">
                                    Crea tu primera categoría
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    Por ejemplo: Novela, Ciencia o Historia.
                                </p>
                            </div>
                        ) : (
                            <ul className="divide-y rounded-xl border">
                                {categories.map((category) => (
                                    <li
                                        key={category.id}
                                        className="flex flex-wrap items-center justify-between gap-4 p-5"
                                    >
                                        <div className="min-w-0 flex-1 space-y-1">
                                            <h2 className="font-medium break-words">
                                                {category.name}
                                            </h2>
                                            <p className="text-sm text-muted-foreground">
                                                {category.books_count}{' '}
                                                {category.books_count === 1
                                                    ? 'libro'
                                                    : 'libros'}
                                            </p>
                                        </div>
                                        {category.books_count ? (
                                            <Button
                                                asChild
                                                variant="outline"
                                                className="min-h-11"
                                            >
                                                <Link
                                                    href={booksIndex({
                                                        query: {
                                                            category:
                                                                category.id,
                                                        },
                                                    })}
                                                >
                                                    Ver libros
                                                </Link>
                                            </Button>
                                        ) : (
                                            <DeleteLibraryItem
                                                action={destroy(category.id)}
                                                name={category.name}
                                                kind="categoría"
                                            />
                                        )}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>
                    <section
                        className="space-y-5 rounded-xl border p-6"
                        aria-labelledby="new-category"
                    >
                        <h2 id="new-category" className="text-lg font-medium">
                            Nueva categoría
                        </h2>
                        <Form
                            {...store.form()}
                            resetOnSuccess
                            options={{ preserveScroll: true }}
                            className="space-y-4"
                        >
                            {({ processing, errors: formErrors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">Nombre</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            required
                                            maxLength={255}
                                            placeholder="Por ejemplo, Novela"
                                            aria-invalid={!!formErrors.name}
                                            aria-describedby="name-error"
                                        />
                                        <InputError
                                            id="name-error"
                                            message={formErrors.name}
                                        />
                                    </div>
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="min-h-11 w-full"
                                    >
                                        {processing
                                            ? 'Creando…'
                                            : 'Crear categoría'}
                                    </Button>
                                </>
                            )}
                        </Form>
                    </section>
                </div>
            </div>
        </>
    );
}

Categories.layout = { breadcrumbs: [{ title: 'Categorías', href: index() }] };
