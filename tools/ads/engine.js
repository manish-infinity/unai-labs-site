// Deterministic ad renderer: window.render(t) draws frame at time t (seconds).
const E = { els: [], dur: 20 };
const ease = {
  lin: x => x,
  out: x => 1 - Math.pow(1 - x, 3),
  inout: x => x < .5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2,
  back: x => { const c1 = 1.70158, c3 = c1 + 1; return 1 + c3 * Math.pow(x - 1, 3) + c1 * Math.pow(x - 1, 2); },
};
const DEF = { x: 0, y: 0, s: 1, r: 0, o: 1, g: 0, b: 1, blur: 0 };
function add(html, cls, kfs, opt = {}) {
  const d = document.createElement('div');
  d.className = 'el ' + (cls || '');
  d.innerHTML = html;
  document.getElementById('stage').appendChild(d);
  E.els.push({ d, kfs, opt });
  return d;
}
function char(src, kfs, opt = {}) { return add(`<img src="${src}">`, 'char ' + (opt.cls || ''), kfs, opt); }
function interp(kfs, t) {
  // kfs: [[time, props, easeName?], ...] sorted
  let p = Object.assign({}, DEF, kfs[0][1]);
  if (t <= kfs[0][0]) return p;
  for (let i = 0; i < kfs.length - 1; i++) {
    const [t0, a] = kfs[i], [t1, b, en] = kfs[i + 1];
    const A = Object.assign({}, p, a);
    if (t < t1) {
      const k = (ease[en || 'out'])((t - t0) / (t1 - t0));
      const B = Object.assign({}, A, b), out = {};
      for (const key in B) out[key] = A[key] + (B[key] - A[key]) * k;
      return out;
    }
    p = Object.assign({}, A, b);
  }
  return p;
}
window.render = function (t) {
  for (const { d, kfs, opt } of E.els) {
    const p = interp(kfs, t);
    let y = p.y, r = p.r, s = p.s;
    if (opt.bob) { y += Math.sin(t * Math.PI * 2 * (opt.bobHz || 0.9) + (opt.phase || 0)) * opt.bob; }
    if (opt.wobble) { r += Math.sin(t * Math.PI * 2 * 0.7 + (opt.phase || 0)) * opt.wobble; }
    if (opt.pulse) { s *= 1 + Math.max(0, Math.sin(t * Math.PI * 2 * 1.4)) * opt.pulse; }
    if (opt.shake && t >= opt.shake[0] && t <= opt.shake[1]) { const q = (t - opt.shake[0]) * 60; p.x += Math.sin(q * 2.1) * 14; r += Math.sin(q * 1.7) * 3; }
    d.style.opacity = Math.max(0, Math.min(1, p.o));
    d.style.transform = `translate(-50%,-50%) translate(${p.x}px,${y}px) rotate(${r}deg) scale(${s})`;
    d.style.filter = `grayscale(${p.g}) brightness(${p.b})` + (p.blur ? ` blur(${p.blur}px)` : '');
    if (opt.count) { const [t0, t1, v0, v1, suf] = opt.count; const k = ease.out(Math.max(0, Math.min(1, (t - t0) / (t1 - t0)))); d.querySelector('.num').textContent = Math.round(v0 + (v1 - v0) * k) + (suf || ''); }
  }
  if (window.bg) window.bg(t);
  const pb = document.getElementById('progress'); if (pb) pb.style.width = (100 * t / E.dur) + '%';
};
// helper: text that pops in at t0 and out at t1
function pop(html, cls, x, y, t0, t1, from = 'up') {
  const dy = from === 'up' ? 60 : from === 'down' ? -60 : 0;
  const s0 = from === 'zoom' ? 0.6 : 0.96;
  return add(html, cls, [[t0, { x, y: y + dy, o: 0, s: s0 }], [t0 + 0.35, { y, o: 1, s: 1 }, 'back'], [t1 - 0.25, { y, o: 1 }], [t1, { y: y - 30, o: 0 }, 'inout']]);
}
