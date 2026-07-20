# CLAUDE.md

## 🏗️ Arsitektur Deployment
- Production: Docker FrankenPHP (PHP 8.4, image `erp-nusantara-frankenphp`, port 8080)
  di belakang **Traefik** dengan TLS Let's Encrypt otomatis, domain
  `general-supply.nusantaraabadijaya.com`. MySQL 8.4 + Gotenberg 8 sebagai container terpisah.
- File deploy resmi ada di **root repo**: `Dockerfile`, `docker-compose.yml`,
  `docker/entrypoint.sh`, `DEPLOYMENT.md`. Setup ini sudah terverifikasi jalan di VPS.
- **JANGAN** mengganti dengan stack nginx/php-fpm atau membuat folder `deploy/` baru
  tanpa mencocokkan dulu dengan setup Traefik+FrankenPHP yang ada — pernah terjadi
  agent membuat stack nginx duplikat yang nyaris menimpa Dockerfile production.
- `bootstrap/app.php` sudah `trustProxies` untuk header Traefik (X-Forwarded-*).

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

## 📌 Riwayat Perbaikan Besar (sudah di-push ke origin/main)

**Commit `e960c8c`** — alur approval email & PDF quotation
- Fix reject via email 500 (argumen tertukar); hapus duplikat ApprovalController/
  VerifyController & route `/verify` ganda
- TTD PDF pakai `approvedBy` (bukan `Auth::user()`), `masa_berlaku` dari kolom DB,
  PPN quotation dibulatkan sekali (`grand_total = subtotal + ppn`)
- Approval email: GET = halaman konfirmasi, POST = eksekusi (anti auto-approve oleh
  email scanner); approver dibawa sebagai parameter signed URL (berlaku 3 hari),
  divalidasi `is_active` + permission per tipe dokumen
- Gotenberg dipindah keluar `DB::transaction`; download quotation regenerate PDF
  bila file hilang; command `approval:resend-emails`

**Commit `68f8186`** — security hardening
- Registrasi publik **ditutup** (user dibuat admin; akun pertama via seeder/tinker)
- `Password::defaults`: min 10 + huruf + angka + `uncompromised()` (butuh akses
  keluar ke API Have I Been Pwned)
- Exception tidak lagi bocor ke user (pesan generik + `Log::error`)
- TTD profil dilayani via route auth `GET /profile/signature`

**Commit `8b3face`** — invoice/PPN, cicilan, race condition, UX error
- `invoices`: kolom baru `ppn` + `grand_total` (migration backfill, status lama
  tidak diubah). Status **Lunas** diukur ke `grand_total` (subtotal + PPN 11%),
  konsisten dengan PDF Invoice/Nota/Tanda Terima
- Pembayaran invoice kini **akumulatif** (cicilan); tolak `<= 0` dan yang melebihi
  sisa tagihan. Command `invoice:recheck-payments` (dry-run) / `--apply`
- Matching harga item invoice: part_no lalu deskripsi; fallback harga-item-lain
  dihapus — item tak cocok kini gagal dengan pesan jelas (termasuk saat download
  PDF invoice lama yang dulu salah harga — disengaja sebagai penanda untuk finance)
- PO tidak bisa di-void bila masih punya SPB/Invoice aktif; alasan reject PO
  disimpan di `catatan_rejection` (tidak menimpa `catatan`)
- Race fix: lock SPB saat buat invoice, lock dokumen sumber saat hitung qty sisa
  SPB, generator nomor dokumen pakai `firstOrCreate` + retry duplicate key
- `FormErrorSummary`: error backend yang dulu tertelan (`no_referensi`,
  `template_id`, item tak cocok, dll) kini tampil di semua modal transaksi

## 📋 Checklist Deploy Berikutnya di VPS
1. Deploy seperti biasa, lalu `php artisan migrate --force` (ada migration baru
   untuk `ppn`/`grand_total` invoices dan `catatan_rejection` purchase_orders)
2. `php artisan invoice:recheck-payments` (dry-run) → review bareng finance →
   baru jalankan `--apply`
3. Coba ulang pembuatan SPB yang pernah gagal — pesan penyebabnya kini tampil di
   modal. Dugaan akar masalah: template SPB default belum di-set di menu Template
   Dokumen, atau No. PO/PR Customer kosong di dokumen sumber
4. Isi SMTP/Graph + 3 email approval di halaman Settings (email approval **harus**
   milik user aktif ber-permission approve terkait), pastikan queue worker jalan,
   lalu `php artisan approval:resend-emails` bila ada dokumen Pending Approval lama

## 🔓 Belum Dikerjakan
1. Badge sidebar role **Gudang** (WIP tersupply belum dibuat SPB) dihitung di
   `SidebarBadgeService` tapi tidak pernah tampil — `AppLayout.jsx` memetakannya
   ke route `spb.index` yang tidak ada. Solusi: gabungkan ke badge menu Quotation
   (pola yang sama dengan badge invoice)
2. Keputusan bisnis tertunda: validasi `jumlah_realisasi` bukti PD terhadap
   nominal yang di-approve; notifikasi disiarkan ke semua user satu role
   (bukan hanya pemilik dokumen)
3. Verifikasi visual SPB multi-halaman di production (dompdf, header berulang
   di `<thead>`)
