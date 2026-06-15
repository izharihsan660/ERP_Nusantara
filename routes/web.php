<?php

use App\Http\Controllers\Access\RoleController;
use App\Http\Controllers\Access\UserController;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/quotations', [QuotationController::class, 'index'])->middleware('permission:Quotation lihat')->name('quotations.index');
    Route::get('/quotations/create', [QuotationController::class, 'create'])->middleware('permission:Quotation buat')->name('quotations.create');
    Route::post('/quotations', [QuotationController::class, 'store'])->middleware('permission:Quotation buat')->name('quotations.store');
    Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->middleware('permission:Quotation lihat')->name('quotations.show');
    Route::post('/quotations/{quotation}/submit', [QuotationController::class, 'submit'])->middleware('permission:Quotation buat')->name('quotations.submit');
    Route::post('/quotations/{quotation}/approve', [QuotationController::class, 'approve'])->middleware('permission:Quotation approve')->name('quotations.approve');
    Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])->middleware('permission:Quotation approve')->name('quotations.reject');
    Route::post('/quotations/{quotation}/void', [QuotationController::class, 'void'])->middleware('permission:Quotation void')->name('quotations.void');
    Route::get('/quotations/{quotation}/download', [QuotationController::class, 'download'])->middleware('permission:Quotation download_pdf')->name('quotations.download');
    Route::post('/quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->middleware('permission:Quotation buat')->name('quotations.duplicate');
    Route::post('/quotations/{quotation}/sales-orders', [SalesOrderController::class, 'store'])->middleware('permission:input_sales_order')->name('quotations.sales-orders.store');
    Route::post('/sales-orders/{salesOrder}/void', [SalesOrderController::class, 'void'])->middleware('permission:void_sales_order')->name('sales-orders.void');
    Route::post('/sales-orders/{salesOrder}/wip-orders', [WipOrderController::class, 'store'])->middleware('permission:WIP buat')->name('sales-orders.wip-orders.store');
    Route::post('/wip-orders/{wipOrder}/void', [WipOrderController::class, 'void'])->middleware('permission:WIP void')->name('wip-orders.void');
    Route::post('/wip-orders/{wipOrder}/spb', [SpbController::class, 'storeFromWip'])->middleware('permission:buat_spb')->name('wip-orders.spb.store');

    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->middleware('permission:lihat_purchase_order')->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->middleware('permission:buat_purchase_order')->name('purchase-orders.create');
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->middleware('permission:buat_purchase_order')->name('purchase-orders.store');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->middleware('permission:lihat_purchase_order')->name('purchase-orders.show');
    Route::post('/purchase-orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit'])->middleware('permission:buat_purchase_order')->name('purchase-orders.submit');
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

    Route::post('/spb/{spb}/void', [SpbController::class, 'void'])->middleware('permission:void_spb')->name('spb.void');
    Route::get('/spb/{spb}/download', [SpbController::class, 'download'])->middleware('permission:download_pdf_spb')->name('spb.download');
    Route::post('/spb/{spb}/invoices', [InvoiceController::class, 'store'])->middleware('permission:buat_invoice')->name('spb.invoices.store');
    Route::post('/invoices/{invoice}/pembayaran', [InvoiceController::class, 'updatePembayaran'])->middleware('permission:update_pembayaran_invoice')->name('invoices.pembayaran');
    Route::post('/invoices/{invoice}/upload-ttd', [InvoiceController::class, 'uploadTtd'])->middleware('permission:upload_ttd_invoice')->name('invoices.upload-ttd');
    Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'void'])->middleware('permission:void_invoice')->name('invoices.void');
    Route::get('/invoices/{invoice}/download/{tipe}', [InvoiceController::class, 'download'])->middleware('permission:lihat_invoice')->name('invoices.download');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:Customer lihat')->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->middleware('permission:Customer tambah')->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->middleware('permission:Customer tambah')->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->middleware('permission:Customer ubah')->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:Customer ubah')->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:Customer hapus')->name('customers.destroy');

    Route::get('/katalog', [KatalogController::class, 'index'])->middleware('permission:Katalog lihat')->name('katalog.index');
    Route::get('/katalog/create', [KatalogController::class, 'create'])->middleware('permission:Katalog tambah')->name('katalog.create');
    Route::post('/katalog', [KatalogController::class, 'store'])->middleware('permission:Katalog tambah')->name('katalog.store');
    Route::post('/katalog/import', [KatalogController::class, 'import'])->middleware('permission:Katalog import')->name('katalog.import');
    Route::get('/katalog/{katalog}/edit', [KatalogController::class, 'edit'])->middleware('permission:Katalog ubah')->name('katalog.edit');
    Route::put('/katalog/{katalog}', [KatalogController::class, 'update'])->middleware('permission:Katalog ubah')->name('katalog.update');
    Route::delete('/katalog/{katalog}', [KatalogController::class, 'destroy'])->middleware('permission:Katalog hapus')->name('katalog.destroy');

    Route::get('/vendors', [VendorController::class, 'index'])->middleware('permission:Vendor lihat')->name('vendors.index');
    Route::get('/vendors/create', [VendorController::class, 'create'])->middleware('permission:Vendor tambah')->name('vendors.create');
    Route::post('/vendors', [VendorController::class, 'store'])->middleware('permission:Vendor tambah')->name('vendors.store');
    Route::get('/vendors/{vendor}/edit', [VendorController::class, 'edit'])->middleware('permission:Vendor ubah')->name('vendors.edit');
    Route::put('/vendors/{vendor}', [VendorController::class, 'update'])->middleware('permission:Vendor ubah')->name('vendors.update');
    Route::delete('/vendors/{vendor}', [VendorController::class, 'destroy'])->middleware('permission:Vendor hapus')->name('vendors.destroy');

    Route::get('/sites', [SiteController::class, 'index'])->middleware('permission:Site lihat')->name('sites.index');
    Route::get('/sites/create', [SiteController::class, 'create'])->middleware('permission:Site tambah')->name('sites.create');
    Route::post('/sites', [SiteController::class, 'store'])->middleware('permission:Site tambah')->name('sites.store');
    Route::get('/sites/{site}/edit', [SiteController::class, 'edit'])->middleware('permission:Site ubah')->name('sites.edit');
    Route::put('/sites/{site}', [SiteController::class, 'update'])->middleware('permission:Site ubah')->name('sites.update');
    Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->middleware('permission:Site hapus')->name('sites.destroy');

    Route::get('/document-templates', [DocumentTemplateController::class, 'index'])->middleware('permission:Template Dokumen lihat')->name('document-templates.index');
    Route::get('/document-templates/create', [DocumentTemplateController::class, 'create'])->middleware('permission:Template Dokumen tambah')->name('document-templates.create');
    Route::post('/document-templates', [DocumentTemplateController::class, 'store'])->middleware('permission:Template Dokumen tambah')->name('document-templates.store');
    Route::get('/document-templates/{documentTemplate}/edit', [DocumentTemplateController::class, 'edit'])->middleware('permission:Template Dokumen ubah')->name('document-templates.edit');
    Route::put('/document-templates/{documentTemplate}', [DocumentTemplateController::class, 'update'])->middleware('permission:Template Dokumen ubah')->name('document-templates.update');
    Route::delete('/document-templates/{documentTemplate}', [DocumentTemplateController::class, 'destroy'])->middleware('permission:Template Dokumen hapus')->name('document-templates.destroy');

    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:Jabatan lihat')->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->middleware('permission:Jabatan tambah')->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:Jabatan tambah')->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->middleware('permission:Jabatan ubah')->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:Jabatan ubah')->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:Jabatan hapus')->name('roles.destroy');

    Route::get('/users', [UserController::class, 'index'])->middleware('permission:User lihat')->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->middleware('permission:User tambah')->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:User tambah')->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware('permission:User ubah')->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:User ubah')->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:User hapus')->name('users.destroy');
});

Route::get('/verify/{token}', [QuotationController::class, 'verify'])->name('verify.quotation');

require __DIR__.'/auth.php';
