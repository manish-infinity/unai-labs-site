# Feature Concept: Homepage Self-Assessment → Personalized Learning Path

**Status:** Idea / roadmap — added Sprint 56 (2026-09-25) from Manish's suggestion. Not yet built.
**Working name:** "The 2-Minute Brain + AI Self-Check" (on-brand: science-backed, curious, non-hype — NOT "quiz" in a gimmicky sense, and NOT a medical test).

---

## The idea in one line
An interactive, lightbox-style self-assessment on the homepage that hooks visitors with relatable, slightly funny questions about how AI and modern life are affecting their brain — then gives a personalized result that validates the pain, reframes it with science, and routes them to the right course(s). As the catalog grows, it becomes the site's **learning-path engine**: "here's your starting point, what's must-learn vs. good-to-have, and the areas most important for your brain health."

## Why it's worth building (conversion + strategy)
- **Hook / pattern interrupt.** Relatable, self-deprecating questions ("Have you ever asked an AI something, then realised you'd forgotten the answer by the time you finished pasting it?") create instant "that's me" recognition → far higher engagement than a static hero.
- **Micro-commitment.** Answering a few easy questions is a foot-in-the-door that lifts downstream conversion (people who start finishing tend to act on the result).
- **Personalization beats a course grid.** A tailored recommendation ("start here, because…") converts better than asking a cold visitor to self-navigate four (eventually many) courses.
- **Lead capture.** The extended plan/result can be emailed → grows the list, reusing the existing "Brain + AI Starter Guide" email plumbing.
- **Merchandising / anti-choice-paralysis.** With many courses, this is the primary "where do I start?" tool and a natural upsell/sequencing engine (path, not a single course).
- **Demand signal.** Aggregate answers show which pains are most common → tells Manish which course to build next.

## Phasing

### Phase 1 — MVP (buildable now, with the 4 live courses)
- 6–8 questions in a lightbox/modal, launched from a hero button ("Take the 2-minute check →") and/or a gentle scroll/exit-intent trigger (not aggressive).
- Simple scoring → pick the visitor's **primary focus area**, mapped to one of the 4 live courses, plus a secondary suggestion.
- Result screen: validate → reframe (science-honest) → recommend a starting course with a one-line "why" → Enrol CTA + optional email capture for the "full plan."

### Phase 2 — Learning-path engine (when the catalog is larger)
- Weighted, multi-dimensional scoring across pillars → a ranked **path**: *Start here → Next → Later*, each labeled **must-learn** vs **good-to-have**.
- A "your focus areas" visual (simple bar/radar of pillar scores) — "these are the levers most important for *your* brain health right now."
- Data-driven mapping: recommend from **course metadata (pillar + level tags)** rather than hardcoded logic, so new courses slot in automatically (see "Do this now to make Phase 2 easy" below).
- Progress-aware for logged-in students: recommendations update as courses are completed; optional 90-day re-check.

## Sample questions (pillars + relatable framing)
Each answer adds points to one or more **pillar buckets**: AI Literacy · Attention/Focus · Sleep · Memory/Learning · Stress/Mood/Connection.

- **AI literacy/dependence:** "Ever asked an AI a question, then realised you'd forgotten the answer by the time you finished copying it?" · "When AI gives you an answer, do you usually understand *why* — or just take it?" · "How often do you reach for AI before trying to think it through yourself?"
- **Attention/focus:** "Can you read one page without checking your phone?" · "By the end of the day, is your brain 'used up' even if you didn't do anything hard?"
- **Sleep:** "How many nights a week do you get 7+ hours?" · "Do you wake up feeling like your brain actually rebooted?"
- **Memory/learning:** "When you learn something new, does it stick — or leak out by next week?"
- **Stress/mood/connection:** "How often does stress make it hard to focus or remember?"

## Pillar → course mapping (Phase 1)
- AI Literacy → **Course 1: AI Literacy for Everyone**
- Attention/Focus → **Course 4: Deep Focus**
- Memory/Learning/neuroplasticity → **Course 2: Neuroplasticity in Practice**
- Sleep / Stress / Nutrition / Connection → **Course 3: Brain Health 101**

## Result copy pattern (validate → reframe → route)
> "Sound familiar? You're not broken, and your brain isn't 'getting worse' — these are normal responses to an overloaded, AI-saturated world. Based on your answers, the highest-leverage place to start is **[Course]** — here's why…"

Then: primary course card + "why this first," a secondary suggestion, and a soft email capture for the full path ("Want the full plan + a weekly nudge? Drop your email").

## Guardrails (brand + wellbeing — important)
- **Not a diagnosis.** Explicit disclaimer; never imply cognitive decline, disease, ADHD, dementia, etc. No fear-mongering or shaming — the tone is warm, curious, science-honest.
- **No overpromising.** Frame as education + habits, not "fix your brain in 30 days."
- **Show the basic result even without email.** Gate only the *extended* plan behind email — better trust and UX than a hard wall.
- **Accessible + skippable.** Keyboard-navigable modal, easy close, no dark patterns, gentle triggers only.
- **Privacy.** Store answers only with consent; disclose any aggregate analytics; no sensitive-category profiling.

## Technical approach (Astra + Gutenberg/Spectra + WooCommerce + Tutor)
- **Build:** a lightweight, self-contained JS widget (vanilla or a tiny React bundle) embedded via a Spectra/Custom-HTML block or a shortcode, opened as a modal. Client-side scoring + routing (no backend needed for Phase 1). Alternatively evaluate an existing quiz/funnel plugin, but a custom widget keeps it dependency-light and fully on-brand.
- **Lead capture:** POST the email into the existing WPForms / lead-magnet flow (reuse current email delivery).
- **Analytics:** fire events (start, complete, segment, CTA click) into whatever analytics is configured; persist aggregate answer distributions for demand signals.
- **Phase 2 engine:** drive recommendations from course metadata. → **Do this now to make Phase 2 easy:** start tagging every course with its **pillar(s)** and **level** (a simple taxonomy) as courses are built, so the path engine can map answers → pillars → courses dynamically instead of via hardcoded rules.

## Success metrics
Quiz start rate · completion rate · email-capture rate · quiz→course-page CTR · quiz→enrollment conversion · most-common pain segments (which pillar dominates → what to build next).

## Open decisions for Manish
1. Confirm scope for a Phase-1 MVP (which ~6–8 questions, which trigger — hero button vs. scroll/exit-intent).
2. Custom lightweight widget vs. an off-the-shelf quiz/funnel plugin.
3. Email gate: basic result free + email for the extended plan (recommended) vs. email required.
4. OK to start tagging courses with pillar/level metadata now (small, unblocks the Phase-2 path engine).
