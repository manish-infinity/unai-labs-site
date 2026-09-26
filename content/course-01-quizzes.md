# Course 1 "AI Literacy for Everyone" — Assessment / Quiz Bank

Drafted Sprint 57 (2026-09-26) for the live Course 1 "AI Literacy for Everyone" (WordPress course ID **30**, slug `ai-literacy-for-everyday-people`). The course's 15 lessons are content-complete and published (lesson IDs 35–49), but the course has **no assessment layer yet**. This file is the publish-ready source; a future **authenticated** sprint adds one Tutor quiz at the end of each module topic and enters the questions below. WordPress is the source of truth once the quizzes are created.

> **Status:** NOT yet live. The wp-admin session was expired at ORIENT this sprint (`/wp-json/wp/v2/users/me` = HTTP 401), so quizzes could not be created via the Tutor admin. Per standing safety rules, no credentials were entered and no Log In was clicked.

## What this is and why it exists
Retrieval practice — being tested on what you just read — is one of the most robustly supported findings in the learning science this brand teaches. A short knowledge check at the end of each module is therefore not busywork; it *is* part of the method. Each quiz below is built to make the learner retrieve the module's load-bearing ideas, and every question's feedback reinforces the course's science-honest framing: AI is genuinely useful **and** genuinely fallible, so you generate with it and judge for yourself.

## How to build these in Tutor LMS (free tier) — turnkey instructions for the authenticated sprint
1. In the Course 1 course builder, at the **end of each of the four module topics**, add a **Quiz** named exactly as given below (e.g. "Module 1 Knowledge Check").
2. For each question: set **Question Type = Multiple Choice** (or **True/False** where marked), paste the question text, add the options in order, and tick the correct option. Tutor's free tier supports Multiple Choice, True/False, and Single/Multiple answer — all question types used here are free-tier compatible.
3. Paste the **Explanation** into the question's *Answer Explanation / feedback* field so it shows after the learner answers.
4. Recommended quiz **settings** (apply to all four): Passing grade **80%** (4 of 5), **Feedback Mode = Retry** (lets learners re-attempt — retrieval practice, not gatekeeping), **Questions order = as listed**, time limit **off** (self-paced learning check, not an exam). Show the answer explanation after each attempt.
5. The **Module 4 capstone rubric** at the bottom is not a Tutor quiz — add it as the final **Lesson** or an **Assignment** so learners can self-score their one-page personal AI strategy.

Question counts: 5 per module = **20 auto-graded questions total**, plus the capstone self-evaluation rubric. The single correct answer for each question is marked **[CORRECT]**.

Module → lesson map (live): Module 1 "What Is AI, Really?" (lessons 35–38); Module 2 "Using AI Tools With Skill" (39–42); Module 3 "Thinking Critically in an AI World" (43–46); Module 4 "Building Your Personal AI Strategy" (47–49).

---

## Module 1 Knowledge Check — What Is AI, Really?
*Covers Lessons 1.1–1.4. Passing grade 80% (4/5).*

### Q1. (Multiple choice) What is the most accurate mental model of a large language model like ChatGPT?
- A. A conscious digital mind that understands and has its own goals
- B. A search engine that looks up and returns stored facts
- C. A fluent pattern-completer that predicts the next piece of text — it generates, and you judge **[CORRECT]**
- D. A calculator that reasons through problems with logical certainty

*Explanation:* The course's opening reframe replaces the science-fiction model (an AGI with goals) with the accurate one: an LLM produces fluent text by predicting likely continuations. It doesn't "look things up" (B) or reason with guaranteed logic (D). The working rule for the whole course is **AI generates, you judge.**

### Q2. (Multiple choice) Where do a large language model's capabilities actually come from?
- A. A giant database of verified answers it retrieves from
- B. Next-token prediction — learning to predict the next piece of text from massive amounts of examples **[CORRECT]**
- C. Hand-written rules an engineer wrote for each task
- D. A live fact-checking connection to the internet on every sentence

*Explanation:* Every capability emerges from next-token prediction learned from data. That's why **fluency is guaranteed by the mechanism but accuracy is not** — the model is optimised to sound right, not to be right. (Option C describes the old rules-based era covered in Lesson 1.3.)

