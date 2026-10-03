# tools/

Operational helper scripts for the unai-labs.com daily sprint.

## batch-publish-blog-backlog.js (added Sprint 63)

A **one-paste** browser-console publisher for the staged blog backlog. It exists to
beat the recurring failure mode where a *brief* authenticated wp-admin window (as in
Sprint 62) only lets one post go live before the session expires.

**Usage (needs a LIVE authenticated wp-admin session):**
1. Log in at https://unai-labs.com/wp-admin/ (tick **Remember Me**).
2. Open DevTools → Console on any wp-admin page.
3. Paste the whole script, press Enter.

It publishes every embedded staged post that is **not already live**, in one pass
(seconds). **Idempotent** — checks each slug first and SKIPs anything that already
exists, so re-running never creates duplicates. Prints a summary table with the new
post IDs; record those in `SPRINT_LOG.md`.

Embedded posts (Sprint 63): 31 retrieval→AI(28), 32 social connection→BH(29),
33 curiosity→Neuro(17), 34 hearing→BH(29), 35 emotion & memory→Neuro(17).
On full publish the corpus is **37 posts**, pillars **Neuroplasticity 13 / AI Literacy 12 / Brain Health 12**.

**Regenerate** (after adding/editing a staged post) with `python3 build_publisher.py`
from the repo root — it re-converts the markdown (strip frontmatter + leading H1;
`markdown` extensions extra+sane_lists+smarty), re-embeds as base64, and rewrites
this script. Keep the embedded set in sync with what is actually still staged.
