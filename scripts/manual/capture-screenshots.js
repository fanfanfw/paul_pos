import { chromium } from 'playwright';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '../..');
const screenshotsDir = path.join(projectRoot, 'docs/manual/screenshots');

const baseURL = process.env.MANUAL_BASE_URL || 'http://127.0.0.1:8000';

const credentials = {
  admin: {
    email: process.env.MANUAL_ADMIN_EMAIL || 'admin@kasirku.com',
    password: process.env.MANUAL_ADMIN_PASSWORD || 'password',
  },
  kasir: {
    email: process.env.MANUAL_KASIR_EMAIL || 'kasir@kasirku.com',
    password: process.env.MANUAL_KASIR_PASSWORD || 'password',
  },
};

// Tambah atau ubah URL di daftar ini saat menu aplikasi berubah.
// role: 'guest' tidak login, sedangkan 'admin' dan 'kasir' memakai akun di credentials.
const pages = [
  {
    role: 'guest',
    name: 'login-page',
    url: '/login',
    title: 'Halaman Login',
    description: 'Halaman untuk masuk ke aplikasi.',
  },
  {
    role: 'admin',
    name: 'admin-dashboard',
    url: '/admin/dashboard',
    title: 'Dashboard Admin',
    description: 'Ringkasan penjualan, produk aktif, stok menipis, transaksi terbaru, dan stok kritis.',
  },
  {
    role: 'admin',
    name: 'admin-products-list',
    url: '/admin/products',
    title: 'Daftar Produk',
    description: 'Halaman pengelolaan katalog produk.',
  },
  {
    role: 'admin',
    name: 'admin-product-create-form',
    url: '/admin/products/create',
    title: 'Form Tambah Produk',
    description: 'Form input produk baru beserta stok awal dan stok minimal.',
  },
  {
    role: 'admin',
    name: 'admin-categories-list',
    url: '/admin/categories',
    title: 'Daftar Kategori',
    description: 'Halaman pengelolaan kategori produk.',
  },
  {
    role: 'admin',
    name: 'admin-stocks-list',
    url: '/admin/stocks',
    title: 'Daftar Stok',
    description: 'Halaman pemantauan dan penyesuaian stok produk.',
  },
  {
    role: 'admin',
    name: 'admin-stock-movements',
    url: '/admin/stocks/movements',
    title: 'Riwayat Pergerakan Stok',
    description: 'Halaman riwayat stok masuk, stok keluar, dan penyesuaian.',
  },
  {
    role: 'admin',
    name: 'admin-users-list',
    url: '/admin/users',
    title: 'Daftar User/Kasir',
    description: 'Halaman pengelolaan akun pengguna.',
  },
  {
    role: 'admin',
    name: 'admin-transactions-list',
    url: '/admin/transactions',
    title: 'Daftar Transaksi',
    description: 'Halaman monitoring transaksi semua kasir.',
  },
  {
    role: 'admin',
    name: 'admin-sales-report',
    url: '/admin/reports/sales',
    title: 'Laporan Penjualan',
    description: 'Halaman laporan penjualan dan export CSV.',
  },
  {
    role: 'admin',
    name: 'admin-stock-report',
    url: '/admin/reports/stocks',
    title: 'Laporan Stok',
    description: 'Halaman laporan kondisi stok.',
  },
  {
    role: 'kasir',
    name: 'kasir-dashboard',
    url: '/kasir/dashboard',
    title: 'Dashboard Kasir',
    description: 'Ringkasan transaksi harian kasir dan transaksi terbaru milik kasir.',
  },
  {
    role: 'kasir',
    name: 'kasir-transaction-create',
    url: '/kasir/transactions/create',
    title: 'Transaksi Baru',
    description: 'Halaman kasir untuk memilih produk, mengatur keranjang, diskon, metode bayar, dan memproses transaksi.',
  },
];

function absoluteUrl(url) {
  return new URL(url, baseURL).toString();
}

async function waitForReady(page) {
  await page.waitForLoadState('domcontentloaded');
  await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
  await page.evaluate(async () => {
    if (document.fonts?.ready) await document.fonts.ready;
    await Promise.all(
      Array.from(document.images)
        .filter((image) => !image.complete)
        .map((image) => new Promise((resolve) => {
          image.onload = image.onerror = resolve;
        })),
    );
  });
}

async function login(page, role) {
  const account = credentials[role];
  if (!account) throw new Error(`Credential untuk role '${role}' tidak tersedia.`);

  await page.goto(absoluteUrl('/login'), { waitUntil: 'domcontentloaded' });
  await page.fill('#email', account.email);
  await page.fill('#password', account.password);
  await Promise.all([
    page.waitForURL((url) => !url.pathname.endsWith('/login'), { timeout: 15000 }),
    page.click('button[type="submit"]'),
  ]).catch(async (error) => {
    const message = await page.locator('body').innerText().catch(() => 'Tidak bisa membaca respons halaman.');
    throw new Error(`Login ${role} gagal untuk ${account.email}. ${error.message}\n${message.slice(0, 500)}`);
  });

  await waitForReady(page);
}

async function capturePage(page, pageConfig) {
  const targetUrl = absoluteUrl(pageConfig.url);
  const outputPath = path.join(screenshotsDir, `${pageConfig.name}.png`);

  console.log(`Membuka ${pageConfig.title}: ${targetUrl}`);
  const response = await page.goto(targetUrl, { waitUntil: 'domcontentloaded' });
  if (!response || !response.ok()) {
    throw new Error(`Gagal membuka ${targetUrl}. Status: ${response?.status() ?? 'tidak ada response'}`);
  }

  await waitForReady(page);
  await page.screenshot({ path: outputPath, fullPage: true });
  console.log(`Screenshot tersimpan: ${path.relative(projectRoot, outputPath)}`);
}

async function run() {
  await mkdir(screenshotsDir, { recursive: true });

  const browser = await chromium.launch({ headless: true });
  try {
    const guestContext = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    const guestPage = await guestContext.newPage();
    for (const pageConfig of pages.filter((item) => item.role === 'guest')) {
      await capturePage(guestPage, pageConfig);
    }
    await guestContext.close();

    for (const role of ['admin', 'kasir']) {
      const rolePages = pages.filter((item) => item.role === role);
      if (rolePages.length === 0) continue;

      const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
      const page = await context.newPage();
      await login(page, role);

      for (const pageConfig of rolePages) {
        await capturePage(page, pageConfig);
      }

      await context.close();
    }
  } finally {
    await browser.close();
  }
}

run().catch((error) => {
  console.error('Gagal mengambil screenshot manual book.');
  console.error(error);
  console.error(`Pastikan Laravel berjalan di ${baseURL}, database sudah migrate/seed, dan akun demo aktif.`);
  process.exit(1);
});
