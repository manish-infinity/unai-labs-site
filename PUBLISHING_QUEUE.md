# PUBLISHING QUEUE — unai-labs.com

Single, ordered checklist of everything drafted in GitHub and waiting on a WordPress-authenticated sprint to publish. Created Sprint 36 (2026-08-15) to make clearing the backlog mechanical once wp-admin access is restored. Keep this in sync with SPRINT_LOG.md's Master Backlog — this file is the "how to publish" companion; the Master Backlog is the "what's blocked on Manish" list.

**Why this exists:** wp-admin publishing has been blocked since Sprint 23 (session expires; wp-login shows reauth=1). Entering credentials is out of scope for automation. Every item below is written and QA'd in `content/` — it just needs pasting into WordPress. **Unblock step (Manish):** log into https://unai-labs.com/wp-admin once in the automation browser profile with "Remember Me" checked, OR create a WordPress Application Password.

Category IDs: **Neuroplasticity = 17**, **AI Literacy = 28**, **Brain Health = 29**.
For each blog post, title / slug / suggested Yoast keyphrase / meta description are in the file's own frontmatter. All internal links in these drafts already point only to PUBLISHED destinations, so publishing in any order is safe — but the order below keeps the newest/highest-value content first.

## 1. Blog posts — ✅ ALL PUBLISHED (Sprint 37, 2026-08-18)

All 9 drafted posts were published to the live site via the authenticated WordPress REST API (correct categories, clean Gutenberg blocks, per-slug duplicate check). WP post IDs:

| # | File | Category | WP Post ID | Slug |
|---|------|----------|-----------|------|
| 1 | content/blog-post-18-sleep-debt.md | Brain Health (29) | 144 | how-to-recover-from-sleep-debt |
| 2 | content/blog-post-17-memory-palace.md | Neuroplasticity (17) | 145 | how-to-build-a-memory-palace |
| 3 | content/blog-post-16-why-you-forget.md | Neuroplasticity (17) | 146 | why-you-forget-and-how-to-remember |
| 4 | content/blog-post-15-mental-downtime.md | Brain Health (29) | 147 | why-your-brain-needs-mental-downtime |
| 5 | content/blog-post-14-cognitive-reserve.md | Neuroplasticity (17) | 148 | cognitive-reserve-why-some-brains-stay-sharp |
| 6 | content/blog-post-13-how-to-actually-learn-with-ai.md | AI Literacy (28) | 149 | how-to-actually-learn-with-ai |
| 7 | content/blog-post-12-prompting-is-thinking.md | AI Literacy (28) | 150 | prompting-is-thinking |
| 8 | content/blog-post-11-neuroscience-of-habits.md | Neuroplasticity (17) | 151 | neuroscience-of-building-habits-that-stick |
| 9 | content/blog-post-10-train-your-attention.md | Neuroplasticity (17) | 152 | train-your-attention-neuroscience-of-focus |

Post 145 now hyperlinks "how memory works" -> post 146. (The post 147->152 forward-link was skipped: no clean anchor in post 147.)

## 2. Course 2 — "Neuroplasticity in Practice" (WP course 124, currently Draft)

Modules 1–3 are already live (lessons 126-128, 137-140, 141-143). To finish:
1. Paste the 3 lessons in `content/course-124-module-4-lessons.md` under **topic 131** ("Resilience & Cognitive Longevity"). Record each lesson ID.
2. Set the course price and decide whether it gets its own WooCommerce product (mirroring product 120 for course 30).
3. Publish course 124 (change from Draft) once all four modules are live.

## 3. Course 3 — "Brain Health 101" (homepage COMING SOON card, not yet in WP)

Fully drafted: outline `courses/course-03-brain-health.md` + all four modules `content/course-03-module-1-lessons.md` … `-module-4-lessons.md`.
1. Create the Tutor LMS course from the outline; create Modules 1-4 and paste each module's lessons. Record course/topic/lesson IDs.
2. Set price; mirror product 120 with a WooCommerce product.
3. Replace the homepage "COMING SOON" card with a real course card — and align the card's "four pillars (sleep, nutrition, stress, movement)" copy with the outline's **five levers** (adds Connection).

## 4. Cleanup / verification (needs wp-admin)

- ✅ Re-verified WooCommerce product 120 (Sprint 37): "AI Literacy for Everyone", ₹2,999, live via Store API.
- Fix product-120 name mismatch: product says "AI Literacy for Everyone", course + pages say "AI Literacy for Everyday People" — align to one name.
- Trash legacy Tutor pages 121/122 ("...-legacy-unused") once confirmed unreferenced by Tutor settings.
- Connect Razorpay (Manish): WooCommerce > Settings > Payments > Razorpay > enter Key ID + Secret > Enable + Save, then place one live test order. Until then no real purchase can complete.
- Submit the "Brain + AI Starter Guide" lead-magnet form once on the live site and confirm both emails arrive (WPForms Lite stores no entries, so this needs a manual submit).

---
Last updated: 2026-08-15 (Sprint 36). When an item ships, strike it here and record the WP ID in SPRINT_LOG.md.
