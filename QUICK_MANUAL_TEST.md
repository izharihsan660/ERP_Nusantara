# Quick Manual Browser Test Guide

## 🚀 Start Vite Server

In a new terminal window:
```bash
cd /Users/mac/Documents/ERP_Nusantara
npm run dev
```

Wait for: `➜  Local:   http://localhost:5173/`

---

## 🧪 Browser Test Steps

### 1. Login
- URL: http://localhost:8000/login
- Email: `superadmin@naj.local`
- Password: `password`

### 2. Open Test Purchase Order
- URL: http://localhost:8000/purchase-orders/17
- Or: Dashboard → Purchase Order → Click "001/PO-NAJ/VI/2026"

### 3. Test SPB Creation
1. **Scroll to SPB section**
2. **Click "Buat SPB" button** (green button with Plus icon)
3. **Modal should open** ✅

### 4. Verify Form Fields
Check these elements exist:
- [x] Sumber SPB dropdown (not "Sumber WIP")
- [x] Customer field (pre-filled: Test Customer PT)
- [x] Site dropdown
- [x] Items table with headers:
  - Part No
  - Deskripsi
  - **QTY** (should be number input)
  - Berat (optional)
  - Volume (optional)
  - Dimensi (optional)
  - SKU
- [x] **NO "Satuan" column** ← KEY CHECK

### 5. Test QTY Input
1. Click on QTY field for first item
2. Clear and type: `25`
3. Value should update to 25 ✅
4. Try typing letters → should be blocked ✅
5. Try typing negative → should be blocked (min=1) ✅

### 6. Try Creating SPB (Optional)
1. Select a site from dropdown
2. Enter expedition name (e.g., "JNE")
3. Click "Simpan"
4. Should redirect with success message ✅

---

## ✅ Success Criteria

- [ ] Modal opens when clicking "Buat SPB"
- [ ] NO "Satuan" column in items table
- [ ] QTY input accepts numbers only
- [ ] QTY input has min=1 validation
- [ ] Can successfully create SPB

---

## 🐛 If Issues Found

1. Check browser console for errors (F12)
2. Verify Vite is running (http://localhost:5173)
3. Hard refresh browser (Cmd+Shift+R / Ctrl+Shift+F5)
4. Check network tab for failed requests

Report any errors with:
- Error message
- Browser console log
- Screenshot

---

## 📋 Current State

✅ Migrations applied  
✅ All 86 tests passing  
✅ Test data created (PO #17)  
✅ Backend code verified  
✅ Frontend code verified  
⏳ Waiting for manual browser test

**Ready for testing!**
