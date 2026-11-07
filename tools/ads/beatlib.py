import numpy as np, soundfile as sf
SR = 44100
rng = np.random.default_rng(7)
def resample(x, sr):
    n = int(len(x) * SR / sr); return np.interp(np.linspace(0, len(x) - 1, n), np.arange(len(x)), x)

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

