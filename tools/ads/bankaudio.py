# Audio for the no-voice reel bank: original beat + a sound on every visual change.
# Safe for paid boosts (all synthesised here). Trending sounds are added in the Instagram app.
import sys, numpy as np, soundfile as sf
sys.path.insert(0, '.')
from beatlib import beat, sfx, put, SR
CUTS = {
 'r1': dict(hit=[0, 5.0, 13.4], pop=[0.2, 2.7, 3.0, 5.1, 5.6, 7.7, 10.0], whoosh=[2.6, 4.9, 9.6, 13.2], riser=[3.9], ding=[5.0], dark=[(0, 5)]),
 'r2': dict(hit=[0, 13.4], tick=[1.0, 3.3, 5.6, 7.9], pop=[0.4, 10.5], ding=[1.0, 3.3, 5.6, 7.9, 10.0], whoosh=[10.3, 13.2], riser=[9.0], dark=[]),
 'r3': dict(hit=[0, 3.7, 13.4], pop=[0.3, 1.0, 6.3, 7.9, 10.7, 11.3], whoosh=[5.6, 10.4, 13.2], tick=[6.3 + k * 0.1 for k in range(10)] + [7.9 + k * 0.1 for k in range(10)], scratch=[3.6], dark=[(3.6, 6)]),
 'r4': dict(hit=[0, 4.6, 13.0], pop=[0.35, 4.9, 9.35], whoosh=[3.0, 8.6, 12.8], riser=[2.2], ding=[4.9], scratch=[3.1], dark=[(0, 3.2)]),
 'r5': dict(hit=[0, 12.9], pop=[3.5, 6.5, 9.7], whoosh=[3.4, 6.4, 9.6, 12.7], heart=[9.8, 10.4, 11.0], ding=[7.4], dark=[(9.7, 12.7)]),
 'r6': dict(hit=[0, 9.6, 13.4], pop=[0.5, 6.5, 9.9, 10.1, 11.4], whoosh=[2.4, 6.3, 13.2], riser=[2.6], tick=[9.9 + k * 0.1 for k in range(12)], dark=[(6.5, 9.6)]),
}
LEN = {'riser': 1.0, 'heart': 0.5, 'scratch': 0.45, 'ding': 0.8, 'impact': 0.9}
GAIN = {'tick': .5, 'ding': .6, 'whoosh': .8}
for name in sys.argv[1:] or CUTS:
    c = CUTS[name]; dur = 16.0; N = int(dur * SR)
    mu = beat(dur, dark=c.get('dark', []))[:N]; fx = np.zeros(N)
    for kind, ts in c.items():
        if kind == 'dark': continue
        k = 'impact' if kind == 'hit' else kind
        for t in ts: put(fx, t, sfx(k, LEN.get(k, 0.3)), GAIN.get(k, 1))
    mix = mu * 0.8 + fx * 0.6; fo = int(0.6 * SR); mix[-fo:] *= np.linspace(1, 0, fo)
    mix = np.tanh(mix / np.max(np.abs(mix)) * 1.3) * 0.92
    sf.write(f'out/{name}.wav', np.stack([mix, mix], 1), SR); print(name, 'audio ok')
