# Manual Book KasirKu POS

Folder ini berisi dokumentasi manual book aplikasi KasirKu POS dalam format HTML, PDF, dan screenshot halaman website.

## Output

- `docs/manual/manual-book.html`: template manual book siap print A4.
- `docs/manual/manual-book.pdf`: hasil PDF dari template HTML.
- `docs/manual/screenshots/*.png`: screenshot halaman penting aplikasi.
- `scripts/manual/capture-screenshots.js`: script Playwright untuk mengambil screenshot.
- `scripts/manual/generate-manual-pdf.js`: script Playwright untuk membuat PDF.

## Prasyarat

Install dependency Node dan Playwright jika belum tersedia:

```bash
npm install
npm install -D playwright
npx playwright install chromium
```

Pastikan dependency PHP dan database aplikasi sudah siap:

```bash
composer install
php artisan migrate --seed
```

Seeder diperlukan agar akun demo, kategori, produk, stok awal, dan data master tersedia untuk screenshot. Jika database sudah memiliki data produksi, sesuaikan perintah migration/seeder dengan prosedur environment tersebut.

## Menjalankan Laravel

Jalankan aplikasi pada URL lokal yang digunakan script dokumentasi:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Default URL script adalah `http://127.0.0.1:8000`. Jika aplikasi berjalan pada URL lain, gunakan environment variable `MANUAL_BASE_URL` saat menjalankan script.

Contoh:

```bash
MANUAL_BASE_URL=http://127.0.0.1:8080 npm run manual:screenshot
```

## Akun Login Dokumentasi

Akun berikut tersedia pada `database/seeders/UserSeeder.php`:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@kasirku.com` | `password` |
| Kasir | `kasir@kasirku.com` | `password` |

Jika akun di database berbeda, jalankan script dengan environment variable berikut:

```bash
MANUAL_ADMIN_EMAIL=admin@example.com MANUAL_ADMIN_PASSWORD=secret npm run manual:screenshot
MANUAL_KASIR_EMAIL=kasir@example.com MANUAL_KASIR_PASSWORD=secret npm run manual:screenshot
```

Variable yang didukung:

- `MANUAL_BASE_URL`
- `MANUAL_ADMIN_EMAIL`
- `MANUAL_ADMIN_PASSWORD`
- `MANUAL_KASIR_EMAIL`
- `MANUAL_KASIR_PASSWORD`

## Mengambil Screenshot

Pastikan Laravel sedang berjalan, lalu jalankan:

```bash
npm run manual:screenshot
```

Script akan membuka Chromium, mengambil screenshot halaman login, login sebagai admin dan kasir, lalu menyimpan screenshot ke `docs/manual/screenshots/`.

Daftar halaman screenshot berada di array `pages` pada `scripts/manual/capture-screenshots.js`. Tambahkan item baru dengan format:

```js
{
  role: 'admin',
  name: 'admin-example-page',
  url: '/admin/example',
  title: 'Contoh Halaman Admin',
  description: 'Deskripsi singkat halaman.'
}
```

Nilai `role` dapat berupa `guest`, `admin`, atau `kasir`. Nilai `name` menjadi nama file PNG, misalnya `admin-example-page.png`.

## Generate PDF

Setelah screenshot tersedia, jalankan:

```bash
npm run manual:pdf
```

PDF akan dibuat di:

```text
docs/manual/manual-book.pdf
```

Untuk menjalankan proses lengkap screenshot lalu PDF:

```bash
npm run manual:build
```

## Mengubah Isi Manual Book

Edit `docs/manual/manual-book.html` jika ada perubahan fitur, menu, role, atau alur kerja. File HTML menggunakan CSS print A4 dengan font Times New Roman dan margin:

- Atas: 3 cm
- Kiri: 4 cm
- Kanan: 3 cm
- Bawah: 3 cm

Path gambar pada HTML menggunakan format relatif `screenshots/nama-file.png`. Pastikan nama file di HTML sama dengan nilai `name` pada array `pages` di script screenshot.

## Catatan Data Dummy

Beberapa halaman seperti laporan penjualan dan daftar transaksi akan terlihat lebih lengkap jika database memiliki transaksi completed. Seeder bawaan membuat akun, kategori, produk, stok, dan stock movement awal, tetapi tidak membuat transaksi penjualan. Buat transaksi demo melalui halaman kasir jika screenshot laporan perlu menampilkan data penjualan.

## Troubleshooting

- Jika screenshot gagal karena koneksi, pastikan `php artisan serve --host=127.0.0.1 --port=8000` sedang berjalan.
- Jika login gagal, pastikan `php artisan migrate --seed` sudah dijalankan atau sesuaikan kredensial dengan environment variable.
- Jika Playwright gagal membuka Chromium, jalankan `npx playwright install chromium`.
- Jika PDF tidak memuat gambar, jalankan `npm run manual:screenshot` sebelum `npm run manual:pdf`.
- Jika route baru membutuhkan permission khusus, dokumentasikan role/permission tersebut di `manual-book.html` dan tambahkan URL ke `capture-screenshots.js`.
