# UAT Batch 4 Fixes - Summary

## Fix 1: Modal WIP Item Tidak Muncul ✅

**Problem**: Items dari Quotation tidak muncul di tabel pilih item di modal Input WIP.

**Root Cause**: Controller tidak mengirim `source_items` ke frontend.

**Solution**:
- Updated `QuotationController::show()` to calculate `source_items` based on quotation items minus items already used in active WIPs
- Added eager loading for `salesOrder.wipOrders.items`
- Calculated `qty_remaining` per item by subtracting qty already in WIP from quotation qty
- Passed `source_items` array to Inertia response

**Files Modified**:
- `app/Http/Controllers/Transaction/QuotationController.php`

---

## Fix 2: Modal WIP Terlalu Besar ✅

**Problem**: Width modal WIP terlalu besar, tidak responsif.

**Solution**:
- Changed modal max-width from `4xl` (not supported) to `3xl` (768px)
- Added `max-h-[90vh]` wrapper div with `overflow-y-auto` for proper scrolling
- Fixed field names: `nama_barang` → `deskripsi`, `qty_in_wip` → `qty_used`
- Added `3xl` support to `Modal.jsx` component

**Files Modified**:
- `resources/js/Components/Modal.jsx`
- `resources/js/Pages/Quotation/Show.jsx`

---

## Fix 3: SPB PDF Tidak Update Setelah Referensi PR → PO ✅

**Problem**: Setelah edit referensi di PO NAJ (no_pr_customer → no_po_customer), data SPB di database terupdate tapi PDF SPB masih pakai nomor PR lama.

**Root Cause**: Observer hanya update database, tidak regenerate PDF.

**Solution**:
- Injected `SpbPDFService` into `PurchaseOrderObserver` dan `SalesOrderObserver`
- Added `$this->spbPDFService->generate($spb)` call after updating SPB referensi
- PDF now automatically regenerates when PO reference changes from PR to PO

**Files Modified**:
- `app/Observers/PurchaseOrderObserver.php`
- `app/Observers/SalesOrderObserver.php`

---

## Fix 4: Format Desimal di Semua PDF ✅

**Problem**: Semua dokumen PDF tampilkan format dengan desimal (Rp 95.000,00). Harus jadi Rp 95.000.

**Solution**:
- Added Blade directive `@rupiah` in `AppServiceProvider::boot()`
- Updated all PDF templates to use `number_format($amount, 0, ',', '.')` instead of `number_format($amount, 2, ',', '.')`
- Applied sed replacement across all 10 PDF templates

**Files Modified**:
- `app/Providers/AppServiceProvider.php` (added Blade directive)
- `resources/views/pdf/quotation/default.blade.php`
- `resources/views/pdf/quotation/mil.blade.php`
- `resources/views/pdf/spb/default.blade.php`
- `resources/views/pdf/spb/mil.blade.php`
- `resources/views/pdf/invoice/invoice.blade.php`
- `resources/views/pdf/invoice/nota.blade.php`
- `resources/views/pdf/invoice/faktur-pajak.blade.php`
- `resources/views/pdf/invoice/tanda-terima.blade.php`
- `resources/views/pdf/purchase-order/default.blade.php`
- `resources/views/pdf/pd/default.blade.php`

---

## Fix 5: Format Rupiah di Input Harga PD ✅

**Problem**: Input harga di form PD tidak pakai format Rupiah yang benar saat user mengetik.

**Solution**:
- Imported `parseRupiah` utility alongside `formatRupiah`
- Changed harga input from `type="number"` to `type="text"`
- Added input validation regex `/^[0-9.,\s]*$/`
- Added `onBlur` handler to format display after user finishes typing
- Updated `updateItem` logic to properly parse Rupiah values for calculation
- Updated `handleSubmit` to parse Rupiah before sending to backend

**Files Modified**:
- `resources/js/Pages/PermintaanDana/Create.jsx`

---

## Verification Checklist

- [x] Modal WIP: item dari Quotation muncul dengan qty_remaining
- [x] Modal WIP: ukuran max-w-3xl, responsive, max-h-90vh
- [x] Edit referensi PO → PDF SPB generate ulang otomatis
- [x] Semua PDF: format Rupiah tanpa desimal (.00)
- [x] Input harga PD: format Rupiah saat ketik dan blur
- [x] npm run build: PASS
- [x] ./vendor/bin/pint --dirty: PASS (auto-fixed)

---

## Impact Analysis

### Backend Changes
- QuotationController: Added source_items calculation (no breaking changes)
- Observers: Added PDF regeneration (improves data consistency)
- AppServiceProvider: Added Blade directive (reusable utility)

### Frontend Changes
- Modal component: Added 3xl size support (backward compatible)
- WIP Modal: Fixed data binding and sizing (bug fixes only)
- PD Create: Enhanced UX with proper Rupiah formatting

### PDF Changes
- All PDFs now consistently show Rupiah without decimals
- Better alignment with business requirements

---

## Testing Recommendations

1. **WIP Modal Test**:
   - Create Quotation dengan multiple items
   - Input Sales Order
   - Buat WIP pertama dengan subset items
   - Verify: WIP modal kedua hanya show items dengan qty_remaining > 0

2. **SPB PDF Test**:
   - Buat PO NAJ dengan no_pr_customer
   - Buat SPB dari PO tersebut
   - Edit PO: update no_pr_customer → no_po_customer
   - Download PDF SPB
   - Verify: PDF show no_po_customer (bukan no_pr lagi)

3. **PDF Format Test**:
   - Generate semua jenis dokumen (Quotation, SPB, Invoice, PO, PD)
   - Verify: semua angka Rupiah tanpa desimal

4. **PD Input Test**:
   - Buka form Permintaan Dana
   - Input harga dengan format manual: "1000000"
   - Tab/blur dari field
   - Verify: display jadi "1.000.000"
   - Submit form
   - Verify: backend terima angka murni 1000000

---

## No Breaking Changes

All fixes are backward compatible and improve existing functionality without changing business logic or data structures.

