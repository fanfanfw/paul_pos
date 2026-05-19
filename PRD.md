# PRD v1.1 - Aplikasi Kasir / Point of Sale (POS)

> Dokumen ini adalah Product Requirements Document (PRD) untuk AI agent dalam membangun aplikasi POS portfolio-grade berbasis Laravel dan Docker. Fokus utama: aplikasi berjalan end-to-end, fitur lengkap, aturan bisnis jelas, dan minim ruang interpretasi agar AI agent tidak berhalusinasi.

---

## 1. Ringkasan Proyek

| Atribut | Detail |
|---|---|
| Nama Aplikasi | KasirKu |
| Tipe | Web Application - Point of Sale (POS) |
| Target Pengguna | Toko retail, warung, kafe kecil, dan bisnis kecil-menengah |
| Bahasa Antarmuka | Bahasa Indonesia |
| Karakter Produk | Portfolio-grade: fungsional, rapi, lengkap, dan siap demo |

### Tujuan Utama

Bangun aplikasi POS yang memiliki alur lengkap:

1. Login dan logout.
2. Role admin dan kasir.
3. Manajemen produk, kategori, stok, dan user kasir.
4. Transaksi penjualan dengan keranjang interaktif.
5. Pembayaran cash, transfer, dan QRIS.
6. Struk pembayaran yang bisa dicetak dan diunduh PDF.
7. Dashboard dan laporan penjualan/stok.

### Keputusan Penting

- Aplikasi hanya memiliki dua role database: `admin` dan `kasir`.
- Jangan gunakan role bernama `user`. `User` hanya nama model Laravel.
- Tidak ada register publik. User/kasir dibuat oleh admin.
- Pembayaran sederhana disimpan langsung di tabel `transactions`. Jangan membuat tabel/model `payments` untuk versi ini.
- Semua total transaksi wajib dihitung ulang di server. Jangan percaya total dari frontend.
- Produk yang tidak aktif tidak muncul di halaman kasir, tetapi data historis transaksi tetap aman lewat snapshot di `transaction_items`.

---

## 2. Kondisi Awal Project

Repository ini diasumsikan masih kosong kecuali file dokumen seperti `PRD.md` dan `design.md`.

AI agent harus melakukan langkah berikut:

1. Membuat fresh Laravel project versi 12.
2. Menambahkan konfigurasi Docker dan Docker Compose.
3. Menginstall Laravel Breeze Blade untuk authentication.
4. Menginstall Tailwind CSS, DaisyUI, Alpine.js, Chart.js, dan dependency lain sesuai PRD.
5. Membuat seluruh fitur POS sesuai urutan implementasi.

Catatan penting untuk repository yang sudah berisi `PRD.md` dan `design.md`:

- Jangan menjalankan `composer create-project laravel/laravel .` langsung jika command tersebut gagal karena direktori tidak kosong.
- Buat Laravel project di folder sementara, misalnya `tmp-laravel`, lalu pindahkan seluruh isi Laravel project ke root repository.
- Pertahankan `PRD.md` dan `design.md`.
- Setelah isi Laravel dipindahkan, hapus folder sementara.

Jika Laravel project sudah ada saat agent dijalankan, agent harus menyesuaikan tanpa menghapus file existing yang tidak perlu.

---

## 3. Scope Produk

### 3.1 Scope MVP Wajib

Fitur berikut wajib selesai dan berjalan end-to-end:

1. Login/logout dengan Laravel Breeze.
2. Role middleware untuk `admin` dan `kasir`.
3. Dashboard admin dan kasir.
4. CRUD kategori.
5. CRUD produk dengan status aktif/nonaktif.
6. Manajemen stok dan riwayat pergerakan stok.
7. Manajemen user/kasir oleh admin.
8. Halaman transaksi kasir dengan pencarian produk, keranjang, diskon, dan pembayaran.
9. Validasi stok, pembayaran, dan diskon di server.
10. Struk browser print.
11. Laporan penjualan dengan filter tanggal, kasir, dan metode pembayaran.

### 3.2 Scope Portfolio Enhancement

Fitur berikut tetap masuk scope karena aplikasi ditujukan untuk portfolio, tetapi dikerjakan setelah MVP core stabil:

