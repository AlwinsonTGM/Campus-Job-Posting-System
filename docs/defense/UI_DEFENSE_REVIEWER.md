## tags: [COAL101, Web Systems and Technologies, UI Defense, Reviewer, Paper Sheet Design, KLD Campus Hire]
## aliases: [COAL101 - UI Defense Reviewer, Campus Job Posting System UI Guide, BSIS201 Midterm Defense]

# 🎓 Campus Job Posting System — UI Defense & Specialization Reviewer

> [!abstract] 📖 Overview
> This reviewer provides a comprehensive, structured preparation guide for the **COAL101 (Web Systems and Technologies)** Midterm UI Defense of the **KLD Campus Job Posting System (Campus Hire)**. 
> 
> Designed specifically for the **6-member BSIS201 engineering team**, this guide divides the platform into dedicated domains of expertise, provides key UI concepts, details component architectures, outlines the 5-minute defense presentation relay, and equips each member with high-yield panel Q&A talking points.

---

## 👥 Team Specialization & Domain Ownership Matrix

| Member | Defense Role | Primary UI Suite | Key Pages & Code Assets |
| :--- | :--- | :--- | :--- |
| **1. Bustamante, Alwinson** | Lead System Architect & Design System Lead | Design System, Tokens & Shell | `tokens.css`, `custom.css`, `components.php`, `navbar.php`, `search-modal.php` |
| **2. Baco, Nico** | Public Suite & Compliance Specialist | Public Discovery & Legal Hub | `index.php`, `about-us.php`, `updates.php`, `faqs.php`, `privacy.php`, `terms.php` |
| **3. Cruzpe, Julius Robert** | Authentication & Validation Engineer | Authentication & Security UI | `login.php`, `register.php`, `forgot-pass.php`, `password-strength.js`, `auth.css` |
| **4. Layco, Andrei Von Breydan** | Student Experience & Pipeline Engineer | Student Portal & Application Flow | `student/dashboard.php`, `student/jobs.php`, `job-details.php`, `apply.php`, `my-applications.php` |
| **5. Salognon, Joeven** | Employer Suite & Hiring Workflow Engineer | Employer Workspace & Evaluation | `employer/dashboard.php`, `create-job.php`, `edit-job.php`, `applicants.php`, `review-app.php` |
| **6. Jurado, Marl Jordan** | System Administration & QA Lead | Admin Governance & Quality Assurance | `admin/reports.php`, `admin/users.php`, `admin/categories.php`, `print.css`, Viewport QA |

---

## 🏛️ Section 1: Design System & UI Architecture (Alwinson Bustamante)

> [!info] 🎨 Domain Focus: Visual Language & Tokens
> 
> * **Theme Definition**: The platform implements a ==tactile Paper Sheet Design System== reflecting an official academic institution rather than a generic corporate dashboard.
> * **Color Token Palette**:
>   * `--canvas`: Desk background (`#CDC0AB` light mode / `#0F0F10` dark mode).
>   * `--surface`: Primary elevated paper sheet (`#F5F4F0` / `#1A1A1E`).
>   * `--cream`: Alternating contrast surface (`#F1EBDC` / `#232328`).
>   * `--ink`: Deep black text & high-contrast typography (`#161616` / `#E8E6E3`).
>   * `--line`: Hairline sheet borders (`#E5E0D4` / `#2E2E33`).
>   * `--accent`: KLD institutional green accent (`#2ECC5E` / `#34D865`).
>   * `--accent-soft`: Mint tint for dynamic badges & pill highlights (`#DDF3E2` / `#1B3324`).
> * **Typography**: Clean humanist sans-serif stack (`Inter`, system UI fallback) prioritizing maximum legibility.

