const { chromium } = require('../../node_modules/playwright-core');
const path = require('path');
(async()=>{
 const browser = await chromium.launch({headless:true});
 const page = await browser.newPage({viewport:{width:1440,height:1000}, deviceScaleFactor:1});
 await page.goto('http://127.0.0.1:8015/',{waitUntil:'networkidle',timeout:30000});
 await page.evaluate(()=>{localStorage.setItem('theme','light');});
 await page.reload({waitUntil:'networkidle'});
 await page.screenshot({path:path.join(__dirname,'website-home.png')});
 console.log((await page.locator('body').innerText()).slice(0,500));
 await browser.close();
})();
