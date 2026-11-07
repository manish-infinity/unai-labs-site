import subprocess, re, numpy as np, soundfile as sf, sys
SR=44100
F={'ad2':'06_32_47','ad3':'06_33_39','ad4':'06_34_04','ad5':'06_34_29'}
N={'ad2':6,'ad3':5,'ad4':4,'ad5':5}
def load(a):
    src=f'/mnt/user-data/uploads/Downloads/ElevenLabs_2026-11-07T{F[a]}_Manish_ivc_s50_sim75_v4.mp3'
    subprocess.run(['ffmpeg','-y','-loglevel','error','-i',src,'-ac','1','-ar',str(SR),f'tts/m_{a}.wav']); x,_=sf.read(f'tts/m_{a}.wav'); return x
def groups(x,n):
    fr=int(0.02*SR); e=np.array([np.sqrt(np.mean(x[i:i+fr]**2)) for i in range(0,len(x)-fr,fr)])
    on=e>0.012; segs=[]; i=0
    while i<len(on):
        if on[i]:
            j=i
            while j<len(on) and on[j]: j+=1
            segs.append([i*0.02,j*0.02]); i=j
        else: i+=1
    # merge tiny gaps (<0.12s) always
    m=[segs[0]]
    for s in segs[1:]:
        if s[0]-m[-1][1]<0.12: m[-1][1]=s[1]
        else: m.append(s)
    while len(m)>n:
        gaps=[m[k+1][0]-m[k][1] for k in range(len(m)-1)]; k=int(np.argmin(gaps)); m[k][1]=m[k+1][1]; del m[k+1]
    return m
if __name__=='__main__':
    for a in F:
        x=load(a); g=groups(x,N[a]); print(a, round(len(x)/SR,2), [(round(s,2),round(e,2),round(e-s,2)) for s,e in g])
