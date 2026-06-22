# CLAUDE.md

## ✅ Completed
- Fix PDF templates sesuai format NAJ
- Embed TTD Manager ke PDF (Quotation)
- TTD embed via PHPWord `setImageValue` + GD (tanpa imagick)
- Kolom perihal, metode_pembayaran, masa_berlaku di quotations
- Sistem PDF Quotation: PHPWord + GD + Gotenberg dari template `.docx`

## ⚙️ Tech Stack PDF
- Quotation: PHPWord + GD + Gotenberg dari template `.docx`
- SPB, Invoice, Nota, PO NAJ, PD: masih memakai sistem PDF lama sampai dimigrasi bertahap
- QR Code: SimpleSoftwareIO/simple-qrcode

## 🔧 PHP Extensions Required
- Tidak ada extension tambahan yang diperlukan.
- GD (built-in PHP) cukup untuk semua operasi.

## 🚀 Deployment Notes
- Setelah update dependency Composer, jalankan deployment seperti biasa dan restart PHP-FPM atau container aplikasi sesuai environment deployment.

## 📁 Struktur PDF Aktif
```text
resources/views/pdf/
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
```

## 🧩 Placeholder Template .docx Quotation

Header:
- `${no_quotation}` → nomor quotation
- `${tgl_quotation}` → tanggal format `dd MMMM yyyy`
- `${customer_name}` → nama customer
- `${customer_alamat}` → alamat customer
- `${customer_kota}` → kota customer untuk field lokasi
- `${revisi}` → nomor revisi
- `${masa_berlaku}` → masa berlaku format `dd MMMM yyyy`
- `${perihal}` → deskripsi permintaan
- `${metode_pembayaran}` → metode pembayaran
- `${subtotal}` → subtotal formatted Rupiah
- `${ppn}` → PPN 11% formatted Rupiah
- `${grand_total}` → grand total formatted Rupiah

Tabel item, taruh placeholder ini dalam satu baris tabel untuk `cloneRow`:
- `${item_no}` → nomor urut
- `${item_part_no}` → part number
- `${item_deskripsi}` → deskripsi item
- `${item_qty}` → quantity
- `${item_satuan}` → satuan, contoh PCS atau SET
- `${item_harga}` → harga satuan formatted Rupiah
- `${item_total}` → total formatted Rupiah
- `${item_status}` → status stok, contoh READY JKT atau INDENT

## 📄 TEMPLATE .DOCX — PLACEHOLDER IMAGE

Untuk TTD dan QR, ketik placeholder sebagai TEKS di `.docx`:
- `${TTD}` → akan diganti gambar tanda tangan Manager (100x40mm)
- `${QR}` → akan diganti gambar QR code verifikasi (25x25mm)

Posisi TTD dan QR mengikuti posisi placeholder di dokumen Word.
Tidak perlu konfigurasi koordinat — drag teks ke posisi yang diinginkan di Word.

## ⏳ Todo
- Migrasi SPB, Invoice, Nota, PO NAJ, PD ke sistem .docx
