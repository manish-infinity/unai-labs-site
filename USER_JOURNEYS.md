# USER JOURNEYS — unai-labs.com

Created Sprint 12 (2026-07-05). This is the product map that gives backlog items their purpose: every task should trace to a journey step. Maintained every sprint during ORIENT/HANDOFF alongside SPRINT_LOG.md. Status legend: [OK] working end-to-end · [PARTIAL] works with gaps · [BROKEN] a real user hits a dead end today.

---

## J1. Discover & Trust — "Who are these people and should I listen to them?"
Visitor lands (search/social/direct) -> Homepage -> Blog post or About -> forms trust impression.

Positive path status: [PARTIAL]
- Homepage hero, course cards, science-first section: live and polished (Sprint 9). 8 quality blog posts across all pillars. About page redesigned (Sprint 10).
Negative scenarios / gaps:
- N1a. Visitor checks About for a real human -> finds no photo/bio -> credibility gap for a "science-backed" brand. -> BACKLOG: Manish photo + bio.
- N1b. Homepage claims "Join 1,000+ learners" -> site has no verified subscriber base of that size -> if untrue this contradicts the hype-free brand promise. NEW ITEM surfaced by this map: verify or soften this claim.
- N1c. No testimonials/social proof anywhere on the purchase path. NEW ITEM (needs real students first — depends on J4 working).
- N1d. Search results lack organization branding -> BACKLOG: Yoast org logo.

## J2. Free Value -> Email Capture — "Give me the guide"
Blog/homepage -> lead magnet CTA -> WPForms signup -> confirmation email with PDF -> ongoing nurture.

Positive path status: [BROKEN] at the email step.
- Form submits and captures the lead correctly; PDF exists in Media Library.
Negative scenarios:
- N2a. CRITICAL: Subscriber submits -> on-screen message says "check your inbox" -> NO EMAIL EVER ARRIVES (PHP mail() disabled; SMTP password not entered). User concludes the site is broken/scammy. -> BACKLOG: WP Mail SMTP password (single blocker).
- N2b. Lead is captured but never contacted again — no nurture sequence exists (MailPoet installed, unconfigured). NEW ITEM: post-signup email sequence (only worth building after N2a is fixed).

## J3. Evaluate the Course — "Is this worth $35 of my money and 14 hours of my life?"
Courses page -> course detail -> curriculum -> (ideally) sample a lesson -> decide.

Positive path status: [PARTIAL]
- /our-courses/ marketing page + course page with honest curriculum, accurate "15 lessons" (fixed Sprint 12), refund policy page live.
Negative scenarios:
- N3a. Visitor wants to sample quality before paying -> no free preview possible (Tutor LMS free tier gates "Lesson Preview"). -> BACKLOG: Tutor LMS Pro decision. Interim option: publish a lesson excerpt as a blog post (NEW ITEM, free workaround).
- N3b. Skeptic checks whether course content is real -> Modules 2-4 are outline-only. -> BACKLOG: content plan decision; sprints continuing to write (Module 1 done Sprint 12).

## J4. Purchase — "Take my money"
Add to cart -> login/register -> checkout -> pay -> confirmation.

Positive path status: [BROKEN] at payment.
- Cart/checkout wiring fixed (Sprint 11); Tutor->Woo pages correct.
Negative scenarios:
- N4a. CRITICAL: No active payment gateway -> literally no one can buy anything. -> BACKLOG: activate Stripe/PayPal/GoDaddy Payments (top revenue blocker).
- N4b. Buyer forced to create an account before adding to cart -> known conversion killer. -> BACKLOG: guest checkout decision.
- N4c. Even after paying, order confirmation email won't send (same SMTP root cause as N2a).
- N4d. Buyer abandons at checkout -> no recovery email possible (SMTP + no automation). Future item, depends on N2a.

## J5. Learn & Succeed — "I paid. Teach me."
Dashboard -> Module 1 -> ... -> Module 4 -> complete -> (review/refer).

Positive path status: [PARTIAL] — was [BROKEN] until Sprint 12.
- Module 1 (lessons 35-38) now has real content; course player renders correctly.
Negative scenarios:
- N5a. CRITICAL if J4 gets fixed first: paying student reaches Module 2 -> empty lessons (IDs 39-49) -> refund request + reputation damage. Sequencing rule: lesson content must be complete BEFORE payments go live, or course must be sold as early-access. -> Carry-forward: Modules 2-4 writing queue.
- N5b. Course copy promises video; only text exists. -> BACKLOG: video decision.
- N5c. No quizzes/certificates/completion moment — weak finish. NEW ITEM (post-content).
- N5d. Student forgets password -> reset email never arrives -> locked out of a paid product (SMTP again).

## J6. Get Help — "Something went wrong"
Contact page -> form -> response. Or: refund request per policy.

Positive path status: [BROKEN] silently.
- N6a. CRITICAL: visitor submits contact form -> admin notification never sends (SMTP) -> Manish never knows -> user is ignored. Worst-case version: a paying customer requesting a refund per the 30-day policy gets silence.

---

## Backlog-to-Journey map (why each item matters)
| Backlog item | Journeys blocked | Severity |
|---|---|---|
| WP Mail SMTP password | J2 (broken), J4c, J5d, J6 (broken) | CRITICAL — 1 action fixes 4 journeys |
| Payment gateway | J4 (broken) | CRITICAL — revenue = 0 until done |
| Lesson content Modules 2-4 | J5, J3b | HIGH — must precede real sales (see N5a) |
| Tutor LMS Pro decision | J3a | MEDIUM |
| Guest checkout decision | J4b | MEDIUM |
| Photo + bio | J1a | MEDIUM |
| Video plan decision | J5b | MEDIUM |
| Yoast org logo | J1d | LOW |
| GoDaddy trial keep/cancel | none (cost hygiene) | LOW |
| Post 83 Yoast minors | J1 (marginal SEO) | LOW |

New items surfaced by this mapping (added to Master Backlog / sprint queue as appropriate): verify-or-soften "1,000+ learners" claim (N1b); free lesson-excerpt blog post as preview workaround (N3a); post-signup nurture sequence (N2b, after SMTP); quizzes/completion experience (N5c, after content); launch-sequencing rule N5a.

Recommended unblock order for Manish: SMTP password -> payment gateway -> (meanwhile sprints finish Modules 2-4) -> guest-checkout + Pro decisions -> launch.
—
