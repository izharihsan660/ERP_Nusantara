import AppLayout from '@/Layouts/AppLayout';

export default function AuthenticatedLayout({ header, children }) {
    return <AppLayout title={header?.props?.children ?? 'Dashboard'}>{children}</AppLayout>;
}
