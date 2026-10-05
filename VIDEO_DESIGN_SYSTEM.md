# UnAI Labs — Video Design System (v1)
**Purpose:** one authoritative spec so every course video looks consistent and is *always readable*. These rules are mandatory and must be stated explicitly in every video brief/compose prompt — the tool does not remember them between videos. Created Sprint 64 (2026-10-05) after the Course 1 pilot (project 6e5a3134) looked great but had dark-text-on-dark-background readability failures.

---

## 0. THE CONTRAST LAW (non-negotiable, rule #1)
The single most important rule, the one that broke the first attempts:

1. **Background is ALWAYS light.** Off-white `#F7FAFF` (or pure white `#FFFFFF` for cards). **Never a dark background. Never a dark gradient. Never a dark full-bleed panel.**
2. **All text is ALWAYS dark.** Navy ink `#12203A` (primary) or slate `#3F4B60` (secondary). **Never white, never pale, never light-coloured text — anywhere.** Not in titles, not in captions, not on the end card.
3. This holds **through every transition, fade, build, zoom and scene change.** At no frame may text sit on a same-or-similar-tone field. If a dark shape animates in, text never sits on top of it (text stays on the light surface).
4. Minimum contrast target: WCAG AA for large text and better (aim ~7:1). When in doubt, make text darker and bigger.
5. **No photographic / stock backgrounds — ever.** Every scene background is the flat off-white brand surface (`#F7FAFF`) or white cards. NEVER a full-frame photo, stock image, or stock video behind the content. Real things (an inbox, a laptop, a tool, a brain) are drawn as **simple flat illustrations or a clean mock-UI** in brand style on the light surface. A photo may appear ONLY as a small image inside a bordered card, never full-bleed and never behind text. (This is what made pilot Scene 3 look like a different video and garbled a caption.)
6. **Strike-throughs/marks must not cover the words they act on.** A red ✗ over "NOT ALIVE" is offset or sized so the words stay fully legible.
7. **Captions are clean whole phrases**, not auto-chunked fragments, and every element sits inside the title-safe margin (no clipped cards at the edges).

> If a design choice would ever put light text on a light area or dark text on a dark area, it is wrong — pick the light-background / dark-text pairing instead, every time.

---

## 1. Canvas & safe areas
- **Resolution:** 1920×1080, 16:9. (Vertical 1080×1920 variant only if we later do shorts.)
- **Title-safe margin:** keep all text and key elements within the inner ~88% (≈115px margin at 1080p). Nothing important touches the edges.
- **Caption zone:** lower-third or centre. Captions are large and never overlap a busy element.

## 2. Colour tokens (the only colours we use)
| Token | Hex | Use |
|---|---|---|
| Surface / background | `#F7FAFF` | Every scene background (light) |
| Card / panel | `#FFFFFF` | Raised cards, chat windows, chart panels |
| Panel fill / divider | `#E4EBF5` | Soft fills, lines, separators |
| Border | `#C9D6EA` | Card/panel borders |
| **Text — primary** | `#12203A` | Headlines, body, captions (navy ink) |
| **Text — secondary** | `#3F4B60` | Sub-text, labels (slate) |
| Accent — brand blue | `#2F6DF0` | Highlights, underlines, icon strokes, key words |
| Accent — deep blue | `#2159D6` | Emphasis / pressed state |
| Positive (correct/✓) | `#1FA971` | Checkmarks, "right", positive data |
| Warning (wrong/✗) | `#E5484D` | Crossed-out words, "wrong", risk |

Rules: **body text is only navy or slate.** Blue/green/red are for *shapes, icons, underlines, data* — not for paragraphs of text. All accent colours above are dark enough to read on the light surface.

## 3. Typography
- **Font:** clean geometric sans — Inter / Poppins / Montserrat (pick one, keep it for the whole course).
- **Weights:** Headlines 700; captions/body 600; sub-labels 500.
- **Minimum on-screen text size:** ≈ 48px at 1080p for captions; headlines larger. Big and bold beats small and elegant for video.
- **Alignment:** centre for hero lines; left for lists. Generous line spacing.

## 4. Fixed components (reused every video)
- **Intro bumper (≈1–1.5s, optional):** UnAI Labs wordmark, navy on light, blue accent mark.
- **Caption pill:** large navy text on a white rounded pill with a soft shadow, bottom-centre, inside safe area.
- **Key-term highlight:** a blue marker/underline swipe under the important word (text stays navy).
- **Icons:** flat line icons, navy or blue stroke on light. One visual weight throughout.
- **Section chip (optional):** small navy text on a `#E4EBF5` pill, top-left (e.g., "Module 1 · Lesson 1").
- **End card:** light background, navy "UnAI Labs" wordmark, blue downward arrow, navy text "Full lesson below ↓".

