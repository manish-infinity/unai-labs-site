import sys; sys.argv=['x','none']
import numpy as np, soundfile as sf, subprocess
from audio2 import *
def fit(x,slot):
    d=len(x)/SR
    if d<=slot: return x
    sf.write('tts/tmp.wav',x,SR); r=min(1.35,d/slot)
    subprocess.run(['ffmpeg','-y','-loglevel','error','-i','tts/tmp.wav','-filter:a',f'atempo={r:.3f}','tts/tmp2.wav']); y,_=sf.read('tts/tmp2.wav'); return y
def build_m(a,dur,parts,chars,fx,gaps,dark):
    m,_=sf.read(f'tts/m_{a}.wav'); N=int(dur*SR); vo=np.zeros(N)
    for t0,t1,s,e in parts:
        x=m[int((s-0.04)*SR):int((e+0.06)*SR)].copy(); x=fit(x,t1-t0); f=int(0.012*SR); x[:f]*=np.linspace(0,1,f); x[-f:]*=np.linspace(1,0,f)
        x=x/np.max(np.abs(x))*0.9; put(vo,t0,x)
        if t0+len(x)/SR>t1+0.05: print('  overflow',a,t0,round(len(x)/SR,2),t1)
    for t0,t1,w,txt in chars: put(vo,t0,say(txt,w,t1-t0))
    music=beat(dur,gaps=gaps,dark=dark)[:N]; fxs=np.zeros(N)
    for t,k,*g in fx: put(fxs,t,sfx(k,{'riser':1.0,'heart':0.5,'scratch':0.45,'ding':0.8,'impact':0.9}.get(k,0.3)),g[0] if g else 1)
    env=np.convolve((np.abs(vo)>0.02).astype(float),np.ones(int(0.12*SR))/int(0.12*SR),'same'); duck=1-0.65*np.clip(env*3,0,1)
    mix=music*duck*0.75+vo*1.05+fxs*0.5; fo=int(0.6*SR); mix[-fo:]*=np.linspace(1,0,fo)
    mix=np.tanh(mix/np.max(np.abs(mix))*1.15)*0.95; sf.write(f'out/{a}_manish.wav',np.stack([mix,mix],1),SR); print(a,'ok')
build_m('ad2',20.5,[(0.05,3.0,0.08,2.68),(3.3,5.9,3.22,5.8),(7.1,10.4,6.5,9.48),(10.95,14.4,10.16,14.74),(14.75,16.6,15.16,17.08),(16.95,20.3,17.4,20.66)],
  [(5.95,6.95,'B',"I've got this!")],[(0,'impact')]+pops([3.4,7.2,11.3,17.3])+[(t,'whoosh') for t in (3.0,6.8,10.6,16.6)]+[(14.6,'whoosh',.8),(14.75,'impact',.6)]+[(3.5+k*0.6,'heart',.7) for k in range(5)],
  [(14.3,14.75)],[(3.2,7),(14.6,16.8)])
build_m('ad3',15.5,[(0.05,2.0,0.3,2.6),(2.0,3.0,2.92,4.12),(3.05,6.95,4.66,8.44),(8.75,11.4,8.8,12.26),(11.45,14.9,12.72,16.68)],
  [(7.3,8.7,'C',"Want everything?")],[(0,'impact'),(0.6,'ding',.6)]+pops([3.0,3.35,3.7,4.05,7.5,11.2])+[(t,'whoosh') for t in (2.6,7.0,10.8)]+[(11.3+k*0.5,'tick') for k in range(7)],[],[])
build_m('ad4',18,[(0.05,2.7,0.12,2.26),(3.0,6.5,2.98,7.1),(6.8,9.7,7.64,10.82),(13.1,17.4,11.54,15.36)],
  [(9.95,12.7,'C',"One week. No internet. Just chai.")],[(0,'impact')]+pops([3.3+0.18*i for i in range(9)])+[(t,'whoosh') for t in (2.6,6.7,9.8,12.9)]+[(7.6,'ding',.7)],[(6.5,6.8)],[(0,6.6)])
build_m('ad5',18,[(0.05,2.7,0.16,2.32),(3.0,6.9,2.9,6.76),(7.1,10.4,7.52,11.2),(10.7,13.9,11.72,14.84),(14.2,17.4,15.34,17.12)],
  [],[(0,'impact')]+[(3.4+0.5*k,'ding',.5) for k in range(6)]+[(t,'whoosh') for t in (2.6,6.9,10.5,13.9)]+pops([8.0,14.4]),[(6.8,7.1)],[(2.8,7)])
