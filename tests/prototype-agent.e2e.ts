import { test } from '@e2e-dev/web';
import { expect } from 'e2e';

test('agent explores student jobs directory', async ({ app, agent, screen, browser }) => {
  await app.open('/login.php?demo=student');
  await expect(browser).toHaveURL(/.*student\/dashboard\.php/);

  // High-level AI goal
  await agent.act('click the link or menu item for Jobs');
  await expect(browser).toHaveURL(/.*student\/jobs\.php/);

  // Deterministic invariant check
  const overflow = await browser.evaluate(() => {
    return document.documentElement.scrollWidth > window.innerWidth;
  });
  expect(overflow).toBe(false);
});
