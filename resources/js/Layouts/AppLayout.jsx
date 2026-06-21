import Dropdown from '@/Components/Dropdown';
import FlashMessage from '@/Components/FlashMessage';
import { Button } from '@/Components/ui/button';
import { Link, router, usePage } from '@inertiajs/react';
import {
    BarChart3,
    Bell,
    Building2,
    ChevronDown,
    ClipboardList,
    FileText,
    HandCoins,
    LayoutDashboard,
    MapPin,
    Menu,
    Moon,
    PackageSearch,
    Settings,
    ShieldCheck,
    Sun,
    Truck,
    Users,
    X,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

const icons = {
    BarChart3,
    Bell,
    Building2,
    ClipboardList,
    FileText,
    HandCoins,
    LayoutDashboard,
    MapPin,
    PackageSearch,
    Settings,
    ShieldCheck,
    Truck,
    Users,
};

function initials(name = '') {
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase() || 'NA';
}

function useTheme() {
    const [theme, setTheme] = useState('light');

    useEffect(() => {
        const storedTheme = localStorage.getItem('theme');
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        const initialTheme = storedTheme || systemTheme;

        document.documentElement.classList.toggle('dark', initialTheme === 'dark');
        setTheme(initialTheme);
    }, []);

    const toggleTheme = () => {
        const nextTheme = theme === 'dark' ? 'light' : 'dark';
        document.documentElement.classList.toggle('dark', nextTheme === 'dark');
        localStorage.setItem('theme', nextTheme);
        setTheme(nextTheme);
    };

    return { theme, toggleTheme };
}

export default function AppLayout({ title, children }) {
    const { auth, navigation = [], notifications = { unread_count: 0, items: [] }, sidebar_badges = {} } = usePage().props;
    const [open, setOpen] = useState(false);
    const { theme, toggleTheme } = useTheme();

    const readNotification = (notification) => {
        router.post(route('notifications.read', notification.id), {}, { preserveScroll: true });
    };

    const readAllNotifications = () => {
        router.post(route('notifications.read-all'), {}, { preserveScroll: true });
    };

    const badgeFor = (item) => {
        if (item.route === 'quotations.index') return (sidebar_badges.quotation || 0) + (sidebar_badges.invoice || 0);
        if (item.route === 'purchase-orders.index') return sidebar_badges.purchase_order || 0;
        if (item.route === 'permintaan-dana.index') return (sidebar_badges.permintaan_dana || 0) + (sidebar_badges.permintaan_dana_procurement || 0);
        if (item.route === 'spb.index') return sidebar_badges.spb || 0;

        return 0;
    };

    const currentTitle = useMemo(() => title || 'Dashboard', [title]);

    return (
        <div className="min-h-screen bg-[hsl(var(--background))] text-[hsl(var(--foreground))]">
            <aside className={`fixed inset-y-0 left-0 z-50 w-[var(--sidebar-width)] border-r border-[hsl(var(--sidebar-border))] bg-[hsl(var(--sidebar-bg))] transition-transform md:translate-x-0 ${open ? 'translate-x-0' : '-translate-x-full'}`}>
                <div className="flex h-14 items-center justify-between border-b border-[hsl(var(--sidebar-border))] px-4">
                    <Link href={route('dashboard')} className="flex min-w-0 items-center gap-3">
                        <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))]">
                            <Building2 className="h-4 w-4" />
                        </span>
                        <span className="truncate text-sm font-semibold">PT. Nusantara Abadi Jaya</span>
                    </Link>
                    <Button type="button" variant="ghost" size="icon" className="md:hidden" onClick={() => setOpen(false)}>
                        <X className="h-4 w-4" />
                    </Button>
                </div>

                <nav className="h-[calc(100vh-3.5rem)] space-y-1 overflow-y-auto p-3">
                    {navigation.map((item) => {
                        if (item.type === 'section') {
                            return (
                                <div key={`section-${item.label}`} className="px-3 pb-2 pt-5 text-xs font-medium uppercase tracking-wider text-[hsl(var(--muted-foreground))] first:pt-2">
                                    {item.label}
                                </div>
                            );
                        }

                        const Icon = icons[item.icon] ?? LayoutDashboard;
                        const active = route().current(item.route);
                        const badgeCount = badgeFor(item);

                        return (
                            <Link
                                key={item.route}
                                href={route(item.route)}
                                className={`flex items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors ${active ? 'bg-[hsl(var(--sidebar-item-active-bg))] text-[hsl(var(--sidebar-item-active-text))]' : 'text-[hsl(var(--foreground))] hover:bg-[hsl(var(--sidebar-item-hover))]'}`}
                                onClick={() => setOpen(false)}
                            >
                                <Icon className={`h-4 w-4 shrink-0 ${active ? 'text-[hsl(var(--sidebar-item-active-text))]' : 'text-[hsl(var(--muted-foreground))]'}`} />
                                <span className="min-w-0 flex-1 truncate font-medium">{item.label}</span>
                                {badgeCount > 0 && (
                                    <span className="ml-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-center text-xs font-semibold text-white">
                                        {badgeCount}
                                    </span>
                                )}
                            </Link>
                        );
                    })}
                </nav>
            </aside>

            {open && <button type="button" aria-label="Tutup menu" className="fixed inset-0 z-40 bg-slate-950/40 md:hidden" onClick={() => setOpen(false)} />}

            <div className="md:pl-[var(--sidebar-width)]">
                <header className="sticky top-0 z-40 flex h-14 items-center justify-between border-b border-[hsl(var(--border))] bg-[hsl(var(--background))]/95 px-4 backdrop-blur sm:px-6">
                    <div className="flex min-w-0 items-center gap-3">
                        <Button type="button" variant="ghost" size="icon" className="md:hidden" onClick={() => setOpen(true)}>
                            <Menu className="h-4 w-4" />
                        </Button>
                        <div className="min-w-0 text-sm text-[hsl(var(--muted-foreground))]">
                            <span>ERP</span>
                            <span className="mx-2">/</span>
                            <span className="font-medium text-[hsl(var(--foreground))]">{currentTitle}</span>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button type="button" variant="ghost" size="icon" onClick={toggleTheme} aria-label="Toggle dark mode">
                            {theme === 'dark' ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
                        </Button>

                        <Dropdown>
                            <Dropdown.Trigger>
                                <Button type="button" variant="ghost" size="icon" className="relative">
                                    <Bell className="h-4 w-4" />
                                    {notifications.unread_count > 0 && (
                                        <span className="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold leading-none text-white">
                                            {notifications.unread_count}
                                        </span>
                                    )}
                                </Button>
                            </Dropdown.Trigger>
                            <Dropdown.Content contentClasses="w-80 overflow-hidden rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--popover))] py-0 shadow-lg">
                                <div className="flex items-center justify-between border-b border-[hsl(var(--border))] px-4 py-3 text-sm">
                                    <span className="font-semibold text-[hsl(var(--popover-foreground))]">Notifikasi</span>
                                    {notifications.unread_count > 0 && (
                                        <button type="button" className="text-xs font-medium text-[hsl(var(--primary))] hover:underline" onClick={readAllNotifications}>
                                            Tandai semua dibaca
                                        </button>
                                    )}
                                </div>
                                <div className="max-h-96 overflow-y-auto py-1">
                                    {notifications.items.length === 0 && (
                                        <div className="px-4 py-6 text-center text-sm text-[hsl(var(--muted-foreground))]">Belum ada notifikasi.</div>
                                    )}
                                    {notifications.items.map((notification) => {
                                        const Icon = icons[notification.icon] ?? Bell;

                                        return (
                                            <button
                                                key={notification.id}
                                                type="button"
                                                className={`flex w-full gap-3 px-4 py-3 text-left text-sm hover:bg-[hsl(var(--accent))] ${notification.read_at ? 'text-[hsl(var(--muted-foreground))]' : 'text-[hsl(var(--foreground))]'}`}
                                                onClick={() => readNotification(notification)}
                                            >
                                                <Icon className="mt-0.5 h-4 w-4 shrink-0 text-[hsl(var(--muted-foreground))]" />
                                                <span className="min-w-0">
                                                    <span className="block truncate font-medium">{notification.title}</span>
                                                    <span className="mt-1 line-clamp-2 block text-xs text-[hsl(var(--muted-foreground))]">{notification.message}</span>
                                                    <span className="mt-1 block text-xs text-[hsl(var(--muted-foreground))]">{notification.created_at}</span>
                                                </span>
                                            </button>
                                        );
                                    })}
                                </div>
                            </Dropdown.Content>
                        </Dropdown>

                        <Dropdown>
                            <Dropdown.Trigger>
                                <button type="button" className="flex items-center gap-2 rounded-md px-2 py-1.5 text-left hover:bg-[hsl(var(--accent))]">
                                    <span className="flex h-8 w-8 items-center justify-center rounded-full bg-[hsl(var(--primary))] text-xs font-semibold text-[hsl(var(--primary-foreground))]">
                                        {initials(auth.user.name)}
                                    </span>
                                    <span className="hidden min-w-0 sm:block">
                                        <span className="block truncate text-sm font-medium leading-none">{auth.user.name}</span>
                                        <span className="mt-1 block truncate text-xs text-[hsl(var(--muted-foreground))]">{auth.user.roles?.join(', ') || 'Tanpa jabatan'}</span>
                                    </span>
                                    <ChevronDown className="hidden h-4 w-4 text-[hsl(var(--muted-foreground))] sm:block" />
                                </button>
                            </Dropdown.Trigger>
                            <Dropdown.Content>
                                <Dropdown.Link href={route('profile.edit')}>Profile</Dropdown.Link>
                                <Dropdown.Link href={route('logout')} method="post" as="button">Log Out</Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>
                    </div>
                </header>

                <main className="mx-auto w-full max-w-[1600px] p-4 sm:p-6">
                    {children}
                </main>
            </div>
            <FlashMessage />
        </div>
    );
}
