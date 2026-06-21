<?php

namespace App\Http\Middleware;

use App\Services\SidebarBadgeService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $permissions = $user?->getAllPermissions()->pluck('name')->values() ?? collect();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    ...$user->only(['id', 'name', 'email']),
                    'roles' => $user->getRoleNames()->values(),
                    'permissions' => $permissions,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'navigation' => fn () => $user ? $this->navigation($permissions->all()) : [],
            'notifications' => fn () => $user ? [
                'unread_count' => $user->unreadNotifications()->count(),
                'items' => $user->notifications()
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(fn ($notification): array => [
                        'id' => $notification->id,
                        'title' => $notification->data['title'] ?? 'Notifikasi',
                        'message' => $notification->data['message'] ?? '',
                        'url' => $notification->data['url'] ?? route('dashboard'),
                        'icon' => $notification->data['icon'] ?? 'Bell',
                        'read_at' => $notification->read_at?->format('Y-m-d H:i'),
                        'created_at' => $notification->created_at?->diffForHumans(),
                    ]),
            ] : ['unread_count' => 0, 'items' => []],
            'sidebar_badges' => fn () => SidebarBadgeService::getBadges($user),
        ];
    }

    /**
     * @param  array<int, string>  $permissions
     * @return array<int, array<string, mixed>>
     */
    private function navigation(array $permissions): array
    {
        $items = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'permission' => null, 'icon' => 'LayoutDashboard'],
            ['label' => 'Quotation', 'route' => 'quotations.index', 'permission' => 'lihat_quotation', 'icon' => 'FileText'],
            ['label' => 'Purchase Order NAJ', 'route' => 'purchase-orders.index', 'permission' => 'lihat_purchase_order', 'icon' => 'ClipboardList'],
            ['label' => 'Permintaan Dana', 'route' => 'permintaan-dana.index', 'permission' => 'lihat_pd', 'icon' => 'HandCoins'],
            ['label' => 'Laporan', 'route' => 'laporan.index', 'permission' => 'laporan_rekapan_po', 'icon' => 'BarChart3'],
            ['type' => 'section', 'label' => 'Master Data'],
            ['label' => 'Customer', 'route' => 'customers.index', 'permission' => 'lihat_customer', 'icon' => 'Building2'],
            ['label' => 'Katalog', 'route' => 'katalog.index', 'permission' => 'lihat_katalog', 'icon' => 'PackageSearch'],
            ['label' => 'Vendor', 'route' => 'vendors.index', 'permission' => 'lihat_vendor', 'icon' => 'Truck'],
            ['label' => 'Site', 'route' => 'sites.index', 'permission' => 'lihat_site', 'icon' => 'MapPin'],
            ['label' => 'Template Dokumen', 'route' => 'document-templates.index', 'permission' => 'lihat_template', 'icon' => 'FileText'],
            ['type' => 'section', 'label' => 'Konfigurasi'],
            ['label' => 'Jabatan', 'route' => 'roles.index', 'permission' => 'lihat_jabatan', 'icon' => 'ShieldCheck'],
            ['label' => 'User', 'route' => 'users.index', 'permission' => 'lihat_user', 'icon' => 'Users'],
            ['label' => 'Settings', 'route' => 'settings.index', 'permission' => 'lihat_jabatan', 'icon' => 'Settings'],
        ];

        return array_values(array_filter(
            $items,
            fn (array $item): bool => ($item['type'] ?? null) === 'section' || $item['permission'] === null || in_array($item['permission'], $permissions, true),
        ));
    }
}
