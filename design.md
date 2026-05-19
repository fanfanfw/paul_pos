# design.md v1.1 - Panduan Desain UI/UX KasirKu

> Dokumen ini adalah panduan desain untuk AI agent saat mengimplementasikan tampilan aplikasi KasirKu. Desain harus mendukung aplikasi POS portfolio-grade: rapi, profesional, cepat dipakai kasir, dan cukup menarik untuk demo.

---

## 1. Filosofi Desain

Tema visual: **Clean Merchant**.

Karakter desain:

1. Bersih dan profesional.
2. Cepat digunakan di meja kasir.
3. Data padat tetapi tetap mudah dibaca.
4. Tidak terlalu dekoratif, tetapi tetap terasa modern dan layak portfolio.
5. Semua aksi penting harus jelas: tambah produk, simpan, proses transaksi, cetak struk.

Prinsip utama:

- **Clarity over cleverness**: jangan mengorbankan kejelasan demi gaya visual.
- **Speed-first**: halaman transaksi harus bisa dipakai cepat tanpa banyak klik.
- **Trustworthy finance UI**: nominal, kembalian, dan status transaksi harus sangat jelas.
- **Consistent feedback**: setiap aksi harus punya loading, success, error, atau disabled state.
- **Portfolio-grade polish**: gunakan spacing, typography, icon, empty state, dan visual hierarchy yang matang.

---

## 2. Palet Warna

Gunakan warna yang stabil dan profesional. Hindari gradient berlebihan, glassmorphism, glow neon, atau gaya generik AI.

### CSS Variables

Tambahkan ke `resources/css/app.css` jika dibutuhkan:

```css
:root {
  --color-primary: #1e40af;
  --color-primary-focus: #1d4ed8;
  --color-primary-content: #ffffff;

  --color-secondary: #64748b;
  --color-secondary-focus: #475569;
  --color-secondary-content: #ffffff;

  --color-accent: #f59e0b;
  --color-accent-focus: #d97706;
  --color-accent-content: #1c1917;

  --color-success: #16a34a;
  --color-warning: #ea580c;
  --color-error: #dc2626;
  --color-info: #0284c7;

  --color-base-100: #f8fafc;
  --color-base-200: #f1f5f9;
  --color-base-300: #e2e8f0;
  --color-base-content: #0f172a;
  --color-base-content-secondary: #64748b;
}
```

### DaisyUI Theme

```js
module.exports = {
  content: ['./resources/**/*.blade.php', './resources/**/*.js'],
  theme: { extend: {} },
  plugins: [require('daisyui')],
  daisyui: {
    themes: [
      {
        kasirku: {
          primary: '#1e40af',
          'primary-content': '#ffffff',
          secondary: '#64748b',
          'secondary-content': '#ffffff',
          accent: '#f59e0b',
          'accent-content': '#1c1917',
          neutral: '#1e293b',
          'neutral-content': '#f8fafc',
          'base-100': '#f8fafc',
          'base-200': '#f1f5f9',
          'base-300': '#e2e8f0',
          'base-content': '#0f172a',
          info: '#0284c7',
          success: '#16a34a',
          warning: '#ea580c',
          error: '#dc2626',
        },
      },
    ],
    defaultTheme: 'kasirku',
  },
};
```

### Penggunaan Warna

| Konteks | Style |
|---|---|
| Aksi utama | `btn-primary` |
| Aksi sekunder | `btn-ghost` atau `btn-outline` |
| Aksi berbahaya | `btn-error` atau `btn-error btn-outline` |
| Simpan/proses transaksi | `btn-primary` |
| Cetak/download | `btn-outline` |
| Stok aman | `badge-success` |
| Stok menipis | `badge-warning` |
| Stok habis | `badge-error` |
| Admin | `badge-primary` |
| Kasir | `badge-secondary` |
| Aktif | `badge-success` |
| Nonaktif | `badge-ghost` |
| Completed | `badge-success` |
| Cancelled | `badge-error` |

---

## 3. Tipografi

Gunakan font yang modern, bersih, dan mudah dibaca di dashboard maupun tabel.

```css
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap');

body {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 14px;
  line-height: 1.6;
  color: #0f172a;
}

.font-mono,
.invoice-number,
.product-code,
.money-value {
  font-family: 'JetBrains Mono', monospace;
}
```

