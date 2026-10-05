const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

(async () => {
  const FPS = 30;
  const DUR = 44.97;
  const total = Math.ceil(DUR * FPS);
  const outDir = path.join(__dirname, 'rframes');
  fs.rmSync(outDir, { recursive: true, force: true });
  fs.mkdirSync(outDir, { recursive: true });

  const browser = await chromium.launch({ args: ['--force-color-profile=srgb','--disable-lcd-text'] });
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 }, deviceScaleFactor: 1 });
  await page.addInitScript(() => { window.__render = true; });
  await page.goto('file://' + path.join(__dirname, 'index.html'));
  await page.evaluate(() => document.fonts.ready);
  const stage = await page.$('#stage');

  for (let i = 0; i < total; i++) {
    const t = i / FPS;
    await page.evaluate((tt) => window.__seek(tt), t);
    await stage.screenshot({ path: path.join(outDir, `f${String(i).padStart(4,'0')}.png`) });
    if (i % 150 === 0) console.log(`frame ${i}/${total}`);
  }
  await browser.close();
  console.log('done frames:', total);
})();
