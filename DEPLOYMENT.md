# Deployment ERP Nusantara

Panduan ini digunakan untuk deployment manual ke VPS production. Jalankan semua perintah dari direktori project ERP Nusantara di VPS.

## Prasyarat

- Docker Engine dan Docker Compose plugin sudah terpasang.
- Traefik sudah berjalan dan terhubung ke external network `workspace_local-dev`.
- Domain `general-supply.nusantaraabadijaya.com` sudah mengarah ke VPS.
- Jangan menyimpan atau commit file `.env` production.

Verifikasi external network sebelum deploy:

```bash
docker network inspect workspace_local-dev
```

## First Deploy

1. Clone repository dan masuk ke direktori project:

```bash
git clone <REPOSITORY_URL> ERP_Nusantara
cd ERP_Nusantara
```

2. Buat konfigurasi production dari template:

```bash
cp .env.production.example .env
```

3. Edit `.env` secara manual. Ganti seluruh nilai `CHANGE_ME_*`, isi kredensial SMTP, dan biarkan `APP_KEY` kosong sementara. Pastikan minimal nilai berikut benar:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://general-supply.nusantaraabadijaya.com
DB_HOST=erp-nusantara-mysql
GOTENBERG_URL=http://erp-nusantara-gotenberg:3000
SESSION_SECURE_COOKIE=true
```

4. Build image dan jalankan seluruh service:

```bash
docker compose build
docker compose up -d
```

5. Jika `APP_KEY` masih kosong, generate key tanpa menulis secret ke image:

```bash
docker compose run --rm --no-deps erp-nusantara-app php artisan key:generate --show
```

Salin hasilnya ke `APP_KEY` di `.env`, lalu recreate container aplikasi agar environment terbaru dipakai:

```bash
docker compose up -d --force-recreate erp-nusantara-app
```

6. Jalankan migration production:

```bash
docker compose exec erp-nusantara-app php artisan migrate --force
```

7. Buat symbolic link storage:

```bash
docker compose exec erp-nusantara-app php artisan storage:link
```

8. Pastikan direktori tanda tangan private dimiliki user web server:

```bash
docker compose exec --user root erp-nusantara-app mkdir -p /app/storage/app/private/signatures
docker compose exec --user root erp-nusantara-app chown -R www-data:www-data /app/storage/app/private/signatures
```

9. Verifikasi status container, healthcheck, dan log aplikasi:

```bash
docker compose ps
docker compose logs --tail=100 erp-nusantara-app
```

Buka `https://general-supply.nusantaraabadijaya.com` dan lakukan smoke test login, halaman utama, serta generate PDF yang menggunakan Gotenberg.

## Update Deploy

1. Ambil perubahan terbaru:

```bash
git pull
```

2. Build ulang image dan recreate service yang berubah:

```bash
docker compose build
docker compose up -d
```

3. Jalankan migration secara aman untuk production:

```bash
docker compose exec erp-nusantara-app php artisan migrate --force
```

4. Pastikan storage link dan permission tanda tangan tetap benar:

```bash
docker compose exec erp-nusantara-app php artisan storage:link
docker compose exec --user root erp-nusantara-app mkdir -p /app/storage/app/private/signatures
docker compose exec --user root erp-nusantara-app chown -R www-data:www-data /app/storage/app/private/signatures
```

5. Periksa health dan log setelah update:

```bash
docker compose ps
docker compose logs --tail=100 erp-nusantara-app
```

## Operasional

Menampilkan log semua service:

```bash
docker compose logs -f
```

Restart aplikasi tanpa menghentikan MySQL dan Gotenberg:

```bash
docker compose restart erp-nusantara-app
```

Menghentikan container tanpa menghapus named volume database dan storage:

```bash
docker compose down
```

Jangan menjalankan `docker compose down -v` di production karena opsi `-v` menghapus named volume MySQL, storage aplikasi, dan cache.
