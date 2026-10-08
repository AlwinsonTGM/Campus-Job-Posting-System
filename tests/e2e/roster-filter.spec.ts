import { test, expect } from '@playwright/test';

test.describe('Employer Roster - Schedule Availability & Filter/Rank Suite', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login.php?demo=employer');
        await expect(page).toHaveURL(/employer\/dashboard\.php/);
    });

    test('1. Roster table displays Schedule Availability column with properly aligned headers and non-overlapping action buttons', async ({ page }) => {
        await page.goto('/employer/applicants.php');
        await page.waitForLoadState('networkidle');

        // Verify all 7 table headers exist and are visible
        const headers = page.locator('table.table-paper-roster thead th');
        await expect(headers).toHaveCount(7);
        await expect(headers.nth(0)).toContainText('Candidate Profile');
        await expect(headers.nth(1)).toContainText('Target Vacancy');
        await expect(headers.nth(2)).toContainText('Degree Program');
        await expect(headers.nth(3)).toContainText('Schedule Availability');
        await expect(headers.nth(4)).toContainText('Applied Date');
        await expect(headers.nth(5)).toContainText('Status');
        await expect(headers.nth(6)).toContainText('Actions');

        // Verify Schedule Availability badges render with institutional styling
        const availBadges = page.locator('table.table-paper-roster tbody td[data-label="Schedule Availability"]');
        expect(await availBadges.count()).toBeGreaterThan(0);
        await expect(availBadges.first()).toContainText(/Limited|Moderate|Strong|No Shift/i);

        // Verify no overlap between Status badge and Actions buttons
        const firstRow = page.locator('table.table-paper-roster tbody tr').first();
        const statusBox = await firstRow.locator('td[data-label="Status"] [class*="badge-status"]').boundingBox();
        const resumeBtnBox = await firstRow.locator('a[href*="view-resume.php"]').boundingBox();

        expect(statusBox).not.toBeNull();
        expect(resumeBtnBox).not.toBeNull();
        if (statusBox && resumeBtnBox) {
            // Status right edge must be to the left of the resume button left edge
            expect(statusBox.x + statusBox.width).toBeLessThanOrEqual(resumeBtnBox.x);
        }
    });

    test('2. Filter roster by Schedule Availability fit tier (Moderate vs Limited)', async ({ page }) => {
        await page.goto('/employer/applicants.php');
        await page.waitForLoadState('networkidle');

        // Select Moderate Fit (12–16h)
        const fitSelect = page.locator('#filter-fit');
        await fitSelect.selectOption('moderate');
        await page.waitForTimeout(500);

        // Check that all displayed candidates have Moderate (12h)
        const rows = page.locator('table.table-paper-roster tbody tr');
        const count = await rows.count();
        expect(count).toBeGreaterThan(0);
        for (let i = 0; i < count; i++) {
            await expect(rows.nth(i).locator('td[data-label="Schedule Availability"]')).toContainText('Moderate (12h)');
        }

        // Switch to Limited Fit (4–8h)
        await fitSelect.selectOption('limited');
        await page.waitForTimeout(500);

        const limitedRows = page.locator('table.table-paper-roster tbody tr');
        const limitedCount = await limitedRows.count();
        expect(limitedCount).toBeGreaterThan(0);
        for (let i = 0; i < limitedCount; i++) {
            await expect(limitedRows.nth(i).locator('td[data-label="Schedule Availability"]')).toContainText('Limited (8h)');
        }
    });

    test('3. Rank candidates based on Availability (Highest Availability First)', async ({ page }) => {
        await page.goto('/employer/applicants.php');
        await page.waitForLoadState('networkidle');

        const sortSelect = page.locator('#filter-sort');
        await sortSelect.selectOption('sched_desc');
        await page.waitForTimeout(500);

        // First row should be Moderate Fit (12h), before Limited Fit (8h)
        const firstRowAdvisory = page.locator('table.table-paper-roster tbody tr').first().locator('td[data-label="Schedule Availability"]');
        await expect(firstRowAdvisory).toContainText('Moderate (12h)');
    });

    test('4. Rank candidates based on Availability (Conflict Risk First)', async ({ page }) => {
        await page.goto('/employer/applicants.php');
        await page.waitForLoadState('networkidle');

        const sortSelect = page.locator('#filter-sort');
        await sortSelect.selectOption('sched_asc');
        await page.waitForTimeout(500);

        // First row should be Limited Fit (8h) or 0h rather than Moderate
        const firstRowAdvisory = page.locator('table.table-paper-roster tbody tr').first().locator('td[data-label="Schedule Availability"]');
        await expect(firstRowAdvisory).toContainText('Limited (8h)');
    });
});
