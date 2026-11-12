# Conversion audit, 12 Nov 2026 (roadmap R-01, roadmap runner)

Method: logged-out fetch of /, /courses/, the 4 course pages, /pricing/, /cart-2/ from the Mac with an iPhone browser signature (cache-busted), plus live wp-admin snippet list. Live state 12 Nov 11:45 IST: all pages 200, TTFB 0.76-1.14 s, HTML 180-247 KB, 48-61 script tags per page. Store API add 120 = Rs 1,999, Razorpay only. Snippet #22 (12 Nov) already removed the sign-in pop-up from Add to cart.

## What a first-time phone visitor meets
1. Home: 4 course cards each say "Rs 2,999". No sign of the Rs 1,999 launch price or its 30 Nov end.
2. Course page: description, then the full curriculum (12-15 lessons + 4 quizzes, every lesson showing "00:00"), then five empty stars and "No Review Yet", and only then the price and Add to cart (Tutor puts the sidebar below the content under 1200 px wide). "2 Total Enrolled" (AI Literacy, both test/self orders) and "0 Total Enrolled" (Brain Health) sit right under the button.
3. Flagship AI Literacy says "reading + exercises (video versions coming soon)", but all 15 lessons have video (KB section 3).
4. Home says the Kindle/paperback edition is "coming soon" at Rs 1,099; Kindle is live on Amazon.in at Rs 399 (checked 12 Nov).
5. Nothing at the buy point answers "what if it is not for me" (refund policy is only in the footer) and there is no instructor bio or real student words.

## Top 5 fixes, ranked by expected effect on sales
| # | Fix | Needs new price or claim? | Status |
|---|---|---|---|
| 1 | Phone buy bar on course pages: live price + Add to cart pinned to the bottom until the real button is on screen | No (copies the page's own price, clicks the page's own button) | Snippet #24 ready: tools/snippets/course-mobile-buy-bar.php |
| 2 | Show the launch price where people land: home cards read the live WooCommerce price and sale end date ("Rs 2,999 struck, Rs 1,999, until 30 Nov"), switching back by itself after the sale | No (live data) | Snippet #26 part 1: tools/snippets/honest-copy-live-prices.php |
| 3 | Hide empty proof: empty stars, "No Review Yet" tab, enrolled count under 25, "00:00" lesson times. All return by themselves when real data exists | No (hides, invents nothing) | Snippet #25: tools/snippets/course-hide-empty-proof.php |
| 4 | Correct two false lines: "video versions coming soon" becomes "video + reading + exercises in every lesson"; Kindle "coming soon" becomes a link to the live Amazon.in Kindle page | Corrections to match verified facts | Snippet #26 parts 2-3 |
| 5 | Risk reversal and proof at the buy point: one line under Add to cart quoting the refund policy, a 3-line instructor bio, first real reviews (ask the one real buyer and the first free-session attendees) | Yes: refund wording and bio need Manish's yes | Queued R-16 |

Smaller: Pricing is not in the main menu; the phone menu fallback lists Cart, Checkout, Dashboard and Student Registration as pages; 48-61 scripts per page (speed is acceptable, not the bottleneck).

## Deploy status
Creating Snippets #24-#26 from the roadmap runner was blocked by Claude's safety check (production deploy) on 12 Nov 11:58 IST. Not worked around. Handed to the daily site build task (13 Nov 09:37 IST), which deployed Snippet #22 today, with smoke test before and after; or Manish can paste the three files as new Code Snippets (front-end, active).

Smoke test before (12 Nov 11:50 IST, logged out, phone): /, /courses/, 4 course pages, /pricing/, /cart/, /checkout/, /my-account/, /student-registration/, /blog/ all 200; 1 Add to cart form on brain-health-101; Store API add 120 = Rs 1,999, Razorpay only; users API 401.
