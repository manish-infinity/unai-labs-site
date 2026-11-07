const { chromium } = require('playwright');
(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:1080,height:1920}});
for(const v of process.argv.slice(2)){await p.goto('file://'+__dirname+'/cover.html?v='+v);await p.evaluate(()=>document.fonts.ready);await p.waitForTimeout(300);await p.screenshot({path:`out/cover_${v}.jpg`,type:'jpeg',quality:92});}
await b.close();})();
