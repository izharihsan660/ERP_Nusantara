# AGENTS.md — ERP PT. Nusantara Abadi Jaya
# File ini dibaca otomatis oleh Codex di setiap chat baru.
# Jangan hapus atau ubah tanpa konfirmasi developer.

---

## 🏢 PROJECT OVERVIEW
Sistem ERP untuk PT. Nusantara Abadi Jaya — distributor spare part & pallet.
Bisnis utama ada 2 alur terpisah:
1. Quotation → Sales Order (PO Customer) → WIP → SPB → Invoice/Nota
2. Purchase Order NAJ (ke vendor) → SPB → Invoice/Nota

---

## ⚙️ TECH STACK
- **Backend**: Laravel 11
- **Frontend**: React 18 + Inertia.js
- **Styling**: Tailwind CSS + shadcn/ui
- **Icons**: Lucide React
- **Auth**: Laravel Breeze (Inertia + React)
- **Permission**: Spatie Laravel-Permission
- **PDF**: Barryvdh Laravel DomPDF
- **QR Code**: SimpleSoftwareIO/simple-qrcode
- **PDF Merge**: jurosh/pdf-merge
- **Excel Export**: Maatwebsite Laravel Excel
- **Pattern**: Service + Action + Form Request
- **Database**: MySQL 8

---

## 📁 STRUKTUR FOLDER
```
app/
  Http/
    Controllers/        → tipis, hanya panggil Service, return Inertia
    Requests/           → semua validasi input (Form Request)
    Middleware/         → auth, permission check
  Services/             → business logic utama (1 Service per modul)
  Actions/              → 1 class = 1 aksi spesifik
  Models/               → Eloquent + relations + casts
  Enums/                → PHP Enum untuk semua status & tipe
  Notifications/        → Laravel Notifications

resources/
  js/
    Pages/              → React pages (1 folder per modul)
    Components/         → reusable components
    Layouts/            → AppLayout, AuthLayout
  views/
    pdf/                → Blade templates untuk PDF per dokumen
      quotation/
        default.blade.php
        mil.blade.php
      spb/
        default.blade.php
        mil.blade.php
      invoice/
        invoice.blade.php
        nota.blade.php
        faktur-pajak.blade.php
        tanda-terima.blade.php
      purchase-order/
        default.blade.php
      pd/
        default.blade.php

routes/
  web.php               → semua route
```

---

## 🧭 CONTROLLERS YANG SUDAH ADA
- DashboardController
- LaporanController
- Transaction/QuotationController
- Transaction/SalesOrderController
- Transaction/WipOrderController
- Transaction/PurchaseOrderController
- Transaction/SpbController
- Transaction/InvoiceController
- Transaction/PermintaanDanaController

---

## 🗄️ MODELS YANG SUDAH ADA
| Model | Table | Keterangan |
|-------|-------|------------|
| User | users | Auth user |
| Customer | customers | Master customer |
| Katalog | katalog | Master barang dari RMA |
| Vendor | vendors | Master vendor (RMA & lain) |
| Site | sites | Lokasi pengiriman |
| DocumentTemplate | document_templates | Template PDF dinamis |
| DocumentNumber | document_numbers | Auto-generate nomor dokumen |
| ActivityLog | activity_logs | Log semua aksi user |
| Quotation | quotations | Dokumen penawaran ke customer |
| QuotationItem | quotation_items | Item barang di quotation |
| SalesOrder | sales_orders | PO Customer yang masuk ke NAJ |
| WipOrder | wip_orders | Nomor order dari portal RMA (input manual) |
| PurchaseOrder | purchase_orders | PO NAJ ke vendor eksternal (bukan RMA) |
| PurchaseOrderItem | purchase_order_items | Item barang di PurchaseOrder |
| Spb | spb | Surat Pengiriman Barang (polymorphic: WipOrder atau PurchaseOrder) |
| SpbItem | spb_items | Item barang di SPB |
| Invoice | invoices | Invoice / Nota Penjualan per SPB |
| PermintaanDana | permintaan_dana | Dokumen permintaan pencairan dana internal |

---

## 🔧 SERVICES YANG SUDAH ADA
- CustomerService
- KatalogService
- SiteService
- VendorService
- DocumentTemplateService
- RoleService
- UserService
- QuotationService
- QuotationPDFService
- SalesOrderService
- WipOrderService
- PurchaseOrderService
- PurchaseOrderPDFService
- SpbService
- SpbPDFService
- InvoiceService
- InvoicePDFService
- PermintaanDanaService
- PermintaanDanaPDFService

