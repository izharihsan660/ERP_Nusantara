import Dropdown from '@/Components/Dropdown';
import { Button } from '@/Components/ui/button';
import { Link, router, usePage } from '@inertiajs/react';
import {
    Bell,
    Building2,
    ClipboardList,
    FileText,
    HandCoins,
    LayoutDashboard,
    MapPin,
    Menu,
    PackageSearch,
    ShieldCheck,
    Truck,
    Users,
    X,
} from 'lucide-react';
import { useState } from 'react';

const icons = {
    Bell,
    Building2,
    ClipboardList,
    FileText,
    HandCoins,
    LayoutDashboard,
    MapPin,
    PackageSearch,
    ShieldCheck,
    Truck,
    Users,
};

export default function AppLayout({ title, children }) {
    const { auth, navigation = [], flash = {}, notifications = { unread_count: 0, items: [] } } = usePage().props;
    const [open, setOpen] = useState(false);
    const readNotification = (notification) => {
        router.post(route('notifications.read', notification.id), {}, { preserveScroll: true });
    };

    return (
        <div className="min-h-screen bg-slate-100 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
            <aside className={`fixed inset-y-0 left-0 z-40 w-72 border-r border-slate-200 bg-white transition-transform dark:border-slate-800 dark:bg-slate-950 lg:translate-x-0 ${open ? 'translate-x-0' : '-translate-x-full'}`}>
                <div className="flex h-16 items-center justify-between border-b border-slate-200 px-5 dark:border-slate-800">
                    <Link href={route('dashboard')} className="font-semibold tracking-normal">
                        PT. Nusantara Abadi Jaya
                    </Link>
                    <Button type="button" variant="ghost" size="icon" className="lg:hidden" onClick={() => setOpen(false)}>
                        <X className="h-4 w-4" />
                    </Button>
                </div>
                <nav className="space-y-1 p-3">
                    {navigation.map((item) => {
                        const Icon = icons[item.icon] ?? LayoutDashboard;
                        const active = route().current(item.route);

                        return (
                            <Link
                                key={item.route}
                                href={route(item.route)}
                                className={`flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition ${active ? 'bg-slate-950 text-white dark:bg-white dark:text-slate-950' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900'}`}
                            >
                                <Icon className="h-4 w-4" />
                                {item.label}
                            </Link>
                        );
                    })}
                </nav>
            </aside>

            {open && <button type="button" aria-label="Tutup menu" className="fixed inset-0 z-30 bg-slate-950/40 lg:hidden" onClick={() => setOpen(false)} />}

            <div className="lg:pl-72">
                <header className="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95 sm:px-6">
                    <div className="flex items-center gap-3">
                        <Button type="button" variant="ghost" size="icon" className="lg:hidden" onClick={() => setOpen(true)}>
                            <Menu className="h-5 w-5" />
                        </Button>
                        <div>
                            <div className="text-sm text-slate-500 dark:text-slate-400">Operasional</div>
                            <div className="font-semibold">{title ?? 'Dashboard'}</div>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <Dropdown>
                            <Dropdown.Trigger>
                                <Button type="button" variant="ghost" size="icon" title="Notifikasi" className="relative">
                                    <Bell className="h-5 w-5" />
                                    {notifications.unread_count > 0 && (
                                        <span className="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold leading-none text-white">
                                            {notifications.unread_count}
                                        </span>
                                    )}
                                </Button>
                            </Dropdown.Trigger>
                            <Dropdown.Content contentClasses="w-80 py-2 bg-white dark:bg-gray-800">
                                <div className="border-b border-slate-200 px-4 pb-2 text-sm font-semibold text-slate-950 dark:border-slate-700 dark:text-white">
                                    Notifikasi
                                </div>
                                <div className="max-h-96 overflow-y-auto py-1">
                                    {notifications.items.length === 0 && (
                                        <div className="px-4 py-3 text-sm text-slate-500">Belum ada notifikasi.</div>
                                    )}
                                    {notifications.items.map((notification) => (
                                        <button
                                            key={notification.id}
                                            type="button"
                                            className={`block w-full px-4 py-3 text-left text-sm hover:bg-slate-100 dark:hover:bg-slate-900 ${notification.read_at ? 'text-slate-500' : 'text-slate-900 dark:text-slate-100'}`}
                                            onClick={() => readNotification(notification)}
                                        >
                                            <div className="font-medium">{notification.title}</div>
                                            <div className="mt-1 line-clamp-2 text-xs text-slate-500">{notification.message}</div>
                                            <div className="mt-1 text-xs text-slate-400">{notification.created_at}</div>
                                        </button>
                                    ))}
                                </div>
                            </Dropdown.Content>
                        </Dropdown>
                        <Dropdown>
                            <Dropdown.Trigger>
                                <button type="button" className="rounded-md px-3 py-2 text-left text-sm hover:bg-slate-100 dark:hover:bg-slate-900">
                                    <div className="font-medium">{auth.user.name}</div>
                                    <div className="text-xs text-slate-500">{auth.user.roles?.join(', ') || 'Tanpa jabatan'}</div>
                                </button>
                            </Dropdown.Trigger>
                            <Dropdown.Content>
                                <Dropdown.Link href={route('profile.edit')}>Profile</Dropdown.Link>
                                <Dropdown.Link href={route('logout')} method="post" as="button">Log Out</Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>
                    </div>
                </header>

                <main className="p-4 sm:p-6">
                    {flash.success && (
                        <div className="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
                            {flash.success}
                        </div>
                    )}
                    {flash.error && (
                        <div className="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                            {flash.error}
                        </div>
                    )}
                    {children}
                </main>
            </div>
        </div>
    );
}