> [!important] ⚠️ Strict Architectural UI Rules
> 
> * **Prohibition of Decorative Pill Badges**: Headings (`h1`, `h2`) stand cleanly with strong typographic hierarchy. Decorative rounded badges or floating eyebrow pills above titles are ==strictly prohibited==.
> * **Badges Reserved for Dynamic System Data**: Badges are exclusively used for dynamic state representation (`Pending Review`, `Accepted`, `Declined`), numeric notification counters, and functional attribute chips (`Part-Time`, `₱80/hr`).
> * **Single Source of Truth**: All UI components are rendered via `includes/components.php` rather than repetitive inline HTML.

> [!tip] 🛠️ Core Shared Components in `includes/components.php`
> 
> * `render_page_head($eyebrow, $title, $lead, $actions)`: Consistent inner-page headers without floating pill badges.
> * `render_status_badge($status)`: Centralized mapping of workflow statuses to color-coded semantic badges.
> * `render_job_card($job)`: Job cards featuring category photo fallbacks, slot progress bars, hourly rates, and action buttons.
> * `render_empty_state($icon, $title, $body, $cta)`: Reusable empty-state cards for zero-data views.
> * `render_flash()`: Dismissible floating paper alerts for system messages.
> * `render_availability_matrix($selected, $name, $readonly)`: 6-day by 3-shift student schedule grid.

---

## 🌐 Section 2: Public Suite & Institutional Compliance (Nico Baco)

> [!info] 🌍 Domain Focus: Public Onboarding & Discovery
> 
> * **Landing Page Hero (`index.php`)**: Features value propositions for both students and campus offices, with dual CTAs (*Find Opportunities* and *Post a Vacancy*).
> * **Live Key Metrics Strip**: Displays live system stats: Total Active Postings, Partnered Campus Offices, Students Hired to Date, and Average Hourly Pay.
> * **KLD Bento Grid Category Explorer**: Asymmetrical grid showcasing primary campus departments (MIS, Library, Registrar, ICDI Laboratories, Peer Tutors) with live requisition counters.
> * **Treasure Map Journey**: 3-step student onboarding sequence (*Discover -> Apply with Schedule -> Get Hired*).

> [!tip] 📜 Institutional Compliance & Content Architecture
> 
> * **Developer Showcase (`about-us.php`)**: Features 6 formal 2x2 portrait cards adhering to university dress codes (KLD green blazers, clean neutral backgrounds, strictly free of casual accessories) with light/dark photo switching.
> * **Interactive 3D DevBlog**: CSS 3D perspective stage displaying daily engineering logs from `data/devblogs.json`.
> * **Data Privacy Policy (`privacy.php`)**: Full statutory alignment with the Philippine ==Data Privacy Act of 2012 (Republic Act No. 10173)== governing student IDs, resumes, and schedule data.
> * **Terms of Service (`terms.php`)**: Enforces the university labor limit of ==maximum 20 hours per week== during active semesters to safeguard student academic priorities.
> * **Campus FAQs (`faqs.php`)**: Searchable Bootstrap accordion addressing 10 common questions regarding eligibility, stipend schedules, and duty adjustments during exam weeks.

---

## 🔐 Section 3: Authentication & Client-Side Validation (Julius Robert Cruzpe)

> [!info] 🔑 Domain Focus: Access Control & Real-Time Validation
> 
> * **Role-Switching Login Tabs (`login.php`)**: Clean tabbed interface separating *Student Sign In* from *Campus Employer & Administrator Sign In*.
> * **Demo Quick-Fill Helpers**: One-click test credential chips for quick evaluator switching (`Student`, `Employer`, `Admin`) without manual typing during demonstrations.
> * **Anti-Enumeration Password Reset (`forgot-pass.php`)**: Minimalist single-input form displaying a uniform success notice regardless of account existence to prevent user enumeration attacks.

