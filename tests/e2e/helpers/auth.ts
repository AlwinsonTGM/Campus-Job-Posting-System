import { Page, expect } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import { execSync } from 'child_process';

export type UserRole = 'student' | 'employer' | 'admin';

const dataDir = path.resolve(__dirname, '../../../data');
const demoSeedsDir = path.resolve(__dirname, '../../../data/seeds/demo');

/**
 * Restores JSON datastore files on disk from pristine baseline seeds.
 */
export function restorePristineData(): void {
  const seedFiles = ['applications.json', 'categories.json', 'jobs.json', 'profile_requests.json', 'users.json', 'updates.json', 'devblogs.json'];
  if (fs.existsSync(demoSeedsDir)) {
    for (const file of seedFiles) {
      const src = path.join(demoSeedsDir, file);
      if (fs.existsSync(src)) {
        fs.copyFileSync(src, path.join(dataDir, file));
      }
    }
  }
}

/**
 * Synchronizes MySQL relational datastore back to uncontaminated baseline state.
 */
export function resetDatabaseFixtures(): void {
  try {
    const rootDir = path.resolve(__dirname, '../../../');
    const resetScript = path.join(rootDir, 'tools/reset-test-fixtures.php');
    if (fs.existsSync(resetScript)) {
      execSync(`C:\\xampp\\php\\php.exe "${resetScript}"`, { stdio: 'pipe' });
    }
  } catch (err) {
    console.warn('[resetDatabaseFixtures] Notice:', err);
  }
}

/**
 * Resets the datastore to clean default demo data fixtures both on disk and in MySQL/session.
 */
export async function resetDatastore(page: Page): Promise<void> {
  restorePristineData();
  resetDatabaseFixtures();
  await page.goto('/login.php?reset=1', { waitUntil: 'domcontentloaded' });
}

/**
 * Logs in as one of the demo roles using the fast single-hop demo bypass hook.
 */
export async function loginAsDemo(page: Page, role: UserRole): Promise<void> {
  await page.goto(`/login.php?demo=${role}`, { waitUntil: 'domcontentloaded' });
  if (role === 'student') {
    await expect(page).toHaveURL(/.*student\/dashboard\.php/);
  } else if (role === 'employer') {
    await expect(page).toHaveURL(/.*employer\/dashboard\.php/);
  } else if (role === 'admin') {
    await expect(page).toHaveURL(/.*admin\/(categories|reports|users)\.php|.*settings\.php/);
  }
}

/**
 * Logs out the current session.
 */
export async function logout(page: Page): Promise<void> {
  await page.goto('/logout.php', { waitUntil: 'domcontentloaded' });
  await expect(page).toHaveURL(/.*login\.php/);
}