---

## 📄 PAGES (REACT) YANG SUDAH ADA
```
Pages/
  Auth/
  Dashboard/
  Profile/
  Shared/
    InvoiceSection.jsx  ← reusable, dipakai di Quotation & PurchaseOrder
  MasterData/
    Customer/
    Katalog/
    Vendor/
    Site/
    DocumentTemplate/
    Role/
    User/
  Quotation/
    Index.jsx
    Create.jsx
    Show.jsx            ← PUSAT TRANSAKSI alur spare part
  PurchaseOrder/
    Index.jsx
    Create.jsx
    Show.jsx            ← PUSAT TRANSAKSI alur pallet/vendor
  PermintaanDana/
    Index.jsx
    Create.jsx
    Show.jsx
  Laporan/
    RekapanPo.jsx
    RekapanWip.jsx
    RekapanSpb.jsx
    RekapanInvoice.jsx
    RekapanPd.jsx
    Profit.jsx
    Outstanding.jsx
  Verify.jsx            ← halaman publik verifikasi QR (tanpa login)
```

---

## 📤 EXPORT EXCEL YANG SUDAH ADA
- RekapanPoExport
- RekapanWipExport
- RekapanSpbExport
- RekapanInvoiceExport
- RekapanPdExport
- ProfitExport
- OutstandingExport

---

## 🎯 ENUM CLASSES YANG SUDAH ADA
```php
App\Enums\QuotationStatus:
  DRAFT, PENDING_APPROVAL, APPROVED, REJECTED, VOID

App\Enums\SalesOrderStatus (di sales_orders):
  OPEN, COMPLETED, VOID

App\Enums\MetodePembayaran (di sales_orders):
  COD, CBD, TOP

App\Enums\TipeOrder (di wip_orders):
  VOR, STK

App\Enums\StatusSupply (di wip_orders):
  BELUM_TERSUPPLY, TERSUPPLY

App\Enums\WIPStatus (di wip_orders):
  ACTIVE, VOID

App\Enums\PurchaseOrderStatus (di purchase_orders):
  DRAFT, PENDING_APPROVAL, APPROVED, VOID

App\Enums\SpbStatus:
  DRAFT, SHIPPED, VOID

App\Enums\ReferensiTipe (di spb):
  PR, PO

App\Enums\TipeDokumen (di invoices):
  INVOICE, NOTA_PENJUALAN

App\Enums\StatusPembayaran (di invoices):
  BELUM, SEBAGIAN, LUNAS

App\Enums\InvoiceStatus (di invoices):
  ACTIVE, VOID

App\Enums\KategoriPD (di permintaan_dana):
  BAYAR_RMA, BIAYA_PENGIRIMAN, OPERASIONAL_KANTOR

App\Enums\PDStatus (di permintaan_dana):
  DRAFT, PENDING_APPROVAL, APPROVED, REJECTED, PAID, VOID
```

---

## 📋 KONVENSI PERMISSION (SPATIE)
Format nama permission: `{aksi}_{modul}`

Permission yang sudah di-seed:
```
# Quotation
lihat_quotation
buat_quotation
approve_quotation
download_pdf_quotation
void_quotation

# Sales Order (PO Customer masuk ke NAJ)
lihat_sales_order
input_sales_order
void_sales_order

# WIP
lihat_wip
buat_wip
update_status_wip
void_wip

# Surat Kuasa
lihat_surat_kuasa
buat_surat_kuasa
download_pdf_surat_kuasa

# Purchase Order NAJ (PO keluar dari NAJ ke vendor)
lihat_purchase_order
buat_purchase_order
approve_purchase_order
download_pdf_purchase_order
void_purchase_order

# SPB
lihat_spb
buat_spb
download_pdf_spb
void_spb

# Invoice / Nota
lihat_invoice
buat_invoice
upload_ttd_invoice
update_pembayaran_invoice
void_invoice

# PD
lihat_pd
buat_pd
approve_pd
upload_bukti_pd
void_pd

# Master Data
lihat_customer, tambah_customer, ubah_customer, hapus_customer
lihat_katalog, tambah_katalog, ubah_katalog, hapus_katalog, import_katalog
lihat_vendor, tambah_vendor, ubah_vendor, hapus_vendor
lihat_site, tambah_site, ubah_site, hapus_site
lihat_template, tambah_template, ubah_template, hapus_template
lihat_jabatan, tambah_jabatan, ubah_jabatan, hapus_jabatan
lihat_user, tambah_user, ubah_user, hapus_user

# Laporan
laporan_rekapan_po
laporan_rekapan_wip
laporan_rekapan_spb
laporan_rekapan_invoice
laporan_rekapan_pd
laporan_profit
laporan_outstanding
```