### Q3. (True/False) Because an LLM writes fluently and confidently, its answers are generally accurate.
- A. True
- B. False **[CORRECT]**

*Explanation:* Fluency and accuracy are separate things. The mechanism guarantees fluent, plausible-sounding text; it does not guarantee the content is true. Confidence is a property of the *writing style*, not a signal of correctness — the core reason the verification habits in Module 3 exist.

### Q4. (Multiple choice) What was the decisive historical shift that produced today's AI?
- A. Computers simply got faster
- B. A move from hand-coded rules to systems that learn patterns from data **[CORRECT]**
- C. A move from text to images
- D. A move from open-source to closed models

*Explanation:* Lesson 1.3's throughline: early AI tried to encode human rules by hand and stalled; the breakthrough was letting systems learn from large amounts of data. Understanding this shift is what supports **calibrated skepticism** — knowing what the tool is and isn't.

### Q5. (Multiple choice) Which statement best describes a genuine, structural limitation of today's AI?
- A. It can never produce anything useful
- B. It has no true understanding and no built-in access to ground truth, so it can state false things fluently — every failure mode is plausibility beating truth **[CORRECT]**
- C. It is wrong about essentially everything
- D. It refuses to answer unless it is 100% certain

*Explanation:* Lesson 1.4 frames every failure as **plausibility beating truth**. The honest stance isn't "AI is useless" (A, C) — it's genuinely capable — but its limits are structural, and each one has a matching habit that Module 3 systematises. Option D is false: models will happily give confident answers even when uncertain.

---

## Module 2 Knowledge Check — Using AI Tools With Skill
*Covers Lessons 2.1–2.4. Passing grade 80% (4/5).*

### Q1. (Multiple choice) What are the four parts of a strong prompt taught in "the art of the prompt"?
- A. Greeting, apology, question, and thanks
- B. Context, task, constraints, and examples **[CORRECT]**
- C. Length, font, tone, and deadline
- D. Keywords stuffed in to please the algorithm

*Explanation:* A strong prompt works like a good briefing: give **context**, state the **task**, set **constraints**, and show **examples**. And treat the first answer as a draft — iteration is part of the method, not a sign you prompted wrong.

### Q2. (Multiple choice) The course frames AI writing as two phases — "AI drafts, you decide." What does the "you decide" phase protect?
- A. Nothing — the AI's first draft is usually final
- B. Your judgement, your voice, and the sincerity of anything that must genuinely be yours **[CORRECT]**
- C. The speed of sending as many messages as possible
- D. The AI's preferences over your own

*Explanation:* Generation is the AI's job; judgement is yours. You preserve **voice** (feed your own samples; keep your first and last sentences), respect the **sincerity boundary** (a heartfelt message can't be delegated), and run a **privacy scan** before pasting anyone's data. AI drafts; you decide what actually goes out.

### Q3. (Multiple choice) When using AI to learn a topic, which habit keeps you actually learning instead of just feeling like you learned?
- A. Reading its explanation once and moving on
- B. Effortful retrieval — explaining the idea back and chasing your confusion — plus a two-source rule for facts **[CORRECT]**
- C. Trusting specific-sounding numbers because detail signals accuracy
- D. Asking it to make the topic feel easy so you finish faster

*Explanation:* Lesson 2.3 warns about the **fluency illusion**: a smooth explanation feels like understanding but isn't. Real learning needs effortful retrieval (explain it back), and facts need verification (two-source rule; be suspicious of oddly specific claims; remember knowledge cutoffs). This ties directly to the neuroplasticity science the brand teaches.

### Q4. (True/False) For brainstorming, the best first move is to ask AI for its single best idea.
- A. True
- B. False **[CORRECT]**

*Explanation:* Lesson 2.4 uses **divergent-then-convergent** thinking: get quantity first, add constraints, shift vantage points, attack your favourites, combine at random. Jumping to one "best" idea triggers the **anchoring trap**. AI is a sparring partner for generating options; your **taste** is what selects among them.

