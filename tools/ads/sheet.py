import sys
from PIL import Image
name=sys.argv[1]; ts=sys.argv[2].split(',')
ims=[Image.open(f'out/{name}_t{t}.jpg').resize((270,480)) for t in ts]
W=Image.new('RGB',(270*len(ims),480))
for i,im in enumerate(ims): W.paste(im,(270*i,0))
W.save(f'out/sheet_{name}.jpg')