| Elemen | Class Tailwind |
|---|---|
| Judul halaman | `text-xl font-bold text-base-content` |
| Section title | `text-base font-semibold text-base-content` |
| Label form | `text-sm font-medium text-base-content` |
| Body | `text-sm text-base-content` |
| Helper text | `text-xs text-base-content/60` |
| Nominal penting | `text-2xl font-bold font-mono` |
| Total transaksi | `text-3xl font-bold font-mono tracking-tight` |
| Kode produk/invoice | `text-xs font-mono tracking-wide uppercase` |

---

## 4. Layout Utama

Layout authenticated menggunakan sidebar tetap di desktop dan drawer di mobile.

```text
+-------------------------------------------------------------+
| Sidebar 256px              | Topbar                         |
| Logo / Store name          | Page title / breadcrumb / user |
| Navigation                 +--------------------------------+
|                            | Page body                      |
| User info + logout         |                                |
+-------------------------------------------------------------+
```

### Sidebar

File: `resources/views/components/sidebar.blade.php`

Aturan:

- Desktop: `w-64 fixed left-0 top-0 h-screen`.
- Background: `bg-base-100 border-r border-base-300`.
- Logo area: nama toko dan label kecil `Point of Sale`.
- Nav aktif: `bg-primary text-primary-content rounded-lg shadow-sm`.
- Nav hover: `hover:bg-base-200 rounded-lg`.
- Kelompokkan menu dengan label kecil uppercase.
- Bagian bawah: nama user, role badge, tombol logout.

Menu admin:

1. Dashboard
2. Produk
3. Kategori
4. Stok
5. Transaksi
6. Laporan Penjualan
7. Laporan Stok
8. User/Kasir

Menu kasir:

1. Dashboard
2. Transaksi Baru
3. Riwayat Saya jika dibuat

Jangan tampilkan menu admin untuk kasir.

### Topbar

File: `resources/views/components/topbar.blade.php`

Aturan:

- Height: `h-14`.
- Background: `bg-base-100 border-b border-base-300`.
- Kiri: judul halaman dan breadcrumb singkat.
- Kanan: indikator stok menipis untuk admin, nama user, role badge.
- Mobile: tampilkan hamburger untuk drawer sidebar.

### Page Body

- Background: `bg-base-200`.
- Padding desktop: `p-6`.
- Padding mobile: `p-4`.
- Gunakan full width, jangan pakai max-width sempit untuk halaman data.

---

## 5. Komponen UI Standar

### Card

```html
<div class="card bg-base-100 border border-base-300 shadow-sm">
  <div class="card-body">
    <!-- content -->
  </div>
</div>
```

### Stat Card

```html
<div class="stat bg-base-100 rounded-xl border border-base-300 shadow-sm">
  <div class="stat-figure text-primary">
    <!-- icon -->
  </div>
  <div class="stat-title text-xs text-base-content/60">Label</div>
  <div class="stat-value text-2xl font-bold font-mono">Rp 0</div>
  <div class="stat-desc text-xs">Deskripsi</div>
</div>
```

### Table

```html
<div class="overflow-x-auto rounded-xl border border-base-300 bg-base-100">
  <table class="table table-sm">
    <thead class="bg-base-200">
      <tr>
        <th class="text-xs font-semibold text-base-content/60 uppercase tracking-wide">Kolom</th>
      </tr>
    </thead>
    <tbody>
      <tr class="hover:bg-base-200 transition-colors">
        <td>...</td>
      </tr>
    </tbody>
  </table>
</div>
```

### Form Input

```html
<div class="form-control">
  <label class="label">
    <span class="label-text text-sm font-medium">Nama <span class="text-error">*</span></span>
  </label>
  <input type="text" class="input input-bordered input-sm w-full focus:input-primary" />
  <label class="label">
    <span class="label-text-alt text-error text-xs">Pesan error</span>
  </label>
</div>
```

### Button

```html
<button class="btn btn-primary btn-sm gap-2">Simpan</button>
<button class="btn btn-ghost btn-sm">Batal</button>
<button class="btn btn-error btn-outline btn-sm">Hapus</button>
<button class="btn btn-primary btn-sm loading">Memproses...</button>
```

### Flash Alert

File: `resources/views/components/alert.blade.php`

Aturan:

- Success: `alert-success`.
- Error: `alert-error`.
- Warning: `alert-warning`.
- Auto-dismiss setelah 4 detik memakai Alpine.js.
- Error validasi form tetap tampil per field, jangan hanya flash global.

### Modal Konfirmasi

