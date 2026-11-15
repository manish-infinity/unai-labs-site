"""Build the session follow-through kit (roadmap R-18).

Outputs, in content/sessions/:
  7-day-challenge-handout.pdf   one A4 page, handed out or shared after the talk
  feedback-slip.pdf             A4 with two identical 3-question slips (cut in half)

Rules: no prices, no product names, no buy links, no em-dashes (the build fails if one appears).
Every habit carries the same named source as the talk deck (tools/build_talk_deck.py).
Edit the text here and re-run: python3 tools/build_session_kit.py
"""
from pathlib import Path

from reportlab.lib.colors import HexColor, white
from reportlab.lib.pagesizes import A4
from reportlab.lib.units import mm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.pdfgen import canvas

NAVY = HexColor("#1B2A4A")
ACCENT = HexColor("#2E7D6B")
GREY = HexColor("#5A6270")
LINE = HexColor("#C9CED6")

FONT_DIR = Path("/usr/share/fonts/truetype/dejavu")
pdfmetrics.registerFont(TTFont("Sans", str(FONT_DIR / "DejaVuSans.ttf")))
pdfmetrics.registerFont(TTFont("Sans-Bold", str(FONT_DIR / "DejaVuSans-Bold.ttf")))

OUT = Path(__file__).resolve().parent.parent / "content" / "sessions"

TITLE = "Your Brain vs AI: the 7-day challenge"
SUB = "From the free talk. Three small habits, one week. Notice what changes."

DAILY = [
    ("Think first, then ask AI",
     "Before you ask AI anything that matters, spend 2 minutes writing your own answer or outline. Then compare.",
     "Bastani et al. (2025), PNAS 122:e2422633122"),
    ("5-minute evening recall",
     "Close the book or laptop. Write what you learned today from memory, then check what you missed.",
     "Roediger & Karpicke (2006), Psychological Science 17:249-255"),
    ("One phone-free deep-work block",
     "25 to 50 minutes, one task, phone out of reach, notifications off. Note where you stopped before you switch.",
     "Leroy (2009), OBHDP 109:168-181; Mark (2023), Attention Span"),
]

BONUS = [
    "Space it out: review today's notes again tomorrow, in 3 days and in a week (Cepeda et al. 2006, Psychological Bulletin).",
    "Protect sleep: it is when the brain locks in what you learned (Diekelmann & Born 2010, Nat Rev Neurosci).",
    "Walk briskly before study or deep work (Erickson et al. 2011, PNAS; that trial was in older adults).",
]

FEEDBACK_Q = [
    ("1. How useful was this talk for you?", "1   2   3   4   5     (1 = not useful, 5 = very useful)"),
    ("2. Which one habit will you try this week?", None),
    ("3. Is there another class, club or team that should hear this talk? (optional, name only)", None),
]


def no_dash(*texts):
    for t in texts:
        if "—" in t or "–" in t:
            raise SystemExit(f"em or en dash found: {t!r}")


def wrap(c, text, font, size, width):
    words, lines, cur = text.split(), [], ""
    for w in words:
        test = (cur + " " + w).strip()
        if c.stringWidth(test, font, size) <= width:
            cur = test
        else:
            lines.append(cur)
            cur = w
    if cur:
        lines.append(cur)
    return lines


def para(c, x, y, text, font="Sans", size=10, color=NAVY, width=170 * mm, lead=1.35):
    c.setFont(font, size)
    c.setFillColor(color)
    for line in wrap(c, text, font, size, width):
        c.drawString(x, y, line)
        y -= size * lead
    return y


