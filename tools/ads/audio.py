import numpy as np, sys, wave
SR=44100
def note(f): return 440*2**((f-69)/12)
def env(n,a=0.01,r=0.3):
    t=np.arange(n)/SR; e=np.minimum(1,t/a)*np.exp(-t/r); return e
def make(dur, cuts, dark):  # dark: list of (t0,t1) tense sections
    N=int(dur*SR); out=np.zeros(N); t=np.arange(N)/SR
    bpm=104; beat=60/bpm
    tense=np.zeros(N)
    for a,b in dark: tense[int(a*SR):int(b*SR)]=1
    k=int(0.4*SR); tense=np.convolve(tense,np.ones(k)/k,'same')
    # pads: minor (A minor) when tense, major lift (C major/F) otherwise
    minor=[57,60,64]; major=[60,64,67]; lift=[53,57,60]
    for ch,w in ((minor,tense),(major,1-tense)):
        for m in ch:
            f=note(m-12); s=(np.sin(2*np.pi*f*t)+0.4*np.sin(2*np.pi*f*2.003*t))*0.06
            out+=s*w
    # sub drone in tense parts
    out+=np.sin(2*np.pi*note(33)*t)*0.12*tense*(0.7+0.3*np.sin(2*np.pi*0.5*t))
    # kick + hat
    i=0
    while i*beat<dur:
        s0=int(i*beat*SR); n=int(0.25*SR); n=min(n,N-s0)
        tt=np.arange(n)/SR; f=55+90*np.exp(-tt*30)
        out[s0:s0+n]+=np.sin(2*np.pi*np.cumsum(f)/SR)*np.exp(-tt*9)*0.35
        h0=int((i+0.5)*beat*SR)
        if h0+2000<N: out[h0:h0+2000]+=np.random.randn(2000)*np.exp(-np.arange(2000)/250)*0.05*(1-tense[h0])
        i+=1
    # pluck arpeggio on the bright parts (8ths)
    arp=[72,76,79,84,79,76]; j=0
    while j*beat/2<dur:
        s0=int(j*beat/2*SR); n=min(int(0.35*SR),N-s0)
        if n>0:
            m=arp[j%len(arp)] if (1-tense[s0])>0.5 else [69,72,76][j%3]
            tt=np.arange(n)/SR; out[s0:s0+n]+=np.sin(2*np.pi*note(m)*tt)*np.exp(-tt*10)*0.07
        j+=1
    # whoosh at cuts, impact at 0
    for c in cuts:
        n=int(0.5*SR); s0=max(0,int((c-0.35)*SR)); n=min(n,N-s0)
        nz=np.random.randn(n); nz=np.convolve(nz,np.ones(30)/30,'same')
        e=np.sin(np.linspace(0,np.pi,n))**2; out[s0:s0+n]+=nz*e*0.5
    n=int(0.8*SR); tt=np.arange(n)/SR
    out[:n]+=(np.sin(2*np.pi*45*tt)*0.6+np.random.randn(n)*0.15)*np.exp(-tt*5)
    # fade out
    fo=int(1.0*SR); out[-fo:]*=np.linspace(1,0,fo)
    out=out/np.max(np.abs(out))*0.85
    return out
cfg={'ad1':(20,[2.8,7,10.8,14.2,16.8],[(0,2.8),(7,14.2)]),
     'ad2':(20,[3.2,7,10.8,14.6,16.8],[(3.2,7),(14.6,16.8)]),
     'ad3':(15,[2.7,7.2,11],[])}
for k,(d,c,dk) in cfg.items():
    a=make(d,c,dk); a=(a*32767).astype(np.int16); st=np.stack([a,a],1)
    w=wave.open(f'out/{k}.wav','wb'); w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes(st.tobytes()); w.close()
print('ok')
