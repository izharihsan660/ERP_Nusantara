# Quick Reference — UAT Improvements

## 🎯 What Changed

### 1. WIP Item Splitting
**Before:** All quotation items automatically added to every WIP  
**After:** Users can select specific items per WIP order

**How to Use:**
1. Go to Quotation detail page (APPROVED status)
2. Click "Input WIP"
3. Fill WIP number and type
4. **NEW:** Check/uncheck items, adjust quantities
5. Submit

**Validation:**
- Cannot exceed remaining qty per item
- Items with qty = 0 are disabled
- At least 1 item required

---

### 2. Permintaan Dana New Structure
**Before:** Single kategori dropdown  
**After:** Multi-item table with bank details

**New Fields:**
- Tujuan (vendor name)
- Rekening Tujuan (account number)
- Bank Tujuan (optional)
- Plan Pembayaran (payment date)
- Items table (unlimited rows)
- 2 file attachments

**PDF Output:** Updated template matching NAJ format

---

### 3. Email Approval
**Trigger:** When user submits Quotation/PO NAJ/PD  
**Recipient:** Configured in Settings (Superadmin only)  
**Action:** Click green button in email = instant approval  
**Security:** Signed URL, 7-day expiry

**Setup Required:**
1. Settings → Email Configuration
2. Enter approval_email_quotation
3. Enter approval_email_po_naj
4. Enter approval_email_pd

---

### 4. Sidebar Badges
**Red number badges** appear on menu items:

| Role | Badge Location | Meaning |
|------|----------------|---------|
| Manager | Quotation, PO, PD | Pending approvals |
| Gudang | SPB | WIP ready to ship |
| Finance | Invoice | SPB ready to invoice |
| Procurement | PD | Approved PD need bukti |

**Refresh:** Auto-updates on page load

---

## 📁 Key Files Modified

**Backend:**
- `app/Services/*Service.php` (email integration)
- `app/Http/Middleware/HandleInertiaRequests.php` (badges)
- `app/Providers/AppServiceProvider.php` (email config)

**Frontend:**
- `resources/js/Pages/Quotation/Show.jsx` (WIP modal)
- `resources/js/Pages/PermintaanDana/Create.jsx` (new form)
- `resources/js/Layouts/AppLayout.jsx` (sidebar badges)

**Templates:**
- `resources/views/emails/approval-request.blade.php`
- `resources/views/pdf/pd/default.blade.php`

---

## ⚠️ Known Limitations

1. **PD Show page:** Manual update needed (see UAT_IMPROVEMENTS_SUMMARY.md)
2. **Email queue:** Requires queue worker running (`php artisan queue:work`)
3. **SMTP:** Must be configured in Settings before emails work

---

## 🧪 Testing Checklist

- [ ] Create WIP with partial items from Quotation
- [ ] Create second WIP with remaining items
- [ ] Submit Quotation and verify email received
- [ ] Click approval link in email
- [ ] Create PD with multiple items
- [ ] Upload attachments to PD
- [ ] Download PD PDF and verify format
- [ ] Check sidebar badges appear for pending tasks

---

## 🆘 Troubleshooting

**Email not sending?**
- Check Settings → Email Configuration
- Check logs: `storage/logs/laravel.log`
- Verify queue is running: `php artisan queue:work`

**Sidebar badges not showing?**
- Clear cache: `php artisan cache:clear`
- Refresh browser (Ctrl+Shift+R)

**WIP items not appearing?**
- Verify Quotation is APPROVED
- Check source_items prop in QuotationController

---

**Last Updated:** 2026-06-21  
**Version:** UAT Phase 1 Complete
