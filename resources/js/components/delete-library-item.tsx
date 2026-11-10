import { Form } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

export default function DeleteLibraryItem({
    action,
    name,
    kind,
}: {
    action: { url: string; method: 'delete' };
    name: string;
    kind: 'libro' | 'categoría';
}) {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button
                    variant="outline"
                    className="min-h-11"
                    aria-label={`Eliminar ${name}`}
                >
                    Eliminar
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Eliminar {kind}</DialogTitle>
                <DialogDescription className="break-words">
                    Se eliminará «{name}». Esta acción no se puede deshacer.
                </DialogDescription>
                <Form
                    action={action.url}
                    method={action.method}
                    options={{ preserveScroll: true }}
                    onSuccess={() => setOpen(false)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <InputError
                                message={errors.category}
                                role="alert"
                            />
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button
                                        variant="secondary"
                                        disabled={processing}
                                        autoFocus
                                    >
                                        Cancelar
                                    </Button>
                                </DialogClose>
                                <Button
                                    variant="destructive"
                                    disabled={processing}
                                >
                                    {processing
                                        ? 'Eliminando…'
                                        : `Eliminar ${kind}`}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