## 5. Charts / graphs / infographics
- Light/white chart background **always**; navy axes and labels; data series in blue `#2F6DF0`, green `#1FA971`, red `#E5484D` (colourblind-safe order).
- Thick lines/bars, **direct labels on the data** (avoid tiny legends), one big takeaway number per chart.
- No dark chart backgrounds, no neon, no 3D, no clutter. A chart should read in 2 seconds.

## 6. Motion & animation
- Smooth ease-in-out; transitions ~0.3–0.5s. No hard flashes, no glitch except a brief, clearly-contrasted beat (e.g., the sci-fi hook).
- **Build one idea at a time** — elements appear in sequence, synced to narration; captions land with or just before the matching words.
- Transitions must never leave any text unreadable mid-motion (no text fading across a same-tone field; no dark wipe under dark text).
- Calm and credible pacing — not frantic. This is a learning brand, not an ad.

## 7. Voice (audio)
- **One locked narrator voice for the entire course** — warm, friendly, clear, mid-pace. Same voice in every video.
- Generate narration once and **lock it as a file**; never regenerate on edits (this is why the voice "disappeared" before).
- Pace ~130–150 words/min. Brief pause after the opening hook. Clear diction; "A.I." read as "ay-eye".
- Optional soft, low background music bed (−24 LUFS under VO); never competes with the voice.

## 8. Structure & length
- **6-beat template:** Hook → Reframe → What it is → Demystify → Promise → CTA.
- Length ~45–70s per chapter (conceptual chapters shorter; hands-on chapters can run longer).
- Every video ends on the standard end card pointing to the written lesson.

## 9. Per-video QA checklist (must pass before publishing)
- [ ] Background light in **every** frame; **no** dark backgrounds.
- [ ] **No** white/light text anywhere; all text dark (navy/slate) and high-contrast.
- [ ] All captions inside the title-safe area and ≥ ~48px.
- [ ] **One** consistent narrator voice start to finish; no voice drop-outs.
- [ ] Narration matches the approved script word-for-word.
- [ ] Only design-system colours used.
- [ ] Transitions smooth; no unreadable mid-transition text.
- [ ] Standard end card present with "Full lesson below ↓".

---

## 10. COMPOSE DIRECTIVE BLOCK — paste this into EVERY video brief
> **DESIGN SYSTEM (MANDATORY — do not deviate):** Faceless animated explainer, 16:9 1080p, motion-graphics only (no human/avatar/webcam). **LIGHT background in every single scene — off-white `#F7FAFF` or white cards. NEVER use a dark background.** **NEVER use a full-frame photograph, stock image, or stock video as a background — depict everything (inboxes, laptops, tools, brains) as flat illustrations or a clean mock-UI on the off-white surface.** **ALL text must be DARK — navy `#12203A` or slate `#3F4B60` — and must NEVER be white or light-coloured, in any scene, title, caption, chart, or end card, including during every transition.** Accent colours blue `#2F6DF0`, green `#1FA971`, red `#E5484D` are for icons/shapes/underlines/data only, never for body text; a strike-through mark must not cover the word it crosses. Font: one clean geometric sans (Inter/Poppins/Montserrat), bold, captions ≥48px, as clean whole phrases, all elements inside the title-safe margin (nothing clipped at edges). **One single consistent warm, clear narrator voice for the whole video — never change voices, keep narration continuous and synced to captions.** Smooth ease-in-out transitions; build one idea at a time; no text ever sits on a same-tone field. Brand: UnAI Labs — warm, credible, science-backed, calm, not hypey. Standard end card: UnAI Labs wordmark + blue down-arrow + "Full lesson below ↓" (dark text on light).

---

## 11. Changelog / lessons from iteration
- **v1.1 (Sprint 64, pilot iteration 2):** Banned full-frame photo/stock backgrounds (pilot Scene 3 used a laptop stock photo — looked off-brand and garbled a caption). Added: strike-through marks must not cover the struck words; captions must be clean whole phrases; nothing clipped outside the title-safe margin.
- **v1 (Sprint 64):** Initial system. Core fix = the Contrast Law (light background + dark text always), after the first pilot had dark-text-on-dark-background readability failures.

_Use §10 verbatim at the top of every compose prompt, then add the chapter's 6-scene storyboard._
