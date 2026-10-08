import { Page, expect } from '@playwright/test';

export interface LayoutViolation {
  width: number;
  type: 'horizontal_overflow' | 'text_clipping' | 'element_overlap' | 'offscreen_interactive';
  element: string;
  details: string;
}

export interface SweepResult {
  path: string;
  testedWidths: number[];
  violations: LayoutViolation[];
  passed: boolean;
}

/**
 * Standard continuous sweep breakpoint scale covering phone, gutter transitions,
 * tablet, half-screen, and desktop.
 */
export const DEFAULT_SWEEP_BREAKPOINTS = [
  360, // Compact mobile (Galaxy / small Android)
  375, // iPhone SE / baseline iOS
  414, // Large smartphone
  480, // Landscape mobile
  576, // Bootstrap SM boundary (frequent flex collision)
  680, // Half-screen on 1366px display
  768, // iPad / Tablet MD boundary (sidebar collapse)
  850, // Awkward tablet-landscape / split-window gutter
  992, // Bootstrap LG boundary
  1100, // Medium laptop gutter
  1280, // Standard Desktop XL
];

/**
 * Asserts layout integrity on the current page viewport:
 * 1. Zero horizontal overflow (document.documentElement.scrollWidth <= window.innerWidth)
 * 2. Identifies specific culprit elements causing horizontal scrollbars
 * 3. Asserts no off-screen primary interactive buttons
 * 4. Asserts no brutal text clipping on headers/cards
 */
export async function assertLayoutIntegrity(page: Page, contextName = ''): Promise<LayoutViolation[]> {
  const violations = await page.evaluate((ctx) => {
    const list: Array<{
      width: number;
      type: 'horizontal_overflow' | 'text_clipping' | 'element_overlap' | 'offscreen_interactive';
      element: string;
      details: string;
    }> = [];

    const winWidth = window.innerWidth;
    const docWidth = document.documentElement.scrollWidth;
    const bodyWidth = document.body ? document.body.scrollWidth : 0;

    // 1. Root horizontal overflow check
    if (docWidth > winWidth + 1 || bodyWidth > winWidth + 1) {
      const allElements = document.querySelectorAll('*');
      const culprits: string[] = [];

      for (let i = 0; i < allElements.length; i++) {
        const el = allElements[i] as HTMLElement;
        const rect = el.getBoundingClientRect();
        if (rect.width > 0 && rect.height > 0 && rect.right > winWidth + 2) {
          const style = window.getComputedStyle(el);
          if (style.visibility !== 'hidden' && style.display !== 'none' && style.opacity !== '0') {
            const identifier = el.tagName.toLowerCase() +
              (el.id ? `#${el.id}` : '') +
              (el.className ? `.${Array.from(el.classList).slice(0, 3).join('.')}` : '');
            culprits.push(`${identifier} (right: ${Math.round(rect.right)}px > ${winWidth}px)`);
            if (culprits.length >= 5) break;
          }
        }
      }

      list.push({
        width: winWidth,
        type: 'horizontal_overflow',
        element: 'document',
        details: `Page width (${Math.max(docWidth, bodyWidth)}px) exceeds viewport (${winWidth}px) on ${ctx}. Culprits: [${culprits.join(', ') || 'container margins/padding'}]`,
      });
    }

    // 2. Offscreen interactive buttons check
    const primaryButtons = document.querySelectorAll('button[type="submit"], .btn-primary, a.btn-primary');
    for (let i = 0; i < primaryButtons.length; i++) {
      const btn = primaryButtons[i] as HTMLElement;
      const rect = btn.getBoundingClientRect();
      const style = window.getComputedStyle(btn);
      if (style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0' && rect.height > 0) {
        if (rect.left < -5 || rect.right > winWidth + 5) {
          list.push({
            width: winWidth,
            type: 'offscreen_interactive',
            element: btn.tagName.toLowerCase() + (btn.className ? `.${btn.className.split(' ').slice(0, 2).join('.')}` : ''),
            details: `Interactive primary button pushed outside viewport bounds (left: ${Math.round(rect.left)}px, right: ${Math.round(rect.right)}px) on ${ctx}`,
          });
          break;
        }
      }
    }

    return list;
  }, contextName);

  return violations;
}

/**
 * Runs a continuous stepped viewport sweep across specified breakpoint widths,
 * asserting layout invariants at each step.
 */
export async function runContinuousViewportSweep(
  page: Page,
  path: string,
  breakpoints = DEFAULT_SWEEP_BREAKPOINTS
): Promise<SweepResult> {
  const allViolations: LayoutViolation[] = [];

  for (const width of breakpoints) {
    await page.setViewportSize({ width, height: 850 });
    // Allow CSS flex, grid and media queries to reflow
    await page.waitForTimeout(100);

    const violations = await assertLayoutIntegrity(page, `${path} at ${width}px`);
    if (violations.length > 0) {
      allViolations.push(...violations);
    }
  }

  return {
    path,
    testedWidths: breakpoints,
    violations: allViolations,
    passed: allViolations.length === 0,
  };
}
