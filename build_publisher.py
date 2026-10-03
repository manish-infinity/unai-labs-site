import re, markdown, json, base64
CAT = {"AI Literacy":28, "Brain Health":29, "Neuroplasticity":17}
posts = {
 31: "content/blog-post-31-retrieval-practice.md",
 32: "content/blog-post-32-social-connection-brain.md",
 33: "content/blog-post-33-curiosity.md",
 34: "content/blog-post-34-hearing-brain.md",
 35: "content/blog-post-35-emotion-memory.md",
}
items = []
for n, path in sorted(posts.items()):
    raw = open(path, encoding="utf-8").read()
    m = re.match(r'^---\n.*?\n---\n', raw, re.DOTALL)
    fm = {}
    fmtext = raw[:m.end()]; body = raw[m.end():]
    for line in fmtext.splitlines():
        mm = re.match(r'^(title|slug|category|suggested_meta_description):\s*(.*)$', line)
        if mm:
            fm[mm.group(1)] = mm.group(2).strip().strip('"')
    body = re.sub(r'^\s*#\s+.*?\n', '', body, count=1)
    html = markdown.markdown(body, extensions=['extra','sane_lists','smarty'])
    assert "<h1" not in html, f"post {n} has stray H1"
    cat = CAT[fm["category"]]
    items.append({
        "n": n, "title": fm["title"], "slug": fm["slug"],
        "category": cat, "categoryName": fm["category"],
        "excerpt": fm.get("suggested_meta_description",""),
        "contentB64": base64.b64encode(html.encode("utf-8")).decode("ascii"),
    })
    print(f"Post {n}: {fm['category']}({cat}) slug={fm['slug']} htmllen={len(html)}")

# Build a self-contained console script
js = """/* ============================================================================
 * UnAI Labs — ONE-PASTE BLOG BACKLOG PUBLISHER  (Sprint 63, 2026-10-03)
 * ----------------------------------------------------------------------------
 * HOW TO USE (requires a LIVE authenticated wp-admin session):
 *   1. Log in to https://unai-labs.com/wp-admin/  (tick "Remember Me").
 *   2. Open the browser DevTools Console on ANY wp-admin page.
 *   3. Paste this entire script and press Enter.
 * It publishes every staged blog post below that is NOT already live, in ONE
 * pass (seconds), so a short admin window drains the whole backlog at once.
 * IDEMPOTENT: it checks each slug first and SKIPS any that already exists, so
 * re-running it is safe and never creates duplicates. Records each new post ID.
 * ==========================================================================*/
(async () => {
  const POSTS = __POSTS__;
  const api = (window.wpApiSettings && wpApiSettings.root) || '/wp-json/';
  // Fresh nonce: prefer wpApiSettings, else fetch one from admin-ajax.
  let nonce = (window.wpApiSettings && wpApiSettings.nonce) || null;
  if (!nonce) {
    try { nonce = (await (await fetch('/wp-admin/admin-ajax.php?action=rest-nonce')).text()).trim(); } catch(e){}
  }
  if (!nonce || nonce === '0') { console.error('NOT AUTHENTICATED — log in first (no valid REST nonce).'); return; }
  const H = { 'Content-Type':'application/json', 'X-WP-Nonce': nonce };
  const dec = b64 => decodeURIComponent(escape(atob(b64)));
  const results = [];
  for (const p of POSTS) {
    try {
      const existing = await (await fetch(`${api}wp/v2/posts?slug=${p.slug}&status=publish,draft,pending,future,private&_fields=id,slug`, {headers:{'X-WP-Nonce':nonce}})).json();
      if (Array.isArray(existing) && existing.length) {
        results.push({post:p.n, slug:p.slug, action:'SKIP (already exists)', id:existing[0].id});
        console.log(`SKIP post ${p.n} — slug "${p.slug}" already exists as id ${existing[0].id}`);
        continue;
      }
      const resp = await fetch(`${api}wp/v2/posts`, { method:'POST', headers:H, body: JSON.stringify({
        title: p.title, slug: p.slug, status:'publish', categories:[p.category],
        excerpt: p.excerpt, content: dec(p.contentB64)
      })});
      const j = await resp.json();
      if (resp.ok && j.id) {
        results.push({post:p.n, slug:p.slug, action:'PUBLISHED', id:j.id, cat:p.categoryName});
        console.log(`PUBLISHED post ${p.n} → id ${j.id}  (${p.categoryName})  ${j.link||''}`);
      } else {
        results.push({post:p.n, slug:p.slug, action:'ERROR', status:resp.status, code:j.code, msg:j.message});
        console.error(`ERROR post ${p.n} (${resp.status}): ${j.code} ${j.message}`);
        if (j.code === 'rest_cookie_invalid_nonce') { console.warn('Nonce expired — reload a wp-admin page to refresh wpApiSettings.nonce, then re-run (SKIP protects already-published posts).'); break; }
      }
    } catch(e) { results.push({post:p.n, slug:p.slug, action:'EXCEPTION', err:String(e)}); console.error(`EXCEPTION post ${p.n}:`, e); }
  }
  console.log('=== BATCH PUBLISH SUMMARY ===');
  console.table(results);
  console.log('Record the PUBLISHED ids in SPRINT_LOG.md. Expected pillars after all 5: Neuroplasticity 13 / AI Literacy 12 / Brain Health 12.');
  return results;
})();
"""
js = js.replace("__POSTS__", json.dumps(items, ensure_ascii=False))
open("tools/batch-publish-blog-backlog.js","w",encoding="utf-8").write(js)
print("\nWrote tools/batch-publish-blog-backlog.js  (%d bytes, %d posts)" % (len(js), len(items)))