1. Upload gambar produk.
2. PDF struk menggunakan `barryvdh/laravel-dompdf`.
3. Export CSV laporan penjualan.
4. Dashboard grafik penjualan dengan Chart.js.
5. Laporan stok lengkap.
6. Custom error pages 403, 404, dan 500.
7. UI polish sesuai `design.md`.

### 3.3 Out of Scope

Jangan implementasikan fitur berikut kecuali diminta secara eksplisit:

1. Multi-cabang toko.
2. Multi-gudang.
3. Split payment.
4. Refund parsial.
5. Integrasi payment gateway nyata.
6. Barcode scanner hardware integration.
7. PWA/offline mode.
8. Akuntansi lengkap.

---

## 4. Tech Stack

### Backend

| Komponen | Teknologi |
|---|---|
| Framework | Laravel 12 |
| PHP | PHP 8.3+ |
| Database | PostgreSQL 16 |
| Cache / Session | Redis 7 |
| Queue | Laravel Queue driver Redis |
| Auth | Laravel Breeze Blade, session-based |
| PDF Generation | `barryvdh/laravel-dompdf` |
| Image Processing | Laravel storage; resize boleh memakai Intervention Image jika diperlukan |

### Frontend

| Komponen | Teknologi |
|---|---|
| Template Engine | Laravel Blade |
| CSS Framework | Tailwind CSS v3 + DaisyUI v4 |
| JS | Alpine.js v3 |
| Chart | Chart.js via CDN atau npm |
| Icons | Heroicons via npm atau inline SVG |
| Print Struk | Browser Print API + CSS `@media print` |

### Infrastruktur

| Komponen | Teknologi |
|---|---|
| Containerization | Docker + Docker Compose |
| Web Server | Nginx Alpine |
| Runtime | PHP-FPM 8.3 Alpine |
| Asset Bundler | Vite |

---

## 5. Docker dan Environment

### File: `docker-compose.yml`

```yaml
services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: kasirku_app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    depends_on:
      - postgres
      - redis
    networks:
      - kasirku_network

  nginx:
    image: nginx:alpine
    container_name: kasirku_nginx
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - kasirku_network

  postgres:
    image: postgres:16-alpine
    container_name: kasirku_postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: ${DB_DATABASE:-kasirku_db}
      POSTGRES_USER: ${DB_USERNAME:-kasirku_user}
      POSTGRES_PASSWORD: ${DB_PASSWORD:-kasirku_password}
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"
    networks:
      - kasirku_network

  redis:
    image: redis:7-alpine
    container_name: kasirku_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - kasirku_network

networks:
  kasirku_network:
    driver: bridge

volumes:
  postgres_data:
```

### File: `docker/php/Dockerfile`

```dockerfile
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    git curl zip unzip libpng-dev libpq-dev \
    oniguruma-dev libxml2-dev nodejs npm

RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
```

Catatan:

- Untuk local development, jangan menjalankan `composer install` dan `npm run build` di Dockerfile sebelum source Laravel tersedia.
- Setelah Laravel project dibuat, dependency dijalankan via `docker compose exec app composer install` dan `docker compose exec app npm install`.
- Boleh menambahkan production Dockerfile terpisah nanti, tetapi local development harus sederhana dan mudah dijalankan.

### File: `docker/nginx/default.conf`

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### `.env.example`

```env
APP_NAME=KasirKu
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=kasirku_db
DB_USERNAME=kasirku_user
DB_PASSWORD=kasirku_password

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PORT=6379

STORE_NAME=KasirKu
STORE_ADDRESS="Jl. Contoh No. 1"
STORE_PHONE="0812-3456-7890"
STORE_RECEIPT_FOOTER="Terima kasih atas kunjungan Anda!"
STORE_TAX_RATE=0
```

### Cara Menjalankan

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app npm install
docker compose exec app php artisan key:generate
docker compose exec app php artisan storage:link
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app npm run dev
```

Akses aplikasi di `http://localhost:8080`.

---

## 6. Authentication dan Role

### Auth

Gunakan Laravel Breeze Blade.

Aturan:

1. Tidak ada register publik.
2. Route register bawaan Breeze harus dihapus atau dinonaktifkan.
3. Login menggunakan email dan password.
4. Setelah login, redirect berdasarkan role:
   - `admin` ke `/admin/dashboard`
   - `kasir` ke `/kasir/dashboard`
5. User dengan `is_active = false` tidak boleh login.
6. Pesan untuk akun nonaktif: `Akun Anda dinonaktifkan. Hubungi admin.`
7. Pesan untuk kredensial salah: `Email atau password salah.`

