# 🛠️ Campus Job Posting System — Daily Engineering DevLogs

> **Author:** Alwinson Bustamante (`2025-2-000065`)  
> **Role:** Lead Developer & System Architect  
> **Institution:** Kolehiyo ng Lungsod ng Dasmariñas (KLD) — Institute of Computing and Digital Innovation (ICDI)  
> **Course:** COAL101 Web Systems and Technologies  
> **Project:** KLD Campus Hire — Institutional Campus Job Posting System  
> **Live DevBlog Stage:** [about-us.php](about-us.php#devblog) (Interactive 3D Coverflow)

---

## 📅 Chronological Development Roadmap (Days 01 – 11)

| Sprint / Day | Date | Milestone Focus | Key Technical Achievements | Primary Commits |
| :--- | :--- | :--- | :--- | :--- |
| **Day 11** | Sep 08, 2026 | **Modular CSS & 20H Labor Safeguards** | Disassembled `custom.css` into 7 scoped modules, 3-column CSS Grid navbar with dynamic persona routing & Google-style center search, multi-turn AI memory, and statutory 20h/wk labor cap enforcement. | `fc0da08`, `5551d43`, `b6f3d28`, `d554021` |
| **Day 10** | Sep 07, 2026 | **Real-Time Notifications & Migration Whitelist** | Relational `notifications` table, 30s background polling with `visibilitychange` lifecycle pause, floating toast alerts, full-page Notification Center, and regex-guarded table migration whitelist. | `b63c467`, `82661b1` |
| **Day 09** | Sep 06, 2026 | **NVIDIA NIM AI Studio & Visual Taxonomy** | Enterprise NVIDIA NIM AI gateway (`Llama-3.3-70B`), fullscreen 2-column studio layout, domain photography across 8 job families, and zero-reload AJAX category filtering with scroll preservation. | `d6ae349`, `70c9a30`, `fbdd6ca`, `6cd00e4`, `28e6e97`, `1218e2f` |
| **Day 08** | Sep 05, 2026 | **Robot Kinematics & Expressions** | 13 procedural kinetic animations (spring-damped matrices), typewriter speech bubble with synchronized mascot micro-gestures, and 3 dialogue personas (`Talk`, `/boost`, `/grill-me`) with OLED eye shifts. | `5eed942` |
| **Day 07** | Sep 04, 2026 | **Persona Wizard & 3D Mascot Genesis** | 3-step registration wizard with 1-click persona grid, defensive hardening against HTML entity double-escaping & double-submits, Three.js WebGL mascot with real-time cursor kinematics & ocular tracking. | `52669ca`, `e2801c4`, `cd81aec`, `600121c`, `6c331dd`, `fefb3cd`, `a7b0952` |
| **Day 06** | Sep 03, 2026 | **Relational Integrity & Defense-in-Depth** | Semantic foreign keys (`fk_jobs_category`, `fk_apps_student`), `UNIQUE (job_id, student_id)`, swarm security audit (role whitelist, bcrypt hashing, upload MIME verification, IDOR boundaries), and demo/real data coexistence. | `ff489ae`, `0c9f976`, `b89faf9`, `6e768e1` |
| **Day 05** | Sep 02, 2026 | **Enterprise MySQL (PDO) Migration & 100vh Hero** | Replaced JSON flat-files with MySQL (PDO) prepared statements, built atomic ACID database migration engine (`database/migrate.php`), and expanded viewport to full-width with a 100vh landing hero. | `fd9adf0`, `2740c4e` |
| **Day 04** | Aug 29, 2026 | **Tactile Paper Sheet UX & 3D DevBlog** | Tactile Paper Sheet design tokens (`#FBF9F4` / `#161616`), keyboard-driven spotlight search (`Ctrl+K`), interactive 3D Coverflow DevBlog stage, and multi-viewport split-screen harmonization. | `33a9c14`, `9a5dee5`, `f4ee755`, `6a57a57` |
| **Day 03** | Aug 28, 2026 | **Team Git Onboarding & Dual-Design Strategy** | Mentored groupmates on Git branching & repository workflows, established dual-design prototype presentation strategy, and completed 11:30 PM applicant review & admin milestones. | `393d3fa`, `aeeb4ba`, `c79e09c`, `8bb7d58`, `f9e9c71`, `cfbc641` |
| **Day 02** | Aug 27, 2026 | **Coding Marathon & Zero-DB JSON Engine** | 16-hour coding marathon ending at 1:00 AM: zero-DB atomic JSON persistence engine, session state synchronization, client-side Shannon password entropy analyzer, and 18-slot schedule matrix. | `22710a4`, `3f6fc2f`, `71cdf1b`, `e946f04`, `435d5c7`, `5e49ae1`, `dd32e9c` |
| **Day 01** | Aug 26, 2026 | **Topic Selection & Pure PHP Foundations** | Selected topic for COAL101, drafted project hierarchy and data schemas, enforced pure native PHP modular template architecture (zero raw `.html` files), and coded foundation throughout campus breaks. | `b47db5a`, `6abb516`, `9c2c64d`, `803676a` |

---

## 📖 Detailed Daily Engineering Chronicles

### Day 11 · September 08, 2026
#### Modular CSS Architecture, Role-Based Navbar, Multi-Turn AI Memory & 20-Hour Academic Labor Cap

With our final project submission and defense evaluation on the horizon, today was dedicated to structural refactoring, navigational fluidity, and strict institutional policy compliance.

##### 1. CSS Architecture Modularization & Precision Asset Caching
As features expanded, maintaining a single monolithic `custom.css` became inefficient. I disassembled the stylesheet into 7 domain-specific modules under `assets/css/`:
- `variables.css`: Design tokens, paper sheet color palette, status hues, and typography hierarchy.
- `base.css`: Reset overrides, typography, paper container elevations, and card foundations.
- `components.css`: Buttons, badge chips, form inputs, modal dialogs, and progress bars.
- `layout.css`: 3-column navbar grid, responsive wrappers, and unified footer layout.
- `auth.css`: 3-step registration wizard, persona selector cards, and login split shells.
- `dashboard.css`: Metric KPI cards, job directories, candidate review drawers, and availability grids.
- `robot.css`: WebGL mascot viewport, speech bubble, kinetic animations, and fullscreen AI studio.

To optimize browser caching without stale assets, I replaced static query parameter cache-busting with `filemtime()` in `includes/header.php`, guaranteeing instant stylesheet reloads upon file edits while retaining client cache speed.

##### 2. Role-Adaptive 3-Column Navbar & Centered Spotlight Search
I restructured `includes/navbar.php` using a robust 3-column desktop CSS Grid:
- **Left Column:** Dynamic persona brand logo that routes students to `student/dashboard.php`, employers to `employer/dashboard.php`, administrators to `admin/reports.php`, and guests to `index.php`.
- **Center Column:** A wide Google-style search bar for authenticated sessions triggering our global debounced AJAX spotlight search modal (`Ctrl+K`).
- **Right Column:** Notification bell dropdown with unread badge counter, user profile chip, and authenticated drawer actions.

##### 3. Campus AI Multi-Turn Memory & Offline Fallback Intelligence
Enhanced the Campus AI companion (`api/robot-chat.php`, `includes/ai-config.php`, `assets/js/hero-robot.js`) with a sliding multi-turn conversational memory buffer. The AI now tracks prior queries within a session, understands role context (`student`, `employer`, `admin`), and utilizes plural-aware regex patterns in our local offline fallback engine when API limits are encountered.

##### 4. Statutory 20-Hour Weekly Labor Safeguards
To safeguard student assistant academic performance, I implemented strict client and server-side validation in `employer/create-job.php` and `employer/edit-job.php` capping requisition shifts to a maximum of **20 hours per week**. Requisitions exceeding 20 hours are rejected server-side. Additionally, the mobile job directory filters were redesigned into a collapsible accordion with live filter count badges and instant AJAX synchronization.

* **Key Deliverables:** Modularized 7-sheet CSS structure; 3-column CSS Grid role navbar; sliding multi-turn AI conversational memory; statutory 20-hour weekly student labor cap; collapsible mobile search accordion.
* **Tech Stack:** Modular CSS Architecture, CSS Grid, Multi-Turn AI Memory, Server Validation, `filemtime` Cache Control.

---

### Day 10 · September 07, 2026
#### Building the Multi-Role Notification Engine: Real-Time Polling, Verification Triggers & Migration Whitelists

Transparent communication and real-time feedback loops are fundamental to a campus platform connecting students, department supervisors, industry partners, and university administrators. Today, I built an end-to-end real-time in-app notification infrastructure.

##### 1. Relational Notification Schema & Automated Lifecycle Triggers
Created the `notifications` table in MySQL with foreign key constraints, read/unread states, action URLs, and indexes on `(user_id, is_read)`. Integrated lifecycle event hooks into `includes/data-helper.php`:
- **Students:** Instantly notified when application status transitions occur (*Under Review*, *Interview Scheduled* with date & venue, *Accepted*, *Declined*) and when official profile edit requests are resolved.
- **Employers:** Notified when candidates submit applications or when their institutional accreditation permit is approved.
- **Administrators:** Alerted upon new employer registrations and student COR profile correction submissions.

##### 2. Background Polling Engine & Floating Toast UI
Engineered `api/notifications.php` and `assets/js/notifications.js` to execute lightweight 30-second background polling. When new notifications arrive, non-intrusive floating toasts appear at the bottom-right corner, and the navbar bell badge updates dynamically. The poller listens to `document.visibilitychange` to pause requests when the browser tab is hidden, conserving client battery and server resources.

##### 3. Dedicated Notification Center (`notifications.php`)
Constructed a dedicated notification management hub featuring chip-selectable tabs (*All* vs *Unread*), timestamp formatting, direct one-click navigation to associated application/profile records, and an instant "Mark All as Read" action.

##### 4. Database Migration Engine Whitelist Hardening
Hardened `database/migrate.php` by establishing a strict table whitelist array combined with regex pattern validation in the table truncate loop, eliminating any possibility of SQL injection during database re-seeding.

* **Key Deliverables:** Relational `notifications` database schema; automated multi-role lifecycle event hooks; 30s background polling engine with tab visibility suspension; full-page Notification Center; migration loop whitelist security.
* **Tech Stack:** MySQL Foreign Keys & Indexes, AJAX Long-Polling, DOM Floating Toasts, PHP PDO Lifecycle Hooks, Regex Whitelisting.

---

### Day 09 · September 06, 2026
#### Integrating NVIDIA NIM AI Campus Studio: 2-Column Interface, Domain Photography & Zero-Reload Filtering

Today bridged artificial intelligence with visual taxonomy, elevating our platform into an interactive, visually stunning campus career studio.

##### 1. NVIDIA NIM AI Gateway & Fullscreen 2-Column Studio
Integrated the enterprise NVIDIA NIM AI API (`Llama-3.3-70B-Instruct`) via `api/robot-chat.php` and `includes/ai-config.php`. The gateway classifies student intent (career counseling, resume tips, interview preparation, policy questions) and serves streaming markdown responses. Designed an immersive **Fullscreen 2-Column Studio**:
- **Left Column:** Live interactive 3D WebGL robot companion with dynamic OLED eye color shaders.
- **Right Column:** Clean conversational console with suggested prompt chips, rate limiting indicators, markdown formatting, and copy-to-clipboard actions.

##### 2. Domain Photography & Visual Category Showcase
Eliminated corporate monotony by curating high-resolution institutional photography for each campus job family (Tech Lab, Library, Science & Health, Peer Tutoring, Athletic Facility, Creative Media, Administrative Office, Campus Cafe). Designed glassmorphic badge overlays for verified employers, work setups, and hourly rates, and built an interactive visual taxonomy showcase banner on `student/jobs.php` and `admin/categories.php`.

##### 3. Zero-Reload AJAX Filtering with Scroll Preservation
Upgraded the student job browsing experience in `assets/js/main.js` using vanilla `fetch()` and `window.history.pushState()`. Students can filter categories and pay ranges seamlessly without full-page reloads, with immediate ring highlight feedback and zero scroll displacement.

##### 4. Campus AI FAQ Modal Integration
Embedded an AI FAQ assistant modal (`#faqChatbotModal`) directly on `faqs.php` to handle edge-case student queries that fall outside standard FAQ listings.

* **Key Deliverables:** NVIDIA NIM AI gateway integration; Fullscreen 2-Column Studio; job family domain photography overhaul; zero-reload AJAX category filtering; AI FAQ modal.
* **Tech Stack:** NVIDIA NIM API, Vanilla JS Fetch API, HTML5 History API (`pushState`), Glassmorphic CSS Tokens.

---

### Day 08 · September 05, 2026
#### Giving Life to the Mascot: 13 Kinetic Animations, Typewriter Speech Bubble & Interactive Dialogue Modes

Having established the 3D WebGL canvas yesterday, today's objective was to give our robot companion genuine personality, charisma, and dynamic responsiveness.

##### 1. 13 Procedural Kinetic Animations
Replaced rigid jumping motions with 13 procedural kinetic routines in `assets/js/hero-robot.js`:
- Head tilts, curious winks, squint scans, double-takes, excited nodding, 360° celebratory spins, empathetic sways, and respectful campus greeting bows.
- Each animation is computed using spring-damped matrices with Euler angle clamping to guarantee natural movement without clipping or jitter.

##### 2. Typewriter Speech Bubble & Visor Micro-Gestures
Constructed an interactive floating speech bubble hovering directly above the mascot with a character-by-character typewriter rendering engine. The robot's head and visor perform synchronized micro-gestures cadence-matched to the text delivery, creating the authentic appearance of real speech articulation.

##### 3. Three Dialogue Modes & OLED Visor Color Shifts
Programmed 3 specialized interactive personas:
1. **Talk Mode:** Friendly campus navigation and student assistantship advice.
2. **`/boost` Mode:** Career encouragement, academic motivation, and confidence booster.
3. **`/grill-me` Mode:** Rigorous mock interview simulation testing behavioral and technical readiness.

The robot's emissive visor shader smoothly shifts colors (emerald green, amber gold, and cyan) depending on the active dialogue persona.

* **Key Deliverables:** 13 procedural kinetic animations with spring damping; interactive typewriter speech bubble; 3 dialogue modes (`Talk`, `/boost`, `/grill-me`); real-time OLED emissive visor shader transitions.
* **Tech Stack:** Three.js WebGL, Procedural Kinematics, Typewriter DOM Engine, Emissive Visor Shaders, `IntersectionObserver`.

---

### Day 07 · September 04, 2026
#### The 3-Step Persona Registration Wizard, Defensive Data Gating & 3D Robot Mascot Genesis

A high-output development day spanning user onboarding UX, rigorous defensive bug fixing, and WebGL 3D mascot integration.

##### 1. 3-Step Registration Persona Wizard
Re-architected `register.php` from an overwhelming vertical form into a guided 3-step wizard:
- **Step 1 (Role & Persona):** 1-click persona selector cards (Student Applicant, University Department, Accredited Industry Partner).
- **Step 2 (Institutional Identity):** Dynamic student ID, academic course, year level, or employer business permit upload.
- **Step 3 (Security & Confirmation):** Real-time Shannon password entropy verification, contact details, and policy acceptance.
- Fitted with an active vertical progress checklist and an 8px luminous green progress track.

##### 2. Defensive Bug Squashing & Datastore Hardening
- Removed premature `htmlspecialchars()` double-encoding across candidate review notes, student cover letters, and category names, storing raw data cleanly in MySQL and eliminating `&amp;amp;` output bugs.
- Implemented double-submit button locking with loading spinners across all POST forms to prevent duplicate state mutations.
- Enhanced the student application tracker with structured status callout alerts for declined applications and dual digital/printable CV action buttons.

##### 3. Interactive 3D WebGL Mascot Genesis
Integrated Three.js and loaded `cute_robot.glb` in the landing hero section. Isolated the primary mascot mesh, styled materials in KLD deep forest green and glowing emerald visor emission (`#22c55e`), and implemented real-time cursor tracking with clamped kinematics (0.058 lerp) and dual-layer visor eye tracking (0.10 lerp).

* **Key Deliverables:** 3-step registration wizard with 1-click persona grid; double-encoding and double-submit bug fixes; enhanced student application tracker; 3D WebGL mascot with real-time cursor & eye tracking.
* **Tech Stack:** Three.js GLTFLoader, Lerp Kinematics, Bootstrap 5 Stepper, PHP Input Normalization, Form Double-Submit Protection.

---

### Day 06 · September 03, 2026
#### Relational Foreign Keys, Swarm Security Hardening & Permanent Demo/Real Data Coexistence

With MySQL operational, today focused on structural integrity, relational modeling constraints, and defense-in-depth security.

##### 1. Relational Foreign Key Constraints & EER Diagramming
Updated `database/schema.sql` with explicit foreign key constraints (`fk_jobs_category`, `fk_jobs_employer`, `fk_apps_job`, `fk_apps_student`) with appropriate `ON DELETE RESTRICT` and `CASCADE` rules, and added `UNIQUE KEY unique_student_job (job_id, student_id)` to prevent duplicate applications at the database engine level.

##### 2. Swarm Security Audit & Defensive Hardening
- **Authentication:** Whitelisted registration roles strictly to `['student', 'employer']`, preventing privilege escalation; upgraded passwords to native `password_hash()` and stripped hashes from session superglobals; secure session regeneration and cookie invalidation on logout.
- **File Upload Security:** Implemented `finfo_file(FILEINFO_MIME_TYPE)` validation across resumes, business permits, registration proofs, and category photos.
- **IDOR Boundaries:** Scoped employer candidate review and applicant endpoints strictly to primary-key ownership (`(int)$job['employer_id'] === (int)$user['id']`).
- **HTTP Security Headers:** Emitted `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, and `Referrer-Policy: strict-origin-when-cross-origin`.

##### 3. Unified Demo & Real Data Coexistence
Abolished destructive table wipes. Benchmark demo listings and real registered students/employers now coexist permanently in MySQL, with live admin COR/ID verification inspection modals in `admin/users.php` that synchronize immediately to active sessions.

* **Key Deliverables:** Relational foreign keys and unique constraints; swarm security audit fixes (auth whitelisting, bcrypt, upload MIME validation, IDOR boundaries); permanent demo/real data coexistence; admin COR verification modal.
* **Tech Stack:** MySQL InnoDB Foreign Keys, Bcrypt Hashing, `finfo` MIME Validation, Anti-CSRF Protection, Defensive HTTP Headers.

---

### Day 05 · September 02, 2026
#### Enterprise MySQL Migration: Relational Schema, ACID Migrations & Full-Width 100vh Hero Viewport

Flat-file JSON persistence carried our prototype through initial milestones, but enterprise scalability and concurrent applicant handling demanded a true relational database.

##### 1. MySQL Data Layer Migration via PDO
Engineered `includes/db.php` as a secure PDO singleton and completely rewrote `includes/data-helper.php`. All user authentication, job requisitions, schedule matrices, and applicant reviews now query MySQL using parameterized statements, completely eliminating SQL injection risks and multi-process file concurrency race conditions.

##### 2. ACID Database Migration & Seeding Engine
Created `database/schema.sql` and `database/migrate.php`. The migration engine orchestrates DDL table generation and imports seed data directly from structured JSON payloads wrapped within single transactional blocks, enabling instantaneous database resets without schema corruption.

##### 3. Full-Width 100vh Hero Viewport
Removed artificial max-width constraints on `.sheet` for a 100% full-width span. Crafted an expansive 100vh landing hero section with vertically centered typography, bottom-anchored partner strips, and refined registration split names (First, Middle, Last).

##### 4. Automated E2E Verification
Executed the full Playwright test suite across all user workflows—achieving 106/106 passed tests with zero regressions on the new MySQL data layer.

* **Key Deliverables:** MySQL (PDO) relational data layer migration; ACID migration & seeding runner; full-width 100vh landing hero section; 106/106 passing Playwright E2E tests.
* **Tech Stack:** PHP Data Objects (PDO), MySQL 8 / MariaDB, ACID Migration Runner, 100vh Full-Width Layout, Playwright E2E.

---

### Day 04 · August 29, 2026
#### Perfecting the Tactile Paper Sheet UX: Spotlight Search, 3D DevBlog & Final Polish

Following up on Day 03's self-critique where the interface still felt incomplete, today's objective was relentless visual and interactive perfection.

##### 1. Tactile Paper Sheet Design System
Turned raw functional components into a unified tactile experience: warm ivory canvas tones (`#FBF9F4`), deep carbon ink contrast (`#161616`), physical paper border elevations, and official KLD Green accents across all student, employer, and administrative views.

##### 2. Universal Spotlight Search (`Ctrl+K`)
Engineered the keyboard-driven universal Spotlight Search modal querying `api/search-jobs.php` with client-side debouncing for instant opportunity discovery.

##### 3. Interactive 3D DevBlog Coverflow
Constructed the interactive CSS 3D perspective DevBlog Coverflow on `about-us.php` so our entire daily journey is recorded transparently for our professor and peers.

* **Key Deliverables:** Tactile Paper Sheet design tokens; universal spotlight search (`Ctrl+K`); interactive 3D perspective DevBlog Coverflow and modal reader; multi-viewport responsiveness polish.
* **Tech Stack:** CSS3 3D Perspective, Spotlight Search API, Tactile Paper Tokens, Vanilla JS ES6.

---

### Day 03 · August 28, 2026
#### Team Git Collaboration, Mentoring Groupmates & The Dual-Design Strategy

Balanced multi-subject coursework with core development, conducted team Git mentoring, and initiated the dual-design presentation strategy.

##### 1. Hands-On Git Mentoring
Conducted an intensive mentoring session teaching groupmates file hierarchy, Git branching, staging, and GitHub collaborative pull request workflows.

##### 2. Dual-Design Presentation Strategy
Established a dual-design strategy to present two strong prototype variations to our professor during the midterm presentation.

##### 3. The 11:30 PM Build & UX Critique
Completed the 11:30 PM milestone build covering applicant review and administrative controls, critiqued visual shortcomings, and set refinement goals for the next sprint.

* **Key Deliverables:** Team Git version control onboarding; dual-design prototype strategy; applicant review and admin control milestones; UX design critique.
* **Tech Stack:** Git & GitHub Workflows, Collaborative Branching, Bootstrap 5.3 Modals, PHP State Handlers.

---

### Day 02 · August 27, 2026
#### The Start of the Madness: All-Day Coding Sprint & 1:00 AM Optimization Marathon

An intensive full-day coding marathon from early morning to 1:00 AM—building the zero-DB JSON persistence engine, session state synchronization, password entropy meter, and end-to-end application workflows.

##### 1. Zero-Database JSON Persistence Engine
Built `includes/data-helper.php` with atomic file operations and dynamic session synchronization, delivering lightning-fast sub-millisecond response times without database overhead.

##### 2. Shannon Password Entropy Analyzer
Engineered `assets/js/password-strength.js`, evaluating client-side password entropy with real-time criteria checkmarks and visual strength scoring.

##### 3. 18-Slot Schedule Availability Matrix
Constructed the interactive weekly schedule availability time grid matrix for student assistant job applications.

* **Key Deliverables:** Atomic JSON persistence engine; client-side Shannon password entropy analyzer; 18-slot weekly schedule availability grid matrix; 16-hour stability sprint ending at 1:00 AM.
* **Tech Stack:** Native PHP 8, Atomic JSON Engine, Shannon Entropy Algorithm, Session State Synchronization.

---

### Day 01 · August 26, 2026
#### Genesis of the System: Topic Selection, Architecture Drafting & Pure PHP Foundations

The project kicked off on topic selection day for our COAL101 Web Systems and Technologies midterm lab project.

##### 1. Project Blueprint & Modular Architecture
Selected the Campus Job Posting System topic, mapped functional requirements, and established the directory hierarchy and initial JSON datastore schemas.

##### 2. Pure Native PHP Stack Compliance
Strictly enforced our professor's stack requirements: Native PHP 8, CSS, Bootstrap 5.3, and Vanilla JavaScript—with **strictly zero standalone `.html` files**. Every view is composed via modular PHP components (`header.php`, `navbar.php`, `footer.php`, `components.php`).

* **Key Deliverables:** Topic selection and functional requirement mapping; pure modular Native PHP architecture; initial data schemas; continuous development through campus free periods.
* **Tech Stack:** Native PHP 8, Bootstrap 5.3, Modular Component Architecture, JSON Schema Definitions.

---

## 📊 Summary Git Commit Log (Aug 26 – Sep 08, 2026)

```text
d554021 | 2026-09-08 | feat(ui/jobs): cap work hours to 20h, modernize footer, and add mobile filter dropdown
b6f3d28 | 2026-09-08 | feat(ai): enhance Campus AI portal navigation, multi-turn memory, and offline fallback
5551d43 | 2026-09-08 | feat(navbar): modernize navbar with role-based logo routing and center search
fc0da08 | 2026-09-08 | refactor(core): modularize CSS architecture, add live account verification, and modernize legal pages
82661b1 | 2026-09-07 | fix(security): add strict table whitelist and regex validation to migrate.php truncate loop
b63c467 | 2026-09-07 | feat(notifications): add multi-role in-app notification system with real-time polling and lifecycle verification triggers
c806c4f | 2026-09-06 | feat(ui): enhance hero section background opacity and update admin AI settings
1218e2f | 2026-09-06 | feat(ui): implement floating flash notifications and solid color eyebrow badges
28e6e97 | 2026-09-06 | feat(faqs): integrate Campus AI Chatbot into unanswered questions section
6cd00e4 | 2026-09-06 | feat(student): zero-reload AJAX category filtering and scroll preservation
fbdd6ca | 2026-09-06 | feat(student): modernize job browsing with domain photography and category tiles
70c9a30 | 2026-09-06 | feat(admin): enhance categories taxonomy with related photography, hero showcase & picture management
d6ae349 | 2026-09-06 | feat: NVIDIA NIM AI Campus Companion — Fullscreen 2-Column Studio, Intent Detection, Security Hardening & UX Polish
5eed942 | 2026-09-05 | feat(hero): enhance 3D robot companion with interactive speech bubble and expressive animations
a7b0952 | 2026-09-04 | feat(hero-3d): implement interactive 3D robot companion with cursor and eye tracking
be099b7 | 2026-09-04 | fake datas
fefb3cd | 2026-09-04 | fix(admin): harden analytics quota auditing, categories taxonomy, user verification and encoding
6c331dd | 2026-09-04 | fix(employer-eval): harden candidate evaluation, prevent double encoding on notes, validate scheduling and add double-submit protection
600121c | 2026-09-04 | feat(student-tracker): enhance application status tracking, declined notice, and dual resume actions
cd81aec | 2026-09-04 | fix(student-portal): harden application form edge cases, availability matrix types, and datastore reset
e2801c4 | 2026-09-04 | fix(employer): harden requisition posting, fix double html encoding, retain sticky inputs & prevent double submission
52669ca | 2026-09-04 | feat(auth): implement 3-step registration wizard, unified persona selector, and UX refinements
6e768e1 | 2026-09-03 | feat: unify demo and real data coexistence, add user verification flow, and fix mobile UI responsiveness
b89faf9 | 2026-09-03 | Merge branch 'feature/mysql-integration' into main
0c9f976 | 2026-09-03 | feat(security): comprehensive swarm audit, defensive loophole hardening & relational integrity guards
ff489ae | 2026-09-03 | feat(schema): add explicit foreign key constraints with semantic ER diagram labels
fd9adf0 | 2026-09-02 | feat: migrate data layer from flat-file JSON to MySQL (PDO)
2740c4e | 2026-09-02 | feat(ui/auth): expand sheet to full-width, add 100vh hero viewport, and refine registration/login workflows
6a57a57 | 2026-08-29 | feat: add floating data mode toggle (Demo/Real) with wipe support
f4ee755 | 2026-08-29 | fix(design-system): harmonize color tokens, form controls, and multi-viewport responsiveness
9a5dee5 | 2026-08-29 | fix(ui): resolve half-screen responsive layout, icon alignment, and announcement modal buttons
33a9c14 | 2026-08-29 | feat(devblogs & branding): update daily engineering devlogs for Alwinson and configure brand favicon
```
