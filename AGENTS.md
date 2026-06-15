# AGENTS.md — ERP PT. Nusantara Abadi Jaya
# File ini dibaca otomatis oleh Codex di setiap chat baru.
# Jangan hapus atau ubah tanpa konfirmasi developer.

---

## 🏢 PROJECT OVERVIEW
Sistem ERP untuk PT. Nusantara Abadi Jaya — distributor spare part & pallet.
Bisnis utama: Quotation → Sales Order → WIP → SPB → Invoice/Nota.

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

routes/
  web.php               → semua route
```

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
| Quotation | quotations | Dokumen penawaran |
| QuotationItem | quotation_items | Item barang di quotation |
| SalesOrder | sales_orders | Sales order dari PO customer |
| WipOrder | wip_orders | Work in progress order dari portal RMA |
| PurchaseOrder | purchase_orders | Purchase order NAJ ke vendor eksternal |
| PurchaseOrderItem | purchase_order_items | Item barang di purchase order NAJ |
| Spb | spb | Surat Pengiriman Barang |
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
    Show.jsx   ← PUSAT TRANSAKSI
  PurchaseOrder/
    Index.jsx
    Create.jsx
    Show.jsx
  Verify.jsx   ← halaman publik verifikasi QR
```

---

## 🎯 ENUM CLASSES YANG SUDAH ADA
```php
App\Enums\QuotationStatus:
  DRAFT, PENDING_APPROVAL, APPROVED, REJECTED, VOID

App\Enums\PurchaseOrderStatus:
  DRAFT, PENDING_APPROVAL, APPROVED, VOID

App\Enums\SpbStatus:
  DRAFT, SHIPPED, VOID

App\Enums\ReferensiTipe:
  PR, PO

App\Enums\TipeDokumen:
  INVOICE, NOTA_PENJUALAN

App\Enums\StatusPembayaran:
  BELUM, SEBAGIAN, LUNAS

App\Enums\InvoiceStatus:
  ACTIVE, VOID

App\Enums\KategoriPD:
  BAYAR_RMA, BIAYA_PENGIRIMAN, OPERASIONAL_KANTOR

App\Enums\PDStatus:
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

# Sales Order
lihat_sales_order
input_sales_order
void_sales_order

# Purchase Order NAJ
lihat_purchase_order
buat_purchase_order
approve_purchase_order
download_pdf_purchase_order
void_purchase_order

# WIP
lihat_wip
buat_wip
update_status_wip
void_wip

# Surat Kuasa
lihat_surat_kuasa
buat_surat_kuasa
download_pdf_surat_kuasa

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
// Contoh cara catat activity log:
ActivityLog::create([
    'user_id'    => $user->id,
    'action'     => 'created_quotation',        // format: {verb}_{model}
    'model_type' => 'Quotation',
    'model_id'   => $quotation->id,
    'description'=> "Membuat quotation {$quotation->no_quotation}",
    'ip_address' => request()->ip(),
]);
```

Format action yang sudah digunakan:
- `created_{model}` → membuat dokumen baru
- `submitted_{model}` → submit ke approval
- `approved_{model}` → approve dokumen
- `rejected_{model}` → reject dokumen
- `voided_{model}` → void dokumen
- `downloaded_{model}` → download PDF
- `duplicated_{model}` → duplikasi dokumen

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
// Setiap tabel transaksi punya:
$table->enum('status', [..., 'VOID']);
$table->text('alasan_void')->nullable();
$table->foreignId('voided_by')->nullable()->constrained('users');
$table->timestamp('voided_at')->nullable();
```

---

## 🔢 AUTO-GENERATE NOMOR DOKUMEN
Menggunakan tabel `document_numbers` dan `DocumentNumberService`.

Format per dokumen:
| Dokumen | Format | Contoh |
|---------|--------|--------|
| Quotation | [SEQ]/QUOT-NAJ/MKS/[BLN-ROM]/[TAHUN] | 018/QUOT/NAJ-MKS/III/26 |
| SPB | [SEQ]/WHMKS/NAJ/[BLN-ROM]/[TAHUN] | 011/WHMKS/NAJ/III/26 |
| Invoice/Nota | [SEQ]/NOTA-NAJ/MKS/NAJGROUP/[BLN-ROM]/[TAHUN] | 024/NOTA-NAJ/MKS/NAJGROUP/III/2026 |
| PO NAJ | [SEQ]/PO-NAJ/[BLN-ROM]/[TAHUN] | 001/PO-NAJ/III/2026 |
| PD | [SEQ]/PD-NAJ/[BLN-ROM]/[TAHUN] | 012/PD-NAJ/III/2026 |
| WIP | Input manual dari portal RMA | WIP 12210 |

---

## 🔐 QR CODE VERIFIKASI
Dokumen yang punya QR Code (hanya yang butuh approval Manager):
- Quotation (setelah APPROVED)
- PO NAJ (setelah APPROVED)
- PD / Permintaan Dana (setelah APPROVED)

QR Code mengarah ke: `/verify/{qr_token}`
Token: random 64 karakter, generate saat dokumen diapprove.
Halaman verifikasi: `Pages/Verify.jsx` (publik, tidak perlu login)

---

## 📊 ALUR BISNIS UTAMA
```
Quotation (APPROVED)
  └── Sales Order (input no. PO dari customer)
        └── WIP (input manual nomor dari portal RMA)
              └── SPB (buat surat pengiriman, bisa parsial)
                    └── Invoice/Nota (1 SPB = 1 Invoice/Nota)
                          └── Upload TTD Customer (gabung jadi 1 PDF)
