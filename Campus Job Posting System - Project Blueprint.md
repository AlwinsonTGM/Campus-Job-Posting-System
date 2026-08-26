## tags: [COAL101, Web Systems and Technologies, Project Blueprint, Campus Job Posting System, Bootstrap 5, PHP, Vanilla JS, Final Project]
## aliases: [Campus Job Posting System Blueprint, Job Board Project Plan, COAL101 Project Draft]

# 🎓 Campus Job Posting System — System Blueprint & Project Draft

[[COAL101 Notes MOC|◀ COAL101 Notes MOC]] · [[1st SEM Notes MOC|🗓️ 1st Sem MOC]]

---

> [!abstract] 📌 Executive Summary
> The **Campus Job Posting System** is a dedicated university-centric web application designed to connect students with on-campus employment opportunities (e.g., Student Assistants, Office Clerks, IT Lab Assistants, Library Aides, Peer Tutors, and approved campus-affiliated employers). 
> 
> The platform streamlines job discovery, resume/application submission, application status tracking, employer candidate evaluation, and system management.

> [!important] ⚠️ Technical & Environmental Constraints
> * **Approved Tech Stack:** ==Native PHP==, ==Bootstrap 5== (via CDN or local asset), and ==Vanilla JavaScript==.
> * **Database Constraint:** ==NO DATABASE / NO XAMPP MySQL YET==. All dynamic features, session persistence, and data rendering must rely on ==PHP Sessions (`$_SESSION`)==, ==JSON mock datastores (`data/*.json`)==, or structured static PHP arrays.
> * **Project Deadline:** ==September 2, 2026== (Strict 7-day sprint).
> * **Development Team:** ==6 Members==.

---

## 🧭 System Architecture & Site Map

```
                           ┌────────────────────────────────────────────────────────┐
                           │          CAMPUS JOB POSTING SYSTEM (PORTAL)            │
                           └───────────────────────────┬────────────────────────────┘
                                                       │
         ┌─────────────────────────────────────────────┼─────────────────────────────────────────────┐
         │                                             │                                             │
┌────────┴─────────┐                         ┌─────────┴──────────┐                        ┌─────────┴──────────┐
│   PUBLIC PAGES   │                         │  STUDENT DASHBOARD │                        │ EMPLOYER/OFFICE DB │
├──────────────────┤                         ├────────────────────┤                        ├────────────────────┤
│ • index.php      │                         │ • dashboard.php    │                        │ • dashboard.php    │
│ • about-us.php   │                         │ • jobs.php         │                        │ • create-job.php   │
│ • privacy.php    │                         │ • job-details.php  │                        │ • edit-job.php     │
│ • terms.php      │                         │ • apply.php        │                        │ • applicants.php   │
│ • faqs.php       │                         │ • my-apps.php      │                        │ • review-app.php   │
│ • login.php      │                         │ • profile.php      │                        │ • categories.php   │
│ • register.php   │                         └────────────────────┘                        │ • reports.php      │
│ • forgot-pass.php│                                                                       │ • settings.php     │
│ • logout.php     │                                                                       └────────────────────┘
└──────────────────┘
```

---

## 📑 Core Module Specifications & Page Requirements

### 1. 🌐 Public & Informational Pages

#### 1.1 🏠 Index / Landing Page (`index.php`)
* **Hero Banner:** Campus-branded tagline (*"Empowering Students, Supporting Campus Offices"*), search input (keyword, category, department), and Call to Action (CTA) buttons (*"Find a Job"* & *"Post a Vacancy"*).
* **Key Metrics Strip:** Total Active Postings, Partnered Campus Offices, Students Hired to date, Average Hourly Pay.
* **Featured Job Postings:** Bootstrap card grid displaying top vacancies with badge tags (*Student Assistant*, *Part-time*, *Flexible Schedule*, *Urgent*).
* **Job Categories Grid:** Visual icons/cards representing departments (IT, Library, Registrar, Student Affairs, Laboratories, Academic Tutors).
* **Why Work On-Campus Section:** Highlighting flexible hours adjusted to class schedules, proximity, official campus certifications, and tuition support.
* **Footer:** Quick links, university logo, accreditation note, social links, Data Privacy & Terms links.

---

#### 1.2 👥 About Us / Developers Page (`about-us.php`)
> [!tip] 📸 Developer Photo Specifications
> * **Standard:** Clean, high-resolution 1:1 square or 3:4 portrait photo.
> * **Attire:** Formal / Semi-formal campus attire (Collared shirt, blazer, or school uniform).
> * **Rules:** ==No accessories== (Strictly no sunglasses, caps, casual beanies, bulky headphones, or facial stickers). Plain or neutral background.

