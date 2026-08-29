# 🎓 Campus Job Posting System (KLD Campus Hire)

A dedicated, university-centric student assistantship and campus employment web application built for **Kolehiyo ng Lungsod ng Dasmariñas (KLD)** as the official midterm lab capstone project for **COAL101: Web Systems and Technologies**.

The platform connects students with on-campus employment opportunities (e.g., Student Assistants, Office Clerks, IT Lab Assistants, Library Aides, Peer Tutors, and approved campus-affiliated partners) with weekly schedule availability matching, transparent 5-stage application tracking, supervisor candidate evaluation, and centralized Career Center announcements.

---

## 🚀 Key Features

### 🎓 For Students
- **Faceted Job Search**: Live multi-criteria filtering by category, department, work setup (On-Campus/Hybrid/Remote), compensation type, and employer type with instant reset.
- **Weekly Shift Availability Matrix**: Interactive 18-slot schedule grid (Monday–Saturday across Morning, Afternoon, and Evening shifts) embedded into applications to prevent academic conflicts.
- **5-Stage Application Tracker**: Visual real-time progress stepper (*Pending Review -> Under Evaluation -> Interview Scheduled -> Accepted / Declined / Withdrawn*), interview scheduling details drawer, and withdrawal flow.
- **Student Profile Management**: Locked core academic fields backed by an official change request queue with Certificate of Registration (COR) verification.

### 🏢 For Campus Employers & Department Offices
- **Requisition Composer & Editor**: Publish vacancies with hourly stipends, 20h/week labor cap enforcement, banner photos, and featured vacancy toggles with IDOR authorization protection.
- **Candidate Dossier & Review Gate**: View candidate cover letters, weekly availability heatmaps, direct resume previews, and execute 3-way hiring decisions (Shortlist for Interview, Accept/Hire, Decline).
- **Department Updates Hub**: Dispatch departmental announcements, hiring calls, and orientation notices.

### 🛠️ For System Administrators
- **Partner Accreditation Queue**: Inspect business permits, SEC/DTI documents, and verify campus partner employers.
- **Student Profile Change Queue**: Review and approve official academic profile modification requests.
- **Category Taxonomy Management**: Real-time CRUD for campus job categories with Bootstrap Icons.
- **Institutional Analytics & Reports**: Pure-CSS hiring quota gauges, in-demand job charts, and printable PDF export via `window.print()`.
- **Global Updates Management**: Full editorial control over Career Center news, tips, and system alerts.

### 🌐 Universal Enhancements
- **Tactile Paper Sheet Design System**: Clean physical paper aesthetic (`assets/css/custom.css`), warm neutrals, and KLD institutional green/gold palette.
- **Universal Spotlight Search**: Keyboard-driven floating search modal (`Ctrl+K`) querying `api/search-jobs.php` with live debounce.
- **3D Stage Coverflow DevBlog**: Interactive 3D perspective stage on `about-us.php` showcasing Alwinson's daily engineering devlogs loaded from `data/devblogs.json`.

---

## 🛠️ Technology Stack & Architecture

- **Backend**: Native PHP 8.2 (Modular Architecture, zero external dependencies).
- **Data Tier**: Flat-file JSON Datastores in `data/` (`users.json`, `jobs.json`, `applications.json`, `categories.json`, `updates.json`, `devblogs.json`, `profile_requests.json`) protected by `data/.htaccess`.
- **Frontend**: Server-Side Rendered (SSR) HTML5, Bootstrap 5.3, Bootstrap Icons, Custom Paper CSS3, Vanilla JavaScript (ES6).
- **Security**: Multi-tier RBAC (`student`, `employer`, `admin`), Anti-CSRF token verification on all POST actions, IDOR authorization contracts (`can_manage_job`, `can_review_application`, `can_view_student_resume`), Bcrypt password hashing (`PASSWORD_DEFAULT`), and client-side double-submit debouncing.
- **Test Automation**: Playwright End-to-End test harness in TypeScript (`tests/e2e/`) with 100% pass rate across 40+ specs.

---

## 👥 Development Team (BSIS201 — ICDI)

| Member | Student ID | Role |
| :--- | :--- | :--- |
| **Bustamante, Alwinson** | `2025-2-000065` | Lead System Architect & Core Backend |
| **Baco, Nico** | `2025-2-000032` | Public Suite & Legal Compliance Specialist |
| **Cruzpe, Julius Robert** | `2025-2-000091` | Authentication & Client Validation Engineer |
| **Layco, Andrei Von Breydan** | `2025-2-000176` | Student Portal & Application Flow Engineer |
| **Salognon, Joeven** | `2025-2-000269` | Department & Hiring Workflow Engineer |
| **Jurado, Marl Jordan** | `2025-2-000166` | System Administration & QA Lead |

---

## 🏁 Getting Started

### Local Development

1. **Clone the repository:**
   ```bash
   git clone https://github.com/AlwinsonTGM/Campus-Job-Posting-System.git
   cd Campus-Job-Posting-System
   ```

2. **Start the PHP Built-in Web Server:**
   ```bash
   php -S 127.0.0.1:8000
   ```

3. **Open the application:**
   Navigate to [http://127.0.0.1:8000](http://127.0.0.1:8000) in your web browser.

### Demo Credentials for Evaluation

| Persona | Email | Password | Role / Access |
| :--- | :--- | :--- | :--- |
| **Student** | `student@kld.edu.ph` | `Student@123` | Student Dashboard & Apply Suite |
| **Department Employer** | `employer@kld.edu.ph` | `Employer@123` | Employer Dashboard & Requisitions |
| **Partner Employer** | `partner@apex.com` | `Employer@123` | External Accredited Employer |
| **Administrator** | `admin@kld.edu.ph` | `Admin@123` | Full Admin Console & Analytics |

> *Tip: You can also use the one-click demo login chips on the [login page](http://127.0.0.1:8000/login.php) for instant evaluation.*

---

## 🧪 Running Automated Tests

Run the comprehensive Playwright test suite:
```bash
npm install
npx playwright test
```

---

## 📜 Compliance & Institutional Policy

- **Data Privacy Compliance**: Fully aligned with the Philippine **Data Privacy Act of 2012 (Republic Act No. 10173)**.
- **Student Labor Cap**: Strictly enforces the university standard maximum of **20 hours per week** for student assistants during active academic semesters.
