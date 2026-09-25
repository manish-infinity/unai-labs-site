# CRITICAL SITE FIXES — found Sprint 56 (2026-09-25)

Two revenue/conversion-impacting problems were found on the **live** site this sprint by
inspecting the rendered pages directly (not just the logs). Both require **wp-admin access**,
which is currently blocked (session lapsed; `/wp-json/wp/v2/users/me` = HTTP 401). They are
staged here as turnkey fixes so Manish — or the next authenticated sprint — can apply them in
a few minutes. Priority order below.

Verified live on 2026-09-25 (all read-only, no credentials entered, no Log In clicked).

---

## FIX 1 — [CRITICAL / REVENUE LEAK] Course 1 "AI Literacy for Everyone" is FREE

### What's wrong
The flagship entry course — **course ID 30**, `/courses/ai-literacy-for-everyday-people/` — is
currently **giving itself away for free.**

Evidence (live rendered DOM, 2026-09-25):
- The course page shows **"Free"** and **"Free access this course."**
- The enrollment control is a Tutor **free-enroll** button, not a WooCommerce purchase:
  the form fires `tutor_course_action=_tutor_course_enroll_now` with `tutor_course_id=30`.
  There is **no price and no "Add to cart"** on the page.
- **WooCommerce product 120** (the paid product that is supposed to back this course) returns
  **HTTP 404** in the Store API — i.e. it is trashed, unpublished, or unlinked from course 30.

For contrast, the other two paid courses are correct:
- Course 124 "Neuroplasticity in Practice" → **Add to cart**, **₹2,999.00**, product **157**. ✅
- Course 209 "Deep Focus" → **Add to cart**, **₹2,999.00**, product **226**. ✅

So the drift is isolated to Course 1. Anyone can currently enrol in the paid flagship course
for ₹0. (Note: earlier sprint logs claimed course 30 rendered "Rs 2,999.00" — that was
incorrect; the live page is the source of truth and it is free right now.)

### Intended state
Course 1 should be **Paid at ₹2,999** (matching Courses 2 & 4), sold through WooCommerce,
same as it was originally built (product 120). Confirm the price with Manish if he wants a
different intro price — but the default is ₹2,999 for consistency.

### Fix steps (wp-admin — ~5 min)
1. Log in to `https://unai-labs.com/wp-admin`.
2. Go to **WooCommerce → Products** and check product **120** ("AI Literacy for Everyone"):
   - If it is in **Trash** → Restore it. If **Draft/Pending** → set to **Published**.
   - If it is gone entirely → **recreate it** as a **Simple, Virtual, "For Tutor"** product,
     Regular price **2999** (INR), Published — mirroring product 226 exactly (see Sprint 54
     log for the proven product-creation recipe).
3. Go to **Tutor LMS → Courses → "AI Literacy for Everyone" (course 30) → edit**.
4. In the course builder, open the **pricing / monetization** panel: set the course to
   **Paid**, and in the **"Select product"** dropdown choose product **120** (or the
   recreated product). Save/Update. (Linking through the Tutor dropdown auto-fixes the
   course↔product meta — this is how Courses 2 & 4 are wired.)
5. Update the course.

### QA after fixing
- Reload `/courses/ai-literacy-for-everyday-people/` (logged out / incognito):
  it should show **₹2,999.00** and an **"Add to cart"** button (NOT "Free" / "Enroll Now").
- Confirm product 120 returns HTTP 200 at `/wp-json/wc/store/v1/products/120` **or** (if kept
  catalog-hidden like 157) that the course page's add-to-cart form carries `add-to-cart=120`.
- Reconcile with FIX 2 below (the homepage card must also stop saying "Coming Soon").

---

## FIX 2 — [HIGH / CONVERSION] Homepage advertises all courses as "COMING SOON"

### What's wrong
On the live homepage (page **22**, `/`), the "Start here. Go deep." section shows **three
course cards, and all three say "COMING SOON" with a non-clickable "Coming Soon" label** —
including the two courses that are actually live:
- **AI Literacy for Everyone** — card says COMING SOON (course 30 exists; see Fix 1)
- **Neuroplasticity in Practice** — card says COMING SOON, but this course is **live and
  purchasable at ₹2,999** (course 124 / product 157)
- **Brain Health 101** — COMING SOON — this one is genuinely correct (not built yet)

