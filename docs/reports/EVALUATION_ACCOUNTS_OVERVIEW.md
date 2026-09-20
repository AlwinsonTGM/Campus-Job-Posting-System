# 📋 KLD Campus Job Posting System — Master Accounts & Activity Directory

This document provides a comprehensive operational breakdown of **all 10 accounts** pre-configured in the **KLD Campus Job Posting System (Campus Hire)** database and datastore.

> **Note on Login Chips vs. Full Database:**  
> The login page previously had quick-click chips for only 4 archetype shortcuts (`Student`, `Campus Office`, `Partner`, and `Admin`). However, the system contains **10 relational accounts** specifically designed to test every edge case in the campus employment workflow—including accepted hires, scheduled interviews, fresh applicants, internal offices, external industry partners, food concessionaires, and pending accreditation gates.

---

## 🔑 Master Credentials Cheat Sheet (All 10 Accounts)

| # | Persona / Entity | Email Address | Password | Role | Verification | Key Test Scenario |
| :-: | :--- | :--- | :--- | :-: | :-: | :--- |
| **1** | 🎓 **Juan Dela Cruz** | `student@kld.edu.ph` | `Password123!` | `student` | `verified` | Active applicant: 2 scheduled interviews, 1 pending COR request |
| **2** | 🎓 **Maria Santos** | `maria.santos@kld.edu.ph` | `Password123!` | `student` | `verified` | **Hired / Accepted Student**: Tests acceptance banner & withdraw flow |
| **3** | 🎓 **Alwinson Bustamante** | `abustamante@kld.edu.ph` | `@Alwinson100` | `student` | `verified` | Fresh student applicant: Zero applications, ready for fresh apply |
| **4** | 🏛️ **University Registrar** | `registrar@kld.edu.ph` | `Password123!` | `employer` | `verified` | Internal office: Manages clerical & library roles, scheduled interviews |
| **5** | 💻 **MIS Directorate** | `itdept@kld.edu.ph` | `Password123!` | `employer` | `verified` | Internal tech office: Manages computer lab & peer tutor requisitions |
| **6** | 🏢 **TechVanguard Solutions** | `techvanguard@partner.kld.edu.ph` | `Password123!` | `employer` | `verified` | External MOA partner: High-pay hybrid Web Dev OJT internship |
| **7** | 🎨 **Dasma Creative Media** | `mediahub@partner.kld.edu.ph` | `Password123!` | `employer` | `verified` | Creative media partner: Social media & content creator vacancy |
| **8** | ☕ **Campus Cafe & Co.** | `campuscafe@partner.kld.edu.ph` | `Password123!` | `employer` | `verified` | Campus food concessionaire: On-campus student barista position |
| **9** | ⏳ **Apex Robotics & Auto** | `apexrobotics@partner.kld.edu.ph` | `Password123!` | `employer` | `pending_approval` | **Unverified Partner Gate**: Awaiting admin accreditation review |
| **10**| 🛡️ **System Administrator** | `admin@kld.edu.ph` | `Password123!` | `admin` | `verified` | Full platform control: Accreditation queue, COR queue, analytics |

---

## Part 1: Student Accounts (3 Accounts)

### 1. Juan Dela Cruz (`student@kld.edu.ph`)
* **Role:** `student` | **User ID:** `1` | **Student ID:** `2024-00123`
* **Department:** Institute of Computing and Digital Innovation (ICDI)
* **Program:** BS Information Systems (BSIS), 2nd Year
* **Phone:** `+63 920 111 2222` | **Status:** `active` / `verified`
* **Schedule Availability (20-Hour Cap):** Mon–Thu 8:00 AM–12:00 PM, Fri 1:00 PM–5:00 PM (20 hrs/week total).
* **Active Applications:**
  * **Application #1 (Job 1 - Computer Lab Technical Assistant - MIS):** Status: `interview_scheduled` on August 30, 2026 at 02:00 PM in KLD Tech Building Room 305. Supervisor Note: *"Strong background in PC troubleshooting. Scheduled for face-to-face technical interview."*
  * **Application #2 (Job 2 - Student Records Assistant - Registrar):** Status: `interview_scheduled` on August 28, 2026 at 02:00 PM in Admin Building Room 102. Supervisor Note: *"Application received, awaiting registrar committee evaluation."*
* **Pending Change Request:** Request ID #1 in `profile_requests` awaiting admin review for birthdate correction (`2007-04-10`, age 19) with submitted COR proof PDF.

---

