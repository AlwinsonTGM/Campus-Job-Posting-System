import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright Configuration for Campus Job Posting System
 * Designed for single-worker sequential execution to preserve flat-file JSON datastore integrity.
 * Using bun for faster test runner startup.
 */
export default defineConfig({
  testDir: './tests/e2e',
  timeout: 90 * 1000,
  expect: {
    timeout: 10000,
  },
  fullyParallel: false,
  workers: 1,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 1,
  reporter: [
    ['list'],
    ['html', { open: 'never', outputFolder: 'playwright-report' }],
    ['json', { outputFile: 'test-results/results.json' }],
  ],
  use: {
    baseURL: 'http://127.0.0.1:8000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    headless: true,
    actionTimeout: 20 * 1000,
    navigationTimeout: 90 * 1000,
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  webServer: {
    command: 'php -S 127.0.0.1:8000',
    env: {
      PHP_CLI_SERVER_WORKERS: '4',
    },
    url: 'http://127.0.0.1:8000/login.php',
    reuseExistingServer: true,
    timeout: 30 * 1000,
  },
});