def handout(path):
    c = canvas.Canvas(str(path), pagesize=A4)
    c.setTitle(TITLE)
    c.setAuthor("Manish Kumar Singh")
    W, H = A4
    x0 = 20 * mm
    c.setFillColor(NAVY)
    c.rect(0, H - 38 * mm, W, 38 * mm, fill=1, stroke=0)
    c.setFillColor(white)
    c.setFont("Sans-Bold", 20)
    c.drawString(x0, H - 20 * mm, TITLE)
    c.setFont("Sans", 10.5)
    c.drawString(x0, H - 29 * mm, SUB)

    y = H - 50 * mm
    c.setFont("Sans-Bold", 12.5)
    c.setFillColor(ACCENT)
    c.drawString(x0, y, "Every day for 7 days")
    y -= 8 * mm
    for i, (head, body, src) in enumerate(DAILY, 1):
        c.setFillColor(ACCENT)
        c.circle(x0 + 3.2 * mm, y + 1.2 * mm, 3.2 * mm, fill=1, stroke=0)
        c.setFillColor(white)
        c.setFont("Sans-Bold", 9.5)
        c.drawCentredString(x0 + 3.2 * mm, y - 0.3 * mm, str(i))
        para(c, x0 + 10 * mm, y, head, "Sans-Bold", 11)
        y = para(c, x0 + 10 * mm, y - 5.2 * mm, body, size=9.8, width=160 * mm)
        y = para(c, x0 + 10 * mm, y - 0.5 * mm, "Source: " + src, size=7.8, color=GREY, width=160 * mm)
        y -= 4 * mm

    # tracker
    c.setFont("Sans-Bold", 12.5)
    c.setFillColor(ACCENT)
    c.drawString(x0, y, "Tick it off")
    y -= 4 * mm
    col0, colw, rowh = 62 * mm, 15.5 * mm, 8 * mm
    days = [f"Day {d}" for d in range(1, 8)]
    c.setFont("Sans-Bold", 8.5)
    c.setFillColor(NAVY)
    for j, d in enumerate(days):
        c.drawCentredString(x0 + col0 + colw * j + colw / 2, y - 5.3 * mm, d)
    rows = ["Thought first, then asked AI", "5-minute evening recall", "Phone-free deep-work block"]
    c.setStrokeColor(LINE)
    c.setLineWidth(0.6)
    top = y - 8 * mm
    for i, r in enumerate(rows):
        ry = top - rowh * i
        c.setFont("Sans", 9)
        c.setFillColor(NAVY)
        c.drawString(x0 + 1 * mm, ry - 5.3 * mm, r)
        for j in range(7):
            c.rect(x0 + col0 + colw * j + colw / 2 - 2.5 * mm, ry - 6.3 * mm, 5 * mm, 5 * mm, fill=0, stroke=1)
        c.line(x0, ry - rowh, x0 + col0 + colw * 7, ry - rowh)
    y = top - rowh * len(rows) - 9 * mm

    c.setFont("Sans-Bold", 12.5)
    c.setFillColor(ACCENT)
    c.drawString(x0, y, "If you want more")
    y -= 6.5 * mm
    for b in BONUS:
        c.setFillColor(NAVY)
        c.setFont("Sans", 9.5)
        c.drawString(x0 + 1 * mm, y, "•")
        y = para(c, x0 + 5 * mm, y, b, size=9.3, width=165 * mm) - 1.5 * mm

    y -= 3 * mm
    c.setFont("Sans-Bold", 12.5)
    c.setFillColor(ACCENT)
    c.drawString(x0, y, "On day 7, write one line")
    y -= 6.5 * mm
    y = para(c, x0, y, "What changed? Focus, memory, how you use AI, nothing at all? Any answer is useful. "
             "Share it with a friend and ask them to try the week with you: habits stick better with company.",
             size=9.5)
    for k in range(2):
        y -= 7 * mm
        c.setStrokeColor(LINE)
        c.line(x0, y, W - x0, y)

    c.setFont("Sans", 8)
    c.setFillColor(GREY)
    c.drawString(x0, 14 * mm, "Evidence note: Bastani et al. is a field experiment with high-school students; the habits are "
                 "low-risk, but results vary by person.")
    c.drawString(x0, 10 * mm, "Manish Kumar Singh  |  unai-labs.com")
    c.showPage()
    c.save()


def slip(c, top, W):
    x0 = 20 * mm
    y = top - 14 * mm
    c.setFont("Sans-Bold", 14)
    c.setFillColor(NAVY)
    c.drawString(x0, y, "Your Brain vs AI: three quick questions")
    y -= 6 * mm
    c.setFont("Sans", 9)
    c.setFillColor(GREY)
    c.drawString(x0, y, "Anonymous. No name or contact details needed. Thank you for listening.")
    y -= 11 * mm
    for q, hint in FEEDBACK_Q:
        y = para(c, x0, y, q, "Sans-Bold", 10.5, width=170 * mm)
        if hint:
            y -= 3 * mm
            c.setFont("Sans", 11)
            c.setFillColor(NAVY)
            c.drawString(x0 + 4 * mm, y, hint)
            y -= 11 * mm
        else:
            for _ in range(2):
                y -= 8 * mm
                c.setStrokeColor(LINE)
                c.line(x0, y, W - x0, y)
            y -= 9 * mm
    c.setFont("Sans", 8)
    c.setFillColor(GREY)
    c.drawString(x0, top - 140 * mm + 6 * mm, "unai-labs.com")


def feedback(path):
    c = canvas.Canvas(str(path), pagesize=A4)
    c.setTitle("Your Brain vs AI: feedback slip")
    c.setAuthor("Manish Kumar Singh")
    W, H = A4
    slip(c, H, W)
    c.setStrokeColor(LINE)
    c.setDash(3, 3)
    c.line(10 * mm, H / 2, W - 10 * mm, H / 2)
    c.setDash()
    slip(c, H / 2, W)
    c.showPage()
    c.save()


if __name__ == "__main__":
    no_dash(TITLE, SUB, *[t for row in DAILY for t in row], *BONUS, *[q for q, _ in FEEDBACK_Q])
    OUT.mkdir(parents=True, exist_ok=True)
    handout(OUT / "7-day-challenge-handout.pdf")
    feedback(OUT / "feedback-slip.pdf")
    print("built", OUT / "7-day-challenge-handout.pdf", OUT / "feedback-slip.pdf")
