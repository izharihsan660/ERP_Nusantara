<?php

namespace App\Http\Middleware;

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
                        'read_at' => $notification->read_at?->format('Y-m-d H:i'),
                        'created_at' => $notification->created_at?->diffForHumans(),
                    ]),
            ] : ['unread_count' => 0, 'items' => []],
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
            ['label' => 'Quotation', 'route' => 'quotations.index', 'permission' => 'Quotation lihat', 'icon' => 'FileText'],
            ['label' => 'Purchase Order NAJ', 'route' => 'purchase-orders.index', 'permission' => 'lihat_purchase_order', 'icon' => 'ClipboardList'],
            ['label' => 'Permintaan Dana', 'route' => 'permintaan-dana.index', 'permission' => 'lihat_pd', 'icon' => 'HandCoins'],
            ['type' => 'section', 'label' => 'Laporan'],
            ['label' => 'Rekapan PO', 'route' => 'laporan.rekapan-po', 'permission' => 'laporan_rekapan_po', 'icon' => 'BarChart3'],
            ['label' => 'Rekapan WIP', 'route' => 'laporan.rekapan-wip', 'permission' => 'laporan_rekapan_wip', 'icon' => 'BarChart3'],
            ['label' => 'Rekapan SPB', 'route' => 'laporan.rekapan-spb', 'permission' => 'laporan_rekapan_spb', 'icon' => 'BarChart3'],
            ['label' => 'Rekapan Invoice', 'route' => 'laporan.rekapan-invoice', 'permission' => 'laporan_rekapan_invoice', 'icon' => 'BarChart3'],
            ['label' => 'Rekapan PD', 'route' => 'laporan.rekapan-pd', 'permission' => 'laporan_rekapan_pd', 'icon' => 'BarChart3'],
            ['label' => 'Laporan Profit', 'route' => 'laporan.profit', 'permission' => 'laporan_profit', 'icon' => 'BarChart3'],
            ['label' => 'Outstanding', 'route' => 'laporan.outstanding', 'permission' => 'laporan_outstanding', 'icon' => 'BarChart3'],
            ['label' => 'Customer', 'route' => 'customers.index', 'permission' => 'Customer lihat', 'icon' => 'Building2'],
            ['label' => 'Katalog', 'route' => 'katalog.index', 'permission' => 'Katalog lihat', 'icon' => 'PackageSearch'],
            ['label' => 'Vendor', 'route' => 'vendors.index', 'permission' => 'Vendor lihat', 'icon' => 'Truck'],
            ['label' => 'Site', 'route' => 'sites.index', 'permission' => 'Site lihat', 'icon' => 'MapPin'],
            ['label' => 'Template Dokumen', 'route' => 'document-templates.index', 'permission' => 'Template Dokumen lihat', 'icon' => 'FileText'],
            ['label' => 'Jabatan', 'route' => 'roles.index', 'permission' => 'Jabatan lihat', 'icon' => 'ShieldCheck'],
            ['label' => 'User', 'route' => 'users.index', 'permission' => 'User lihat', 'icon' => 'Users'],
        ];

        return array_values(array_filter(
            $items,
            fn (array $item): bool => ($item['type'] ?? null) === 'section' || $item['permission'] === null || in_array($item['permission'], $permissions, true),
        ));
    }
}
