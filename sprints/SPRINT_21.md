# Sprint 21 — 2026-07-20

> NOTE FOR SPRINT 22: this entry lives in its own file because the GitHub web editor became unresponsive while editing the 90KB+ SPRINT_LOG.md and the in-progress edit was lost. Sprint 22 should merge this entry into SPRINT_LOG.md and apply the Master Backlog changes listed at the bottom, then delete this file. Prefer small incremental edits to SPRINT_LOG.md — it is now large enough that one big edit can hang the browser editor.
>
> ## Sprint Goal
>
> wp-admin access was RESTORED this sprint after four consecutive blocked sprints. The goal was therefore to clear the backed-up publishing queue and reconcile the state drift that had accumulated while the log and the live site were out of contact: full ORIENT reconciliation against wp-admin rather than public REST alone; publish the queued blog post; advance Course 2 with real lesson content.
>
> ## Completed This Sprint
>
> **ORIENT reconciliation — TWO significant discrepancies found, both resolved this sprint.** Public REST showed exactly what Sprint 20 logged: 9 posts, 17 pages, 1 course. wp-admin showed more.
>
> FIRST DISCREPANCY: the Courses list shows TWO courses, not one. Course 124 "Neuroplasticity in Practice" exists as a DRAFT, created by the unlogged 2026-07-11 run that was retroactively recovered as "Sprint 16". It was invisible to public REST because drafts are not exposed there. Sprints 18, 19 and 20 all believed Course 2 did not exist at all.
>
> SECOND DISCREPANCY: course 124 is not an empty shell. Its Module 1 already contained three lessons with full published content, also written by that unlogged run and never recorded: lesson 126 "What neuroplasticity really is and what it isn't", lesson 127 "The four conditions for rewiring: attention, effort, rest, repetition", lesson 128 "Myths, hype, and what the science actually supports", roughly 3.4 to 3.7KB each. Modules 2, 3 and 4 existed as topics with descriptions only and ZERO lesson items. Topic IDs are 125 for Module 1, 129 for Module 2, 130 for Module 3, 131 for Module 4.
>
> Everything else matched Sprint 20's log by ID exactly: posts 29, 63, 66, 68, 83, 90, 96, 99, 132 and the 17 pages.
>
> **Structural conflict identified and decided.** The Course 2 content drafted in Sprints 18, 19 and 20 was written against courses/course-02-neuroplasticity-learning.md, an outline for a course called "The Neuroplastic Learner" whose modules and lessons do NOT match the real WP course 124 "Neuroplasticity in Practice". Three sprints of drafting were aimed at the wrong structure. Per the standing rule that the live site is the source of truth, and because the homepage COMING SOON card already advertises "Neuroplasticity in Practice", the WP structure was kept and this sprint's new content was written to fit it. The three GitHub drafts remain in the repo as raw material but are not a 1:1 match for any WP module.
>
> **Published Blog Post 9 (post ID 135):** "Is AI Making You Sharper — or Just Faster? How to Use AI Without Dulling Your Brain". Category AI Literacy (28), slug use-ai-without-dulling-your-brain, live at https://unai-labs.com/use-ai-without-dulling-your-brain/. Queued in GitHub since Sprint 17 and blocked for four sprints. Published from content/blog-post-9-use-ai-without-dulling-your-brain.md, converted to Gutenberg block markup. Covers cognitive offloading and the Google effect, desirable difficulty, and automation complacency, with an attempt-before-outsource habit set. Cross-links posts 66, 99 and 132 plus course 30. QA'd on the live frontend: all headings, key takeaways and internal links render correctly.
>
> **Built out Course 2 Module 2 — four new lessons with full content, lesson IDs 137, 138, 139 and 140**, under topic 129 "Module 2 — Learning Faster & Remembering Longer" of course 124.
>
> Lesson 137 "Why forgetting is a feature: the science of memory" covers encoding, consolidation and retrieval, the forgetting curve, forgetting as a filter, and storage strength versus retrieval strength.
>
> Lesson 138 "Spaced repetition — the highest-leverage learning tool there is" covers the spacing effect, expanding intervals, the three-touch rule, and when flashcard tools are and are not worth it.
>
> Lesson 139 "Active recall and desirable difficulty" covers the testing effect, desirable difficulty, five retrieval techniques that need no system, and interleaving.
>
> Lesson 140 "Building a personal learning system that sticks" covers intent, method and review; one hour three times a week; matching method to content type; designing for the bad week; and the Week 2 project.
>
> All four follow the established house lesson template used by course 30 and by lessons 126 to 128: reading-time header, h2 sections, Key takeaways, a Try this exercise, and a next-lesson pointer. Each lesson builds explicitly on the previous one. Topic 129's summary was also rewritten — it previously ended with "Full lesson content coming in a future course update", a promise that is now fulfilled.
>
> **Course 2 status after this sprint:** course 124 is still a DRAFT and was deliberately NOT published. Module 1 (126-128) and Module 2 (137-140) now carry seven lessons of full content; Modules 3 and 4 remain topic-description-only with no lesson items. Publishing a course with half its modules empty would be worse for the site than the current COMING SOON card, so it stays unpublished until Modules 3 and 4 are written.
>
> ## Technical Note For Future Sprints
>
> Tutor LMS lessons and topics are NOT exposed in the WP REST API — only courses are. They can be created and updated through /wp-admin/admin-ajax.php with the actions tutor_save_lesson and tutor_save_topic, using the _tutor_nonce found in the Course Builder page HTML.
>
> CRITICAL: for tutor_save_lesson the content parameter is named "description", NOT "lesson_content", and the title parameter is "title", NOT "lesson_title". Passing the wrong names returns a cheerful "Lesson updated successfully" while silently saving nothing. For tutor_save_topic the parameters are "title" and "summary". This cost real time this sprint — do not rediscover it.
>
> Also: creating a lesson requires lesson_id 0 plus topic_id and course_id; updating requires the real lesson_id. Always verify a save by fetching /wp-admin/post.php?post=ID&action=edit and searching for a distinctive phrase, because the ajax success message is not proof of a save.
>
> ## Decisions Made
>
> Kept the live WP course structure for Course 2 rather than restructuring it to match the GitHub outline, because the live site is the designated source of truth and the homepage already markets that name. The mismatch is logged to the Master Backlog so Manish can overrule.
>
> Left course 124 as a Draft rather than publishing it half-complete.
>
> Did not create a new Course 2 — found and extended the existing draft instead. This is exactly the duplicate-content failure mode the ORIENT step exists to prevent, and it would have happened this sprint without it.
>
> Decided the two stale wp-admin blocker items should be pruned from the Master Backlog now that access is restored.
>
> ## Carry-Forward for Sprint 22+
>
> FIRST: merge this file into SPRINT_LOG.md, apply the Master Backlog changes below, then delete this file.
>
> Write Course 2 Module 3 lessons under topic 130 "Module 3 — Focus & Attention as Trainable Skills" and Module 4 lessons under topic 131 "Module 4 — Resilience & Cognitive Longevity". That completes course 124 and makes it publishable. Reuse the tutor_save_lesson technique documented above.
>
> Once Course 2 is content-complete: publish course 124, set its price, create its WooCommerce product mirroring product 120, and replace the homepage COMING SOON card with a real course card.
>
> Re-verify WooCommerce product 120 in wp-admin — flagged as unverifiable during the Sprint 20 audit and still not re-checked.
>
> Apply the visual-polish pattern to the Courses page (page 25) — pending since Sprint 10.
>
> Back up post 132's content to the content/ folder for parity — noted in Sprint 16, still not done.
>
> Next mandatory content audit due Sprint 25.
>
> ## Master Backlog changes to apply to SPRINT_LOG.md
>
> REMOVE the two wp-admin blocker items dated 2026-07-19 (Sprint 20) and 2026-07-13 (Sprint 17). Access is restored and the whole queue is cleared, so both are moot.
>
> ADD — **RESOLVED 2026-07-20 (Sprint 21): wp-admin access is back and the entire 4-sprint publishing queue has been cleared.** Blog post 9 published as post 135 and Course 2 Module 2 built out as lessons 137-140. Nothing needed from Manish right now; if the session expires again, log into wp-admin once with Remember Me checked in the automation's browser profile, or create a WordPress Application Password.
>
> ADD — **Decide when to publish Course 2 "Neuroplasticity in Practice" (course 124).** Still a Draft: Modules 1 and 2 carry full content (lessons 126-128 and 137-140), Modules 3 and 4 are empty shells. Manish: confirm the default plan of publishing only once all four modules are written, and decide its price plus whether it needs its own WooCommerce product the way course 30 does.
>
> ADD — **Course 2's GitHub outline does not match the live course.** courses/course-02-neuroplasticity-learning.md describes "The Neuroplastic Learner" with different modules and lessons than WP course 124 "Neuroplasticity in Practice". Sprint 21 treated the live site as source of truth. Manish: no action needed unless you prefer the outline's structure — say so and the course will be rebuilt to match it.
>
> ALL OTHER existing backlog items carry forward unchanged: Razorpay keys, the Module 1 video plan decision, Tutor LMS Pro, guest checkout, About photo and bio, Yoast logo, GoDaddy trial keep-or-cancel, the lead-magnet email end-to-end test, and the low-priority naming and legacy-page cosmetics.
>
> Last updated: 2026-07-20 (Sprint 21)
> 