> [!tip] 📝 Multi-Role Registration Gateway (`register.php`)
> 
> * **Dynamic Role-Morphing Form**:
>   * *Student Assistant*: Requires Student ID, Course, Year Level, and institutional `@kld.edu.ph` email.
>   * *University Office*: Department selector and campus building location (instantly verified).
>   * *Approved Partner Employer*: Requires Company Name, Industry, and Business Permit / DTI document upload (placed in `pending_approval` queue).
> * **5-Point Dynamic Password Strength Meter (`assets/js/password-strength.js`)**:
>   * Evaluates 5 regex criteria in real-time: Minimum 8 characters, Uppercase (`[A-Z]`), Lowercase (`[a-z]`), Numbers (`[0-9]`), and Symbols (`[!@#$%^&*]`).
>   * Dynamic visual progress bar: 🔴 **Weak** (<40%), 🟡 **Medium** (40%–75%), 🟢 **Strong** (>75%).
>   * Form submission is blocked until password requirements and match validations pass.

---

## 🎓 Section 4: Student Portal & Application Pipeline (Andrei Von Breydan Layco)

> [!info] 📊 Domain Focus: Student Discovery & Application Lifecycle
> 
> * **Student Dashboard (`student/dashboard.php`)**:
>   * Metric cards: Total Applications, In Review, Scheduled Interviews, Accepted Offers.
>   * Recommended requisitions filtered by student department.
>   * Real-time chronological application timeline.
> * **Faceted Job Search (`student/jobs.php`)**:
>   * Multi-facet filter controls: Keyword search, Category dropdown, Department filter, Work Setup (On-Campus, Hybrid, Remote), and Pay Type toggle (Hourly vs Stipend).
>   * Slot progress bars (`X of Y slots filled`) showing visual vacancy urgency.

> [!example] 📅 The Weekly Availability Matrix (`student/apply.php`)
> 
> * **The Student Dilemma**: Regular job portals only ask for resumes, leading to scheduling conflicts between work duties and college classes.
> * **Our Solution**: An interactive 6-day (Monday to Saturday) by 3-shift time grid:
>   * *Morning*: 8:00 AM – 12:00 NN
>   * *Afternoon*: 1:00 PM – 5:00 PM
>   * *Evening*: 5:00 PM – 8:00 PM
> * **Mobile Usability**: Built with responsive overflow and an explicit touch swipe hint (`Swipe horizontally to view full schedule`).

> [!tip] 📋 4-Stage Visual Application Stepper (`student/my-applications.php`)
> 
> * Tracks application progress across 4 standardized milestones:
>   1. 🟡 `Pending Review`: Initial submission state.
>   2. 🔵 `Under Evaluation`: Application is actively being reviewed by the office supervisor.
>   3. 🟣 `Interview Scheduled`: Displays an alert card with interview date, time, and venue/Zoom link.
>   4. 🟢 `Accepted / Hired` OR 🔴 `Declined / Position Filled`.
> * Provides a self-service **Withdrawal Trigger** for pending submissions.

---

## 🏢 Section 5: Employer Suite & Candidate Evaluation (Joeven Salognon)

> [!info] 💼 Domain Focus: Department Workspaces & Candidate Review
> 
> * **Employer Dashboard (`employer/dashboard.php`)**: Department banner, KPI cards (Active Postings, Total Candidates, Pending Reviews, Hired SAs), and vacancy management table.
> * **Requisition Composer (`employer/create-job.php`)**:
>   * Form fields: Title, Category, Slots Total, Pay Rate, Duty Shifts, and Description.
>   * **Labor Policy Enforcement**: Enforces a strict maximum limit of ==20 hours per week== for student assistantships.
>   * Featured requisition toggle (`is_featured`).
> * **Candidate Ledger (`employer/applicants.php`)**: Filterable roster organized by vacancy and hiring stage.

> [!example] 🔍 Candidate Evaluation Drawer (`employer/review-app.php`)
> 
> * **Candidate Dossier**: Displays applicant profile, degree program, year level, submission date, cover letter, and secure resume view link.
> * **Schedule Heatmap Comparison**: Renders the student's selected availability matrix in read-only mode so the supervisor can cross-reference candidate availability against department shifts.
> * **3-Way Decision Controls**:
>   1. `[ Shortlist for Interview ]`: Opens modal to set Date, Time, and Venue/Link ➔ Transitions status to `interview_scheduled`.
>   2. `[ Accept / Hire Candidate ]`: Automatically increments `slots_filled` ➔ Transitions status to `accepted`.
>   3. `[ Decline Application ]`: Allows supervisor to input constructive feedback notes ➔ Transitions status to `declined`.

