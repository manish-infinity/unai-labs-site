// usage: node render.js ad1 [fps] [stillsAt comma list]
const { chromium } = require('playwright');
const { spawn } = require('child_process');
(async () => {
  const name = process.argv[2], fps = +(process.argv[3] || 30), stills = process.argv[4];
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1080, height: 1920 }, deviceScaleFactor: 1 });
  await p.goto('file://' + __dirname + '/' + name + '.html');
  await p.evaluate(() => document.fonts.ready);
  await p.waitForTimeout(300);
  const dur = await p.evaluate(() => E.dur);
  if (stills) {
    for (const t of stills.split(',')) { await p.evaluate(t => render(t), +t); await p.screenshot({ path: `out/${name}_t${t}.jpg`, type: 'jpeg', quality: 80 }); }
    await b.close(); return;
  }
  const ff = spawn('ffmpeg', ['-y', '-loglevel', 'error', '-f', 'image2pipe', '-framerate', String(fps), '-c:v', 'mjpeg', '-i', '-', '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-preset', 'medium', '-crf', '19', `out/${name}_silent.mp4`]);
  const n = Math.round(dur * fps);
  for (let i = 0; i < n; i++) {
    await p.evaluate(t => render(t), i / fps);
    const buf = await p.screenshot({ type: 'jpeg', quality: 92 });
    if (!ff.stdin.write(buf)) await new Promise(r => ff.stdin.once('drain', r));
  }
  ff.stdin.end(); await new Promise(r => ff.on('close', r)); await b.close(); console.log('done', name, n);
})();