### Role

Role yang valid hanya:

| Role | Keterangan |
|---|---|
| `admin` | Mengelola master data, stok, kasir, transaksi, laporan, dan dapat membuat transaksi |
| `kasir` | Membuat transaksi, melihat transaksi miliknya, dan mencetak struk |

### Permission Matrix

| Fitur | Admin | Kasir |
|---|---:|---:|
| Login | Ya | Ya |
| Dashboard | Ya | Ya, terbatas |
| Lihat produk aktif | Ya | Ya |
| Tambah/edit produk | Ya | Tidak |
| Aktif/nonaktif produk | Ya | Tidak |
| Hapus produk | Ya, soft delete | Tidak |
| Manajemen kategori | Ya | Tidak |
| Manajemen user | Ya | Tidak |
| Lihat stok | Ya | Ya, read-only |
| Penyesuaian stok | Ya | Tidak |
| Buat transaksi | Ya | Ya |
| Lihat transaksi sendiri | Ya | Ya |
| Lihat semua transaksi | Ya | Tidak |
| Cetak struk | Ya | Ya, hanya transaksi miliknya |
| Laporan penjualan | Ya | Tidak |
| Laporan stok | Ya | Tidak |

### Middleware

Buat `RoleMiddleware` dan registrasikan di `bootstrap/app.php`.

```php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    if (! in_array(auth()->user()->role, $roles, true)) {
        abort(403, 'Akses tidak diizinkan');
    }

    return $next($request);
}
```

Catatan route: gunakan `role:admin` atau `role:admin,kasir`, bukan format pipe jika middleware menggunakan variadic parameter.

---

## 7. Database Schema

Gunakan migration Laravel. PostgreSQL enum boleh dibuat sebagai `string` dengan validation agar migrasi lebih sederhana.

### `users`

| Kolom | Tipe | Aturan |
|---|---|---|
| id | bigserial PK | |
| name | varchar(100) | required |
| email | varchar(150) | unique, required |
| password | varchar | bcrypt |
| role | varchar(20) | hanya `admin` atau `kasir`, default `kasir` |
| is_active | boolean | default true |
| email_verified_at | timestamp nullable | bawaan Breeze boleh tetap ada |
| remember_token | varchar nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |

### `categories`

| Kolom | Tipe | Aturan |
|---|---|---|
| id | bigserial PK | |
| name | varchar(100) | unique, required |
| description | text nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |

### `products`

| Kolom | Tipe | Aturan |
|---|---|---|
| id | bigserial PK | |
| category_id | FK categories nullable | `nullOnDelete` |
| code | varchar(50) | unique, uppercase |
| name | varchar(150) | required |
| description | text nullable | |
| price | numeric(15,2) | harga jual, min 0 |
| cost_price | numeric(15,2) nullable | harga modal, min 0 |
| image | varchar nullable | path gambar |
| is_active | boolean | default true |
| deleted_at | timestamp nullable | gunakan SoftDeletes |
| created_at | timestamp | |
| updated_at | timestamp | |

### `stocks`

| Kolom | Tipe | Aturan |
|---|---|---|
| id | bigserial PK | |
| product_id | FK products | unique, cascade delete jika product hard-deleted |
| quantity | integer | default 0, tidak boleh minus |
| min_quantity | integer | default 5, min 0 |
| created_at | timestamp | |
| updated_at | timestamp | |

### `stock_movements`

| Kolom | Tipe | Aturan |
|---|---|---|
| id | bigserial PK | |
| product_id | FK products | |
| user_id | FK users nullable | nullable untuk seeder/system |
| type | varchar(20) | `in`, `out`, `adjustment` |
| quantity | integer | jumlah pergerakan positif |
| before_quantity | integer | stok sebelum perubahan |
| after_quantity | integer | stok setelah perubahan |
| notes | text nullable | |
| reference | varchar nullable | invoice atau referensi manual |
| created_at | timestamp | |
| updated_at | timestamp | |

### `transactions`

