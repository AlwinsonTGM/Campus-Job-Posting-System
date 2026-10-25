import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo } from './helpers/auth';

test.describe('Roblox-Style Category Search Bar Suite', () => {
  test.beforeEach(async ({ page }) => {
    await resetDatastore(page);
    await loginAsDemo(page, 'admin');
    await page.goto('/admin/users.php');
    await expect(page).toHaveTitle(/User Directory|Campus User/i);
  });

  test('1. Search bar displays leading icon, clear button, and opens Roblox category dropdown on focus', async ({ page }) => {
    const searchInput = page.locator('#search-user');
    const dropdown = page.locator('#robloxSearchDropdown');
    const clearBtn = page.locator('#searchUserClearBtn');

    await expect(searchInput).toBeVisible();
    await expect(dropdown).not.toBeVisible();
    await expect(clearBtn).toBeHidden();

    // Focus input -> dropdown opens with categories
    await searchInput.focus();
    await expect(dropdown).toBeVisible();

    const items = dropdown.locator('.roblox-search-item');
    expect(await items.count()).toBe(10);

    // Verify categories contain icons and correct labels
    await expect(items.nth(0)).toContainText('All Directory');
    await expect(items.nth(0).locator('.item-icon-box i.bi')).toBeVisible();
    await expect(items.nth(0)).toContainText('Default');

    await expect(items.nth(1)).toContainText('Students');
    await expect(items.nth(1).locator('.item-icon-box i.bi')).toBeVisible();

    await expect(items.nth(2)).toContainText('Employers & Offices');
    await expect(items.nth(3)).toContainText('Organization / Program');
    await expect(items.nth(4)).toContainText('ID / Code');
    await expect(items.nth(5)).toContainText('University Offices');
    await expect(items.nth(6)).toContainText('Approved Partners');
    await expect(items.nth(7)).toContainText('Administrators');
    await expect(items.nth(8)).toContainText('Pending Review');
    await expect(items.nth(9)).toContainText('Verified Accounts');
  });

  test('2. Typing updates query preview in all categories and reveals clear button', async ({ page }) => {
    const searchInput = page.locator('#search-user');
    const dropdown = page.locator('#robloxSearchDropdown');
    const clearBtn = page.locator('#searchUserClearBtn');

    await searchInput.fill('alwinsontgm');
    await expect(dropdown).toBeVisible();
    await expect(clearBtn).toBeVisible();

    // Each row should preview "alwinsontgm" in <Category>
    const items = dropdown.locator('.roblox-search-item');
    await expect(items.nth(0)).toContainText('"alwinsontgm"');
    await expect(items.nth(0)).toContainText('in All Directory');

    await expect(items.nth(1)).toContainText('"alwinsontgm"');
    await expect(items.nth(1)).toContainText('in Students');

    // Click clear button -> resets value, hides clear button, closes dropdown
    await clearBtn.click();
    await expect(searchInput).toHaveValue('');
    await expect(clearBtn).toBeHidden();
  });

  test('3. Clicking a category selection filters immediately for that scope', async ({ page }) => {
    const searchInput = page.locator('#search-user');
    const dropdown = page.locator('#robloxSearchDropdown');
    const roleSelect = page.locator('#role-select');

    await searchInput.fill('admin');
    await expect(dropdown).toBeVisible();

    // Click "Administrators" category
    const adminCategory = dropdown.locator('.roblox-search-item:has-text("Administrators")');
    await adminCategory.click();

    // Dropdown closes, role select updates to admin
    await expect(dropdown).toBeHidden();
    await expect(roleSelect).toHaveValue('admin');
    await expect(searchInput).toHaveValue('admin');

    // Results container shows admin user accounts
    await expect(page.locator('#filter-results-container')).toContainText(/Administrator|Admin/i);
  });

  test('4. Pressing Enter without arrow navigation defaults the user (All Directory)', async ({ page }) => {
    const searchInput = page.locator('#search-user');
    const dropdown = page.locator('#robloxSearchDropdown');
    const roleSelect = page.locator('#role-select');

    // Fill query and press Enter immediately
    await searchInput.fill('kld');
    await searchInput.press('Enter');

    // Dropdown should be dismissed, role should default to All ('')
    await expect(dropdown).toBeHidden();
    await expect(roleSelect).toHaveValue('');
    await expect(searchInput).toHaveValue('kld');
  });

  test('5. Keyboard Arrow navigation selects highlighted category on Enter', async ({ page }) => {
    const searchInput = page.locator('#search-user');
    const dropdown = page.locator('#robloxSearchDropdown');
    const roleSelect = page.locator('#role-select');

    await searchInput.fill('test');
    await searchInput.focus();
    await expect(dropdown).toBeVisible();

    // Press ArrowDown to highlight 1st (All Directory), then 2nd (Students)
    await searchInput.press('ArrowDown');
    await searchInput.press('ArrowDown');

    const studentsItem = dropdown.locator('.roblox-search-item').nth(1);
    await expect(studentsItem).toHaveClass(/highlighted/);

    // Press Enter to select Students
    await searchInput.press('Enter');
    await expect(dropdown).toBeHidden();
    await expect(roleSelect).toHaveValue('student');
  });

  test('6. Pressing Escape or clicking outside dismisses dropdown', async ({ page }) => {
    const searchInput = page.locator('#search-user');
    const dropdown = page.locator('#robloxSearchDropdown');

    await searchInput.focus();
    await expect(dropdown).toBeVisible();

    await page.keyboard.press('Escape');
    await expect(dropdown).toBeHidden();

    // Reopen by clicking and dismiss by clicking outside
    await searchInput.click();
    await expect(dropdown).toBeVisible();
    await page.locator('body').click({ position: { x: 10, y: 10 } });
    await expect(dropdown).toBeHidden();
  });

  test('7. All Roblox search category icons strictly use unified text-accent brand color', async ({ page }) => {
    const searchInput = page.locator('#search-user');
    const dropdown = page.locator('#robloxSearchDropdown');

    await searchInput.focus();
    await expect(dropdown).toBeVisible();

    const items = dropdown.locator('.roblox-search-item');
    const count = await items.count();
    expect(count).toBe(10);

    for (let i = 0; i < count; i++) {
      const icon = items.nth(i).locator('.item-icon-box i.bi');
      await expect(icon).toHaveClass(/text-accent/);
      // Ensure no multi-color classes are present
      await expect(icon).not.toHaveClass(/text-primary/);
      await expect(icon).not.toHaveClass(/text-info/);
      await expect(icon).not.toHaveClass(/text-warning/);
      await expect(icon).not.toHaveClass(/text-secondary/);
    }
  });

  test('8. Sort By filter supports alphabetical (A to Z) and reverse alphabetical (Z to A)', async ({ page }) => {
    const sortSelect = page.locator('#sort-select');
    await expect(sortSelect).toBeVisible();

    // Verify sort options exist
    await expect(sortSelect.locator('option[value="name_asc"]')).toHaveText(/Name: A → Z/i);
    await expect(sortSelect.locator('option[value="name_desc"]')).toHaveText(/Name: Z → A/i);
    await expect(sortSelect.locator('option[value="newest"]')).toHaveText(/Recently Registered/i);
    await expect(sortSelect.locator('option[value="oldest"]')).toHaveText(/Earliest Registered/i);

    // Sort by Name Ascending (A -> Z)
    await sortSelect.selectOption('name_asc');
    await page.waitForTimeout(500);

    const nameElements = page.locator('#filter-results-container table tbody tr td:first-child .fw-bold');
    const namesCount = await nameElements.count();
    expect(namesCount).toBeGreaterThan(1);

    const firstNames: string[] = [];
    for (let i = 0; i < namesCount; i++) {
      firstNames.push((await nameElements.nth(i).innerText()).trim().toLowerCase());
    }

    // Verify alphabetically sorted
    for (let i = 0; i < firstNames.length - 1; i++) {
      expect(firstNames[i].localeCompare(firstNames[i + 1])).toBeLessThanOrEqual(0);
    }

    // Sort by Name Descending (Z -> A)
    await sortSelect.selectOption('name_desc');
    await page.waitForTimeout(500);

    const descNameElements = page.locator('#filter-results-container table tbody tr td:first-child .fw-bold');
    const descNames: string[] = [];
    for (let i = 0; i < await descNameElements.count(); i++) {
      descNames.push((await descNameElements.nth(i).innerText()).trim().toLowerCase());
    }

    for (let i = 0; i < descNames.length - 1; i++) {
      expect(descNames[i].localeCompare(descNames[i + 1])).toBeGreaterThanOrEqual(0);
    }
  });

  test('9. Synergy: Combining search, category dropdown, role, and sorting works seamlessly', async ({ page }) => {
    const searchInput = page.locator('#search-user');
    const dropdown = page.locator('#robloxSearchDropdown');
    const sortSelect = page.locator('#sort-select');

    // Filter by Student Role using Roblox Category selection
    await searchInput.focus();
    await expect(dropdown).toBeVisible();
    const studentsCat = dropdown.locator('.roblox-search-item:has-text("Students")');
    await studentsCat.click();
    await page.waitForTimeout(500);

    // Verify role hidden input was set to student
    await expect(page.locator('#role-select')).toHaveValue('student');

    // Sort Students by Name A -> Z
    await sortSelect.selectOption('name_asc');
    await page.waitForTimeout(500);

    // Verify all displayed rows are students
    const roleBadges = page.locator('#filter-results-container table tbody tr td:nth-child(2)');
    const count = await roleBadges.count();
    expect(count).toBeGreaterThan(0);
    for (let i = 0; i < count; i++) {
      await expect(roleBadges.nth(i)).toContainText(/Student/i);
    }
  });

  test('10. Targeted Search: Filtering specifically by Organization / Program and ID / Code', async ({ page }) => {
    const searchInput = page.locator('#search-user');
    const dropdown = page.locator('#robloxSearchDropdown');

    // 1. Search in Organization / Program
    await searchInput.fill('Technology');
    await expect(dropdown).toBeVisible();
    const orgProgCat = dropdown.locator('.roblox-search-item:has-text("Organization / Program")');
    await expect(orgProgCat).toBeVisible();
    await orgProgCat.click();
    await page.waitForTimeout(500);

    // Verify search_field hidden input is set to org_program
    await expect(page.locator('#search-field')).toHaveValue('org_program');

    // 2. Search in ID / Code
    await searchInput.fill('202');
    await expect(dropdown).toBeVisible();
    const idCodeCat = dropdown.locator('.roblox-search-item:has-text("ID / Code")');
    await expect(idCodeCat).toBeVisible();
    await idCodeCat.click();
    await page.waitForTimeout(500);

    // Verify search_field hidden input is set to id_code
    await expect(page.locator('#search-field')).toHaveValue('id_code');
  });
});