* **Developer Profile Cards (6 Members):**
  1. **Member 1 (Project Lead / Core Architect):** System flow, auth state management, page routing.
  2. **Member 2 (Frontend Specialist — Public & Legal Pages):** Index, About Us, Privacy Policy, Terms of Service, FAQs.
  3. **Member 3 (Frontend Specialist — Authentication UI & JS Validation):** Login, Registration with Password Strength Meter, Forgot Password.
  4. **Member 4 (Frontend & Flow Specialist — Student Module):** Student Dashboard, Job Search/Filter, Job Details, Application Form.
  5. **Member 5 (Frontend & Flow Specialist — Employer Module):** Employer Dashboard, Create/Edit Job, Applicant Roster, Review Modal.
  6. **Member 6 (Admin & Data Architect — Mock Datastore & Reports):** Categories, User Management, PDF/Printable Reports Mock, JSON/Session datastore handler.
* **Mission & Vision Statement:** Our goal as BSIS / IT students to modernize student employment on campus.
* **Tech Stack Showcase:** Interactive badges for PHP 8, Bootstrap 5.3, HTML5/CSS3, JavaScript ES6.

---

#### 1.3 🔒 Data Privacy Policy (`privacy.php`)
* **Compliance:** Aligned with the Philippine **Data Privacy Act of 2012 (Republic Act No. 10173)**.
* **Collected Information:** Student ID, Full Name, Institutional Email, Course/Year, Resume/CV, Class Schedule availability, Academic Standing.
* **Purpose of Collection:** Evaluation of eligibility for campus student assistantships and internal university office staffing.
* **Data Protection & Storage:** Non-disclosure to third-party commercial entities; secure mock data session lifecycles.
* **Rights of the Data Subject:** Right to access, correct, delete application information, and withdraw applications.
* **Data Officer Contact:** Designated campus contact email and physical office location.

---

#### 1.4 📜 Terms of Service (`terms.php`)
* **Eligibility Clause:** Only currently enrolled bona fide students in good academic standing may apply for student assistantships.
* **Work Hour Regulations:** Adherence to campus policy (maximum 20 hours/week during regular semesters to prevent academic compromise).
* **Employer/Office Obligations:** Accurate job descriptions, fair stipend compensation, respect for student examination schedules.
* **Code of Conduct:** Zero tolerance for fraudulent credentials, ghost attendance, or harassment.
* **Disclaimer & Termination:** Rights of the university administration to revoke posting or applying privileges.

---

#### 1.5 ❓ Frequently Asked Questions (`faqs.php` — 10 Q&As)

> [!faq] 1. Who is eligible to apply for on-campus jobs?
> Any currently enrolled undergraduate or graduate student in good academic standing with no disciplinary records is eligible to apply.

> [!faq] 2. How many hours per week can a Student Assistant (SA) work?
> SAs are permitted to work a maximum of **20 hours per week** during regular school terms and up to **40 hours per week** during semester breaks, ensuring academic priorities remain uncompromised.

> [!faq] 3. Can I apply for multiple campus jobs at the same time?
> Yes, you can submit applications to multiple departments simultaneously. However, you can only hold **one active on-campus contract** at a time once officially hired.

> [!faq] 4. How do I know if my application was approved or shortlisted?
> You can track real-time status updates (*Pending*, *Under Review*, *Interview Scheduled*, *Accepted*, *Rejected*) in your **Student Dashboard > My Applications** tab.

> [!faq] 5. What documents are required when submitting an application?
> Standard requirements include your updated Resume/CV, Certificate of Registration (COR) / Study Load, and an optional Cover Letter stating your available hours.

> [!faq] 6. How are student assistant stipends or salaries disbursed?
> Stipends are processed semi-monthly or monthly through the University Accounting/Cashier Office or credited directly to your registered student bank/e-wallet account upon submission of signed Daily Time Records (DTR).

> [!faq] 7. How can campus departments or offices post new job openings?
> Department heads and authorized office supervisors can register with an official university email, access the **Employer Dashboard**, and submit a job requisition form via **Create Job**.

> [!faq] 8. What should I do if I forget my account password?
> Navigate to the **Forgot Password** page, enter your registered institutional email address, and follow the password reset link or instructions sent to your inbox.

> [!faq] 9. Can I adjust my work schedule during midterm or final exam weeks?
> Yes. University policy mandates campus employers to provide flexible adjustments to work shifts during designated examination periods.

> [!faq] 10. How is my personal academic and contact data protected?
> All student and employer records are strictly managed in compliance with the **Data Privacy Act of 2012 (RA 10173)** and are solely utilized for internal campus recruitment purposes.