| Kolom | Tipe | Aturan |
|---|---|---|
| id | bigserial PK | |
| user_id | FK users | kasir yang memproses |
| invoice_number | varchar(50) | unique, format `INV-YYYYMMDD-XXXX` |
| subtotal | numeric(15,2) | dihitung server |
| discount_type | varchar(20) nullable | `amount`, `percent`, atau null |
| discount_value | numeric(15,2) | input diskon asli, default 0 |
| discount_amount | numeric(15,2) | hasil nominal diskon, default 0 |
| tax_amount | numeric(15,2) | default 0 |
| total_amount | numeric(15,2) | dihitung server |
| payment_method | varchar(20) | `cash`, `transfer`, `qris` |
| amount_paid | numeric(15,2) | untuk cash wajib >= total; transfer/qris boleh sama dengan total |
| change_amount | numeric(15,2) | default 0 |
| status | varchar(20) | `completed`, `cancelled`, default `completed` |
| notes | text nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |

Catatan: status `refunded` tidak dipakai di versi ini karena refund out of scope. Jangan implementasikan refund kecuali diminta.

### `transaction_items`

| Kolom | Tipe | Aturan |
|---|---|---|
| id | bigserial PK | |
| transaction_id | FK transactions | cascade delete jika transaksi hard-deleted, tetapi transaksi normal tidak dihapus |
| product_id | FK products nullable | nullable agar aman jika produk dihapus permanen |
| product_name | varchar(150) | snapshot |
| product_code | varchar(50) | snapshot |
| price | numeric(15,2) | snapshot harga jual |
| cost_price | numeric(15,2) nullable | snapshot harga modal untuk laporan profit sederhana |
| quantity | integer | min 1 |
| subtotal | numeric(15,2) | price x quantity |
| created_at | timestamp | |
| updated_at | timestamp | |

Snapshot wajib disimpan agar laporan historis tetap benar walaupun produk diedit di kemudian hari.

---

## 8. Model Laravel

Buat model berikut:

1. `User`
2. `Category`
3. `Product`
4. `Stock`
5. `StockMovement`
6. `Transaction`
7. `TransactionItem`

Jangan membuat model `Payment` pada versi ini.

### Relasi Minimum

- `User hasMany Transaction`
- `User hasMany StockMovement`
- `Category hasMany Product`
- `Product belongsTo Category`
- `Product hasOne Stock`
- `Product hasMany StockMovement`
- `Product hasMany TransactionItem`
- `Stock belongsTo Product`
- `StockMovement belongsTo Product`
- `StockMovement belongsTo User`
- `Transaction belongsTo User`
- `Transaction hasMany TransactionItem`
- `TransactionItem belongsTo Transaction`
- `TransactionItem belongsTo Product`

### Casts dan Helpers

Tambahkan cast numeric/boolean yang relevan:

```php
protected $casts = [
    'price' => 'decimal:2',
    'cost_price' => 'decimal:2',
    'is_active' => 'boolean',
];
```

Helper yang disarankan:

- `User::isAdmin()`
- `User::isKasir()`
- `Product::getImageUrlAttribute()`
- `Product::getStockQuantityAttribute()`
- `Transaction::scopeCompleted()`
- `Transaction::scopeToday()`

---

## 9. Service Layer

Gunakan service layer agar controller tetap tipis.

### `TransactionService`

Method utama:

```php
public function createTransaction(array $data, User $kasir): Transaction
```

Alur wajib:

1. Jalankan semua proses dalam `DB::transaction()`.
2. Validasi role user adalah `admin` atau `kasir` dan user aktif.
3. Validasi item tidak kosong.
4. Ambil produk dari database berdasarkan `product_id`.
5. Validasi produk aktif, tidak soft-deleted, dan stok cukup.
6. Hitung `subtotal` di server berdasarkan harga produk dari database.
7. Hitung `discount_amount` dari `discount_type` dan `discount_value`.
8. Validasi diskon tidak boleh melebihi subtotal.
9. Hitung pajak dari `config('store.tax_rate')`. Jika tax rate 0, `tax_amount = 0`.
10. Hitung `total_amount`.
11. Validasi pembayaran:
    - cash: `amount_paid >= total_amount`, `change_amount = amount_paid - total_amount`
    - transfer/qris: `amount_paid = total_amount`, `change_amount = 0`
12. Generate `invoice_number`.
13. Buat record `transactions`.
14. Buat semua `transaction_items` dengan snapshot produk.
15. Kurangi stok lewat `StockService::decrementStock()`.
16. Return transaksi dengan relasi `items` dan `user`.

Frontend hanya boleh mengirim:

