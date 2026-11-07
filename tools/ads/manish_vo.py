import sys; sys.argv=['x','none']
import numpy as np, soundfile as sf, subprocess
from audio2 import *
src='/mnt/user-data/uploads/Downloads/ElevenLabs_2026-11-07T05_41_21_Manish_ivc_s50_sim75_v4 (1).mp3'
subprocess.run(['ffmpeg','-y','-loglevel','error','-i',src,'-ac','1','-ar',str(SR),'tts/manish.wav'])
m,_=sf.read('tts/manish.wav')
def seg(a,b):
    x=m[int(a*SR):int(b*SR)]; nz=np.where(np.abs(x)>0.012)[0]
    mg=int(0.04*SR); x=x[max(0,nz[0]-mg):min(len(x),nz[-1]+mg)]
    f=int(0.015*SR); x=x.copy(); x[:f]*=np.linspace(0,1,f); x[-f:]*=np.linspace(1,0,f); return x
def fit(x,slot):
    d=len(x)/SR
    if d<=slot: return x
    sf.write('tts/tmp.wav',x,SR); r=min(1.35,d/slot)
    subprocess.run(['ffmpeg','-y','-loglevel','error','-i','tts/tmp.wav','-filter:a',f'atempo={r:.3f}','tts/tmp2.wav']); y,_=sf.read('tts/tmp2.wav'); return y
gap=np.zeros(int(0.18*SR))
parts=[(0.02,2.8,[seg(0,3.15)]),(2.95,5.95,[seg(3.15,7.0)]),(7.05,10.3,[seg(7.0,8.57),gap,seg(8.57,10.49)]),
       (10.95,14.1,[seg(10.49,14.09)]),(14.25,16.8,[seg(14.09,15.62),gap,seg(15.62,17.5)]),(16.95,19.0,[seg(17.5,19.81)]),(19.15,21.25,[seg(19.81,22.05)])]
dur=21.5; N=int(dur*SR); vo=np.zeros(N)
for t0,t1,xs in parts:
    x=fit(np.concatenate(xs),t1-t0); f=int(0.01*SR); x[:f]*=np.linspace(0,1,f); x[-f:]*=np.linspace(1,0,f); x=x/np.max(np.abs(x))*0.9; put(vo,t0,x)
put(vo,6.0,say("Easy. I'll write it!",'B',0.95))
# presence boost 2-5 kHz on voice (simple: add band-passed copy)
from numpy.fft import rfft,irfft
music=beat(dur,gaps=[(10.0,10.9),(14.0,14.25)],dark=[(0,2.8),(7,14.2)])[:N]
fxs=np.zeros(N)
fx=[(0,'impact'),(0.55,'whoosh',.8)]+pops([2.9,7.0,7.8,14.7,17.3,17.6])+[(t,'whoosh') for t in (2.6,6.8,10.6,14.0,16.6)]+[(7.4+k*0.55,'heart') for k in range(6)]+[(9.9,'riser'),(10.8,'impact',.55),(12.0,'ding',.7)]
for t,k,*g in fx: put(fxs,t,sfx(k,{'riser':1.0,'heart':0.5,'scratch':0.45,'ding':0.8,'impact':0.9}.get(k,0.3)),g[0] if g else 1)
env=np.convolve((np.abs(vo)>0.02).astype(float),np.ones(int(0.12*SR))/int(0.12*SR),'same'); duck=1-0.65*np.clip(env*3,0,1)
mix=music*duck*0.75+vo*1.05+fxs*0.5; fo=int(0.6*SR); mix[-fo:]*=np.linspace(1,0,fo)
mix=np.tanh(mix/np.max(np.abs(mix))*1.15)*0.95
np.save('out/c_vo.npy',vo);np.save('out/c_mu.npy',music);np.save('out/c_fx.npy',fxs);sf.write('out/ad1_manish.wav',np.stack([mix,mix],1),SR); print('ok')