There is **no working "Enrol"/"View course" link and no price** on any homepage card, so a
visitor landing on the homepage has **no path to buy anything.** For a course-selling site,
this is the single biggest conversion leak on the highest-traffic page.

Also: **Course 4 "Deep Focus" (course 209, live & purchasable at ₹2,999) has no card on the
homepage at all** (long-standing backlog item). It is only discoverable via `/our-courses/`.

### Intended state
Homepage course cards should reflect live reality:
- **Live courses** → show price + a working CTA linking to the course page.
- **Not-yet-built courses** → keep an honest "Coming soon."

### Desired card copy (drop-in)

**Card 1 — AI Literacy for Everyone** *(after Fix 1 makes it paid)*
> **AI Literacy for Everyone**
> Use AI as a tool for thinking, not a crutch — what it is, what it is not, and how to use it well.
> *Who it's for:* Anyone who uses or is curious about AI. No tech background needed.
> *Format:* 4 weeks · 3–4 hrs/week · Self-paced
> **₹2,999** — **[Enrol now →](/courses/ai-literacy-for-everyday-people/)**

**Card 2 — Neuroplasticity in Practice** *(live now)*
> **Neuroplasticity in Practice**
> Rewire your brain for faster learning, deeper focus, and lasting cognitive resilience.
> *Who it's for:* Students, professionals, and lifelong learners. No science background needed.
> *Format:* 4 modules · Self-paced
> **₹2,999** — **[Enrol now →](/courses/neuroplasticity-in-practice/)**

**Card 3 — Deep Focus — Reclaiming Attention in the Age of AI** *(live now — NEW card)*
> **Deep Focus**
> Reclaim your attention in an age of infinite distraction — the science of training focus.
> *Who it's for:* Anyone who feels scattered and wants durable, trainable concentration.
> *Format:* 4 modules · Self-paced · Intermediate
> **₹2,999** — **[Enrol now →](/courses/deep-focus-reclaiming-attention/)**

**Card 4 — Brain Health 101** *(genuinely coming soon — keep honest placeholder)*
> **Brain Health 101**
> Sleep, movement, nutrition, stress and connection — the five levers that decide how well your brain works.
> *Who it's for:* Anyone who wants durable focus, memory, and cognitive longevity.
> *Format:* 4 modules · Self-paced
> **Coming soon** — *[Get notified via the free guide ↓](/#lead-magnet)*

> Note on Card 4 copy: align it to the **five levers (sleep, movement, nutrition, stress,
> connection)** — the current live card says "four pillars," which no longer matches the
> Course 3 outline. Fixed above.

### Fix steps (wp-admin — depends on how the section is built)
The section is a **fixed 3-column manual card layout with per-card images** (per Sprint 54
notes), so adding a 4th card needs a small layout change, not just text edits.
1. **Edit page 22 (Home)** in the block/page builder.
2. For **Cards 1–3**: replace "COMING SOON" / "Coming Soon" with the price + **Enrol** button
   linking to each course page (URLs above). Remove the "COMING SOON" ribbon/overlay on these.
3. **Add a 4th card** for **Deep Focus** (duplicate an existing card block, swap copy + link +
   thumbnail). If a clean 4-across layout is awkward, a 2×2 grid also works.
4. Update Card 4 (Brain Health) copy to the five-lever wording; keep its "Coming soon" state.
5. Add a course thumbnail for Deep Focus (course 209 currently has a placeholder image —
   Manish can supply one, else reuse the brand tile).

### QA after fixing
- Load `/` logged out: Cards 1–3 show ₹2,999 + a working **Enrol** link to the right course;
  Card 4 (Brain Health) still honestly says "Coming soon."
- No card links 404. Deep Focus now appears on the homepage.
- Mobile viewport: cards stack cleanly.

---

## Summary for the Master Backlog
1. **[CRITICAL] Course 1 (course 30) is FREE — revenue leak.** Restore/republish/re-link
   WooCommerce product 120 and set course 30 to Paid ₹2,999. Needs wp-admin.
2. **[HIGH] Homepage cards all say "COMING SOON"** including the live Neuroplasticity course;
   no purchase path from the homepage; Deep Focus missing. Replace card copy/CTAs + add a
   4th card (copy staged above). Needs wp-admin.

Both are blocked only by durable wp-admin access — the same single unblock that gates
everything else in the backlog.