```json
{
  "items": [{ "product_id": 1, "quantity": 2 }],
  "discount_type": "amount",
  "discount_value": 5000,
  "payment_method": "cash",
  "amount_paid": 50000,
  "notes": "opsional"
}
```

Jangan percaya `price`, `subtotal`, `total_amount`, atau `change_amount` dari frontend.

### Invoice Number

Format:

```text
INV-YYYYMMDD-XXXX
```

Contoh:

```text
INV-20260519-0001
```

Aturan:

1. Sequence reset setiap hari.
2. Invoice harus unique.
3. Generate di dalam database transaction.
4. Jika terjadi duplicate karena transaksi bersamaan, ulangi generate maksimal 3 kali lalu throw exception.

### `StockService`

Method:

```php
decrementStock(Product $product, int $quantity, string $reference, User $user): void
incrementStock(Product $product, int $quantity, string $notes, User $user): void
adjustStock(Product $product, int $newQuantity, string $notes, User $user): void
```

Aturan:

1. Stok tidak boleh minus.
2. Semua perubahan stok wajib membuat `stock_movements`.
3. `adjustment` berarti set stok ke nilai baru, bukan tambah/kurang.
4. Simpan `before_quantity` dan `after_quantity`.
5. Gunakan row lock saat update stok untuk mencegah race condition.

### `ReportService`

Method:

```php
getSalesSummary(Carbon $from, Carbon $to, ?int $userId, ?string $paymentMethod): array
getDailyTrend(Carbon $from, Carbon $to): Collection
getStockSummary(): array
exportSalesToCsv(Carbon $from, Carbon $to, ?int $userId, ?string $paymentMethod): StreamedResponse
```

---

## 10. Routing

Gunakan route auth dari Breeze, tetapi hapus/nonaktifkan register publik.

### Redirect Root

```php
Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('kasir.dashboard');
});
```

### Admin Routes

```php
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', Admin\CategoryController::class);
    Route::resource('products', Admin\ProductController::class);
    Route::post('products/{product}/toggle', [Admin\ProductController::class, 'toggle'])->name('products.toggle');

    Route::resource('users', Admin\UserController::class);
    Route::post('users/{user}/toggle', [Admin\UserController::class, 'toggle'])->name('users.toggle');

    Route::get('stocks', [Admin\StockController::class, 'index'])->name('stocks.index');
    Route::get('stocks/{product}/adjust', [Admin\StockController::class, 'adjustForm'])->name('stocks.adjust');
    Route::post('stocks/{product}/adjust', [Admin\StockController::class, 'adjust'])->name('stocks.adjust.store');
    Route::get('stock-movements', [Admin\StockController::class, 'movements'])->name('stocks.movements');

    Route::get('transactions', [Admin\TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/{transaction}', [Admin\TransactionController::class, 'show'])->name('transactions.show');

    Route::get('reports/sales', [Admin\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/sales/export', [Admin\ReportController::class, 'exportSales'])->name('reports.sales.export');
    Route::get('reports/stocks', [Admin\ReportController::class, 'stocks'])->name('reports.stocks');
});
```

### Kasir Routes

```php
Route::prefix('kasir')->name('kasir.')->middleware(['auth', 'role:admin,kasir'])->group(function () {
    Route::get('/dashboard', [Kasir\DashboardController::class, 'index'])->name('dashboard');
    Route::get('transactions/create', [Kasir\TransactionController::class, 'create'])->name('transactions.create');
    Route::post('transactions', [Kasir\TransactionController::class, 'store'])->name('transactions.store');
    Route::get('transactions/{transaction}/receipt', [Kasir\ReceiptController::class, 'show'])->name('transactions.receipt');
    Route::get('transactions/{transaction}/receipt/pdf', [Kasir\ReceiptController::class, 'pdf'])->name('transactions.receipt.pdf');
    Route::get('api/products/search', [Kasir\TransactionController::class, 'searchProducts'])->name('api.products.search');
});
```

### Security Akses Transaksi dan Struk

1. Admin boleh melihat semua transaksi dan struk.
2. Kasir hanya boleh melihat transaksi dan struk miliknya sendiri.
3. Jika kasir mengakses transaksi milik kasir lain, tampilkan 403.
4. Terapkan pengecekan ini di controller atau policy.

---

## 11. Struktur Direktori

