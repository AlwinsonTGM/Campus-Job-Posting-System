# Project Instructions & UI Guidelines

## Strict UI Rules: Prohibition on Decorative "Circle Pill" & Eyebrow Badges

1. **NO Decorative Eyebrow Pills Above Headings**:
   - NEVER generate decorative `<span class="badge rounded-pill ...">` or `<div class="eyebrow-tag ...">` pills above page titles (`h1`, `h2`), modal titles, section headers, or prose documents.
   - NEVER add random pill tags like `<span class="badge rounded-pill ..."><i class="bi ..."></i> Category / Law / Feature</span>` above headings in templates (e.g., `terms.php`, `privacy.php`, `about-us.php`, `login.php`).
   - Headings must stand cleanly on their own with proper typography hierarchy (`.page-head-title`, `h1`, `h2`), without floating pill clutter.

2. **Strict Badges-For-Data-Only Rule**:
   - Badges are strictly reserved for **dynamic system status data**:
     - Application workflow statuses: `.badge-status--pending`, `.badge-status--accepted`, etc.
     - Unread notification counts (numeric counts only).
     - Job type metadata chips when strictly displaying attributes (e.g., "Part-Time", "₱150/hr").
   - NEVER use rounded pills as visual ornaments, section category labels, or decorative metadata eyebrows.

3. **Clean Paper Aesthetic**:
   - Prefer clean typographic contrast, subtle borders (`var(--line)`), and generous spacing over decorative badge bubbles.
