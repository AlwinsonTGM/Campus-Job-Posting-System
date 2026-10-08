import { test } from '@e2e-dev/web';
import { expect } from 'e2e';

test('admin agent explores user management and governance', async ({ app, agent, screen, browser }) => {
  await app.open('/login.php?demo=admin');
  await expect(browser).toHaveURL(/.*admin\/reports\.php/);

  // High-level AI role navigation
  await agent.act('click the navigation link for Users or User Directory');
  await expect(browser).toHaveURL(/.*admin\/users\.php/);

  // Deterministic layout invariant
  const overflow = await browser.evaluate(() => {
    return document.documentElement.scrollWidth > window.innerWidth ||
           (document.body ? document.body.scrollWidth > window.innerWidth : false);
  });
  expect(overflow).toBe(false);
});