---

## 🛠️ Section 6: System Administration, Analytics & QA (Marl Jordan Jurado)

> [!info] 📈 Domain Focus: Institutional Governance & Quality Assurance
> 
> * **Institutional Analytics & Printable Reports (`admin/reports.php`)**:
>   * Department Hiring Quota Fulfillment gauges across campus offices.
>   * Category demand breakdowns and candidate funnel metrics.
>   * **Dedicated Print Engine (`assets/css/features/print.css`)**: Invokes `window.print()` using clean monochrome styling that strips away navigation bars, action buttons, and digital artifacts to produce an official board-ready report.
> * **Category Taxonomy Management (`admin/categories.php`)**: CRUD interface for campus job categories with Bootstrap Icon pickers.

> [!important] 🛡️ Document Verification Queues (`admin/users.php`)
> 
> * **Partner Employer Accreditation Queue**:
>   * External employers cannot post jobs until verified.
>   * Administrators inspect uploaded Business Permits / DTI registration documents in an audit modal and execute Approve/Reject decisions.
> * **Student Profile Change Requests Queue**:
>   * Locked academic fields (Student ID, Program, Year Level) cannot be modified directly by students.
>   * Students must submit a request accompanied by an official Certificate of Registration (COR) document, which administrators audit before approving.
> * **Responsive Quality Assurance**: Verified across 8 distinct viewports (Desktop, Laptop, Tablet, Mobile, Half-Screen Split) with double-submit debouncing across all action forms.

---

## ⏱️ Section 7: 5-Minute Team Defense Speaking Relay

When presenting to the panel, follow this sequential baton handoff:

```
[0:00 - 1:00] Bustamante, Alwinson  ──▶ System Architecture, Paper Sheet Design & CSS Tokens
[1:00 - 1:45] Baco, Nico             ──▶ Landing Page, Bento Grid Explorer & About Us
[1:45 - 2:30] Cruzpe, Julius Robert  ──▶ 1-Click Demo Login & Real-Time Password Strength
[2:30 - 3:30] Layco, Andrei          ──▶ Student Job Search, Weekly Availability Matrix & Stepper
[3:30 - 4:15] Salognon, Joeven       ──▶ Candidate Evaluation Drawer, Shift Heatmap & Decision Gate
[4:15 - 5:00] Jurado, Marl Jordan    ──▶ Verification Queues (Permits/COR) & Printable Report
```

> [!tip] 🎤 Transition Phrases Between Members
> 
> * **Alwinson to Nico**: *"Now that we have reviewed our design system and token foundation, Nico will walk you through our public discovery suite and institutional landing page."*
> * **Nico to Julius**: *"To demonstrate how users securely access these opportunities, Julius will present our multi-role authentication and validation engine."*
> * **Julius to Andrei**: *"With authentication established, Andrei will demonstrate the primary user journey from the student's perspective."*
> * **Andrei to Joeven**: *"Once a student submits their availability matrix, the requisition moves to the campus office. Joeven will now show the employer evaluation drawer."*
> * **Joeven to Marl**: *"To complete the ecosystem, Marl will present the administrative governance queues, category controls, and institutional reporting."*

---

## ❓ Section 8: Top 10 High-Yield Panel Questions & Answers

### 1. General UI & Architecture
* **Q: Why did you build a custom Paper Sheet design instead of using a standard Bootstrap theme?**
  * **Answer:** *"Standard Bootstrap looks generic. For KLD Campus Hire, we engineered a tactile Paper Sheet aesthetic that reflects an official university administrative environment—warm paper surfaces (`#F5F4F0`), high-contrast ink typography (`#161616`), delicate sheet borders, and KLD green accents."*
