"""Build the free awareness-session deck "Your Brain vs AI" (roadmap R-15).
Run: python3 tools/build_talk_deck.py  -> content/sessions/your-brain-vs-ai-talk.pptx
Rules: plain words, every claim with a named source, no sales slide, no em-dashes,
last slide shows only the speaker's name and unai-labs.com.
"""
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pathlib import Path

NAVY = RGBColor(0x14, 0x1E, 0x3C)
TEAL = RGBColor(0x1F, 0x9E, 0x8F)
INK = RGBColor(0x22, 0x22, 0x22)
GREY = RGBColor(0x6B, 0x72, 0x80)
WHITE = RGBColor(0xFF, 0xFF, 0xFF)

# (kind, title, bullets, source line, speaker notes)
SLIDES = [
    ("title", "Your Brain vs AI", ["How to stay sharp in the age of AI", "Manish Kumar Singh"], "",
     "Welcome. 45 minutes: about 30 of talk, 15 of questions. Nothing to buy today; this is about how your brain works and how to use AI without losing your edge. Ask them: who used an AI tool this week? Most hands go up. Good, this talk is for you."),
    ("body", "A 10-second test", [
        "Without looking: what is a close friend's phone number?",
        "Now: where would you find it?",
        "Most of us know where, not what.",
        "When we expect to look something up later, we remember it less, but we remember where it is."],
     "Source: Sparrow, Liu & Wegner (2011), Google effects on memory, Science 333:776-778",
     "Let them try. Most people cannot recall the number but know it is in their phone. That is not a failing; it is the brain saving effort. The question for today is what happens when we hand over not just phone numbers but our thinking."),
    ("plain", "What we will cover", [
        "1. How attention works",
        "2. How memory works",
        "3. What leaning on AI does to thinking: 3 studies, stated honestly",
        "4. Five habits that protect focus and memory",
        "5. Your questions"], "",
     "Set expectations: real studies, with their limits. Where the evidence is early or small, I will say so."),
    ("body", "Your brain, in numbers", [
        "About 86 billion neurons",
        "About 2% of body weight, about 20% of the body's energy: roughly the power of a dim bulb",
        "It changes with use: London taxi drivers who learned the city's streets had a larger back part of the hippocampus, the brain's map-maker"],
     "Sources: Azevedo et al. (2009), J Comp Neurol 513:532-541; Raichle & Gusnard (2002), PNAS 99:10237-10239; Maguire et al. (2000), PNAS 97:4398-4403",
     "The point of this slide: the brain is efficient and it adapts to what we ask of it. That cuts both ways. Practise a skill and the brain invests in it; stop practising and it reallocates. Avoid saying 'more powerful than AI' as a fact; say it is astonishingly efficient and adaptable."),
    ("body", "Attention is a spotlight, not a floodlight", [
        "Working memory holds only about 4 chunks at once",
        "Switching tasks leaves 'attention residue': part of your mind stays on the last task",
        "So every switch has a cost, even a quick check of a message"],
     "Sources: Cowan (2001), Behavioral and Brain Sciences 24:87-114; Leroy (2009), Organizational Behavior and Human Decision Processes 109:168-181",
     "Demo: ask them to hold 7 random letters in mind while you ask a simple question. Most drop letters. Working memory is small, so where we point the spotlight matters."),
    ("body", "Screens and the shrinking spotlight", [
        "Researchers logged how long people stay on one screen before switching",
        "Around 2004: about 2.5 minutes",
        "In recent years: about 47 seconds on average",
        "Fast switching was linked with higher stress in her studies"],
     "Source: Gloria Mark (2023), Attention Span, Hanover Square Press (summary of her field studies at UC Irvine)",
     "Be careful with wording: this is time on one screen, not a 'human attention span'. Do not claim goldfish comparisons; those are myths."),
    ("body", "How memory actually works", [
        "Encode: pay attention and make meaning",
        "Consolidate: sleep replays and strengthens new memories",
        "Retrieve: every time you recall something, it gets easier to recall again",
        "Without review, we forget much of new material within days: the forgetting curve"],
     "Sources: Diekelmann & Born (2010), Nature Reviews Neuroscience 11:114-126; Murre & Dros (2015), PLOS ONE 10:e0120644 (replication of Ebbinghaus 1885)",
     "Three steps. AI can help or hurt at each one. If AI does the encoding (it reads and summarises for you) and the retrieving (you ask again instead of recalling), the brain has little to strengthen."),
    ("body", "Recalling beats re-reading", [
        "Students read a text, then either re-read it or tested themselves",
        "Five minutes later, re-readers did slightly better",
        "One week later, the self-testers remembered clearly more",
        "Effort now is memory later"],
     "Source: Roediger & Karpicke (2006), Psychological Science 17:249-255",
     "This is one of the most replicated findings in learning science. Re-reading feels productive; recalling feels hard. The hard feeling is the learning."),
    ("section", "What does leaning on AI do to thinking?", ["Three studies, with their limits"], "",
     "Transition. Stress: these are early studies. None says AI makes you stupid. They point at one risk: handing over the thinking, not just the typing."),
    ("body", "Study 1: students and maths", [
        "About 1,000 high-school students in Turkey, real classes",
        "Plain ChatGPT access raised practice scores",
        "When the AI was removed for the exam, those students scored 17% lower than students who never had it",
        "An AI tutor that gave hints, not answers, largely avoided that harm"],
     "Source: Bastani et al. (2025), Generative AI without guardrails can harm learning, PNAS 122:e2422633122",
     "Key message: how you use AI matters more than whether you use it. Answers on demand felt helpful and hurt learning. Hints that made students think did not."),
    ("body", "Study 2: brains while writing essays", [
        "54 adults wrote essays with ChatGPT, with a search engine, or with no tools, while EEG recorded brain activity",
        "The ChatGPT group showed the weakest brain connectivity of the three",
        "Many in that group struggled to quote from the essay they had just written",
        "Caution: small study, published as a preprint"],
     "Source: Kosmyna et al. (2025), Your Brain on ChatGPT: Accumulation of Cognitive Debt, MIT Media Lab, arXiv:2506.08872",
     "Be honest: 54 people, a lab task, not a final word. The authors call it 'cognitive debt': saving effort now, paying later in what you can remember and own."),
    ("body", "Study 3: people at work", [
        "319 knowledge workers described 936 real uses of AI at work",
        "More trust in the AI went with less critical thinking; more self-confidence went with more",
        "A separate survey of 666 people linked heavier AI use with lower critical-thinking scores, via 'offloading'",
        "These are surveys: they show links, not cause and effect"],
     "Sources: Lee et al. (2025), CHI 2025, Microsoft Research and Carnegie Mellon University; Gerlich (2025), Societies 15(1):6",
     "Company variant: spend longer here. The practical lesson: stay the editor, not the copier. Check, question and rewrite what the AI gives you."),
    ("body", "The honest summary", [
        "AI is a powerful tool. The risk is not using it; it is letting it do the thinking",
        "Use AI after you have thought, not instead of thinking",
        "Make it ask you questions, not just give you answers",
        "Keep the skills you want to keep in regular use"],
     "Based on: Bastani et al. (2025); Kosmyna et al. (2025); Lee et al. (2025)",
     "Pause for a quick show of hands: who has copied an AI answer without reading it closely? No judgement; we all have."),
    ("body", "Habits 1 and 2", [
        "1. Think first, then ask AI. Write your own answer or outline in 2 minutes, then compare",
        "2. Test yourself instead of re-reading. Close the book, write what you remember, then check",
        "Ask AI to quiz you, not to summarise for you"],
     "Sources: Bastani et al. (2025), PNAS; Roediger & Karpicke (2006), Psychological Science",
     "Student variant: tie to exam revision. Company variant: before a meeting, write your own 3 points before asking AI for a draft."),
    ("body", "Habits 3 and 4", [
        "3. Space it out: short reviews spread over days beat one long cram",
        "4. One task at a time: phone out of reach, one tab, notifications off for a 25 to 50 minute block",
        "End each block by noting where you stopped: it reduces attention residue"],
     "Sources: Cepeda et al. (2006), Psychological Bulletin 132:354-380; Leroy (2009), OBHDP; Mark (2023)",
     "Spacing: review tomorrow, then in 3 days, then in a week. Single-tasking: the note at the end of a block is a tip from Leroy's later work on 'ready-to-resume' plans."),
    ("body", "Habit 5: sleep and move", [
        "Sleep is when the brain locks in what you learned. An all-nighter trades memory for hours",
        "Regular aerobic exercise: in a one-year trial, older adults who walked briskly grew their hippocampus by about 2%",
        "A walk before study or deep work is a cheap upgrade"],
     "Sources: Diekelmann & Born (2010), Nat Rev Neurosci; Erickson et al. (2011), PNAS 108:3017-3022",
     "Keep it practical: 7 to 9 hours for adults is the usual guidance; avoid prescribing medical advice. The exercise trial was in older adults; say so."),
    ("plain", "Try this for 7 days, then questions", [
        "Day 1-7: think first, then ask AI",
        "Each evening: 5 minutes of recall on what you learned today",
        "One phone-free deep-work block a day",
        "Notice what changes. Then over to you: what would you like to ask?"],
     "",
     "Open Q&A. If no questions, seed one: 'Is it okay to use AI for homework or reports?' Answer with the hint-not-answer idea from Study 1. Do not mention courses, prices or the book. If someone asks where to learn more, say the website is on the next slide."),
    ("close", "Manish Kumar Singh", ["unai-labs.com"], "",
     "Thank them. Leave this slide up during the last questions. No offer, no link to buy."),
]


