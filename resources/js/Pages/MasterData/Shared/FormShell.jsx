import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Save } from 'lucide-react';

export default function FormShell({ title, description, backRoute, processing, children, onSubmit }) {
    return (
        <AppLayout title={title}>
            <Head title={title} />
            <PageHeader
                title={title}
                description={description}
                actions={<Button asChild variant="outline"><Link href={route(backRoute)}>Kembali</Link></Button>}
            />
            <form onSubmit={onSubmit} className="max-w-4xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div className="grid gap-5 md:grid-cols-2">{children}</div>
                <div className="mt-6 flex justify-end">
                    <Button type="submit" disabled={processing}>
                        <Save className="h-4 w-4" />
                        Simpan
                    </Button>
                </div>
            </form>
        </AppLayout>
    );
}