```text
app/
  Http/
    Controllers/
      Admin/
        DashboardController.php
        CategoryController.php
        ProductController.php
        UserController.php
        StockController.php
        TransactionController.php
        ReportController.php
      Kasir/
        DashboardController.php
        TransactionController.php
        ReceiptController.php
    Middleware/
      RoleMiddleware.php
    Requests/
      StoreCategoryRequest.php
      StoreProductRequest.php
      UpdateProductRequest.php
      StoreUserRequest.php
      UpdateUserRequest.php
      AdjustStockRequest.php
      StoreTransactionRequest.php
  Models/
    User.php
    Category.php
    Product.php
    Stock.php
    StockMovement.php
    Transaction.php
    TransactionItem.php
  Services/
    TransactionService.php
    StockService.php
    ReportService.php
config/
  store.php
database/
  migrations/
  seeders/
resources/
  views/
    layouts/
      app.blade.php
      auth.blade.php
      print.blade.php
    admin/
    kasir/
    components/
routes/
  web.php
  auth.php
```

---

## 12. Detail Fitur

### 12.1 Login

- Form: email dan password.
- Validasi email format valid.
- Tidak ada link register.
- Redirect berdasarkan role.
- User nonaktif tidak bisa login.

### 12.2 Dashboard Admin

Tampilkan:

1. Total penjualan hari ini.
2. Jumlah transaksi hari ini.
3. Total produk aktif.
4. Produk stok menipis.
5. Grafik penjualan 7 hari terakhir.
6. 5 transaksi terbaru.
7. 5 produk stok kritis.

### 12.3 Dashboard Kasir

Tampilkan:

1. Penjualan kasir hari ini.
2. Jumlah transaksi kasir hari ini.
3. Tombol besar `Mulai Transaksi Baru`.
4. 10 transaksi terakhir milik kasir tersebut.

### 12.4 Kategori

- Admin dapat CRUD kategori.
- Nama kategori unique.
- Kategori yang masih dipakai produk tidak boleh dihapus permanen. Jika dihapus, produk boleh dibuat `category_id = null` atau tampilkan error. Pilih salah satu dan konsisten; rekomendasi: tampilkan error.

### 12.5 Produk

Index:

- Tabel: gambar, kode, nama, kategori, harga, stok, status.
- Filter kategori dan status.
- Search nama/kode.
- Pagination 15 per halaman.

Create/Edit:

- Field: kode, nama, kategori, harga jual, harga modal, deskripsi, gambar, status aktif, stok awal/minimum stok saat create.
- Kode produk auto-generate jika kosong dengan format `PRD-XXXXX`.
- Gambar disimpan di `storage/app/public/products`.
- Jalankan `php artisan storage:link`.

Delete:

- Gunakan SoftDeletes.
- Produk soft-deleted tidak muncul di halaman kasir.
- Transaksi historis tetap aman karena snapshot ada di `transaction_items`.
- Untuk demo portfolio, tetap sediakan toggle aktif/nonaktif sebagai cara utama menyembunyikan produk.

### 12.6 User/Kasir

- Admin dapat membuat, mengedit, dan mengaktifkan/nonaktifkan user.
- Password required saat create, optional saat edit.
- Admin tidak boleh menonaktifkan dirinya sendiri jika itu menyebabkan tidak ada admin aktif tersisa.
- Minimal harus ada satu admin aktif.

### 12.7 Stok

Index:

- Tabel produk, stok saat ini, stok minimum, status stok.
- Status:
  - aman: `quantity > min_quantity`
  - menipis: `0 < quantity <= min_quantity`
  - habis: `quantity = 0`

Adjustment:

- `in`: menambah stok.
- `out`: mengurangi stok, tidak boleh melebihi stok saat ini.
- `adjustment`: set stok ke angka baru.

Riwayat:

- Tampilkan waktu, produk, tipe, sebelum, perubahan, sesudah, catatan, user.
- Filter produk, tipe, dan tanggal.

### 12.8 Transaksi Kasir

Layout dua kolom:

- Kolom kiri: search dan grid produk.
- Kolom kanan: keranjang dan pembayaran.

Produk:

- Hanya produk aktif, tidak soft-deleted.
- Produk stok 0 tampil disabled atau tidak muncul. Rekomendasi: tampil disabled saat sudah terlihat di hasil search.
- Search berdasarkan nama atau kode.
- Limit hasil API: 20 produk.

Keranjang:

