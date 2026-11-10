import { Form, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, store, update } from '@/routes/books';
import { index as categoriesIndex } from '@/routes/categories';
import type { Book, Category } from '@/types/library';

export default function BookForm({
    book,
    categories,
    currentYear,
}: {
    book?: Book;
    categories: Category[];
    currentYear: number;
}) {
    if (categories.length === 0) {
        return (
            <div className="space-y-4 rounded-xl border p-6">
                <p>Crea una categoría antes de registrar tu primer libro.</p>
                <Button asChild>
                    <Link href={categoriesIndex()}>Crear una categoría</Link>
                </Button>
            </div>
        );
    }

    return (
        <Form
            {...(book ? update.form(book.id) : store.form())}
            className="space-y-6"
        >
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="title">Título</Label>
                        <Input
                            id="title"
                            name="title"
                            defaultValue={book?.title}
                            minLength={3}
                            maxLength={255}
                            required
                            autoFocus
                            aria-invalid={!!errors.title}
                            aria-describedby="title-error"
                        />
                        <InputError id="title-error" message={errors.title} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="author">Autor</Label>
                        <Input
                            id="author"
                            name="author"
                            defaultValue={book?.author}
                            maxLength={255}
                            required
                            aria-invalid={!!errors.author}
                            aria-describedby="author-error"
                        />
                        <InputError id="author-error" message={errors.author} />
                    </div>
                    <div className="grid gap-6 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="published_year">
                                Año de publicación
                            </Label>
                            <Input
                                id="published_year"
                                name="published_year"
                                type="number"
                                min={1000}
                                max={currentYear}
                                step={1}
                                defaultValue={book?.published_year}
                                required
                                aria-invalid={!!errors.published_year}
                                aria-describedby="year-help year-error"
                            />
                            <p
                                id="year-help"
                                className="text-sm text-muted-foreground"
                            >
                                Entre 1000 y {currentYear}.
                            </p>
                            <InputError
                                id="year-error"
                                message={errors.published_year}
                            />
                        </div>
                        <div className="grid content-start gap-2">
                            <Label htmlFor="category_id">Categoría</Label>
                            <select
                                id="category_id"
                                name="category_id"
                                defaultValue={book?.category_id ?? ''}
                                required
                                className="h-11 w-full min-w-0 rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                aria-invalid={!!errors.category_id}
                                aria-describedby="category-error"
                            >
                                <option value="" disabled>
                                    Selecciona una categoría
                                </option>
                                {categories.map((category) => (
                                    <option
                                        key={category.id}
                                        value={category.id}
                                    >
                                        {category.name}
                                    </option>
                                ))}
                            </select>
                            <InputError
                                id="category-error"
                                message={errors.category_id}
                            />
                        </div>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="description">
                            Descripción{' '}
                            <span className="font-normal text-muted-foreground">
                                (opcional)
                            </span>
                        </Label>
                        <textarea
                            id="description"
                            name="description"
                            defaultValue={book?.description ?? ''}
                            rows={5}
                            className="w-full resize-y rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            aria-invalid={!!errors.description}
                            aria-describedby="description-error"
                        />
                        <InputError
                            id="description-error"
                            message={errors.description}
                        />
                    </div>
                    <div className="flex flex-wrap gap-3 border-t pt-6">
                        <Button
                            type="submit"
                            disabled={processing}
                            className="min-h-11"
                        >
                            {processing
                                ? 'Guardando…'
                                : book
                                  ? 'Guardar cambios'
                                  : 'Crear libro'}
                        </Button>
                        <Button variant="outline" asChild className="min-h-11">
                            <Link href={index()}>Cancelar</Link>
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