---

## 📝 KONVENSI ACTIVITY LOG
Setiap aksi penting wajib dicatat ke tabel `activity_logs`.

```php
ActivityLog::create([
    'user_id'    => $user->id,
    'action'     => 'created_quotation',   // format: {verb}_{model}
    'model_type' => 'Quotation',
    'model_id'   => $quotation->id,
    'description'=> "Membuat quotation {$quotation->no_quotation}",
    'ip_address' => request()->ip(),
]);
```

Format action:
- `created_{model}` → membuat dokumen baru
- `submitted_{model}` → submit ke approval
- `approved_{model}` → approve dokumen
- `rejected_{model}` → reject dokumen
- `voided_{model}` → void dokumen
- `downloaded_{model}` → download PDF
- `duplicated_{model}` → duplikasi dokumen
- `paid_{model}` → dokumen lunas/realisasi

---

## 🔄 KONVENSI INERTIA RESPONSE
```php
// Controller return selalu pakai Inertia::render
return Inertia::render('Quotation/Show', [
    'quotation' => QuotationResource::make($quotation),
    'can' => [
        'approve' => $request->user()->can('approve_quotation'),
        'void'    => $request->user()->can('void_quotation'),
    ],
]);

// Setelah store/update/delete → redirect dengan flash message
return redirect()->route('quotations.show', $quotation)
    ->with('success', 'Quotation berhasil dibuat.');

// Flash message dibaca di frontend via usePage().props.flash
```

---

## 🚫 KONVENSI VOID (TIDAK ADA DELETE)
Semua dokumen transaksi TIDAK BISA dihapus. Hanya bisa **VOID**.

```php
$table->enum('status', [..., 'VOID']);
$table->text('alasan_void')->nullable();
$table->foreignId('voided_by')->nullable()->constrained('users');
$table->timestamp('voided_at')->nullable();
```

---

## 🔢 AUTO-GENERATE NOMOR DOKUMEN
Menggunakan tabel `document_numbers` dan `DocumentNumberService`.

| Dokumen | Format | Contoh |
|---------|--------|--------|
| Quotation | [SEQ]/QUOT-NAJ/MKS/[BLN-ROM]/[TAHUN] | 018/QUOT/NAJ-MKS/III/26 |
| SPB | [SEQ]/WHMKS/NAJ/[BLN-ROM]/[TAHUN] | 011/WHMKS/NAJ/III/26 |
| Invoice/Nota | [SEQ]/NOTA-NAJ/MKS/NAJGROUP/[BLN-ROM]/[TAHUN] | 024/NOTA-NAJ/MKS/NAJGROUP/III/2026 |
| Purchase Order NAJ | [SEQ]/PO-NAJ/[BLN-ROM]/[TAHUN] | 001/PO-NAJ/III/2026 |
| PD | [SEQ]/PD-NAJ/[BLN-ROM]/[TAHUN] | 012/PD-NAJ/III/2026 |
| WIP | Input manual dari portal RMA | WIP 12210 |

---

## 🔐 QR CODE VERIFIKASI
Dokumen yang punya QR Code (hanya yang butuh approval Manager):
- Quotation (setelah APPROVED)
- Purchase Order NAJ (setelah APPROVED)
- Permintaan Dana / PD (setelah APPROVED)

QR Code → `/verify/{qr_token}`
Token: Str::random(64), generate saat dokumen diapprove.
Halaman: `Pages/Verify.jsx` (publik, tidak perlu login)

---

## 📊 ALUR BISNIS

### ALUR 1 — SPARE PART (via Quotation)
```
Quotation (APPROVED)
  └── Sales Order (PO Customer masuk dari customer)
        └── WIP (nomor order dari portal RMA, input manual)
              └── SPB (polymorphic → WipOrder)
                    └── Invoice/Nota (1 SPB = 1 dokumen tagihan)
                          └── Upload TTD Customer (gabung jadi 1 PDF)
```
Semua section ada di `Quotation/Show.jsx`.
Tombol muncul bertahap:
- "Input PO Customer" → muncul setelah Quotation APPROVED
- "Input WIP" → muncul setelah Sales Order diinput
- "Buat SPB" → muncul setelah WIP diinput
- "Buat Invoice/Nota" → muncul setelah SPB dibuat
- "Upload TTD" → muncul setelah Invoice/Nota dibuat

