import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo } from './helpers/auth';

interface ViewportConfig {
  name: string;
  width: number;
  height: number;
  category: 'mobile' | 'tablet_horizontal' | 'web_full' | 'half_screen';
}

const VIEWPORTS: ViewportConfig[] = [
  { name: 'Mobile 375px', width: 375, height: 812, category: 'mobile' },
  { name: 'Mobile 414px', width: 414, height: 896, category: 'mobile' },
  { name: 'Half Screen 720px', width: 720, height: 900, category: 'half_screen' },
  { name: 'Half Screen 800px', width: 800, height: 900, category: 'half_screen' },
  { name: 'Half Screen 960px', width: 960, height: 900, category: 'half_screen' },
  { name: 'Tablet Horizontal 1024px', width: 1024, height: 768, category: 'tablet_horizontal' },
  { name: 'Tablet Horizontal 1180px', width: 1180, height: 820, category: 'tablet_horizontal' },
  { name: 'Web Desktop Full 1440px', width: 1440, height: 900, category: 'web_full' },
];

const PUBLIC_ROUTES = [
  '/',
  '/index.php',
  '/about-us.php',
  '/faqs.php',
  '/privacy.php',
  '/terms.php',
  '/updates.php',
  '/update-detail.php?id=upd_1',
  '/login.php',
  '/register.php',
  '/forgot-pass.php',
];

const STUDENT_ROUTES = [
  '/student/dashboard.php',
  '/student/jobs.php',
  '/student/job-details.php?id=1',
  '/student/apply.php?id=1',
  '/student/my-applications.php',
  '/settings.php',
];

const EMPLOYER_ROUTES = [
  '/employer/dashboard.php',
  '/employer/create-job.php',
  '/employer/edit-job.php?id=1',
  '/employer/applicants.php',
  '/employer/review-app.php?id=1',
  '/employer/updates.php',
  '/settings.php',
];

const ADMIN_ROUTES = [
  '/admin/users.php',
  '/admin/categories.php',
  '/admin/reports.php',
  '/admin/updates.php',
  '/settings.php',
];

test.beforeAll(async ({ browser }) => {
  const page = await browser.newPage();
  await resetDatastore(page);
  await page.close();
});

test.describe('Cross-Viewport Audit', () => {
  for (const vp of VIEWPORTS) {
    test.describe(`Viewport: ${vp.name}`, () => {
      test.use({ viewport: { width: vp.width, height: vp.height } });

      test.beforeEach(async () => {
        test.setTimeout(90000);
      });

      test(`Public pages at ${vp.width}px`, async ({ page }) => {
        for (const route of PUBLIC_ROUTES) {
          await page.goto(route, { waitUntil: 'domcontentloaded' });
          await page.waitForLoadState('domcontentloaded');

          const data = await page.evaluate(() => {
            const docWidth = document.documentElement.scrollWidth;
            const winWidth = window.innerWidth;
            const hasHScroll = docWidth > winWidth;
            const offColors = [];
            for (const el of Array.from(document.querySelectorAll('a, button, .badge, .btn, h1, h2, h3, h4, h5, h6, .card, span, p'))) {
              const s = window.getComputedStyle(el);
              if (s.color === 'rgb(13, 110, 253)' || s.backgroundColor === 'rgb(13, 110, 253)' || s.borderColor === 'rgb(13, 110, 253)') {
                offColors.push(el.tagName + '.' + Array.from(el.classList).join('.'));
              }
            }
            return { hasHScroll, docWidth, winWidth, offColors };
          });

          if (data.hasHScroll || data.offColors.length > 0) {
            console.log(`ISSUE in ${route} at ${vp.name}:`, data);
          }
          expect(data.hasHScroll).toBe(false);
        }
      });

      test(`Student pages at ${vp.width}px`, async ({ page }) => {
        await loginAsDemo(page, 'student');
        for (const route of STUDENT_ROUTES) {
          await page.goto(route, { waitUntil: 'domcontentloaded' });
          await page.waitForLoadState('domcontentloaded');

          const data = await page.evaluate(() => {
            const docWidth = document.documentElement.scrollWidth;
            const winWidth = window.innerWidth;
            const hasHScroll = docWidth > winWidth;
            const offColors = [];
            for (const el of Array.from(document.querySelectorAll('a, button, .badge, .btn, h1, h2, h3, h4, h5, h6, .card, span, p'))) {
              const s = window.getComputedStyle(el);
              if (s.color === 'rgb(13, 110, 253)' || s.backgroundColor === 'rgb(13, 110, 253)' || s.borderColor === 'rgb(13, 110, 253)') {
                offColors.push(el.tagName + '.' + Array.from(el.classList).join('.'));
              }
            }
            return { hasHScroll, docWidth, winWidth, offColors };
          });

          if (data.hasHScroll || data.offColors.length > 0) {
            console.log(`ISSUE in ${route} at ${vp.name}:`, data);
          }
          expect(data.hasHScroll).toBe(false);
        }
      });

      test(`Employer pages at ${vp.width}px`, async ({ page }) => {
        await loginAsDemo(page, 'employer');
        for (const route of EMPLOYER_ROUTES) {
          await page.goto(route, { waitUntil: 'domcontentloaded' });
          await page.waitForLoadState('domcontentloaded');

          const data = await page.evaluate(() => {
            const docWidth = document.documentElement.scrollWidth;
            const winWidth = window.innerWidth;
            const hasHScroll = docWidth > winWidth;
            const offColors = [];
            for (const el of Array.from(document.querySelectorAll('a, button, .badge, .btn, h1, h2, h3, h4, h5, h6, .card, span, p'))) {
              const s = window.getComputedStyle(el);
              if (s.color === 'rgb(13, 110, 253)' || s.backgroundColor === 'rgb(13, 110, 253)' || s.borderColor === 'rgb(13, 110, 253)') {
                offColors.push(el.tagName + '.' + Array.from(el.classList).join('.'));
              }
            }
            return { hasHScroll, docWidth, winWidth, offColors };
          });

          if (data.hasHScroll || data.offColors.length > 0) {
            console.log(`ISSUE in ${route} at ${vp.name}:`, data);
          }
          expect(data.hasHScroll).toBe(false);
        }
      });

      test(`Admin pages at ${vp.width}px`, async ({ page }) => {
        await loginAsDemo(page, 'admin');
        for (const route of ADMIN_ROUTES) {
          await page.goto(route, { waitUntil: 'domcontentloaded' });
          await page.waitForLoadState('domcontentloaded');

          const data = await page.evaluate(() => {
            const docWidth = document.documentElement.scrollWidth;
            const winWidth = window.innerWidth;
            const hasHScroll = docWidth > winWidth;
            const offColors = [];
            for (const el of Array.from(document.querySelectorAll('a, button, .badge, .btn, h1, h2, h3, h4, h5, h6, .card, span, p'))) {
              const s = window.getComputedStyle(el);
              if (s.color === 'rgb(13, 110, 253)' || s.backgroundColor === 'rgb(13, 110, 253)' || s.borderColor === 'rgb(13, 110, 253)') {
                offColors.push(el.tagName + '.' + Array.from(el.classList).join('.'));
              }
            }
            return { hasHScroll, docWidth, winWidth, offColors };
          });

          if (data.hasHScroll || data.offColors.length > 0) {
            console.log(`ISSUE in ${route} at ${vp.name}:`, data);
          }
          expect(data.hasHScroll).toBe(false);
        }
      });
    });
  }
});
