// Shared helpers for the no-voice reel bank (r1..rN). Loaded after engine.js.
const cl = (x, a = 0, b = 1) => Math.max(a, Math.min(b, x));
const eo = x => 1 - Math.pow(1 - cl(x), 3);
const eb = x => { x = cl(x); const c = 1.70158; return 1 + (c + 1) * Math.pow(x - 1, 3) + c * Math.pow(x - 1, 2); };
// kinetic caption: words pop one by one between t0 and t1 (the "narration" when nobody speaks)
function say(text, cls, x, y, t0, t1, wps = 7) {
  const tmp = document.createElement('div'); tmp.innerHTML = text;
  const walk = n => { [...n.childNodes].forEach(c => { if (c.nodeType === 3) { const f = document.createDocumentFragment(); c.textContent.split(/(\s+)/).forEach(p => { if (!p) return; if (/^\s+$/.test(p)) f.appendChild(document.createTextNode(' ')); else { const s = document.createElement('span'); s.className = 'w'; s.textContent = p; f.appendChild(s); } }); c.replaceWith(f); } else if (c.nodeName !== 'BR') walk(c); }); };
  walk(tmp);
  const d = add(tmp.innerHTML, cls, [[t0, { x, y: y + 40, o: 0 }], [t0 + 0.25, { y, o: 1 }, 'out'], [t1 - 0.2, { y, o: 1 }], [t1, { y: y - 30, o: 0 }, 'inout']]);
  const ws = [...d.querySelectorAll('.w')];
  E.hooks.push(t => ws.forEach((w, i) => { const k = eb((t - t0 - i / wps) / 0.22); w.style.opacity = cl((t - t0 - i / wps) / 0.12); w.style.display = 'inline-block'; w.style.transform = `translateY(${(1 - k) * 26}px) scale(${0.85 + 0.15 * k})`; }));
  return d;
}
E.hooks = [];
const _r = window.render;
window.render = t => { _r(t); E.hooks.forEach(h => h(t)); };
function cta(T0, o) {
  add(`<div class="card"><div class="pill" style="background:#0f1e3d;color:#fff;border:none;font-size:32px">${o.kicker}</div>
<h2 style="margin-top:24px">${o.title}</h2><p>${o.sub}</p>
<div class="price"><b>${o.price}</b>${o.was ? `<s>${o.was}</s>` : ''}</div>
${o.tag ? `<div class="tag">${o.tag}</div>` : ''}<div class="btn">${o.btn || 'unai-labs.com'}</div></div>`, '', [[T0, { y: -80, o: 0, s: 0.8 }], [T0 + 0.45, { o: 1, s: 1 }, 'back']]);
}
function foot(text, t0, t1, y = 760) { return pop(text, 'foot', 0, y, t0, t1, 'none'); }