### ALUR 2 — PALLET / VENDOR (via Purchase Order NAJ)
```
Purchase Order NAJ (ke vendor, APPROVED)
  └── SPB (polymorphic → PurchaseOrder)
        └── Invoice/Nota (1 SPB = 1 dokumen tagihan)
              └── Upload TTD Customer (gabung jadi 1 PDF)
```
Semua section ada di `PurchaseOrder/Show.jsx`.
- Customer di Purchase Order diambil dari field customer_id
- SPB otomatis ambil customer dari PurchaseOrder (tidak perlu pilih manual)
- no_pr_customer & no_po_customer ada di purchase_orders
  → SPB referensi PR dulu, update ke PO ketika PO terbit

### ALUR 3 — PERMINTAAN DANA (PD)
```
Permintaan Dana (DRAFT → PENDING → APPROVED → PAID)
```
Berdiri sendiri, tidak terkait Quotation atau PurchaseOrder.

---

## 🔗 POLYMORPHIC SPB
SPB bisa berasal dari 2 sumber:
```php
// spb.spb_able_type = 'App\Models\WipOrder'
// spb.spb_able_id   = wip_orders.id
// → digunakan di alur Quotation/Spare Part

// spb.spb_able_type = 'App\Models\PurchaseOrder'
// spb.spb_able_id   = purchase_orders.id
// → digunakan di alur PurchaseOrder/Pallet
```

---

## 💳 METODE PEMBAYARAN
Disimpan di `sales_orders.metode_pembayaran` (alur Quotation)
atau diinput manual di modal Invoice (alur PurchaseOrder).

- **COD** → Nota Penjualan + Faktur Pajak + Tanda Terima
- **CBD** → Nota Penjualan + Faktur Pajak + Tanda Terima
- **TOP** → Invoice + Faktur Pajak + Tanda Terima
  - Input jangka waktu (hari)
  - Jatuh tempo = tgl_dokumen + top_hari
  - Notifikasi H-7 via bell dashboard (Finance & Manager)

---

## 👥 ROLE & JABATAN
Sistem jabatan dinamis — Superadmin bisa buat jabatan baru.
1 user bisa punya lebih dari 1 jabatan.

| Jabatan | Akses Utama |
|---------|-------------|
| Superadmin | Semua akses |
| Sales | Quotation, Sales Order (PO Customer), WIP, Purchase Order NAJ |
| Gudang | SPB, update status WIP |
| Finance | Invoice, Nota, pembayaran |
| Procurement | Permintaan Dana |
| Manager | Approve Quotation, Purchase Order NAJ, PD + semua laporan |

---

## 🛡️ KEAMANAN
- CSRF protection aktif di semua form
- Rate limiting login: maks 5x gagal → locked 15 menit
- Semua input divalidasi di Form Request
- Permission dicek via Spatie middleware per route
- File upload: validasi tipe & ukuran (maks 10MB)
- Gunakan Eloquent ORM, tidak boleh raw query dengan input user

---

## ✅ CHECKLIST SETIAP MODUL BARU
- [ ] Migration
- [ ] Enum class
- [ ] Model (fillable, casts, relations)
- [ ] Form Request
- [ ] Service class
- [ ] Controller (tipis)
- [ ] Routes (middleware auth + permission)
- [ ] React Page/Component
- [ ] Activity log setiap aksi
- [ ] Void (bukan delete)

---

## 📦 PHASE YANG SUDAH SELESAI
- ✅ Phase 1: Foundation & Master Data
- ✅ Phase 2: Modul Quotation
- ✅ Phase 3: Sales Order (PO Customer) & WIP
- ✅ Phase 4: Purchase Order NAJ (ke vendor)
- ✅ Phase 5: SPB (polymorphic)
- ✅ Phase 6: Invoice & Nota Penjualan
- ✅ Phase 7: Permintaan Dana (PD)
- ✅ Phase 8: Laporan & Dashboard

## 🚧 PHASE BERIKUTNYA
- ⏳ UI polish
