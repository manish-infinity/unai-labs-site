# UnAI Labs — Backlog (all workstreams)

_Snapshot: 2026-09-26 (Sprint 57). QA is embedded in every workstream (each item is verified live before it's called done). A dedicated **Hardening sprint** — security, observability, performance — is intentionally deferred until the social + digital-marketing push begins driving real traffic, leads, and sales data. Razorpay and other input-dependent decisions are parked for go-live._

## Legend
**Status:** ✅ Done · 🔨 In progress · ⏳ Ready to build (unblocked) · 🅼 Blocked on Manish · 🕓 Deferred (by plan)

---

## 1. Content & Courses
| Item | Status | QA / verification | Blocked on |
|---|---|---|---|
| Courses 1–4 built & published (30, 124, 209, 232), all Paid ₹2,999 | ✅ | Live course pages render price + Add to cart; curricula verified | — |
| 31 blog posts published (pillars balanced) | ✅ | Post IDs + categories verified via REST | — |
| **Course 1 video scripts (15 × 1-min, per chapter)** | ✅ (this sprint) | Peer-read for plain language; mapped to lesson IDs 35–49 | — |
| Course 2 / 3 / 4 video scripts (per chapter) | ⏳ | Same plain-language standard | — |
| **Record & upload chapter videos** (all courses) | 🅼 | Each video ≤75s, captioned, mobile-readable; attach via Tutor "Intro Video" | Manish records on camera |
| Course featured images / thumbnails (209, 232 are placeholders) | ⏳ | Real image set per course; card + course page check | (Claude can generate, or Manish supplies) |
| Quiz banks staged for Courses 3 & 4 (20 Q each) | ✅ (staged) | Byte-verified in GitHub | — |
| Blog post 31+ (ongoing pipeline) | ⏳ | House template + links-to-published check | — |

## 2. Sales & Conversion
| Item | Status | QA / verification | Blocked on |
|---|---|---|---|
| Homepage: 4 live course cards w/ price + Enrol CTAs | ✅ | Per-card price + href verified; mobile stack confirmed | — |
| Homepage self-check widget (2-min Brain+AI Self-Check) | ✅ | All 4 routing paths + all-clear verified live; close/Esc/overlay; theme-proof CSS; no console errors | — |
| Self-check Phase 2 (email capture → lead magnet, answer analytics, pillar-tag → ranked learning path) | ⏳/🅼 | Capture→email delivery test; analytics events fire | Manish: approve email-gate + pillar tagging |
| Lead magnet email end-to-end test ("Brain + AI Starter Guide") | 🅼 | Submit form once; confirm subscriber + admin copies arrive | Manish submits once |
| **Activate payment gateway (Razorpay)** — no real purchase possible until done | 🅼 (go-live) | Place one live test order after keys entered | Manish: Razorpay Key ID + Secret |
| Guest checkout vs. login-gate decision | 🅼 (go-live) | Checkout flow test after decision | Manish decides |

## 3. Feature / Platform Dev
| Item | Status | QA / verification | Blocked on |
|---|---|---|---|
| **Build quiz banks in Tutor (Course 3, then 4; opt 1 & 2)** | 🔨 (this sprint) | Each quiz renders; sample attempt scores correctly | — (wp-admin live) |
| Catalog-visibility consistency (products 226/249 vs 157) | ⏳ | Store API / shop archive check | — (cosmetic) |
| Trash legacy Tutor pages 121/122 | ⏳ | Confirm unreferenced, then trash (reversible) | — |
| Cache config review (homepage served with ~31-day browser cache) | ⏳ | Verify updates show without manual flush | (GoDaddy/CDN settings) |
| Tutor LMS Pro upgrade (free lesson previews, richer quizzes) | 🅼 (go-live) | — | Manish: buy decision |

## 4. QA (embedded everywhere) + acceptance
| Item | Status | QA / verification | Blocked on |
|---|---|---|---|
| Per-item live verification before "done" | ✅ (standing) | Rendered-DOM checks, routing tests, mobile spot-checks | — |
| Cross-browser / device spot-check of new UI | ⏳ | Chrome + Safari + one mobile per release | — |
| Content accuracy / science-honesty review | ✅ (standing) | Claims checked; no overpromising; disclaimers on health topics | — |

## 5. Infra / Hardening — 🕓 DEFERRED to the marketing-push phase
| Item | Status | Notes |
|---|---|---|
| Security review (auth, forms, plugin surface, spam/abuse) | 🕓 | Do before/at first real traffic |
| Observability (uptime, error logging, analytics, funnel tracking) | 🕓 | Needed once leads/sales data starts flowing |
| Performance (page speed, image optimization, caching strategy) | 🕓 | Tie to the cache-config item above |
| Backups + rollback rehearsal | 🕓 | Confirm GoDaddy backup cadence pre-launch |

## 6. Decisions parked for go-live (Manish input)
| Decision | Why parked |
|---|---|
| Razorpay keys + first live test order | Enables real revenue; do at launch |
| About page photo + bio | Personal content |
| Yoast SEO organization logo | Brand asset |
| Guest checkout vs. login-gate | Product/UX call |
| Tutor LMS Pro upgrade | Paid decision |
| GoDaddy Digital Marketing trial keep/cancel | Billing decision |
| Confirm course prices (default ₹2,999 held) | Pricing call |

---
_Next up (unblocked, no Manish input): Course 3/4 quiz banks in Tutor · Course 2–4 video scripts · course thumbnails · catalog-visibility + legacy-page cleanup. Hardening sprint stays parked until the marketing push._
