# 01: Reusable Invariant Engine & Continuous Viewport Sweep Harness

**What to build:** An automated layout inspection engine and stepped viewport sweep runner that dynamically steps through eleven responsive gutter widths (360px through 1280px) and checks that pages exhibit zero horizontal overflow (`scrollWidth <= clientWidth`), no offscreen interactive controls, and no layout collisions when resized.

**Blocked by:** None (can start immediately)

**Status:** ready-for-agent

- [x] A reusable layout assertion helper evaluates `document.documentElement.scrollWidth <= window.innerWidth` and pinpoints any overflowing DOM elements.
- [x] A continuous stepped sweep function steps through `[360, 375, 414, 480, 576, 680, 768, 850, 992, 1100, 1280]` pixels width, allowing CSS transitions and flex layouts to settle.
- [x] Culprit reporting extracts tag names, classes, IDs, and bounding rectangles for any element causing horizontal document scrollbars.
- [x] Verified runnable across headless Chrome in under 15 seconds per page sweep.
