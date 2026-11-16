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
for _n in ['r1','r4','r5','r6']: CUTS[_n+'v'] = CUTS[_n]
# 16 Nov 2026 (REELS-004): 20 s value reels
CUTS['s1'] = dict(dur=20.0, hit=[0, 15.8], tick=[1.5 + 0.6 * i for i in range(5)] + [4.7 + 0.7 * i for i in range(6)], pop=[9.1, 10.0], ding=[10.7], whoosh=[1.4, 4.6, 9.0, 11.4, 15.6], riser=[3.7], dark=[(4.7, 9.0)])
CUTS['m1'] = dict(dur=20.0, hit=[0, 15.8], pop=[0.25, 0.95, 1.65, 2.35, 6.85, 13.5, 13.75, 14.0], ding=[3.9], scratch=[6.85], whoosh=[6.3, 12.8, 15.6], riser=[5.6], dark=[(6.3, 9.6)])
CUTS['g1'] = dict(dur=20.0, hit=[0, 15.8], heart=[3.0, 3.4, 3.8, 4.2, 4.6, 5.0], pop=[8.7], whoosh=[2.9, 5.6, 8.4, 11.7, 15.6], riser=[7.5], dark=[(11.8, 15.6)])
LEN = {'riser': 1.0, 'heart': 0.5, 'scratch': 0.45, 'ding': 0.8, 'impact': 0.9}
GAIN = {'tick': .5, 'ding': .6, 'whoosh': .8}
for name in sys.argv[1:] or CUTS:
    c = CUTS[name]; dur = c.get('dur', 16.0); N = int(dur * SR)
    mu = beat(dur, dark=c.get('dark', []))[:N]; fx = np.zeros(N)
    for kind, ts in c.items():
        if kind in ('dark', 'dur'): continue
        k = 'impact' if kind == 'hit' else kind
        for t in ts: put(fx, t, sfx(k, LEN.get(k, 0.3)), GAIN.get(k, 1))
    mix = mu * 0.8 + fx * 0.6; fo = int(0.6 * SR); mix[-fo:] *= np.linspace(1, 0, fo)
    mix = np.tanh(mix / np.max(np.abs(mix)) * 1.3) * 0.92
    sf.write(f'out/{name}.wav', np.stack([mix, mix], 1), SR); print(name, 'audio ok')
