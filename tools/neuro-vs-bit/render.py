import json, math, subprocess, numpy as np, soundfile as sf, sys
from PIL import Image, ImageDraw, ImageFont, ImageFilter
W,H,FPS=1080,1920,24
F='/usr/share/fonts/truetype/google-fonts/'
def font(n,s): return ImageFont.truetype(F+n,s)
NAVY=(15,30,61); TEAL=(40,170,165); BG1=(244,247,255); BG2=(226,234,250)
a,sr=sf.read('ep1.wav'); a=a if a.ndim==1 else a.mean(1)
lines=json.load(open('lines.json')); dur=len(a)/sr+1.6; N=int(dur*FPS)
hop=sr//FPS; env=np.array([np.sqrt(np.mean(a[i*hop:(i+1)*hop]**2)) if i*hop<len(a) else 0 for i in range(N)])
env=np.convolve(env,np.ones(2)/2,'same'); env=env/(env.max() or 1)
def speaker(t):
    for l in lines:
        if l['start']<=t<=l['end']+0.12: return l
    return None
bg=Image.new('RGB',(W,H),BG1); d=ImageDraw.Draw(bg)
for y in range(H):
    k=y/H; d.line((0,y,W,y),fill=tuple(int(BG1[i]*(1-k)+BG2[i]*k) for i in range(3)))
def draw_neuro(im,cx,cy,s,mouth,blink,active):
    d=ImageDraw.Draw(im); r=int(170*s)
    pink=(246,150,175) if active else (236,170,188); dark=(214,96,130)
    # brain lobes
    for dx,dy,rr in [(-0.55,-0.25,0.62),(0.55,-0.25,0.62),(-0.62,0.25,0.55),(0.62,0.25,0.55),(0,-0.45,0.6),(0,0.3,0.62)]:
        x,y,q=cx+dx*r,cy+dy*r,rr*r; d.ellipse((x-q,y-q,x+q,y+q),fill=pink)
    for i in range(-2,3):
        x=cx+i*r*0.32; d.arc((x-r*0.25,cy-r*0.9,x+r*0.25,cy-r*0.2),200,340,fill=dark,width=max(3,int(7*s)))
    d.line((cx,cy-r*0.95,cx,cy-r*0.35),fill=dark,width=max(3,int(7*s)))
    ey=cy+r*0.05
    for ex in (cx-r*0.32,cx+r*0.32):
        if blink: d.line((ex-r*0.12,ey,ex+r*0.12,ey),fill=NAVY,width=int(8*s))
        else:
            d.ellipse((ex-r*0.14,ey-r*0.17,ex+r*0.14,ey+r*0.17),fill='white'); d.ellipse((ex-r*0.07,ey-r*0.07,ex+r*0.09,ey+r*0.11),fill=NAVY)
    my=cy+r*0.42; mw=r*0.26; mh=r*(0.03+0.22*mouth)
    d.ellipse((cx-mw,my-mh,cx+mw,my+mh),fill=(120,30,55)) if mouth>0.08 else d.arc((cx-mw,my-r*0.12,cx+mw,my+r*0.08),20,160,fill=(120,30,55),width=int(8*s))
    for sx in (-1,1): d.ellipse((cx+sx*r*0.62-r*0.1,cy+r*0.3-r*0.06,cx+sx*r*0.62+r*0.1,cy+r*0.3+r*0.06),fill=(250,120,150))
def draw_bit(im,cx,cy,s,mouth,blink,active):
    d=ImageDraw.Draw(im); r=int(165*s); body=(250,250,252) if active else (232,234,240); edge=(150,160,180)
    d.line((cx,cy-r*1.05,cx,cy-r*0.75),fill=edge,width=int(10*s)); d.ellipse((cx-r*0.11,cy-r*1.22,cx+r*0.11,cy-r*1.0),fill=TEAL)
    d.rounded_rectangle((cx-r,cy-r*0.78,cx+r,cy+r*0.78),radius=int(r*0.35),fill=body,outline=edge,width=int(6*s))
    d.rounded_rectangle((cx-r*0.78,cy-r*0.55,cx+r*0.78,cy+r*0.55),radius=int(r*0.22),fill=(22,36,70))
    ey=cy-r*0.12
    for ex in (cx-r*0.33,cx+r*0.33):
        if blink: d.line((ex-r*0.13,ey,ex+r*0.13,ey),fill=TEAL,width=int(9*s))
        else: d.rounded_rectangle((ex-r*0.13,ey-r*0.13,ex+r*0.13,ey+r*0.13),radius=int(r*0.05),fill=(90,230,220))
    my=cy+r*0.28; mw=r*0.3; mh=r*(0.02+0.14*mouth)
    d.rounded_rectangle((cx-mw,my-mh,cx+mw,my+mh),radius=int(r*0.04),fill=(90,230,220))
    for sx in (-1,1): d.rounded_rectangle((cx+sx*r*1.0-r*0.12,cy-r*0.2,cx+sx*r*1.0+r*0.12,cy+r*0.2),radius=int(r*0.05),fill=edge)