Gunakan modal untuk aksi berisiko:

- Hapus kategori.
- Soft delete produk.
- Nonaktifkan user.
- Batalkan transaksi jika fitur cancel dibuat.

Microcopy harus jelas, misalnya:

```text
Produk ini akan disembunyikan dari halaman kasir. Data transaksi lama tetap aman.
```

---

## 6. Halaman Login

Login harus sederhana dan profesional.

Layout:

- Centered card.
- Background `bg-base-200`.
- Card `max-w-md`.
- Tampilkan nama aplikasi `KasirKu` dan subtitle `Point of Sale`.
- Field: email dan password.
- Tombol utama: `Masuk`.
- Tidak ada link register.

Pesan error:

- Kredensial salah: `Email atau password salah.`
- Akun nonaktif: `Akun Anda dinonaktifkan. Hubungi admin.`

Demo helper boleh ditampilkan kecil di bawah form untuk portfolio:

```text
Demo Admin: admin@kasirku.com / password
Demo Kasir: kasir@kasirku.com / password
```

---

## 7. Dashboard

### Dashboard Admin

Susunan:

1. Header: judul, tanggal hari ini.
2. Grid stat cards: penjualan hari ini, transaksi hari ini, produk aktif, stok menipis.
3. Chart penjualan 7 hari.
4. Dua tabel ringkas: transaksi terbaru dan stok kritis.

Gunakan chart secukupnya. Jangan memenuhi halaman dengan dekorasi.

### Dashboard Kasir

Fokus ke aksi cepat.

Susunan:

1. Greeting personal: `Selamat bekerja, Kasir Demo`.
2. Stat cards: penjualanku hari ini, jumlah transaksi hari ini.
3. Tombol besar: `Mulai Transaksi Baru`.
4. Tabel 10 transaksi terakhir milik kasir.

Tombol transaksi baru harus menjadi elemen paling menonjol di dashboard kasir.

---

## 8. Halaman Data Admin

Berlaku untuk produk, kategori, stok, user, transaksi, dan laporan.

### Header Halaman

Struktur:

- Kiri: judul dan deskripsi singkat.
- Kanan: primary action jika ada, misalnya `Tambah Produk`.

Contoh:

```text
Produk
Kelola katalog produk yang tersedia di halaman kasir.
```

### Filter Bar

Gunakan filter bar dalam card putih:

- Search input di kiri.
- Filter select di kanan.
- Tombol reset filter jika filter aktif.

### Empty State

Setiap tabel/list kosong wajib punya empty state.

```html
<div class="flex flex-col items-center justify-center py-16 text-center text-base-content/60">
  <svg class="w-12 h-12 mb-3 opacity-30"><!-- icon --></svg>
  <p class="text-sm font-semibold text-base-content">Belum ada data produk</p>
  <p class="text-xs mt-1">Mulai dengan menambahkan produk pertama Anda.</p>
  <a href="..." class="btn btn-primary btn-sm mt-4">Tambah Produk</a>
</div>
```

### Produk

Visual produk:

- Thumbnail 40x40 atau 48x48.
- Jika tidak ada gambar, tampilkan placeholder inisial produk.
- Kode produk pakai font mono.
- Status aktif/nonaktif pakai badge.
- Stok ditampilkan sebagai badge kecil.

### Stok

Status stok harus sangat mudah dipindai:

- Aman: hijau.
- Menipis: orange/kuning.
- Habis: merah.

Riwayat stok tampilkan `sebelum -> sesudah` agar perubahan mudah diaudit.

### User/Kasir

Tampilkan warning saat admin mencoba menonaktifkan dirinya sendiri atau admin terakhir.

Microcopy:

```text
Minimal harus ada satu admin aktif agar aplikasi tetap bisa dikelola.
```

---

## 9. Halaman Kasir / Transaksi Baru

Ini halaman paling penting. Prioritasnya kecepatan, kejelasan total, dan minim error.

Desktop layout:

```text
+--------------------------------------------------------------+
| Topbar: Transaksi Baru                         User / Logout |
+--------------------------------------+-----------------------+
| Produk                               | Keranjang             |
| Search                               | Items                 |
| Grid produk                          | Subtotal              |
|                                      | Diskon                |
|                                      | Pajak jika ada        |
|                                      | Total besar           |
|                                      | Metode bayar          |
|                                      | Uang diterima         |
|                                      | Kembalian             |
|                                      | Proses Transaksi      |
+--------------------------------------+-----------------------+
```