* **Q: How did you ensure visual consistency across all pages built by 6 different developers?**
  * **Answer:** *"We established a single source of truth in `assets/css/tokens.css` for all layout dimensions, radii, and color variables. Additionally, all core components (job cards, status badges, page headers, empty states) are rendered through shared PHP functions in `includes/components.php`."*

### 2. Public Suite & Compliance
* **Q: Why use a Bento Grid layout on the homepage?**
  * **Answer:** *"Traditional job boards rely on monotonous vertical lists. The Bento Grid provides dynamic visual hierarchy, giving prominent weight to major departments like MIS and the Library while displaying live position counters, making campus exploration engaging."*
* **Q: How does the UI communicate labor laws and university policies?**
  * **Answer:** *"Our `terms.php` surfaces the university-mandated 20-hour weekly cap, while our job creation forms physically restrict weekly hour inputs to 20 to protect students from academic overload."*

### 3. Authentication & Security
* **Q: How does the dynamic password strength algorithm work in real-time?**
  * **Answer:** *"In `password-strength.js`, an input event listener checks the password against 5 criteria: length, uppercase, lowercase, numbers, and symbols. It calculates a strength score, animates the progress bar color from red to green, and actively blocks submission until security criteria are met."*
* **Q: Why did you include demo login chips on the sign-in page?**
  * **Answer:** *"The demo chips allow one-click role switching during evaluation. They populate test credentials for students, employers, and administrators instantly without requiring manual typing."*

### 4. Student Application Flow
* **Q: Why did you create the Weekly Availability Matrix instead of just accepting resumes?**
  * **Answer:** *"Student assistants are full-time students first. A resume does not show class hours. The matrix lets students check off their available shifts (Mon–Sat across Morning, Afternoon, and Evening), allowing supervisors to instantly identify if the applicant can cover department shifts without class conflicts."*
* **Q: How does the student track their application progress?**
  * **Answer:** *"In `student/my-applications.php`, we built a 4-step visual stepper (`Pending Review -> Under Evaluation -> Interview Scheduled -> Accepted / Declined`). When an interview is booked, Step 3 displays a badge with the exact date, time, and venue or Zoom link."*

### 5. Employer & Admin Suite
* **Q: How does the employer evaluate applicants efficiently?**
  * **Answer:** *"In `employer/review-app.php`, the applicant's schedule is rendered as a read-only availability heatmap alongside their dossier and resume link. The supervisor can make decisions immediately using 3 streamlined action gates: Shortlist, Accept, or Decline."*
* **Q: How does the reporting module cater to university officials who need hard copies?**
  * **Answer:** *"In `admin/reports.php`, clicking 'Print Report' triggers `window.print()`. Our dedicated `print.css` stylesheet strips away navigation bars, search modals, and backgrounds, outputting an executive black-and-white summary formatted for institutional meetings."*

---

> [!success] 🎯 Defense Final Checklist
> 
> * [ ] Open [index.php](file:///c:/xampp/htdocs/Final-Campus-Job-Posting-System/index.php) in browser and test `Ctrl+K` spotlight search.
> * [ ] Open [login.php](file:///c:/xampp/htdocs/Final-Campus-Job-Posting-System/login.php) and verify the 1-click demo chips work smoothly.
> * [ ] Open [student/jobs.php](file:///c:/xampp/htdocs/Final-Campus-Job-Posting-System/student/jobs.php) and demonstrate faceted category filtering.
> * [ ] Open [student/apply.php](file:///c:/xampp/htdocs/Final-Campus-Job-Posting-System/student/apply.php) and show the Weekly Availability Matrix.
> * [ ] Open [employer/review-app.php](file:///c:/xampp/htdocs/Final-Campus-Job-Posting-System/employer/review-app.php) and show the candidate schedule heatmap.
> * [ ] Open [admin/reports.php](file:///c:/xampp/htdocs/Final-Campus-Job-Posting-System/admin/reports.php) and show the Print Report preview.
