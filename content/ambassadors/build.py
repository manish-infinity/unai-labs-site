"""Build the Campus Ambassador public page (WordPress content block) and the Playbook PDF
from ONE source, so they never disagree. Usage: python3 build.py <outdir>
Outputs: page.html (paste into page 363 above the [unai_ambassador_portal] shortcode),
preview.html (page in a simple site-like frame), playbook.html, playbook.pdf.
Security: static content only. No forms, no scripts, no tracking, no personal data.
"""
import sys, pathlib, html

OUT = pathlib.Path(sys.argv[1] if len(sys.argv) > 1 else '.')
OUT.mkdir(parents=True, exist_ok=True)

CONTACT = 'manish.singh@unai-labs.com'
PRODUCTS = [  # name, regular price, launch price (to 30 Nov 2026)
    ('AI Literacy for Everyone', 2999, 1999),
    ('Neuroplasticity in Practice', 2999, 1999),
    ('Deep Focus: Reclaiming Attention', 2999, 1999),
    ('Brain Health 101', 2999, 1999),
    ('How to Be a Hippie (eBook)', 399, None),
]
TIERS = [('1 to 24', 0.10), ('25 to 99', 0.15), ('100 to 249', 0.20), ('250 or more', 0.25)]
BUYER_PAYS = 0.90

def rs(x):  # Indian grouping for amounts used here (< 1 crore)
    n = int(round(x)); s = str(n)
    if len(s) <= 3: return 'Rs ' + s
    head, tail = s[:-3], s[-3:]
    parts = []
    while len(head) > 2: parts.insert(0, head[-2:]); head = head[:-2]
    if head: parts.insert(0, head)
    return 'Rs ' + ','.join(parts + [tail])

def share(price, rate):
    return round(price * BUYER_PAYS * rate)

# ---- earnings table (computed, not typed) ----
rows = [('Course at launch price ' + rs(1999) + ' (until 30 Nov 2026)', 1999),
        ('Course at ' + rs(2999) + ' (from 1 Dec 2026)', 2999),
        ('eBook at ' + rs(399), 399)]
earn_table = '<table class="t"><thead><tr><th>One sale</th>' + ''.join(
    f'<th>{r} sales<br><small>{int(p*100)}%</small></th>' for r, p in TIERS) + '</tr></thead><tbody>'
for label, price in rows:
    earn_table += f'<tr><td>{html.escape(label)}<br><small>buyer pays {rs(price*BUYER_PAYS)}</small></td>' + ''.join(
        f'<td class="n">{rs(share(price, p))}</td>' for _, p in TIERS) + '</tr>'
earn_table += '</tbody></table>'

def total(n, price=2999):
    rate = [p for lim, p in [(25, .10), (100, .15), (250, .20), (10**9, .25)] if n < lim][0]
    return rs(n * share(price, rate))
examples = [(5, 'at 10%'), (10, 'at 10%'), (25, 'all 25 move to 15%'), (50, 'at 15%'), (100, 'all 100 move to 20%')]
ex_list = '<ul>' + ''.join(f'<li><strong>{n} course sales</strong>: about {total(n)} ({why})</li>' for n, why in examples) + '</ul>'

prod_list = '<ul>' + ''.join(
    f'<li>{html.escape(n)}: {rs(r)}' + (f' (launch price {rs(l)} until 30 Nov 2026)' if l else '') + '</li>'
    for n, r, l in PRODUCTS) + '</ul>'

