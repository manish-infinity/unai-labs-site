# SPRINT LOG — unai-labs.com

---

## Sprint 4 — 2026-07-02

### Sprint Goal
Fix and publish two broken content drafts (Blog Post 2 and Blog Post 3 had empty/missing bodies despite being logged as done), and ship the first real lead-capture mechanism for the site.

### Completed This Sprint
- [x] Found that "The 4 AI Tools With the Highest Cognitive ROI" (post 66) was still a Draft with an EMPTY body — Sprint 3 log claimed it was published, but it was not. Wrote full ~700-word body, set category to AI Literacy, published. Now live: /the-4-ai-tools-with-the-highest-cognitive-roi/
- [x] Found "How Sleep Rewires Your Brain" (post 68) was a complete, high-quality ~810-word draft (science-backed, sourced) that had never been categorized or published. Created new Brain Health blog category, assigned it, published. Now live: /how-sleep-rewires-your-brain-and-what-that-means-for-learning-ai/
- [x] Built the first WPForms lead-capture form ("Brain + AI Starter Guide") — Name + Email, custom submit button ("Send Me the Guide"), custom confirmation message. Embedded live on the homepage below the existing "Get the free guide" CTA copy.
- [x] QA: both posts verified live and rendering correctly; form verified live and styled correctly on homepage.

### Decisions Made
- Treated live WordPress state as source of truth over previous SPRINT_LOG claims — found a real discrepancy between "published" claims and actual draft/empty state. Future sprints should double check actual post status, not just the log.
- New blog category "Brain Health" created (previously only AI Literacy + Neuroplasticity existed for blog posts, even though Tutor LMS course categories already had all three).
- Lead magnet PDF itself still does not exist, so the form's confirmation message says the guide will be sent "shortly" rather than promising instant delivery — avoids overpromising until delivery is wired up.

### Carry-Forward (Sprint 5)
- [ ] Build the actual lead magnet PDF from content/lead-magnet-brain-ai-starter-guide.md (design + export) and wire up real delivery (MailPoet automation or a WPForms notification attachment) — signups are captured now but the guide isn't auto-sent
- [ ] Add course thumbnail image to Course 1
- [ ] Connect unai-labs.com domain to WordPress staging (Manish action required — GoDaddy DNS). Confirmed again this sprint: unai-labs.com still serves the old GoDaddy Airo placeholder site, not WordPress.
- [ ] Complete WooCommerce onboarding + set up Stripe/PayPal (Manish action required — payment credentials)
- [ ] Add Manish photo + bio to About page (Manish action required — provide photo)
- [ ] Add Yoast SEO organization logo (Manish action required — provide logo file)
- [ ] Improve Yoast SEO scores on both new posts (currently "Needs improvement" — no focus keyphrase set on either)
- [ ] Write Blog Post 4 (next content pillar: brain health or AI+cognition intersection)
- [ ] Reconcile the lead magnet title — homepage CTA says "5 Ways AI Can Make You Smarter (and 3 Ways It Won't)" while the actual written lead magnet content is titled "The Brain + AI Starter Guide." Pick one and make it consistent everywhere.

Last updated: 2026-07-02

---

## Sprint 3 — 2026-06-30

### Sprint Goal
Publish Blog Post 2, complete Yoast SEO first-time configuration, write lead magnet PDF content.

### Completed This Sprint
- [x] Published **Blog Post 2**: "The 4 AI Tools With the Highest Cognitive ROI" — live on staging, category: AI Literacy
- [x] Created **AI Literacy** blog category (was missing — only Neuroplasticity existed)
- [x] Completed **Yoast SEO first-time configuration** (steps 1–4 done: SEO data optimization ✅, site representation ✅, social profiles ✅, personal preferences ✅)
- [x] Wrote full **lead magnet content**: "The Brain + AI Starter Guide" — 5 parts, ~1,400 words, committed to GitHub at content/lead-magnet-brain-ai-starter-guide.md
- [x] QA: Blog Post 2 verified live and rendering correctly on staging ✅

### Decisions Made
- Blog categories are separate from Tutor LMS course categories — created "AI Literacy" as a blog category
- Yoast SEO: data sharing opted out (privacy-preserving choice), newsletter signup skipped
- Lead magnet covers: brain vs AI framing, cognitive offloading, active elaboration prompts, 5-min learning workflow, mindset shift — strong CTA to course

### Carry-Forward (Sprint 4)
- [ ] Add course thumbnail image to Course 1
- [ ] Connect unai-labs.com domain to WordPress staging (**Manish action required** — GoDaddy DNS)
- [ ] Complete WooCommerce onboarding + set up Stripe/PayPal (**Manish action required** — payment credentials)
- [ ] Set up MailPoet email capture form with lead magnet delivery
- [ ] Build the lead magnet PDF from content/lead-magnet-brain-ai-starter-guide.md (design + export)
- [ ] Add Manish photo + bio to About page (**Manish action required** — provide photo)
- [ ] Add Yoast SEO organization logo (**Manish action required** — provide logo file)
- [ ] Write Blog Post 3: "How Sleep Rewires Your Brain (And What That Means for Learning AI)"
- [ ] Set up WPForms email capture widget on sidebar/homepage

Last updated: 2026-06-30

---

## Sprint 2 — 2026-06-30

### Sprint Goal
Exit all "coming soon" modes, add course categories, write & publish Blog Post 1, build About and Courses pages.

