# v2 audio: character voiceover (Kokoro TTS) + trap-style beat + synced sound design.
import numpy as np, soundfile as sf, wave, sys
from kokoro_onnx import Kokoro
SR = 44100
K = Kokoro('tts/kokoro-v1.0.onnx', 'tts/voices-v1.0.bin')
VO = {'N': 'am_fenrir', 'B': 'am_puck', 'C': 'af_bella'}
rng = np.random.default_rng(7)

def resample(x, sr):
    n = int(len(x) * SR / sr); return np.interp(np.linspace(0, len(x) - 1, n), np.arange(len(x)), x)

def say(text, who, slot):
    sp = 1.08
    for _ in range(6):
        s, sr = K.create(text, voice=VO[who], speed=sp, lang='en-us'); s = resample(s, sr)
        if len(s) / SR <= slot or sp >= 1.45: break
        sp = min(1.45, sp * (len(s) / SR) / slot * 1.02)
    if who == 'B':  # robot: ring mod + light bitcrush
        t = np.arange(len(s)) / SR; s = s * (0.85 + 0.15 * np.sin(2 * np.pi * 55 * t))
    return s / (np.max(np.abs(s)) + 1e-9) * 0.9

def note(m): return 440 * 2 ** ((m - 69) / 12)
def put(out, s0, x, g=1.0):
    x = np.array(x, dtype=float); f = min(len(x) // 4, int(0.012 * SR))
    if f > 1: x[:f] *= np.linspace(0, 1, f); x[-f:] *= np.linspace(1, 0, f)
    i = int(s0 * SR); n = min(len(x), len(out) - i)
    if n > 0 and i >= 0: out[i:i + n] += x[:n] * g

def sfx(kind, d=0.3):
    n = int(d * SR); t = np.arange(n) / SR
    if kind == 'pop': return np.sin(2 * np.pi * (900 - 600 * t / d) * t) * np.exp(-t * 40) * 0.6
    if kind == 'whoosh':
        nz = np.convolve(rng.standard_normal(n), np.ones(25) / 25, 'same'); return nz * np.sin(np.linspace(0, np.pi, n)) ** 2 * 1.2
    if kind == 'impact': return (np.sin(2 * np.pi * np.cumsum(50 + 120 * np.exp(-t * 20)) / SR) + rng.standard_normal(n) * 0.25 * np.exp(-t * 25)) * np.exp(-t * 4)
    if kind == 'riser':
        f = 200 + 1800 * (t / d) ** 2; return (np.sin(2 * np.pi * np.cumsum(f) / SR) * 0.3 + rng.standard_normal(n) * 0.25 * (t / d)) * (t / d) ** 1.5 * np.minimum(1, (d - t) / 0.12)
    if kind == 'heart':
        x = np.zeros(n)
        for off in (0, 0.22):
            m = int(off * SR); tt = np.arange(int(0.15 * SR)) / SR; b = np.sin(2 * np.pi * 52 * tt) * np.exp(-tt * 28)
            x[m:m + len(b)] += b[:max(0, n - m)]
        return x * 1.3
    if kind == 'tick': return np.sin(2 * np.pi * 2600 * t) * np.exp(-t * 180) * 0.5
    if kind == 'scratch':
        f = 300 + 900 * np.abs(np.sin(2 * np.pi * 6 * t)); return np.sign(np.sin(2 * np.pi * np.cumsum(f) / SR)) * np.exp(-t * 3) * 0.25 * (rng.standard_normal(n) * 0.3 + 1)
    if kind == 'ding': return (np.sin(2 * np.pi * 1318 * t) + 0.5 * np.sin(2 * np.pi * 1976 * t)) * np.exp(-t * 6) * 0.35
    return np.zeros(n)

def beat(dur, drop_at=None, gaps=(), dark=()):
    N = int(dur * SR); out = np.zeros(N); bpm = 124; b = 60 / bpm
    dk = np.zeros(N)
    for a, z in dark: dk[int(a * SR):int(z * SR)] = 1
    roots_dark = [45, 45, 43, 41]; roots_bright = [48, 43, 45, 41]
    i = 0
    while i * b < dur:
        t0 = i * b; s = int(t0 * SR)
        muted = any(a <= t0 < z for a, z in gaps)
        if not muted:
            bar = int(i // 4); dark_now = dk[min(s, N - 1)] > 0.5
            root = (roots_dark if dark_now else roots_bright)[bar % 4]
            if i % 4 in (0, 2.5) or i % 8 == 3:  # 808 hits
                n = int(b * 1.6 * SR); tt = np.arange(n) / SR
                f = note(root - 24) * (1 + 0.6 * np.exp(-tt * 18))
                put(out, t0, np.tanh(np.sin(2 * np.pi * np.cumsum(f) / SR) * 2.2) * np.exp(-tt * 2.2) * 0.55)
            if i % 4 in (1, 3):  # clap
                n = int(0.18 * SR); tt = np.arange(n) / SR
                put(out, t0, rng.standard_normal(n) * np.exp(-tt * 22) * 0.32)
            roll = 4 if (i % 8 == 7) else 2  # hats, with rolls
            for k in range(roll):
                n = int(0.04 * SR); tt = np.arange(n) / SR
                put(out, t0 + k * b / roll, rng.standard_normal(n) * np.exp(-tt * 120) * 0.12)
            # bell melody on bright sections
            if not dark_now and i % 2 == 0:
                m = [72, 76, 79, 76, 74, 77, 81, 77][(i // 2) % 8]; n = int(0.5 * SR); tt = np.arange(n) / SR
                put(out, t0, (np.sin(2 * np.pi * note(m) * tt) + 0.3 * np.sin(2 * np.pi * note(m) * 3 * tt)) * np.exp(-tt * 7) * 0.09)
            if dark_now and i % 4 == 0:  # dark pad stab
                n = int(b * 4 * SR); tt = np.arange(n) / SR
                pad = sum(np.sin(2 * np.pi * note(m) * tt) for m in (root, root + 3, root + 7)) * 0.03 * np.minimum(1, tt * 8) * np.exp(-tt * 0.8)
                put(out, t0, pad)
        i += 1
    return out

def build(name, dur, lines, fx, gaps=(), dark=()):
    N = int(dur * SR)
    music = beat(dur, gaps=gaps, dark=dark)[:N]
    vo = np.zeros(N)
    for t0, t1, who, text in lines: put(vo, t0, say(text, who, t1 - t0))
    fxs = np.zeros(N)
    for t, k, *g in fx: put(fxs, t, sfx(k, {'riser': 1.0, 'heart': 0.5, 'scratch': 0.45, 'ding': 0.8, 'impact': 0.9}.get(k, 0.3)), g[0] if g else 1)
    env = np.convolve((np.abs(vo) > 0.02).astype(float), np.ones(int(0.12 * SR)) / int(0.12 * SR), 'same')
    duck = 1 - 0.62 * np.clip(env * 3, 0, 1)
    mix = music * duck * 0.8 + vo * 1.0 + fxs * 0.55
    fo = int(0.6 * SR); mix[-fo:] *= np.linspace(1, 0, fo)
    mix = np.tanh(mix / np.max(np.abs(mix)) * 1.6) * 0.92
    sf.write(f'out/{name}_v2.wav', np.stack([mix, mix], 1), SR)
    print(name, 'ok')

def pops(ts): return [(t, 'pop') for t in ts]
which = sys.argv[1:] or ['ad1', 'ad2', 'ad3']
if 'ad1' in which:
    build('ad1', 20, [
        (0.05, 2.7, 'N', "Is AI quietly making you worse at thinking?"),
        (2.95, 5.6, 'N', "MIT scanned the brains of fifty four people, writing essays."),
        (5.7, 6.9, 'B', "Easy. I'll write it!"),
        (7.05, 10.2, 'N', "The ChatGPT users? Weakest brain connectivity."),
        (11.0, 14.0, 'N', "Eighty three percent couldn't quote one line, they had just written."),
        (14.35, 16.6, 'N', "AI isn't the problem. Switching your brain off, is."),
        (16.95, 19.5, 'N', "Use AI without losing your edge. Launch price ends November thirtieth."),
    ], [(0, 'impact'), (0.55, 'scratch', .6)] + pops([2.9, 7.0, 7.8, 14.7, 17.3, 17.6]) + [(t, 'whoosh') for t in (2.6, 6.8, 10.6, 14.0, 16.6)] +
       [(7.4 + k * 0.55, 'heart') for k in range(6)] + [(9.9, 'riser'), (10.9, 'impact'), (12.0, 'ding', .7)],
       gaps=[(10.0, 10.9), (14.0, 14.35)], dark=[(0, 2.8), (7, 14.2)])
if 'ad2' in which:
    build('ad2', 20, [
        (0.05, 3.0, 'N', "There are already two kinds of people at work."),
        (3.3, 5.9, 'N', "One. People who let AI think for them."),
        (5.95, 6.95, 'B', "I've got this!"),
        (7.1, 10.4, 'N', "Two. People who make AI work, for their brain."),
        (10.95, 14.4, 'N', "Microsoft found: the more people trusted AI, the less they thought critically."),
        (14.75, 16.6, 'N', "So which one gets replaced first?"),
        (16.95, 19.5, 'N', "Be the second kind. Launch price ends November thirtieth."),
    ], [(0, 'impact')] + pops([3.4, 7.2, 11.3, 17.3]) + [(t, 'whoosh') for t in (3.0, 6.8, 10.6, 16.6)] +
       [(14.6, 'scratch', .8), (14.75, 'impact')] + [(3.5 + k * 0.6, 'heart', .7) for k in range(5)],
       gaps=[(14.3, 14.75)], dark=[(3.2, 7), (14.6, 16.8)])
if 'ad3' in which:
    build('ad3', 15, [
        (0.05, 1.75, 'N', "A thousand rupees off. Every course."),
        (1.8, 2.75, 'N', "Until November thirtieth."),
        (3.0, 6.9, 'N', "Four courses on AI and your brain. Nineteen ninety nine each."),
        (7.3, 8.7, 'C', "Want everything?"),
        (8.8, 10.9, 'N', "All four, plus the book. Five nine nine nine."),
        (11.2, 14.5, 'N', "Launch price ends November thirtieth. Start at unai labs dot com."),
    ], [(0, 'impact'), (0.6, 'ding', .6)] + pops([3.0, 3.35, 3.7, 4.05, 7.5, 11.2]) + [(t, 'whoosh') for t in (2.6, 7.0, 10.8)] +
       [(11.3 + k * 0.5, 'tick') for k in range(7)], dark=[])

if 'ad4' in which:
    build('ad4', 18, [
        (0.05, 2.7, 'N', "Your phone is rewiring your brain."),
        (3.0, 6.5, 'N', "Just having your phone nearby lowered people's thinking capacity, in tests."),
        (6.8, 9.7, 'N', "But a brain that can be rewired, can be rewired back."),
        (9.95, 12.7, 'C', "One week. No internet. Just chai."),
        (13.1, 17.4, 'N', "How to Be a Hippie. On Amazon, or the eBook for three ninety nine."),
    ], [(0, 'impact')] + pops([3.3+0.18*i for i in range(9)]) + [(t, 'whoosh') for t in (2.6, 6.7, 9.8, 12.9)] + [(7.6, 'ding', .7)],
       gaps=[(6.5, 6.8)], dark=[(0, 6.6)])
if 'ad5' in which:
    build('ad5', 18, [
        (0.05, 2.7, 'N', "Can't focus? It's not you."),
        (3.0, 6.9, 'N', "Every time you switch, part of your attention stays stuck on the last task."),
        (3.6, 4.6, 'B', "New message!"),
        (7.1, 10.4, 'N', "Focus isn't a personality. It's a skill. You can train it."),
        (10.7, 13.9, 'N', "Deep Focus. Twelve lessons to win your attention back."),
        (14.2, 17.4, 'N', "Launch price ends November thirtieth."),
    ], [(0, 'impact')] + [(3.4+0.5*k, 'ding', .5) for k in range(6)] + [(t, 'whoosh') for t in (2.6, 6.9, 10.5, 13.9)] + pops([8.0, 14.4]),
       gaps=[(6.8, 7.1)], dark=[(2.8, 7)])

if 'styles' in which:
    d=21.9; N=int(d*SR); mu=beat(d)[:N]; fx=np.zeros(N)
    for k in range(9): put(fx, k*2.4, sfx('whoosh'), .7); put(fx, k*2.4+0.15, sfx('pop'))
    mix=mu*0.8+fx*0.5; fo=int(0.6*SR); mix[-fo:]*=np.linspace(1,0,fo); mix=np.tanh(mix/np.max(np.abs(mix))*1.15)*0.95
    sf.write('out/styles_v2.wav', np.stack([mix,mix],1), SR); print('styles ok')