### Q5. (Multiple choice) Across all of Module 2, what single line separates skillful AI use from lazy AI use?
- A. Use the most expensive tool available
- B. Deliberate use — AI as a tool you direct and check — versus reflexive use, where you hand over the thinking itself **[CORRECT]**
- C. Always accept the first output to save time
- D. Never give the AI any context about your task

*Explanation:* Every lesson returns to the same boundary: used **on purpose** — briefed, iterated, verified, voice preserved — AI multiplies what you can do; reached for **reflexively**, it quietly erodes the skills and judgement that are yours to keep.

---

## Module 3 Knowledge Check — Thinking Critically in an AI World
*Covers Lessons 3.1–3.4. Passing grade 80% (4/5).*

### Q1. (Multiple choice) Why do AI "hallucinations" happen?
- A. The AI is lying on purpose
- B. They're structural — the model completes patterns with no built-in lookup step, so a false-but-plausible answer is generated the same way a true one is **[CORRECT]**
- C. They only happen on cheap or free models
- D. They happen only when you ask rude questions

*Explanation:* Lesson 3.1's "**fluent is not true.**" Hallucinations aren't dishonesty (A) or a defect of certain models (C) — they're intrinsic to pattern completion with no verification step. Errors cluster in predictable places: numbers, citations, niche topics, post-cutoff events, and arithmetic.

### Q2. (Multiple choice) What is the "one-question rule" for deciding whether to offload a task to AI?
- A. "Is it faster with AI?"
- B. "Do I want to still have this ability in a year?" — offload the output, not the understanding **[CORRECT]**
- C. "Will anyone find out I used AI?"
- D. "Is the AI confident about it?"

*Explanation:* Lesson 3.2, "your brain on AI," draws on **cognitive offloading** (the Google effect; GPS eroding spatial memory) and **desirable difficulty** from neuroplasticity — effort is the stimulus for learning. Offload drudgery, but keep the thinking that builds a skill you want to own: AI as coach (retrieval + feedback), not crutch.

### Q3. (Multiple choice) What is the most practical feature of the "verification mindset"?
- A. Verify every single sentence equally, always
- B. Stakes-based checking — match the effort of checking to what it costs to be wrong, and always check the load-bearing fact **[CORRECT]**
- C. Assume the AI is right unless it feels wrong
- D. Trust any answer that includes citations