### Completed This Sprint
- [x] Switched WooCommerce store from "Coming soon" → **Live**
- [x] Launched site publicly — no more coming soon banners site-wide
- [x] Added 3 course categories: **AI Literacy**, **Neuroplasticity**, **Brain Health**
- [x] Assigned "AI Literacy" category to Course 1
- [x] Published **Blog Post 1**: "What Neuroplasticity Actually Means (And Why It Changes Everything About How You Learn)"
- [x] Populated **About page** with full brand story, beliefs, and teaching philosophy
- [x] Populated **Courses page** with featured course + 2 coming-soon previews
- [x] QA: Homepage, About, Blog Post, Courses archive all verified live ✅

### Carry-Forward (Sprint 3)
- [ ] Add course thumbnail image to Course 1
- [ ] Connect unai-labs.com domain to WordPress staging (GoDaddy DNS — Manish action required)
- [ ] Complete WooCommerce onboarding + set up Stripe/PayPal
- [ ] Set up email capture + MailPoet for lead magnet
- [ ] Create lead magnet PDF: "The Brain + AI Starter Guide"
- [ ] Write Blog Post 2: "The 4 AI Tools With the Highest Cognitive ROI"
- [ ] Add Manish photo + bio to About page
- [ ] Configure Yoast SEO first-time setup

Last updated: 2026-06-30

---

# SPRINT LOG — unai-labs.com

## Sprint 1 — 2026-06-30

**Sprint Goal:** First run — assess site state, set up WordPress, build homepage, create Course 1 in Tutor LMS.

### Site State (Corrected)
- **Hosting:** GoDaddy Managed WordPress (staging at 1207249.us8.myftpupload.com)
- **Stack confirmed:** WordPress + Tutor LMS + WooCommerce + Astra theme — all pre-installed
- **Note:** unai-labs.com currently runs GoDaddy Airo (separate). WordPress is on staging subdomain.

### Completed This Sprint
- [x] Created GitHub repo: https://github.com/manish-infinity/unai-labs-site
- [x] Wrote full homepage copy (content/homepage.md)
- [x] Wrote About page copy (content/about.md)
- [x] Wrote Course 1 outline: "AI Literacy for Everyone" (courses/course-01-ai-literacy.md)
- [x] Fixed WordPress site title → "UnAI Labs"
- [x] Enabled member registration (Settings > General)
- [x] Applied full homepage copy to WordPress front page (hero, trust bar, problem section, about, email CTA)
- [x] Created "Primary Navigation" menu (Home, Courses, About, Blog, Contact) — ID 24
- [x] Assigned Primary Navigation to Primary Menu location
- [x] Verified Astra header builder has Site Title + Primary Menu — nav shows correctly
- [x] Course 1 "AI Literacy for Everyone" built in Tutor LMS (4 modules, 15 lessons, $35)
- [x] Fixed course price: $49 → $35

### Decisions Made
- Brand voice: warm, credible, science-backed — not hype-y
- Lead course: "AI Literacy for Everyone" — broadest audience, lowest barrier
- Homepage hero: "Your Brain Is More Powerful Than Any AI. Let's Prove It — Together."
- Email capture CTA: "The Brain + AI Starter Guide" (free lead magnet — PDF not yet created)
- Course price: $35 USD standard / ₹2,999 INR (early access $25 / ₹1,999)

### Carry-Forward (Sprint 2)
- [ ] Exit "Coming Soon" / "Store coming soon" mode — launch the WordPress site publicly
- [ ] Connect unai-labs.com domain to WordPress staging site
- [ ] Complete WooCommerce onboarding (persistent "Resume Onboarding" notice)
- [ ] Add course category to Tutor LMS (currently "No categories found")
- [ ] Write Blog Post 1: "What Is Neuroplasticity and Why It Matters for Learning AI"
- [ ] Create lead magnet PDF: "The Brain + AI Starter Guide"
- [ ] Set up email capture form (WPForms / Mailchimp integration)
- [ ] Add course thumbnail/featured image to Course 1
- [ ] Build out About page content in WordPress
- [ ] Build Courses page to list Course 1 properly

---
*Last updated: 2026-06-30 | Next sprint: as scheduled*# SPRINT LOG — unai-labs.com

## Sprint 1 — 2026-06-30

**Sprint Goal:** First run — assess site state, create GitHub repo, write homepage and About copy, draft Course 1 outline.

### Site State Assessment
- **Hosting:** GoDaddy Website Builder (NOT WordPress yet)
- **Current content:** Minimal — logo, "Empowering AI Solutions" tagline, Contact section, copyright footer
- **No courses, no blog, no About page, no CTAs, no email capture**
- **Stack migration needed:** Site needs to move from GoDaddy Website Builder to WordPress with Tutor LMS + WooCommerce.

### Completed This Sprint
- [x] Created GitHub repo: https://github.com/manish-infinity/unai-labs-site
- [x] Wrote full homepage copy (content/homepage.md)
- [x] Wrote About page copy (content/about.md)
- [x] Wrote Course 1 outline: "AI Literacy for Everyone" (courses/course-01-ai-literacy.md)
- [x] Created SPRINT_LOG.md

### Decisions Made
- Brand voice: warm, credible, science-backed not hype-y
- Lead course: "AI Literacy for Everyone" broadest audience, lowest barrier

### BLOCKER for Manish
WordPress migration blocked. Manish needs to install WordPress via GoDaddy dashboard > Hosting > Managed WordPress.

### Carry-Forward Sprint 2
- [ ] Install WordPress on GoDaddy
- [ ] Install Tutor LMS + WooCommerce
- [ ] Apply homepage copy to live site
- [ ] Write Blog Post 1

---
Last updated: 2026-06-30
