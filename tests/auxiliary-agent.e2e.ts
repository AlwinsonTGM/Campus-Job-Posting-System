import { test } from '@e2e-dev/web';
import { expect } from 'e2e';

test('agent explores public legal and auxiliary content', async ({ app, agent, screen, browser }) => {
  await app.open('/faqs.php');
  await expect(browser).toHaveURL(/.*faqs\.php/);

  // High-level AI action exploring auxiliary footer / links
  await agent.act('click the link in the footer or navigation for Terms of Service');
  await expect(browser).toHaveURL(/.*terms\.php/);

  // Deterministic layout invariant
  const overflow = await browser.evaluate(() => {
    return document.documentElement.scrollWidth > window.innerWidth ||
           (document.body ? document.body.scrollWidth > window.innerWidth : false);
  });
  expect(overflow).toBe(false);
});
