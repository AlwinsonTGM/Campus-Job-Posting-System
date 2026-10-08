import { test } from '@e2e-dev/web';
import { expect } from 'e2e';

test('student views dashboard and jobs directory with zero overflow', async ({ app, screen, browser }) => {
  await app.open('/login.php?demo=student');
  await expect(browser).toHaveURL(/.*student\/dashboard\.php/);

  // Check zero overflow on whatever viewport target is active
  const dashboardOverflow = await browser.evaluate(() => {
    return document.documentElement.scrollWidth > window.innerWidth;
  });
  expect(dashboardOverflow).toBe(false);

  // Navigate to jobs directory
  await app.open('/student/jobs.php');
  await expect(browser).toHaveURL(/.*student\/jobs\.php/);

  const jobsOverflow = await browser.evaluate(() => {
    return document.documentElement.scrollWidth > window.innerWidth;
  });
  expect(jobsOverflow).toBe(false);
});