---

### 2. 🔐 Authentication & Security Suite

```
  ┌────────────────────────────────────────────────────────────────────────┐
  │                           AUTHENTICATION FLOWS                         │
  ├────────────────────────────────────┬───────────────────────────────────┤
  │ Registration                       │ Login                             │
  │ • First & Last Name                │ • Institutional Email             │
  │ • Institutional Email (@univ.edu)  │ • Password                        │
  │ • Role Selector (Student/Employer) │ • Role Toggle                     │
  │ • Password + Real-time Meter       │ • "Remember Me" Checkbox          │
  │ • Confirm Password Validation      │ • "Forgot Password?" Link         │
  └────────────────────────────────────┴───────────────────────────────────┘
```

#### 2.1 🔑 Login & Logout (`login.php`, `logout.php`)
* **Role Switcher Tab:** Clean toggle between `[ Student Login ]` and `[ Campus Employer / Office Login ]`.
* **Inputs:** Email address, Password.
* **Helper Features:** "Remember me" checkbox, "Forgot Password?" redirect link.
* **Validation:** Server-side PHP credentials check + client-side Bootstrap form validation feedback (`.is-invalid`, `.invalid-feedback`).
* **Logout (`logout.php`):** Destroys `$_SESSION`, clears active user context, redirects to `login.php?msg=logged_out`.

---

#### 2.2 📝 Registration Page (`register.php`)
* **Critical Form Fields:**
  1. Full Name & Student ID / Department Name.
  2. Institutional Email (Validation ensures format matches `@university.edu.ph` or standard email).
  3. Role Selection: Radio or select toggle (`Student` vs `Department Employer`).
  4. **Password Input** with **Real-Time Dynamic Password Strength Meter**.
  5. **Confirm Password Input** with instant matching feedback.
  6. Terms and Privacy Agreement checkbox.
* **⚡ JavaScript Password Strength Algorithm:**
  * Minimum 8 characters.
  * Checks: Contains Uppercase (`[A-Z]`), Lowercase (`[a-z]`), Numbers (`[0-9]`), Special Symbols (`[!@#$%^&*]`).
  * Visual Multi-Level Progress Bar:
    * 🔴 **Weak** (< 40%): Red bar — *"Needs more characters & variety"*
    * 🟡 **Medium** (40% - 75%): Yellow/Orange bar — *"Good, add symbols or length"*
    * 🟢 **Strong** (> 75%): Green bar — *"Strong password!"*
  * JS dynamically blocks submission if passwords do not match.

---

#### 2.3 ❓ Forgot Password Page (`forgot-pass.php`)
> [!important] 🎯 Strict Minimalist Requirement
> * **Only Visible Form Element:** Single **Institutional Email Input** field.
> * **Supporting UI:** Explanatory prompt (*"Enter your registered email address to receive password reset instructions"*), Submit Button (*"Send Reset Instructions"*), and a return link (*"← Back to Login"*).
> * **Interactive Mock Response:** Upon submission, display a dismissible Bootstrap alert: *"If an account exists with this email, reset instructions have been dispatched."*

---

### 3. 🎓 Student Experience & Job Application Suite

```
  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
  │   Student    │ ──> │ Browse Jobs  │ ──> │ View Details │ ──> │    Apply     │
  │  Dashboard   │     │ & Filter UI  │     │ & Schedule   │     │ (Modal/Form) │
  └──────┬───────┘     └──────────────┘     └──────────────┘     └──────┬───────┘
         │                                                              │
         └───────────────────> My Applications <────────────────────────┘
                               (Track Status)
```

#### 3.1 📊 Student Dashboard (`student/dashboard.php`)
* **Welcome Card:** Personalized greeting with student name, student ID, and enrolled program.
* **Quick Metrics Counters:**
  * 📄 Total Applications Submitted.
  * ⏳ Pending Review.
  * 📅 Scheduled Interviews.
  * 🔖 Saved Jobs.
* **Recommended Vacancies:** Automated suggestions matching the student's college or department.
* **Recent Activity Feed:** Timeline of recent application status changes.

#### 3.2 🔍 Job Listings & Search (`student/jobs.php`)
* **Search & Filter Bar:**
  * Keyword search (Job title, office name).
  * Category dropdown (IT, Admin, Library, Lab, Research).
  * Work Type filter (Hourly, Stipend-based, Project-based).
  * Weekly hours selector (5-10 hrs, 10-15 hrs, 15-20 hrs).
