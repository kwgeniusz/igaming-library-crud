import { Head } from '@inertiajs/react';
import BookForm from '@/components/book-form';
import { index } from '@/routes/books';
import type { Book, Category } from '@/types/library';

export default function EditBook({
    book,
    categories,
    currentYear,
}: {
    book: Book;
    categories: Category[];
    currentYear: number;
}) {
    return (
        <>
            <Head title="Editar libro" />
            <div className="w-full max-w-3xl space-y-8 p-4 sm:p-8">
                <header className="space-y-2">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Editar libro
                    </h1>
                    <p className="break-words text-muted-foreground">
                        Actualiza la información de «{book.title}».
                    </p>
                </header>
                <BookForm
                    book={book}
                    categories={categories}
                    currentYear={currentYear}
                />
            </div>
        </>
    );
}

EditBook.layout = {
    breadcrumbs: [{ title: 'Libros', href: index() }],
};
