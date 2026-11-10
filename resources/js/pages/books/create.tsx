import { Head } from '@inertiajs/react';
import BookForm from '@/components/book-form';
import { create, index } from '@/routes/books';
import type { Category } from '@/types/library';

export default function CreateBook({
    categories,
    currentYear,
}: {
    categories: Category[];
    currentYear: number;
}) {
    return (
        <>
            <Head title="Nuevo libro" />
            <div className="w-full max-w-3xl space-y-8 p-4 sm:p-8">
                <header className="space-y-2">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Nuevo libro
                    </h1>
                    <p className="text-muted-foreground">
                        Añade un libro al catálogo compartido de la biblioteca.
                    </p>
                </header>
                <BookForm categories={categories} currentYear={currentYear} />
            </div>
        </>
    );
}

CreateBook.layout = {
    breadcrumbs: [
        { title: 'Libros', href: index() },
        { title: 'Nuevo libro', href: create() },
    ],
};