* **Job Cards:** Displays Title, Department, Location on Campus, Hourly Rate / Allowance, Slots available, Application Deadline, and "Apply Now" / "View Details" buttons.

#### 3.3 📄 Job Details Page (`student/job-details.php`)
* Full job description, key responsibilities, qualifications, required skills, duty hours, supervisor name, office building/room number, and slot vacancy badge.

#### 3.4 📥 Apply for Job Form / Modal (`student/apply.php`)
* Cover Letter textarea.
* File Upload UI mock (Resume / Student Study Load).
* Available Shift Availability picker (Monday to Saturday timeslots).
* Confirmation modal with prompt feedback.

#### 3.5 📋 My Applications & Status Tracker (`student/my-applications.php`)
* Interactive table & cards showing all student submissions.
* **Status Badges & Progress Stepper:**
  * 🟡 `Pending Review`
  * 🔵 `Under Evaluation`
  * 🟣 `Interview Scheduled` (Shows date, time, venue / Zoom link)
  * 🟢 `Accepted / Hired`
  * 🔴 `Declined / Position Filled`
* Option to withdraw application if still pending.

---

### 4. 🏢 Campus Employer & Office Administration Suite

#### 4.1 📊 Employer Dashboard (`employer/dashboard.php`)
* Department overview banner (e.g., *"University Library Administration"*).
* **Key Metrics:** Active Job Postings, Total Received Applications, Pending Reviews, Hired Student Assistants.
* Quick Actions: `[ + Post New Vacancy ]`, `[ View Applicants ]`, `[ Generate Hiring Report ]`.

#### 4.2 ➕ Create / Edit Job (`employer/create-job.php`, `employer/edit-job.php`)
* Form inputs: Job Title, Category, Department, Number of Vacancies, Hourly Rate / Monthly Allowance, Work Shift/Hours per week, Minimum Year Level Requirement, Application Deadline, Detailed Job Description, and Specific Qualifications.
* Server-side PHP mock validation & session/JSON persistence.

#### 4.3 👥 Applicant Roster (`employer/applicants.php`)
* Filterable table by job title and status.
* Columns: Applicant Name, Course/Year, Applied Job, Submission Date, Resume Preview link, Status badge, Actions (`Review`).

#### 4.4 🔎 Application Review Modal / Page (`employer/review-app.php`)
* Comprehensive view of student application: Cover letter, schedule availability matrix, resume preview.
* **Action Decision Controls:**
  * `[ Shortlist for Interview ]` -> Sets status to *Interview Scheduled* with date/time modal.
  * `[ Accept / Hire Applicant ]` -> Sets status to *Accepted*.
  * `[ Decline Application ]` -> Sets status to *Declined* with optional polite feedback.

---

### 5. 🛠️ System Administration, Categories & Reporting

#### 5.1 🏷️ Categories Management (`admin/categories.php`)
* List of campus job categories (IT & Technical Support, Library Services, Administrative Clerk, Science & Computer Lab Assistant, Peer Tutor, Sports & Athletics Aide).
* Add new category modal & category toggle.

#### 5.2 👤 User Management (`admin/users.php`)
* Table of registered students and department employer accounts.
* Role assignment, account status (`Active`, `Suspended`), search by ID or name.

#### 5.3 📈 Reports & Analytics (`admin/reports.php`)
* Summary charts & data tables:
  * Most in-demand student job categories.
  * Total student applications per college.
  * Department hiring quotas and filled positions.
* **Print / Export Mock:** Clean printable view with `window.print()` styling and PDF download trigger mock.

#### 5.4 ⚙️ Settings (`admin/settings.php`, `student/settings.php`)
* Profile update, contact details, password change form, email notification toggles.

---

## 🗄️ Database-Less Architecture (PHP Session & JSON Mock Datastore)

> [!tip] 💡 How to Build Full Interactivity Without MySQL/XAMPP Database
> Since external MySQL databases are prohibited for this milestone, we use a modular **PHP Data Handler** that initializes default data and stores runtime state in `$_SESSION` and structured JSON files (`data/`).