def wrap(d,text,f,w):
    out=[];cur=''
    for wd in text.split():
        t=(cur+' '+wd).strip()
        if d.textlength(t,font=f)<=w: cur=t
        else: out.append(cur); cur=wd
    out.append(cur); return out
labels={1:"MYTH",2:"Brain scans: almost all regions active",4:"2% of body weight  |  ~20% of energy",5:"SCIENCE"}
cap=font('Poppins-Bold.ttf',62); small=font('Poppins-Medium.ttf',40); hook=font('Poppins-Bold.ttf',92)
ff=subprocess.Popen(['ffmpeg','-y','-loglevel','error','-f','rawvideo','-pix_fmt','rgb24','-s',f'{W}x{H}','-r',str(FPS),'-i','-','-i','ep1.wav','-c:v','libx264','-preset','veryfast','-crf','20','-pix_fmt','yuv420p','-af','apad','-c:a','aac','-b:a','128k','-t',f'{N/FPS:.2f}','ep1.mp4'],stdin=subprocess.PIPE)
for i in range(N):
    t=i/FPS; im=bg.copy(); d=ImageDraw.Draw(im); sp=speaker(t)
    who=sp['who'] if sp else None; idx=lines.index(sp)+1 if sp else 0
    m=env[i] if i<len(env) else 0
    blink=(i%(FPS*4) in (0,1)); blink2=((i+30)%(FPS*5) in (0,1))
    bo=math.sin(t*2.2)*10; bo2=math.sin(t*2.0+1)*10
    draw_bit(im,300,980+bo,1.12 if who=='bit' else 0.98,m if who=='bit' else 0,blink,who=='bit')
    draw_neuro(im,780,1000+bo2,1.12 if who=='neuro' else 0.98,m if who=='neuro' else 0,blink2,who=='neuro')
    d.text((300,1210),"BIT",font=small,fill=NAVY,anchor='mm'); d.text((780,1240),"NEURO",font=small,fill=NAVY,anchor='mm')
    d.text((W//2,150),"NEURO vs BIT",font=font('Poppins-Bold.ttf',54),fill=NAVY,anchor='mm')
    if idx in labels:
        lb=labels[idx]; col=(200,40,60) if lb=="MYTH" else (TEAL if lb=="SCIENCE" else NAVY)
        tw=d.textlength(lb,font=small)+60; d.rounded_rectangle((W/2-tw/2,600,W/2+tw/2,680),radius=40,fill='white',outline=col,width=4); d.text((W//2,640),lb,font=small,fill=col,anchor='mm')
    if sp:
        ls=wrap(d,sp['text'],cap,W-160); y=1420
        for l in ls: d.text((W//2,y),l,font=cap,fill=NAVY,anchor='mm',stroke_width=0); y+=82
    if t<1.2:
        ov=Image.new('RGBA',(W,H),(15,30,61,235)); od=ImageDraw.Draw(ov); y=780
        for l in ["YOU ONLY USE","10% OF YOUR","BRAIN?"]: od.text((W//2,y),l,font=hook,fill='white',anchor='mm'); y+=120
        im=Image.alpha_composite(im.convert('RGBA'),ov).convert('RGB')
    if t>lines[-1]['start']:
        d=ImageDraw.Draw(im); d.text((W//2,1800),"Sources: Raichle & Gusnard, PNAS 2002; Beyerstein, Mind Myths 1999",font=font('Poppins-Regular.ttf',28),fill=(90,100,125),anchor='mm')
    if t>lines[-1]['end']+0.2:
        im=Image.new('RGB',(W,H),NAVY); d=ImageDraw.Draw(im)
        d.text((W//2,860),"Full lesson at",font=small,fill=(200,210,230),anchor='mm'); d.text((W//2,950),"unai-labs.com",font=font('Poppins-Bold.ttf',88),fill=(90,220,212),anchor='mm')
        d.text((W//2,1060),"Your brain is more powerful than any AI.",font=small,fill='white',anchor='mm')
    ff.stdin.write(im.tobytes())
ff.stdin.close(); ff.wait(); print('done',N,'frames')