# ---- shared sections ----
S = {}
S['intro'] = f'''<p class="lead">Share UnAI Labs with your campus. Your friends get 10% off. You earn a share of every sale made with your personal code.</p>
<p>UnAI Labs makes short, science-backed online courses in plain language: AI literacy, deep focus, neuroplasticity and brain health, plus the eBook <em>How to Be a Hippie</em>. Our belief: your brain is more powerful than any AI, if you train it.</p>
<p><strong>The honest part first:</strong> this is commission only. There is no salary. If nobody buys with your code, you earn nothing. There is no fee to join and you never have to buy anything.</p>'''
S['help'] = f'''<div class="help"><strong>Need help?</strong> Email <a href="mailto:{CONTACT}">{CONTACT}</a> with your question (and your code, if you have one). We reply within 2 working days.</div>'''
S['who'] = '''<h2>Who can join</h2><ul>
<li>College or university students in India, aged 18 or older, any year, any stream.</li>
<li>You need your official college email (for example name@college.ac.in). Personal emails like Gmail are not accepted, because the college email is how we confirm you are a student.</li>
<li>Community builders without a college email (club organisers, tutors, group admins) cannot apply online yet. Write to us and we will tell you when that opens.</li></ul>'''
S['how'] = '''<h2>How it works</h2><ol>
<li><strong>Create a free UnAI Labs account</strong> (or log in).</li>
<li><strong>Apply on this page</strong> with your college name and college email.</li>
<li><strong>Open the confirmation link</strong> we send to your college email (valid for 48 hours), while logged in.</li>
<li><strong>Get your code and dashboard.</strong> College emails ending in .ac.in, .edu.in or .edu are approved straight away (up to 5 ambassadors per college). Others are reviewed by hand within 3 working days.</li></ol>'''
S['buyers'] = f'''<h2>What your friends get</h2><p>10% off any of these, with your code or your share link:</p>{prod_list}
<p>Each buyer can use your code once. Team and campus licences are not included.</p>'''
S['earn'] = f'''<h2>What you earn</h2>
<p>You earn a percentage of what the buyer actually pays (after their 10% discount). The percentage depends on how many sales you have made in total:</p>{earn_table}
<p><strong>When you reach a new level, all your earlier sales move up to the new percentage too.</strong> Example: at your 25th sale, your first 24 sales also move from 10% to 15%.</p>
<p>Examples at the {rs(2999)} course price:</p>{ex_list}
<p class="note">These are examples of how the maths works, not a promise of what you will earn. Amounts are rounded to the nearest rupee.</p>'''
S['counts'] = '''<h2>When a sale counts</h2><ul>
<li><strong>Your code must be on the order when it is paid.</strong> Your share link adds it automatically. If a buyer removes it or uses a different code, the sale cannot be added later.</li>
<li><strong>7-day wait:</strong> a sale is confirmed 7 days after payment, because buyers can ask for a refund in that time. A refunded sale does not count.</li>
<li><strong>Your own purchases never count</strong>, and your code will not work for you.</li></ul>'''
S['paid'] = f'''<h2>How you get paid</h2><ul>
<li>Add your UPI ID on your dashboard. The name on your UPI account should match your name on UnAI Labs.</li>
<li>We pay by UPI once a month, in the first week of the month, when your confirmed earnings are {rs(500)} or more. Smaller amounts carry over to the next month.</li>
<li>Your dashboard shows every number: sales, your current percentage, pending (inside the 7 days), confirmed, paid so far and balance due.</li>
<li>Questions about a payment: write to {CONTACT} with your code.</li></ul>'''
S['tax'] = '''<h2>Tax, in plain words</h2><ul>
<li>What you earn is your income. Declaring it is your responsibility.</li>
<li>We do not deduct tax from your payouts today. If the law ever requires us to, we will tell you before any payout it affects.</li>
<li>Ask us any time for a summary of what you were paid in a financial year.</li></ul>'''
S['rules'] = '''<h2>The rules</h2><ul>
<li>Be honest. Share what the courses actually are. No promises of results, jobs or marks.</li>
<li>No spam: no mass messages, no posting the same message again and again, no adding people to groups without asking.</li>
<li>No fake reviews, no fake accounts, no paid ads in the UnAI Labs name, no pretending to be UnAI Labs staff.</li>
<li>Breaking a rule means your code is switched off. Sales made honestly before that are still paid.</li></ul>'''
S['data'] = f'''<h2>Your data</h2><ul>
<li>We keep only what the programme needs: your account, college name and college email, city, course and year, and your UPI ID for payouts.</li>
<li>Only UnAI Labs can see it. It is never shown to other ambassadors or buyers, and never sold.</li>
<li>You never see your buyers' names or details either: your dashboard shows numbers only.</li>
<li>Want to leave or have your data deleted? Write to {CONTACT}. Any confirmed balance is paid in the next payout.</li></ul>'''
S['faq'] = f'''<h2>Questions</h2>
<p><strong>Do I have to buy a course to join?</strong> No. Joining is free and you never have to buy anything.</p>
<p><strong>Is there a target?</strong> No. Share as much or as little as you like. A good first month is about 5 sales.</p>
<p><strong>My friend bought but it is not on my dashboard.</strong> Check that your code was on their order. If it was, the sale shows straight away as pending. Sales without the code cannot be added later.</p>
<p><strong>Can I use my code myself?</strong> No. Codes are for friends and classmates.</p>
<p><strong>I am under 18.</strong> Sorry, you need to be 18 or older to join.</p>
<p><strong>Where do I start?</strong> Read the Playbook below. It has a 30-day plan and messages you can copy.</p>
<p><strong>Anything else?</strong> Email <a href="mailto:{CONTACT}">{CONTACT}</a>. We reply within 2 working days.</p>'''