### Kolom Produk

- Search input sticky di bagian atas kolom produk.
- Placeholder: `Cari nama atau kode produk...`.
- Grid: `grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3`.
- Card produk bisa diklik penuh.
- Card yang stok habis: opacity rendah dan disabled.
- Card yang sudah ada di keranjang: border primary.

Product card minimal menampilkan:

- Gambar/placeholder.
- Nama produk.
- Kode produk kecil.
- Harga.
- Stok.

### Kolom Keranjang

- Desktop: lebar sekitar `w-[420px]`, sticky/fixed dalam area halaman.
- Background: `bg-base-100 border-l border-base-300`.
- Item list scroll jika panjang.
- Total dan tombol proses tetap terlihat di bawah.

Item keranjang:

- Nama produk.
- Harga satuan.
- Quantity stepper minus/plus.
- Subtotal.
- Tombol hapus kecil.

### Ringkasan Transaksi

Urutan wajib:

1. Subtotal.
2. Diskon.
3. Pajak jika `STORE_TAX_RATE > 0`.
4. Total, paling besar dan tebal.

Diskon:

- Toggle `Nominal` dan `Persen`.
- Jika persen, tampilkan suffix `%`.
- Jika nominal, format sebagai Rupiah.
- Tampilkan error inline jika diskon lebih besar dari subtotal.

### Pembayaran

Metode bayar:

- Cash.
- Transfer.
- QRIS.

Cash:

- Tampilkan input `Uang Diterima`.
- Tampilkan `Kembalian` dengan font mono besar.
- Jika uang kurang, tampilkan warning dan disable submit.

Transfer/QRIS:

- Sembunyikan input `Uang Diterima`.
- Tampilkan helper text: `Pembayaran dianggap pas sesuai total transaksi.`

### Tombol Proses

- Full width.
- `btn btn-primary btn-lg`.
- Disabled jika keranjang kosong.
- Loading state saat submit.
- Setelah sukses, redirect ke struk.

Microcopy di bawah tombol:

```text
Total akan divalidasi ulang oleh server sebelum transaksi disimpan.
```

---

## 10. Struk Pembayaran

Struk harus terlihat seperti receipt sungguhan, bukan invoice besar.

### Browser Receipt

- Container `max-w-sm mx-auto`.
- Background putih.
- Font monospace.
- Border tipis.
- Tombol aksi di luar area struk.

Isi struk:

1. Nama toko.
2. Alamat dan telepon.
3. Invoice.
4. Tanggal dan jam.
5. Kasir.
6. Daftar item.
7. Subtotal.
8. Diskon jika ada.
9. Pajak jika ada.
10. Total.
11. Metode bayar.
12. Dibayar dan kembalian.
13. Footer terima kasih.

### Print CSS

```css
@media print {
  body * { visibility: hidden; }
  #receipt-area, #receipt-area * { visibility: visible; }
  #receipt-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 80mm;
    font-size: 10px;
    font-family: 'Courier New', monospace;
  }
  .no-print { display: none !important; }
}
```

Tombol aksi:

- `Cetak Struk`.
- `Download PDF`.
- `Transaksi Baru`.
- `Kembali ke Dashboard`.

---

## 11. Laporan

### Laporan Penjualan

Layout:

1. Filter card di atas.
2. Summary cards.
3. Chart tren harian.
4. Tabel transaksi.
5. Tombol export CSV.

Filter harus jelas:

- Tanggal dari.
- Tanggal sampai.
- Kasir.
- Metode pembayaran.

Jika filter aktif, tampilkan ringkasan filter agar user tahu konteks data.

### Laporan Stok

Layout:

1. Summary: total produk, menipis, habis.
2. Filter status stok.
3. Tabel produk dan stok.

Gunakan badge warna konsisten dengan halaman stok.

---

## 12. Format Data

### Rupiah

Semua nominal uang harus memakai format:

```text
Rp 125.000
```

Bukan:

```text
Rp 125000.00
```

Gunakan helper Blade atau PHP:

```php
'Rp ' . number_format($value, 0, ',', '.')
```

### Tanggal

Format tampilan:

```text
19 Mei 2026, 14:30
```

### Kode

- Kode produk uppercase.
- Invoice uppercase.
- Gunakan font mono.

---

## 13. Responsivitas

Aplikasi dioptimalkan untuk desktop karena POS biasanya dipakai di laptop/PC. Mobile tetap harus usable untuk demo.

