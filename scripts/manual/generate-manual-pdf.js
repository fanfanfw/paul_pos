import { chromium } from 'playwright';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL, fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '../..');
const manualDir = path.join(projectRoot, 'docs/manual');
const htmlPath = path.join(manualDir, 'manual-book.html');
const pdfPath = path.join(manualDir, 'manual-book.pdf');

async function waitForAssets(page) {
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

  const brokenImages = await page.evaluate(() => Array.from(document.images)
    .filter((image) => !image.complete || image.naturalWidth === 0)
    .map((image) => image.getAttribute('src') || image.currentSrc));

  if (brokenImages.length > 0) {
    throw new Error(`Gambar manual book gagal dimuat:\n${brokenImages.map((src) => `- ${src}`).join('\n')}\nJalankan npm run manual:screenshot terlebih dahulu.`);
  }
}

async function run() {
  await mkdir(manualDir, { recursive: true });

  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage();
    await page.goto(pathToFileURL(htmlPath).toString(), { waitUntil: 'domcontentloaded' });
    await waitForAssets(page);

    await page.pdf({
      path: pdfPath,
      format: 'A4',
      printBackground: true,
      preferCSSPageSize: true,
    });

    console.log(`PDF manual book berhasil dibuat: ${path.relative(projectRoot, pdfPath)}`);
  } finally {
    await browser.close();
  }
}

run().catch((error) => {
  console.error('Gagal generate PDF manual book.');
  console.error(error);
  process.exit(1);
});