PLAN = '''<h2>Your first 30 days</h2><table class="t"><tbody>
<tr><td><strong>Day 1</strong></td><td>Add your UPI ID. Read this Playbook once. Look through the course pages so you can describe them honestly.</td></tr>
<tr><td><strong>Week 1</strong></td><td>Post one Instagram story with your share link. Send one message in each class or club group you are already in. Tell 5 friends in person why you joined.</td></tr>
<tr><td><strong>Week 2</strong></td><td>Ask your wellness centre, placement cell or tech club if they would like a free 45-minute online session, "Your Brain vs AI". Introduce them to us by email; we do the session.</td></tr>
<tr><td><strong>Weeks 3 and 4</strong></td><td>Answer questions from people who replied. Share one idea from a lesson you found useful. Do not repeat your group message.</td></tr>
<tr><td><strong>Good first month</strong></td><td>About 5 sales. Some ambassadors will do more, some less. That is fine.</td></tr>
</tbody></table>'''
SCRIPTS = f'''<h2>Messages you can copy</h2>
<p>Change them into your own words. Messages that sound like you work better than copied ones.</p>
<div class="keep"><h3>Class or club WhatsApp group (send once)</h3>
<blockquote>Hi all, I've joined the UnAI Labs campus programme. They make short online courses on using AI without losing your own focus and thinking, and on how the brain learns. Plain language, science-backed. My link gives you 10% off: [your share link]. Happy to answer questions, and no pressure at all.</blockquote>
</div><div class="keep"><h3>Instagram story</h3>
<blockquote>Exams, notifications, AI doing half the work. I've been learning how attention and memory actually work. If you want to try it, 10% off with my link: [your share link]</blockquote>
</div><div class="keep"><h3>One-to-one message</h3>
<blockquote>Hey, you mentioned you struggle to focus while studying. There's a short course called Deep Focus that I think is actually useful, and I can get you 10% off: [your share link]. Totally fine if it's not for you.</blockquote>
</div><div class="keep"><h3>Email to a club, wellness centre or placement cell</h3>
<blockquote>Subject: A free 45-minute session for students: "Your Brain vs AI"<br><br>Dear [name],<br><br>I'm a [year/course] student and a campus ambassador for UnAI Labs, which teaches short, science-backed courses on AI literacy, focus and brain health. They offer a free 45-minute online session, "Your Brain vs AI", on keeping your own attention and judgement strong while using AI tools. Would [club/centre] be interested? I can connect you with the founder at {CONTACT}.<br><br>Thank you,<br>[your name]</blockquote>
</div><div class="keep"><h3>Do and don't</h3><table class="t"><tbody>
<tr><td><strong>Do</strong></td><td>Share once per group. Say what the course is. Tell people it is fine to say no. Answer questions honestly, or ask us.</td></tr>
<tr><td><strong>Don't</strong></td><td>Promise marks, jobs or results. Post fake reviews. Spam groups or add people without asking. Say you work for UnAI Labs.</td></tr>
</tbody></table></div>'''
CHECK = '''<h2>Start checklist</h2><ul class="check">
<li>Account created and college email confirmed</li><li>Code and share link on my dashboard</li><li>UPI ID added</li>
<li>Looked through the course pages</li><li>First story or group message sent</li><li>Wellness centre or club contacted</li></ul>'''

PUBLIC_ORDER = ['intro', 'help', 'who', 'how', 'buyers', 'earn', 'counts', 'paid', 'tax', 'rules', 'data', 'faq']