- Tambah produk dengan klik card.
- Quantity bisa dinaikkan/diturunkan.
- Quantity tidak boleh melebihi stok yang diketahui frontend.
- Server tetap validasi ulang stok.
- Diskon bisa nominal atau persen.
- Tombol proses disabled jika keranjang kosong.

Pembayaran:

- Cash: input uang diterima dan tampilkan kembalian.
- Transfer/QRIS: tidak perlu input uang diterima; server set `amount_paid = total_amount`.
- Jika cash kurang dari total, transaksi ditolak.

### 12.9 Struk

Browser receipt:

- Nama toko dari `config/store.php`.
- Alamat dan telepon toko.
- No invoice.
- Tanggal dan jam transaksi.
- Nama kasir.
- Daftar item: nama, qty, harga, subtotal.
- Subtotal, diskon, pajak jika ada, total.
- Metode bayar, dibayar, kembalian.
- Footer ucapan terima kasih.
- Tombol cetak, download PDF, transaksi baru.

Print:

- Area struk saja yang tercetak.
- Lebar 80mm.
- Font monospace.

PDF:

- Gunakan `barryvdh/laravel-dompdf`.
- Template terpisah.
- Boleh inline display di browser.

### 12.10 Laporan Penjualan

Filter:

- Tanggal dari/sampai.
- Kasir.
- Metode pembayaran.

Statistik:

- Total pendapatan.
- Total transaksi.
- Rata-rata per transaksi.
- Total diskon.
- Estimasi profit sederhana jika `cost_price` tersedia.

Tabel:

- Invoice, tanggal, kasir, jumlah item, subtotal, diskon, total, metode bayar, status.

Export:

- CSV sesuai filter.

### 12.11 Laporan Stok

- Total produk.
- Produk stok menipis.
- Produk habis.
- Tabel stok semua produk.
- Filter status stok.

---

## 13. Konfigurasi Toko

Buat `config/store.php`:

```php
return [
    'name' => env('STORE_NAME', 'KasirKu'),
    'address' => env('STORE_ADDRESS', 'Jl. Contoh No. 1'),
    'phone' => env('STORE_PHONE', '0812-3456-7890'),
    'footer' => env('STORE_RECEIPT_FOOTER', 'Terima kasih atas kunjungan Anda!'),
    'tax_rate' => (float) env('STORE_TAX_RATE', 0),
];
```

Aturan pajak:

- Jika `tax_rate = 0`, sembunyikan pajak dari UI transaksi dan struk.
- Jika `tax_rate > 0`, tampilkan pajak dan hitung di server.

---

## 14. Validasi dan Error Handling

Gunakan Form Request untuk validasi input utama.

Validasi penting:

1. Email unique dan format valid.
2. Password minimal 8 karakter.
3. Harga produk minimal 0.
4. Stok minimal 0.
5. Quantity transaksi minimal 1.
6. Diskon tidak boleh melebihi subtotal.
7. Cash payment harus cukup.
8. Produk nonaktif tidak bisa dijual.
9. Produk stok kurang tidak bisa dijual.

Error handling:

- 403 custom page.
- 404 custom page.
- 500 custom page.
- Flash message success/error.
- Pesan validasi dalam Bahasa Indonesia.

---

## 15. Seeder

Seeder wajib membuat data demo.

### User Seeder

Admin:

```text
name: Administrator
email: admin@kasirku.com
password: password
role: admin
is_active: true
```

Kasir:

```text
name: Kasir Demo
email: kasir@kasirku.com
password: password
role: kasir
is_active: true
```

### Category Seeder

Buat minimal 5 kategori:

1. Makanan
2. Minuman
3. Snack
4. Alat Tulis
5. Lainnya

### Product Seeder

Buat minimal 20 produk demo dengan:

- kode unik
- kategori berbeda
- harga jual
- harga modal
- status aktif
- stok awal
- minimum stok

Seeder produk wajib membuat record `stocks` dan `stock_movements` tipe `in` untuk stok awal.

---

## 16. Urutan Implementasi

Ikuti urutan ini. Jangan lompat ke fitur enhancement sebelum core berjalan.