| Breakpoint | Perilaku |
|---|---|
| `lg` dan lebih besar | Sidebar terbuka, layout normal |
| `md` | Sidebar boleh collapse atau tetap drawer |
| `< md` | Sidebar menjadi drawer hamburger |

Halaman transaksi:

- Desktop: produk kiri, keranjang kanan.
- Tablet/mobile: keranjang pindah ke bawah atau menjadi sticky bottom summary dengan detail expandable.
- Tombol proses tetap mudah dijangkau.

---

## 14. State dan Feedback

Setiap halaman harus punya state berikut jika relevan:

- Loading.
- Empty.
- Success.
- Error.
- Disabled.
- Confirmation.

Contoh loading tombol:

```html
<button class="btn btn-primary loading" disabled>Memproses...</button>
```

Contoh disabled produk habis:

```html
<button class="card opacity-50 cursor-not-allowed" disabled>
  <!-- produk -->
</button>
```

Jangan biarkan user menebak apakah aksi sedang diproses.

---

## 15. Animasi

Animasi minimal, fungsional, dan cepat.

```css
.btn,
.card,
.input,
.badge {
  transition: all 0.15s ease;
}

@keyframes fadeSlideDown {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}
```

Boleh gunakan micro-interaction:

- Product card sedikit scale saat ditambahkan ke keranjang.
- Alert fade in/out.
- Row hover di tabel.
- Button loading state.

Jangan gunakan animasi panjang yang memperlambat kasir.

---

## 16. Ikonografi

Gunakan Heroicons outline untuk navigasi dan solid untuk aksi penting.

| Konteks | Icon |
|---|---|
| Dashboard | `squares-2x2` |
| Produk | `shopping-bag` |
| Kategori | `tag` |
| Stok | `cube` |
| Transaksi | `calculator` |
| Laporan | `chart-bar` |
| User | `users` |
| Tambah | `plus` |
| Edit | `pencil-square` |
| Hapus | `trash` |
| Print | `printer` |
| Download | `arrow-down-tray` |
| Search | `magnifying-glass` |
| Logout | `arrow-right-on-rectangle` |
| Warning | `exclamation-triangle` |
| Success | `check-circle` |
| Error | `x-circle` |

---

## 17. Anti-AI-Slop Rules

Jangan membuat UI terlihat seperti template AI generik.

Hindari:

- Gradient text besar.
- Glow neon.
- Glass card blur berlebihan.
- Hero section marketing yang tidak relevan untuk POS.
- Card grid identik tanpa hierarchy.
- Warna terlalu banyak tanpa fungsi.
- Empty state tanpa konteks.
- Tombol utama yang tidak jelas.

Pastikan:

- Halaman transaksi terasa seperti alat kerja kasir.
- Dashboard terasa seperti command center toko.
- Laporan terasa kredibel dan mudah diaudit.
- Struk terasa seperti receipt nyata.

---

## 18. Checklist UI Sebelum Selesai

- [ ] Tidak ada link register publik di halaman auth.
- [ ] Semua halaman authenticated memakai layout utama.
- [ ] Sidebar hanya menampilkan menu sesuai role.
- [ ] Sidebar menandai nav aktif dengan benar.
- [ ] Topbar menampilkan judul halaman yang sesuai.
- [ ] Semua tabel memiliki empty state.
- [ ] Semua form punya error per field.
- [ ] Semua aksi berbahaya punya konfirmasi.
- [ ] Semua flash message tampil dan auto-dismiss.
- [ ] Semua nominal uang berformat `Rp 10.000`.
- [ ] Semua kode produk dan invoice uppercase + font mono.
- [ ] Halaman transaksi nyaman dipakai di desktop.
- [ ] Produk habis tidak bisa ditambahkan ke keranjang.
- [ ] Cash kurang dari total memberi warning sebelum submit.
- [ ] Transfer/QRIS menyembunyikan input uang diterima.
- [ ] Pajak hanya tampil jika tax rate lebih dari 0.
- [ ] Struk bisa diprint tanpa elemen UI lain ikut tercetak.
- [ ] Laporan memiliki filter yang jelas dan mudah direset.
- [ ] UI tetap usable di layar kecil.

---

*Dokumen ini versi 1.1. Desain tetap ambisius untuk portfolio, tetapi semua pola UI diarahkan untuk mendukung workflow POS yang cepat, jelas, dan aman.*