### 2. Maria Santos (`maria.santos@kld.edu.ph`)
* **Role:** `student` | **User ID:** `2` | **Student ID:** `2024-00456`
* **Department:** Institute of Computing and Digital Innovation (ICDI)
* **Program:** BS Computer Science (BSCS), 3rd Year
* **Phone:** `+63 928 234 5678` | **Status:** `active` / `verified`
* **Schedule Availability:** Monday & Wednesday 8:00 AM – 12:00 PM (8 hrs/week).
* **Active Applications:**
  * **Application #3 (Job 3 - Digital Cataloging & Library Aid):** Status: **`accepted`** (Hired!). Interview conducted August 23, 2026. Supervisor Note: *"Excellent communication skills and schedule fit. Officially approved for 1st Semester duty."*
* **Key Functionality Tested:**  
  Logging in as Maria demonstrates the **Accepted-Student Experience** in `student/my-applications.php`, which triggers the congratulatory status card and the opt-in **"Withdraw Others"** workflow with CSRF protection.

---

### 3. Alwinson Bustamante (`abustamante@kld.edu.ph`)
* **Role:** `student` | **User ID:** `10` | **Student ID:** `2025-2-000065`
* **Program:** BS Information Systems (BSIS), 2nd Year
* **Phone:** `9278664393` | **Password:** `@Alwinson100` | **Status:** `active` / `verified`
* **Active Applications:** **0 (Clean Slate)**
* **Key Functionality Tested:**  
  Logging in as Alwinson provides an unencumbered applicant view. Perfect for testing fresh job searches, applying to open vacancies, uploading new resumes/study loads, and configuring initial availability matrices.

---

## Part 2: Internal Campus Office Employers (2 Accounts)

### 4. Prof. Roberto Hernandez (`registrar@kld.edu.ph`)
* **Role:** `employer` | **Subtype:** `university_office` | **User ID:** `3`
* **Organization:** Office of the University Registrar (Admin Building, 1st Floor, Room 102)
* **Phone:** `(046) 416-0000 loc 102` | **Accreditation:** `INTERNAL-UNIV-REG01` (Pre-verified)
* **Requisitions Managed:**
  * **Job #2: Student Records & Archival Assistant:** 4 total slots, 2 filled, 2 open. Active candidate: Juan Dela Cruz (interview scheduled).
  * **Job #3: Digital Cataloging & Library Aid:** 3 total slots, 1 filled, 2 open. Active candidate: Maria Santos (accepted).
  * **Job #9: Gymnasium Equipment Custodian:** 3 slots, 1 filled.
  * **Job #10: Intramural Scorekeeper & Athletics Aide:** 2 slots, 0 filled.
* **Key Functionality Tested:** Candidate Evaluation Drawer (`review-app.php`), candidate status mutation, interview time/venue booking, and slot auto-closure locking.

---

### 5. Engr. Clara Villanueva (`itdept@kld.edu.ph`)
* **Role:** `employer` | **Subtype:** `university_office` | **User ID:** `4`
* **Organization:** Management Information Systems (MIS) (Tech Building, 3rd Floor, MIS Center)
* **Phone:** `(046) 416-0000 loc 305` | **Accreditation:** `INTERNAL-UNIV-MIS02` (Pre-verified)
* **Requisitions Managed:**
  * **Job #1: Computer Lab Technical Assistant:** ₱85/hr, 5 total slots, 3 filled, 2 open. Active applicant: Juan Dela Cruz (interview scheduled).
  * **Job #4: Peer Tutor - Linear Algebra & Discrete Math (ISM):** ₱100/hr, 2 total slots, 1 filled, 1 open.
* **Key Functionality Tested:** Reviewing technical skill qualifications, evaluating student availability heatmaps against computer lab operating hours.

---

## Part 3: Accredited External & Partner Employers (4 Accounts)

### 6. Atty. Katrina Alcantara (`techvanguard@partner.kld.edu.ph`)
* **Role:** `employer` | **Subtype:** `approved_partner` | **User ID:** `6`
* **Company:** TechVanguard Solutions Inc. (Dasma Tech Park, Building B, 4th Floor)
* **Phone:** `(046) 489-7200` | **Accreditation:** `MOA-2026-IT004` (Verified)
* **Permit Document:** `uploads/permits/sample_permit_apex.svg` on file.
* **Requisition Managed:**
  * **Job #5: Junior Web Developer Intern (OJT):** Hybrid setup (2 days on-site, 2 days remote), ₱120.00/hour, 15–20 hrs/week, 3 total slots, 1 filled, 2 open.
* **Key Functionality Tested:** External corporate internship/practicum postings, industry partner badges, and academic credit compatibility.

---