def add_text(slide, left, top, width, height, text, size, color, bold=False, align=PP_ALIGN.LEFT):
    tb = slide.shapes.add_textbox(left, top, width, height)
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = text
    p.alignment = align
    for r in p.runs:
        r.font.size = Pt(size)
        r.font.color.rgb = color
        r.font.bold = bold
        r.font.name = "Calibri"
    return tf


def fill(slide, color):
    bg = slide.background.fill
    bg.solid()
    bg.fore_color.rgb = color


def build(out: Path):
    prs = Presentation()
    prs.slide_width, prs.slide_height = Inches(13.333), Inches(7.5)
    blank = prs.slide_layouts[6]
    for i, (kind, title, bullets, source, notes) in enumerate(SLIDES, 1):
        s = prs.slides.add_slide(blank)
        if kind in ("title", "section", "close"):
            fill(s, NAVY)
            add_text(s, Inches(0.9), Inches(2.4), Inches(11.5), Inches(1.4), title, 54 if kind != "section" else 44, WHITE, True,
                     PP_ALIGN.CENTER if kind == "close" else PP_ALIGN.LEFT)
            for j, b in enumerate(bullets):
                add_text(s, Inches(0.9), Inches(3.9 + j * 0.7), Inches(11.5), Inches(0.7), b, 28, TEAL if j == 0 else WHITE,
                         False, PP_ALIGN.CENTER if kind == "close" else PP_ALIGN.LEFT)
        else:
            fill(s, WHITE)
            bar = s.shapes.add_shape(1, 0, 0, Inches(0.25), prs.slide_height)
            bar.fill.solid(); bar.fill.fore_color.rgb = TEAL; bar.line.fill.background()
            add_text(s, Inches(0.8), Inches(0.5), Inches(11.8), Inches(1.0), title, 38, NAVY, True)
            tb = s.shapes.add_textbox(Inches(0.8), Inches(1.7), Inches(11.8), Inches(4.6))
            tf = tb.text_frame; tf.word_wrap = True
            for j, b in enumerate(bullets):
                p = tf.paragraphs[0] if j == 0 else tf.add_paragraph()
                p.text = b
                p.space_after = Pt(14)
                for r in p.runs:
                    r.font.size = Pt(24); r.font.color.rgb = INK; r.font.name = "Calibri"
            if source:
                add_text(s, Inches(0.8), Inches(6.55), Inches(11.8), Inches(0.7), source, 12, GREY)
        add_text(s, Inches(12.3), Inches(7.0), Inches(0.9), Inches(0.4), str(i), 11,
                 GREY if kind in ("body", "plain") else TEAL, False, PP_ALIGN.RIGHT)
        s.notes_slide.notes_text_frame.text = notes
    out.parent.mkdir(parents=True, exist_ok=True)
    prs.save(out)
    return len(SLIDES)


if __name__ == "__main__":
    root = Path(__file__).resolve().parent.parent
    n = build(root / "content/sessions/your-brain-vs-ai-talk.pptx")
    # Guardrail checks
    text = Path(__file__).read_text()
    assert chr(0x2014) not in text and chr(0x2013) not in text, "em/en dash found"
    for k, t, b, src, _ in SLIDES:
        if k == "body":
            assert src, f"slide '{t}' has no source line"
    print(f"built {n} slides")