CSS = '''
.unai-amb{font-family:Inter,system-ui,sans-serif;color:#0f1e3d;line-height:1.6;max-width:820px}
.unai-amb h1{font-size:2.1em;line-height:1.2;margin:.2em 0 .4em}
.unai-amb h2{font-size:1.35em;margin:1.6em 0 .5em;border-top:3px solid #40c4be;padding-top:.6em}
.unai-amb h3{font-size:1.05em;margin:1.2em 0 .3em}
.unai-amb .lead{font-size:1.2em}
.unai-amb .note{color:#555;font-size:.92em}
.unai-amb .t{border-collapse:collapse;width:100%;margin:.6em 0;font-size:.95em}
.unai-amb .t th,.unai-amb .t td{border:1px solid #d5dbe6;padding:.5em .6em;text-align:left;vertical-align:top}
.unai-amb .t th{background:#0f1e3d;color:#fff}
.unai-amb .t small{color:#667}
.unai-amb .t td.n{white-space:nowrap}
.unai-amb .t th small{color:#cfe}
.unai-amb blockquote{background:#f2f6fb;border-left:4px solid #40c4be;margin:.4em 0 1em;padding:.7em 1em}
.unai-amb .check{list-style:none;padding-left:0}.unai-amb .check li:before{content:"\\2610  "}
.unai-amb .help{background:#e9f8f7;border:1px solid #40c4be;border-radius:6px;padding:.8em 1em;margin:1em 0}
.unai-amb .cta{display:inline-block;background:#0f1e3d;color:#fff;padding:.6em 1.2em;border-radius:6px;text-decoration:none}
'''

page_body = '<div class="unai-amb"><h1>Campus Ambassadors</h1>' + ''.join(S[k] for k in PUBLIC_ORDER) + \
    '<h2>The Playbook</h2><p>Everything on this page, plus a 30-day plan and messages you can copy, in one printable PDF: ' \
    '<a href="/wp-content/uploads/2026/11/unai-campus-ambassador-playbook.pdf">Download the Ambassador Playbook (PDF)</a>.</p><h2>Apply</h2></div>'
page = f'<style>{CSS}</style>\n' + page_body + '\n<!-- the existing [unai_ambassador_portal] shortcode stays below this block -->\n'
(OUT / 'page.html').write_text(page)
(OUT / 'preview.html').write_text('<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
    '<title>Campus Ambassadors preview</title><body style="margin:0;background:#fff"><div style="padding:24px 16px;max-width:860px;margin:auto">'
    + page + '<p style="border:2px dashed #aaa;padding:1em;color:#666">[Existing apply form / dashboard appears here]</p></div></body>')

PDF_CSS = CSS + '''
@page{size:A4;margin:16mm 16mm 18mm}
body{margin:0}.unai-amb{max-width:none;font-size:10.5pt}
.cover{background:#0f1e3d;color:#fff;padding:30mm 14mm;height:255mm;box-sizing:border-box;page-break-after:always;position:relative;border-radius:4mm}
.cover h1{color:#fff;font-size:34pt;margin:0 0 6mm}.cover .k{color:#40c4be;letter-spacing:.12em;font-weight:600}
.cover p{font-size:13pt;max-width:140mm}.cover .f{position:absolute;bottom:30mm;color:#40c4be;font-weight:600}
h2,h3{page-break-after:avoid;break-after:avoid}table,blockquote,section,.keep{page-break-inside:avoid;break-inside:avoid}.pb{page-break-before:always}
'''
pdf_order = ['intro', 'help', 'who', 'how', 'buyers', 'earn', 'counts', 'paid', 'tax', 'rules', 'data']
book = ('<!doctype html><html><head><meta charset="utf-8"><title>UnAI Labs Campus Ambassador Playbook</title>'
        f'<style>{PDF_CSS}</style></head><body><div class="unai-amb">'
        '<div class="cover"><p class="k">UNAI LABS</p><h1>Campus Ambassador Playbook</h1>'
        '<p>How the programme works, what you earn, how you get paid, a 30-day plan and messages you can copy.</p>'
        '<p style="margin-top:20mm">Version 1, November 2026. Prices and rules on unai-labs.com/campus-ambassadors are the latest; if this PDF and the page ever differ, the page is right.</p>'
        '<p class="f">unai-labs.com</p></div>'
        + ''.join(f'<section>{S[k]}</section>' for k in pdf_order) + '<div class="pb"></div>' + PLAN + SCRIPTS + CHECK + S['faq'].replace('Read the Playbook below. It has a 30-day plan and messages you can copy.','Start with the 30-day plan and the messages in this Playbook.') + '</div></body></html>')
(OUT / 'playbook.html').write_text(book)
print('built', OUT)