### 7. Marco V. Domingo (`mediahub@partner.kld.edu.ph`)
* **Role:** `employer` | **Subtype:** `approved_partner` | **User ID:** `7`
* **Company:** Dasma Creative Media Studio (Creative Hub Center, Suite 201)
* **Phone:** `+63 919 888 4321` | **Accreditation:** `MOA-2026-M012` (Verified)
* **Requisition Managed:**
  * **Job #6: Part-Time Social Media & Content Creator:** ₱110.00/hour, 12–16 hrs/week, 2 slots, 0 filled, 2 open.
* **Key Functionality Tested:** Near-campus flexible creative employment opportunities for multimedia arts and communications students.

---

### 8. Chef Patricia Reyes (`campuscafe@partner.kld.edu.ph`)
* **Role:** `employer` | **Subtype:** `approved_partner` | **User ID:** `8`
* **Company:** Campus Cafe & Co. (University Student Pavilion & Commercial Arcade)
* **Phone:** `+63 927 555 1234` | **Accreditation:** `CONC-2026-F007` (Verified Concessionaire)
* **Requisition Managed:**
  * **Job #7: Student Barista & Customer Service Crew:** ₱90.00/hour, 10–15 hrs/week, 3 slots, 1 filled, 2 open.
* **Key Functionality Tested:** Food service and commercial student assistant opportunities on campus grounds.

---

### 9. Engr. Dennis Ramirez (`apexrobotics@partner.kld.edu.ph`)
* **Role:** `employer` | **Subtype:** `approved_partner` | **User ID:** `9`
* **Company:** Apex Robotics & Automation PH Corp. (Innovation Tower, Dasmariñas City)
* **Phone:** `+63 917 889 5432` | **Accreditation:** `SEC-CS2026-098412`
* **Account Status:** `active` | **Verification Status:** **`pending_approval`**
* **Requisitions Managed:** **0 (Posting Gated)**
* **Key Functionality Tested:**  
  Tests the **Unverified Partner Security Wall**. When logging in as Dennis, the portal shows a notification that the business registration and permit are under administrative review. The employer cannot publish live student vacancies until the Administrator approves the accreditation.

---

## Part 4: Institutional Administration (1 Account)

### 10. KLD Campus System Admin (`admin@kld.edu.ph`)
* **Role:** `admin` | **User ID:** `5`
* **Unit:** Student Affairs & Services Office (SASO) / MIS Directorate (Main Building, 2nd Floor)
* **Phone:** `(046) 416-0000 loc 101` | **Status:** `active`
* **Active Administrative Tasks & Queues:**
  1. **Employer Accreditation Gate (`/admin/users.php`):** Review Apex Robotics (`apexrobotics@partner.kld.edu.ph`), inspect submitted SEC permit, and approve or reject accreditation.
  2. **Student Profile Correction Gate (`/admin/requests.php`):** Review Juan Dela Cruz’s COR update request, inspect proof file, and approve or reject demographic mutations.
  3. **Statutory Compliance Monitoring (`/admin/reports.php`):** Automated auditing to ensure zero jobs exceed the statutory 20-hour weekly student labor ceiling.
  4. **Announcements Management (`/admin/updates.php`):** Author and manage career fair news, policy guidelines, and resume workshop articles.
  5. **System Mode Switcher:** Toggle between Demo Datastore mode and live relational mode.

---

## Summary Matrix: System Testing Coverage by Account

| Feature / System Boundary | Account to Log In With | Expected Result |
| :--- | :--- | :--- |
| **Apply for a Job** | `abustamante@kld.edu.ph` | Fresh application submission with resume upload and shift selection |
| **View Scheduled Interviews** | `student@kld.edu.ph` | Displays interview date, time, venue, and supervisor notes for 2 jobs |
| **Accepted Student Banner** | `maria.santos@kld.edu.ph` | Displays acceptance card with "Withdraw Other Applications" button |
| **Evaluate Candidates** | `registrar@kld.edu.ph` | Review applicants, inspect schedule heatmaps, mutate status |
| **Technical Requisitions** | `itdept@kld.edu.ph` | Manage IT support and tutoring requisitions |
| **Hybrid Corporate OJT** | `techvanguard@partner.kld.edu.ph` | High-pay hybrid internship posting with verified partner badge |
| **Unverified Partner Gate** | `apexrobotics@partner.kld.edu.ph` | Restricted access; shows pending accreditation message |
| **Approve Accreditation & COR**| `admin@kld.edu.ph` | Full authority to approve Apex Robotics and Juan Dela Cruz's COR |

---
*Comprehensive Master Account Guide for the KLD Campus Job Posting System.*