*Explanation:* Lesson 3.3 scales checking to stakes (low/medium/high) rather than exhausting you on everything. The toolkit: two-source rule, **open the citations** (they can be fabricated), ask it to argue against itself, re-ask fresh, and always verify the one fact the whole decision rests on — **before you paste.** (Citations alone aren't proof — option D — because they can be invented.)

### Q4. (True/False) It's fine to paste a colleague's private information into a public AI tool as long as it helps you get a better answer.
- A. True
- B. False **[CORRECT]**

*Explanation:* Lesson 3.4's everyday ethics starts with **privacy**: never paste others' data, anonymise, and follow workplace rules. The broader code covers **honesty** (disclosure norms; sincerity can't be delegated), **fairness** (bias-check outputs used on people; respect creators), and guarding against **dependence.**

### Q5. (Multiple choice) What is the healthiest overall stance toward AI that Module 3 builds?
- A. Blind trust — it's usually right
- B. Blanket rejection — never use it
- C. Calibrated skepticism — genuinely useful, genuinely fallible, so you keep judgement and verification on your side **[CORRECT]**
- D. Whatever feels easiest in the moment

*Explanation:* The module's whole arc is neither hype nor doom. AI is a powerful tool with structural failure modes, so the skill is **calibrated skepticism**: use it widely, verify what matters, protect the thinking you want to keep, and use it ethically.

---

## Module 4 Knowledge Check — Building Your Personal AI Strategy
*Covers Lessons 4.1–4.3. Passing grade 80% (4/5).*

### Q1. (Multiple choice) Lesson 4.1 says to build your AI system starting from:
- A. The newest, most hyped tools, then find uses for them
- B. Your real week — the actual tasks you do — run through a filter of value, verifiability, stakes, and skill-to-keep **[CORRECT]**
- C. Whatever your friends are using
- D. As many tools as possible, to cover all bases

*Explanation:* Start from your life, not from tools. Run each task through the **four-question filter**, sort into **Delegate / Assist / Keep-human**, and define your **red-zone** tasks in advance (others' private data, high-stakes decisions, messages that must genuinely be yours).

### Q2. (Multiple choice) What toolkit does Lesson 4.2 actually recommend?
- A. One tool for absolutely everything
- B. As many specialist tools as you can subscribe to
- C. A few tools well used — typically a general assistant, a research tool, a writing aid, and one specialist — chosen for fit, trust, cost, and data practices **[CORRECT]**
- D. Only free tools, regardless of fit

*Explanation:* "**Few tools, well used.**" Most people need only a handful of categories, turned into reusable prompt templates for repeated tasks and wrapped in a **draft → verify → decide** workflow. Prune the kit periodically so it stays lean.

### Q3. (True/False) Once you've built a good AI workflow, "staying sharp" means letting AI do the load-bearing thinking so your mind can rest.
- A. True
- B. False **[CORRECT]**

*Explanation:* Lesson 4.3 (the capstone) says the opposite: offload the drudgery but keep **desirable difficulty**. Its three principles — attempt before you outsource, verify actively (not passively), and keep core skills warm — are how you use AI heavily without letting the underlying abilities atrophy.

### Q4. (Multiple choice) The course closes on which compressed summary of the whole AI-literacy stance?
- A. "Trust everything."
- B. "Think first, verify always." **[CORRECT]**
- C. "Automate all of it."
- D. "Avoid it entirely."

*Explanation:* The final lesson lands the course on "**think first, verify always**" — a compression of everything before it: do your own thinking first, use AI deliberately, and always check what matters.

### Q5. (Multiple choice) Which pattern is a warning sign that you may be over-relying on AI?
- A. You use AI to draft, then edit heavily
- B. You reach for it reflexively, accept outputs without checking, and notice a skill you used to have getting rusty **[CORRECT]**
- C. You keep a short list of tools you trust
- D. You verify high-stakes facts before acting

*Explanation:* Over-reliance shows up as **reflexive reaching, passive acceptance, and skill atrophy** — the exact failure the "staying sharp" lesson guards against. The other options are the *healthy* signs: deliberate use with judgement and verification intact.

---

## Module 4 Capstone — Self-Evaluation Rubric (not a Tutor quiz)
*Add this as the final lesson/assignment description. The capstone asks the learner to write a **one-page personal AI strategy**. There's no single right answer, so learners self-score against the rubric below. A strong strategy hits all six.*

Score each item Yes / Partly / Not yet. Aim for six "Yes."

1. **Task map (Module 4.1):** Have you sorted your real recurring tasks into **Delegate / Assist / Keep-human** using the four-question filter (value, verifiability, stakes, skill-to-keep)?
2. **Red-zone rules (Modules 3.4 & 4.1):** Have you named, in advance, the tasks you will **not** hand to AI — others' private data, high-stakes decisions, and messages that must be genuinely yours?
3. **A lean toolkit (Module 4.2):** Does your page list a **small** set of tools chosen for fit, trust, cost, and data practices — not a pile of apps?
4. **A default workflow (Modules 2 & 4.2):** Is "**draft → verify → decide**" written down as your standard way of working, with voice-preservation for anything that goes out in your name?
5. **A verification habit (Module 3.3):** Have you set a **stakes-based** checking rule and a "before I paste / before I act" trigger for the load-bearing fact?
6. **A staying-sharp rule (Module 4.3):** Does your plan protect at least one skill you'll keep doing manually — attempt before outsourcing, keep core skills warm?

*If you scored "Not yet" on any item, revisit that module's lesson — the fix is there. Your one-page strategy is what you leave the course with; keep it where you'll see it.*

---

*File ends. 4 module quizzes (20 auto-graded questions) + 1 capstone self-evaluation rubric. All question types are Tutor LMS free-tier compatible (Multiple Choice, True/False). Science-honest framing preserved throughout; every quiz maps directly to its module's lessons and key takeaways. Course ID 30; lesson IDs 35–49.*
