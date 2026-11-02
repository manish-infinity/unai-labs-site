"""Brand card generator for @un_ai_labs (1080x1350). Usage: python ig_card.py out.png "KICKER" "HEADLINE" "BODY" "SOURCE" """
import sys, textwrap
from PIL import Image, ImageDraw, ImageFont
W,H=1080,1350; NAVY=(15,30,61); TEAL=(64,196,190); WHITE=(255,255,255); GREY=(170,182,204)
F='/usr/share/fonts/opentype/inter/'
def f(n,s): return ImageFont.truetype(F+n,s)
def wrap(d,text,font,width):
    words=text.split(); lines=[]; cur=''
    for w in words:
        t=(cur+' '+w).strip()
        if d.textlength(t,font=font)<=width: cur=t
        else: lines.append(cur); cur=w
    lines.append(cur); return lines
out,kicker,head,body,src=sys.argv[1:6]
im=Image.new('RGB',(W,H),NAVY); d=ImageDraw.Draw(im)
d.ellipse((W-420,-260,W+260,420),outline=(40,62,104),width=3)
x=96; y=150
d.text((x,y),kicker.upper(),font=f('Inter-SemiBold.otf',34),fill=TEAL); y+=90
hf=f('InterDisplay-Bold.otf',84)
for l in wrap(d,head,hf,W-2*x): d.text((x,y),l,font=hf,fill=WHITE); y+=100
y+=40; d.line((x,y,x+120,y),fill=TEAL,width=6); y+=60
bf=f('Inter-Regular.otf',42)
for para in body.split('|'):
    for l in wrap(d,para.strip(),bf,W-2*x): d.text((x,y),l,font=bf,fill=(225,232,245)); y+=58
    y+=26
d.text((x,H-170),src,font=f('Inter-Regular.otf',28),fill=GREY)
d.text((x,H-110),'un^AI  |  unai-labs.com',font=f('Inter-SemiBold.otf',34),fill=TEAL)
im.save(out); print(out, y)