```
Campus-Job-Posting-System/
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css (or Bootstrap 5 CDN)
│   │   └── custom.css
│   ├── js/
│   │   ├── bootstrap.bundle.min.js
│   │   ├── password-strength.js
│   │   └── main.js
│   └── img/
│       ├── logo.png
│       └── developers/
│           ├── member1.jpg
│           ├── member2.jpg
│           ├── member3.jpg
│           ├── member4.jpg
│           ├── member5.jpg
│           └── member6.jpg
├── includes/
│   ├── header.php
│   ├── navbar.php
│   ├── footer.php
│   ├── data-helper.php   <-- Manages mock data array / JSON / $_SESSION
│   └── auth-check.php
├── data/
│   ├── jobs.json
│   ├── applications.json
│   ├── users.json
│   └── categories.json
├── student/
│   ├── dashboard.php
│   ├── jobs.php
│   ├── job-details.php
│   ├── apply.php
│   ├── my-applications.php
│   └── settings.php
├── employer/
│   ├── dashboard.php
│   ├── create-job.php
│   ├── edit-job.php
│   ├── applicants.php
│   ├── review-app.php
│   └── reports.php
├── admin/
│   ├── categories.php
│   ├── users.php
│   ├── reports.php
│   └── settings.php
├── index.php
├── about-us.php
├── privacy.php
├── terms.php
├── faqs.php
├── login.php
├── register.php
├── forgot-pass.php
└── logout.php
```

---

## 👥 6-Member Team Task Distribution & Work Breakdown Structure (WBS)

| Member | Assigned Role | Primary Deliverables & Pages |
| :--- | :--- | :--- |
| **Member 1** | **Team Lead & Core Architecture** | • `includes/header.php`, `navbar.php`, `footer.php`<br>• `includes/data-helper.php` (Session/JSON data engine)<br>• Overall routing, theme consistency & git synchronization |
| **Member 2** | **Frontend Lead (Public & Legal Suite)** | • `index.php` (Landing page with search & featured jobs)<br>• `about-us.php` (Developer profile cards & formal photo specs)<br>• `privacy.php` (Data Privacy Act 2012 clauses)<br>• `terms.php` (Campus terms & student work rules) |
| **Member 3** | **Security & Auth Specialist** | • `login.php` & `logout.php`<br>• `register.php` (Bootstrap form + multi-role selector)<br>• `assets/js/password-strength.js` (Dynamic strength meter)<br>• `forgot-pass.php` (Strict single-email field page) |
| **Member 4** | **Student Experience Specialist** | • `faqs.php` (10 campus Q&As with accordion UI)<br>• `student/dashboard.php` & `student/jobs.php`<br>• `student/job-details.php`<br>• `student/apply.php` (Application modal/form)<br>• `student/my-applications.php` (Status tracker) |
| **Member 5** | **Employer Experience Specialist** | • `employer/dashboard.php`<br>• `employer/create-job.php` & `employer/edit-job.php`<br>• `employer/applicants.php`<br>• `employer/review-app.php` (Review & status decision triggers) |
| **Member 6** | **Admin, Analytics & Quality Assurance** | • `admin/categories.php`<br>• `admin/users.php`<br>• `admin/reports.php` (Visual metrics + Print/PDF mock)<br>• Cross-browser testing, mobile responsiveness, final audit |

---

## 📅 7-Day Sprint Roadmap (Target Deadline: September 2, 2026)

```
Day 1 (Aug 26)  ━━━> Project Setup, Blueprint Approval, Shared Layouts (Navbar/Footer)
Day 2 (Aug 27)  ━━━> Public Suite: Index, About Us, Privacy Policy, Terms, 10 FAQs
Day 3 (Aug 28)  ━━━> Auth Suite: Login, Register + JS Password Meter, Forgot Password
Day 4 (Aug 29)  ━━━> Student Suite: Dashboard, Job Search, Job Details, Apply Flow
Day 5 (Aug 30)  ━━━> Employer Suite: Dashboard, Job Creation, Applicant Review Flow
Day 6 (Aug 31)  ━━━> Admin & Reporting: Categories, User Manager, Reports, Mock Data Integration
Day 7 (Sep 01)  ━━━> Full End-to-End Testing, Polish, Mobile Responsiveness, Code Freeze
D-Day (Sep 02)  ━━━> 🚀 FINAL SUBMISSION & PROJECT PRESENTATION
```

---

## ✅ Pre-Submission Quality Assurance Checklist

- [ ] **Bootstrap 5 UI Responsiveness:** Verified on mobile (375px), tablet (768px), and desktop (1200px+).
- [ ] **No Database Dependency:** Tested cleanly without MySQL/XAMPP DB — session & mock JSON datastore functional.
- [ ] **Developer Photos Verified:** All 6 developer images are formal/semi-formal, clear, and free of accessories.
- [ ] **Auth Validation:** Password strength meter accurately tests lowercase, uppercase, digits, symbols, and length.
- [ ] **Forgot Password Layout:** Exactly one email field present.
- [ ] **10 FAQs:** Clear accordion implementation answering all 10 campus-related questions.
- [ ] **Clean Code & Directory Structure:** Well-commented PHP and modular `include` files.
