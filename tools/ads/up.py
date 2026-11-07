import cv2, sys, time
from cv2 import dnn_superres
sr=dnn_superres.DnnSuperResImpl_create(); sr.readModel('EDSR_x4.pb'); sr.setModel('edsr',4)
for n in sys.argv[1:]:
    t=time.time(); im=cv2.imread(f'src/{n}.png'); out=sr.upsample(im); cv2.imwrite(f'src/{n}_x4.png',out); print(n,out.shape,round(time.time()-t))