1. Setup Laravel project. Jika root repository tidak kosong karena berisi dokumen, buat project di folder sementara lalu pindahkan isi Laravel ke root tanpa menghapus dokumen.
2. Setup Docker, `.env.example`, dan koneksi PostgreSQL/Redis.
3. Install Breeze Blade dan matikan register publik.
4. Install Tailwind, DaisyUI, Alpine.js.
5. Buat migration semua tabel.
6. Buat model, relasi, casts, dan helper.
7. Buat seeder dan pastikan `migrate:fresh --seed` berhasil.
8. Buat role middleware dan redirect login berdasarkan role.
9. Buat layout utama, sidebar, topbar, alert, dan format helper.
10. Admin kategori.
11. Admin produk.
12. Admin stok.
13. Admin user.
14. Dashboard admin dan kasir.
15. TransactionService dan StockService.
16. Halaman transaksi kasir dan API search produk.
17. Struk browser print.
18. Laporan penjualan.
19. Export CSV.
20. PDF struk.
21. Laporan stok.
22. Custom error pages.
23. UI polish dan test manual end-to-end.

---

## 17. Acceptance Criteria

Aplikasi dianggap selesai jika semua poin berikut terpenuhi.

### Setup

1. `docker compose up -d --build` berhasil.
2. `php artisan migrate:fresh --seed` berhasil.
3. `npm run dev` berjalan tanpa error.
4. `php artisan storage:link` berhasil.
5. Aplikasi bisa diakses di `http://localhost:8080`.

### Auth dan Role

1. Admin bisa login dengan `admin@kasirku.com / password`.
2. Kasir bisa login dengan `kasir@kasirku.com / password`.
3. Kasir tidak bisa membuka halaman admin.
4. User nonaktif tidak bisa login.
5. Tidak ada register publik.

### Produk dan Stok

1. Admin bisa membuat, mengedit, mengaktifkan/nonaktifkan, dan soft delete produk.
2. Produk baru otomatis memiliki record stok.
3. Admin bisa menyesuaikan stok.
4. Semua perubahan stok tercatat di `stock_movements`.
5. Produk nonaktif tidak muncul sebagai produk yang bisa dijual.

### Transaksi

1. Kasir bisa mencari produk aktif.
2. Kasir bisa menambahkan produk ke keranjang.
3. Sistem menolak transaksi dengan keranjang kosong.
4. Sistem menolak quantity melebihi stok.
5. Sistem menghitung ulang subtotal, diskon, pajak, total, dan kembalian di server.
6. Cash ditolak jika uang diterima kurang dari total.
7. Transfer dan QRIS berhasil tanpa input uang diterima manual.
8. Setelah transaksi berhasil, stok berkurang.
9. Invoice number unique.
10. Setelah transaksi berhasil, user diarahkan ke struk.

### Struk dan Laporan

1. Struk tampil dengan format rapi.
2. Struk bisa dicetak via browser print tanpa elemen UI ikut tercetak.
3. PDF struk bisa dibuka.
4. Admin bisa melihat laporan penjualan dengan filter.
5. Admin bisa export CSV laporan penjualan.
6. Admin bisa melihat laporan stok.
7. Kasir hanya bisa melihat transaksi miliknya sendiri.

### UI

1. Semua halaman authenticated memakai layout utama.
2. Sidebar menandai nav aktif.
3. Semua tabel memiliki empty state.
4. Semua form menampilkan error per field.
5. Semua nominal uang format `Rp 10.000`.
6. Halaman transaksi nyaman dipakai di desktop.
7. Halaman tetap bisa digunakan di layar kecil meski desktop adalah prioritas.

---

## 18. Instruksi Untuk AI Agent

1. Ikuti PRD ini dan `design.md`.
2. Jika ada konflik, prioritaskan aturan bisnis dan acceptance criteria di PRD ini.
3. Jangan menambahkan fitur out of scope.
4. Jangan membuat model/tabel `Payment`.
5. Jangan menggunakan role selain `admin` dan `kasir`.
6. Jangan percaya total dari frontend; hitung ulang di server.
7. Kerjakan sesuai urutan implementasi.
8. Setelah menyelesaikan bagian besar, jalankan command verifikasi yang relevan.
9. Tulis kode yang sederhana, jelas, dan mengikuti konvensi Laravel.
10. Jika harus memilih antara fitur dekoratif dan stabilitas transaksi, prioritaskan stabilitas transaksi.

---

*Dokumen ini versi 1.1. Scope tetap portfolio-grade, tetapi keputusan teknis dan aturan bisnis dibuat eksplisit agar AI agent dapat mengimplementasikan aplikasi dengan konsisten.*
