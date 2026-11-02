import soundfile as sf, numpy as np, json
from kokoro_onnx import Kokoro
k=Kokoro('../../kokoro.onnx','../../voices.bin')
lines=[("bit","Fact! Humans only use ten percent of their brain!"),
("neuro","Nope. Brain scans show almost every region active across a normal day."),
("bit","So the other ninety percent..."),
("neuro","Doesn't exist. I'm two percent of your body weight and I burn about twenty percent of your energy. Evolution doesn't pay for idle parts."),
("bit","Updating my files."),
("neuro","Full lesson at unai-labs dot com.")]
V={"bit":("am_puck",1.08),"neuro":("af_heart",1.0)}
segs=[];meta=[];t=0.0;sr=24000
for who,text in lines:
    v,sp=V[who]
    try: a,sr=k.create(text,voice=v,speed=sp,lang="en-us")
    except Exception as e: a,sr=k.create(text,voice="am_michael" if who=="bit" else "af_bella",speed=sp,lang="en-us")
    gap=np.zeros(int(0.25*sr),dtype=np.float32)
    meta.append({"who":who,"text":text,"start":t,"end":t+len(a)/sr}); t+=len(a)/sr+0.25
    segs+= [a.astype(np.float32),gap]
sf.write("ep1.wav",np.concatenate(segs),sr); json.dump(meta,open("lines.json","w"),indent=1); print(round(t,2),"s")
