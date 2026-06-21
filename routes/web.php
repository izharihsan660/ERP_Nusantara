<?php

use App\Http\Controllers\Access\RoleController;
use App\Http\Controllers\Access\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\MasterData\DocumentTemplateController;
use App\Http\Controllers\MasterData\KatalogController;
use App\Http\Controllers\MasterData\SiteController;
use App\Http\Controllers\MasterData\VendorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Transaction\InvoiceController;
use App\Http\Controllers\Transaction\PermintaanDanaController;
use App\Http\Controllers\Transaction\PurchaseOrderController;
use App\Http\Controllers\Transaction\QuotationController;
use App\Http\Controllers\Transaction\SalesOrderController;
use App\Http\Controllers\Transaction\SpbController;
use App\Http\Controllers\Transaction\WipOrderController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/laporan/rekapan-po', [LaporanController::class, 'rekapanPo'])->middleware('permission:laporan_rekapan_po')->name('laporan.rekapan-po');
    Route::get('/laporan/rekapan-wip', [LaporanController::class, 'rekapanWip'])->middleware('permission:laporan_rekapan_wip')->name('laporan.rekapan-wip');
    Route::get('/laporan/rekapan-spb', [LaporanController::class, 'rekapanSpb'])->middleware('permission:laporan_rekapan_spb')->name('laporan.rekapan-spb');
    Route::get('/laporan/rekapan-invoice', [LaporanController::class, 'rekapanInvoice'])->middleware('permission:laporan_rekapan_invoice')->name('laporan.rekapan-invoice');
    Route::get('/laporan/rekapan-pd', [LaporanController::class, 'rekapanPd'])->middleware('permission:laporan_rekapan_pd')->name('laporan.rekapan-pd');
    Route::get('/laporan/profit', [LaporanController::class, 'profit'])->middleware('permission:laporan_profit')->name('laporan.profit');
    Route::get('/laporan/outstanding', [LaporanController::class, 'outstanding'])->middleware('permission:laporan_outstanding')->name('laporan.outstanding');
    Route::get('/laporan/{tipe}/export', [LaporanController::class, 'export'])->middleware('permission:laporan_rekapan_po|laporan_rekapan_wip|laporan_rekapan_spb|laporan_rekapan_invoice|laporan_rekapan_pd|laporan_profit|laporan_outstanding')->name('laporan.export');

    Route::get('/quotations', [QuotationController::class, 'index'])->middleware('permission:lihat_quotation')->name('quotations.index');
    Route::get('/quotations/create', [QuotationController::class, 'create'])->middleware('permission:buat_quotation')->name('quotations.create');
    Route::post('/quotations', [QuotationController::class, 'store'])->middleware('permission:buat_quotation')->name('quotations.store');
    Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->middleware('permission:lihat_quotation')->name('quotations.show');
    Route::post('/quotations/{quotation}/submit', [QuotationController::class, 'submit'])->middleware('permission:buat_quotation')->name('quotations.submit');
    Route::post('/quotations/{quotation}/approve', [QuotationController::class, 'approve'])->middleware('permission:approve_quotation')->name('quotations.approve');
    Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])->middleware('permission:approve_quotation')->name('quotations.reject');
    Route::post('/quotations/{quotation}/void', [QuotationController::class, 'void'])->middleware('permission:void_quotation')->name('quotations.void');
    Route::get('/quotations/{quotation}/download', [QuotationController::class, 'download'])->middleware('permission:download_pdf_quotation')->name('quotations.download');
    Route::post('/quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->middleware('permission:buat_quotation')->name('quotations.duplicate');
    Route::post('/quotations/{quotation}/sales-orders', [SalesOrderController::class, 'store'])->middleware('permission:input_sales_order')->name('quotations.sales-orders.store');
    Route::post('/sales-orders/{salesOrder}/void', [SalesOrderController::class, 'void'])->middleware('permission:void_sales_order')->name('sales-orders.void');
    Route::post('/sales-orders/{salesOrder}/wip-orders', [WipOrderController::class, 'store'])->middleware('permission:buat_wip')->name('sales-orders.wip-orders.store');
    Route::post('/wip-orders/{wipOrder}/void', [WipOrderController::class, 'void'])->middleware('permission:void_wip')->name('wip-orders.void');
    Route::post('/wip-orders/{wipOrder}/spb', [SpbController::class, 'storeFromWip'])->middleware('permission:buat_spb')->name('wip-orders.spb.store');

    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->middleware('permission:lihat_purchase_order')->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->middleware('permission:buat_purchase_order')->name('purchase-orders.create');
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->middleware('permission:buat_purchase_order')->name('purchase-orders.store');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->middleware('permission:lihat_purchase_order')->name('purchase-orders.show');
    Route::post('/purchase-orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit'])->middleware('permission:buat_purchase_order')->name('purchase-orders.submit');
    Route::patch('/purchase-orders/{purchaseOrder}/referensi', [PurchaseOrderController::class, 'updateReferensi'])->middleware('permission:buat_purchase_order')->name('purchase-orders.referensi.update');
    Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->middleware('permission:approve_purchase_order')->name('purchase-orders.approve');
    Route::post('/purchase-orders/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject'])->middleware('permission:approve_purchase_order')->name('purchase-orders.reject');
    Route::post('/purchase-orders/{purchaseOrder}/void', [PurchaseOrderController::class, 'void'])->middleware('permission:void_purchase_order')->name('purchase-orders.void');
    Route::get('/purchase-orders/{purchaseOrder}/download', [PurchaseOrderController::class, 'download'])->middleware('permission:download_pdf_purchase_order')->name('purchase-orders.download');
    Route::post('/purchase-orders/{purchaseOrder}/spb', [SpbController::class, 'storeFromPurchaseOrder'])->middleware('permission:buat_spb')->name('purchase-orders.spb.store');

    Route::get('/permintaan-dana', [PermintaanDanaController::class, 'index'])->middleware('permission:lihat_pd')->name('permintaan-dana.index');
    Route::get('/permintaan-dana/create', [PermintaanDanaController::class, 'create'])->middleware('permission:buat_pd')->name('permintaan-dana.create');
    Route::post('/permintaan-dana', [PermintaanDanaController::class, 'store'])->middleware('permission:buat_pd')->name('permintaan-dana.store');
    Route::get('/permintaan-dana/{permintaanDana}', [PermintaanDanaController::class, 'show'])->middleware('permission:lihat_pd')->name('permintaan-dana.show');
    Route::post('/permintaan-dana/{permintaanDana}/submit', [PermintaanDanaController::class, 'submit'])->middleware('permission:buat_pd')->name('permintaan-dana.submit');
    Route::post('/permintaan-dana/{permintaanDana}/approve', [PermintaanDanaController::class, 'approve'])->middleware('permission:approve_pd')->name('permintaan-dana.approve');
    Route::post('/permintaan-dana/{permintaanDana}/reject', [PermintaanDanaController::class, 'reject'])->middleware('permission:approve_pd')->name('permintaan-dana.reject');
    Route::post('/permintaan-dana/{permintaanDana}/upload-bukti', [PermintaanDanaController::class, 'uploadBukti'])->middleware('permission:upload_bukti_pd')->name('permintaan-dana.upload-bukti');
    Route::post('/permintaan-dana/{permintaanDana}/void', [PermintaanDanaController::class, 'void'])->middleware('permission:void_pd')->name('permintaan-dana.void');
    Route::get('/permintaan-dana/{permintaanDana}/download', [PermintaanDanaController::class, 'download'])->middleware('permission:lihat_pd')->name('permintaan-dana.download');
    Route::get('/permintaan-dana-documents/{document}/download', [PermintaanDanaController::class, 'downloadDocument'])->middleware('permission:lihat_pd')->name('permintaan-dana.documents.download');

    Route::post('/spb/{spb}/void', [SpbController::class, 'void'])->middleware('permission:void_spb')->name('spb.void');
    Route::get('/spb/{spb}/download', [SpbController::class, 'download'])->middleware('permission:download_pdf_spb')->name('spb.download');
    Route::post('/spb/{spb}/invoices', [InvoiceController::class, 'store'])->middleware('permission:buat_invoice')->name('spb.invoices.store');
    Route::post('/invoices/{invoice}/pembayaran', [InvoiceController::class, 'updatePembayaran'])->middleware('permission:update_pembayaran_invoice')->name('invoices.pembayaran');
    Route::post('/invoices/{invoice}/upload-ttd', [InvoiceController::class, 'uploadTtd'])->middleware('permission:upload_ttd_invoice')->name('invoices.upload-ttd');
    Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'void'])->middleware('permission:void_invoice')->name('invoices.void');
    Route::get('/invoices/{invoice}/download/{tipe}', [InvoiceController::class, 'download'])->middleware('permission:lihat_invoice')->name('invoices.download');
    Route::get('/invoice-payment-documents/{document}/download', [InvoiceController::class, 'downloadPaymentDocument'])->middleware('permission:lihat_invoice')->name('invoices.payment-documents.download');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:lihat_customer')->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->middleware('permission:tambah_customer')->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->middleware('permission:tambah_customer')->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->middleware('permission:ubah_customer')->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:ubah_customer')->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:hapus_customer')->name('customers.destroy');

    Route::get('/katalog', [KatalogController::class, 'index'])->middleware('permission:lihat_katalog')->name('katalog.index');
    Route::get('/katalog/search', [KatalogController::class, 'search'])->middleware('permission:lihat_katalog')->name('katalog.search');
    Route::get('/katalog/create', [KatalogController::class, 'create'])->middleware('permission:tambah_katalog')->name('katalog.create');
    Route::post('/katalog', [KatalogController::class, 'store'])->middleware('permission:tambah_katalog')->name('katalog.store');
    Route::post('/katalog/import', [KatalogController::class, 'import'])->middleware('permission:import_katalog')->name('katalog.import');
    Route::get('/katalog/{katalog}/edit', [KatalogController::class, 'edit'])->middleware('permission:ubah_katalog')->name('katalog.edit');
    Route::put('/katalog/{katalog}', [KatalogController::class, 'update'])->middleware('permission:ubah_katalog')->name('katalog.update');
    Route::delete('/katalog/{katalog}', [KatalogController::class, 'destroy'])->middleware('permission:hapus_katalog')->name('katalog.destroy');

    Route::get('/vendors', [VendorController::class, 'index'])->middleware('permission:lihat_vendor')->name('vendors.index');
    Route::get('/vendors/create', [VendorController::class, 'create'])->middleware('permission:tambah_vendor')->name('vendors.create');
    Route::post('/vendors', [VendorController::class, 'store'])->middleware('permission:tambah_vendor')->name('vendors.store');
    Route::get('/vendors/{vendor}/edit', [VendorController::class, 'edit'])->middleware('permission:ubah_vendor')->name('vendors.edit');
    Route::put('/vendors/{vendor}', [VendorController::class, 'update'])->middleware('permission:ubah_vendor')->name('vendors.update');
    Route::delete('/vendors/{vendor}', [VendorController::class, 'destroy'])->middleware('permission:hapus_vendor')->name('vendors.destroy');

    Route::get('/sites', [SiteController::class, 'index'])->middleware('permission:lihat_site')->name('sites.index');
    Route::get('/sites/create', [SiteController::class, 'create'])->middleware('permission:tambah_site')->name('sites.create');
    Route::post('/sites', [SiteController::class, 'store'])->middleware('permission:tambah_site')->name('sites.store');
    Route::get('/sites/{site}/edit', [SiteController::class, 'edit'])->middleware('permission:ubah_site')->name('sites.edit');
    Route::put('/sites/{site}', [SiteController::class, 'update'])->middleware('permission:ubah_site')->name('sites.update');
    Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->middleware('permission:hapus_site')->name('sites.destroy');

    Route::get('/document-templates', [DocumentTemplateController::class, 'index'])->middleware('permission:lihat_template')->name('document-templates.index');
    Route::get('/document-templates/create', [DocumentTemplateController::class, 'create'])->middleware('permission:tambah_template')->name('document-templates.create');
    Route::post('/document-templates', [DocumentTemplateController::class, 'store'])->middleware('permission:tambah_template')->name('document-templates.store');
    Route::get('/document-templates/{documentTemplate}/edit', [DocumentTemplateController::class, 'edit'])->middleware('permission:ubah_template')->name('document-templates.edit');
    Route::put('/document-templates/{documentTemplate}', [DocumentTemplateController::class, 'update'])->middleware('permission:ubah_template')->name('document-templates.update');
    Route::delete('/document-templates/{documentTemplate}', [DocumentTemplateController::class, 'destroy'])->middleware('permission:hapus_template')->name('document-templates.destroy');

    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:lihat_jabatan')->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->middleware('permission:tambah_jabatan')->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:tambah_jabatan')->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->middleware('permission:ubah_jabatan')->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:ubah_jabatan')->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:hapus_jabatan')->name('roles.destroy');

    Route::get('/users', [UserController::class, 'index'])->middleware('permission:lihat_user')->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->middleware('permission:tambah_user')->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:tambah_user')->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware('permission:ubah_user')->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:ubah_user')->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:hapus_user')->name('users.destroy');
});

Route::get('/verify/{token}', [QuotationController::class, 'verify'])->name('verify.quotation');

require __DIR__.'/auth.php';