```

Semua section di atas ada di dalam `Quotation/Show.jsx`.
Tombol aksi muncul bertahap sesuai progress:
- Tombol "Input Sales Order" → muncul setelah Quotation APPROVED
- Tombol "Input WIP" → muncul setelah Sales Order diinput
- Tombol "Buat SPB" → muncul setelah WIP diinput
- Tombol "Buat Invoice/Nota" → muncul setelah SPB dibuat
- Tombol "Upload TTD" → muncul setelah Invoice/Nota dibuat

---

## 🏭 ALUR KHUSUS KMSI (PALLET)
- Customer bisa kirim PR dulu sebelum PO resmi terbit
- no_pr_customer disimpan di sales_orders (nullable)
- SPB bisa dibuat dengan referensi no. PR
- Ketika PO customer terbit → SPB update referensi ke no. PO
- Custom tanggal di SPB untuk kebutuhan tagihan

---

## 💳 METODE PEMBAYARAN
- **COD** → Nota Penjualan + Faktur Pajak + Tanda Terima
- **CBD** → Nota Penjualan + Faktur Pajak + Tanda Terima
- **TOP** → Invoice + Faktur Pajak + Tanda Terima
  - Input jangka waktu (hari)
  - Sistem hitung jatuh tempo otomatis
  - Notifikasi H-7 via bell di dashboard (Finance & Manager)

---

## 👥 ROLE & JABATAN
Sistem jabatan dinamis — Superadmin bisa buat jabatan baru.
1 user bisa punya lebih dari 1 jabatan.

Default jabatan:
| Jabatan | Akses Utama |
|---------|-------------|
| Superadmin | Semua akses |
| Sales | Quotation, Sales Order, WIP, PO NAJ |
| Gudang | SPB, update status WIP |
| Finance | Invoice, Nota, pembayaran |
| Procurement | Permintaan Dana |
| Manager | Approve Quotation, PO NAJ, PD + semua laporan |

---

## 🛡️ KEAMANAN
- CSRF protection aktif di semua form
- Rate limiting login: maks 5x gagal → locked 15 menit
- Semua input divalidasi di Form Request
- Permission dicek via Spatie middleware per route
- File upload: validasi tipe & ukuran
- Gunakan Eloquent ORM, tidak boleh raw query dengan input user

---

## ✅ CHECKLIST SETIAP MODUL BARU
Pastikan setiap modul baru punya:
- [ ] Migration
- [ ] Enum class (untuk field status/tipe)
- [ ] Model (fillable, casts, relations)
- [ ] Form Request (store, update jika ada, void)
- [ ] Service class
- [ ] Controller (tipis, panggil Service)
- [ ] Routes (group middleware auth + permission)
- [ ] React Page/Component
- [ ] Activity log di setiap aksi
- [ ] Void (bukan delete)

---

## 📦 PHASE YANG SUDAH SELESAI
- ✅ Phase 1: Foundation & Master Data
- ✅ Phase 2: Modul Quotation
- ✅ Phase 3: Sales Order & WIP
- ✅ Phase 4: PO NAJ
- ✅ Phase 5: SPB
- ✅ Phase 6: Invoice & Nota Penjualan
- ✅ Phase 7: Permintaan Dana (PD)

## 🚧 PHASE BERIKUTNYA
- ⏳ Phase 8: Laporan & Dashboard
