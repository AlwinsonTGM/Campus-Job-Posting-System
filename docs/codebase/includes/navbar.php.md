---
title: "includes/navbar.php — Dynamic Role-Aware Navigation Bar Partial"
type: "code-walkthrough"
layer: "presentation / partials / navigation"
original_file: "includes/navbar.php"
tags:
  - code-walkthrough
  - navbar
  - rbac-navigation
  - notification-bell
  - paper-sheet
  - defense-core
aliases:
  - NavbarPartial
  - navbar.php
related:
  - "[[includes/auth-check.php.md]]"
  - "[[includes/header.php.md]]"
  - "[[includes/footer.php.md]]"
  - "[[includes/search-modal.php.md]]"
---

# 🧭 `includes/navbar.php` — Dynamic Role-Aware Navigation Bar

> [!abstract] 📌 Executive Summary
> `includes/navbar.php` is the **contextual navigation bar controller**. It determines the visitor's authenticated role (`student`, `employer`, `admin`, or guest), queries live unread notification counters, dynamically resolves deep dashboard routes, and delegates presentation to `includes/templates/includes-navbar-view.php`.

---

## 🏛️ Placement in System Architecture

```mermaid
flowchart TD
    Req["Page loads navbar.php"] --> CheckUser["get_logged_user()"]
    
    CheckUser --> RoleRouter{"Identify Active Role"}
    
    RoleRouter -- "Guest" --> NavGuest["Render Public Links:<br/>• Find Jobs<br/>• Career Updates<br/>• Sign In / Register buttons"]
    
    RoleRouter -- "Student" --> NavStudent["Render Student Portal Links:<br/>• Find Jobs (student/jobs.php)<br/>• My Applications (student/my-applications.php)<br/>• Availability Settings"]
    
    RoleRouter -- "Employer" --> NavEmp["Render Office Links:<br/>• Department Dashboard (employer/dashboard.php)<br/>• Post a Vacancy (employer/create-job.php)<br/>• Applicants Ledger (employer/applicants.php)"]
    
    RoleRouter -- "Admin" --> NavAdmin["Render Governance Links:<br/>• Executive Reports (admin/reports.php)<br/>• User & Verification Queues (admin/users.php)<br/>• Category Taxonomy (admin/categories.php)"]

    CheckUser --> NotifQuery["Query Unread Count via get_unread_notifications_count()"]
    NotifQuery --> BellBadge["Render Dynamic Red Bell Counter Badge"]

    NavGuest & NavStudent & NavEmp & NavAdmin & BellBadge --> Template["Require includes/templates/includes-navbar-view.php"]

    %% Semantic styling
    classDef step fill:#EFF6FF,stroke:#2563EB,stroke-width:1px,color:#1E40AF;
    classDef check fill:#FEF3C7,stroke:#D97706,stroke-width:2px,color:#92400E;
    classDef out fill:#DCFCE7,stroke:#16A34A,stroke-width:2px,color:#166534;

    class Req,CheckUser,NotifQuery,BellBadge,Template step;
    class RoleRouter check;
    class NavGuest,NavStudent,NavEmp,NavAdmin out;
```

---

## 🔍 Detailed Function-by-Function Breakdown

### 1. Context & Role Resolution (Lines 6–14)
```php
require_once __DIR__ . '/auth-check.php';
$current_user = get_logged_user();
$current_script = basename($_SERVER['PHP_SELF'] ?? '');
$dashboard_link = $base_url . get_role_dashboard_url($current_user['role'] ?? 'student');
```
* **Active Script Detection**: Uses `basename($_SERVER['PHP_SELF'])` to mark the active page link with `.active` styling.
* **Role Dashboard Link**: Maps to the canonical destination using `get_role_dashboard_url()`.

---

### 2. Live Notification Badging (Lines 15–16)
```php
$user_unread_notifs_count = ($current_user && function_exists('get_unread_notifications_count')) 
    ? get_unread_notifications_count($current_user['id']) 
    : 0;
$user_recent_notifs = ($current_user && function_exists('get_user_notifications')) 
    ? get_user_notifications($current_user['id'], 5) 
    : [];
```
* Queries real-time notification counts directly for the authenticated user, feeding both the red bell badge and the quick-view dropdown menu.

---

### 3. Template Delegation (Line 18)
```php
require __DIR__ . '/templates/includes-navbar-view.php';
```
* Follows the platform's strict Separation of Concerns, delegating HTML markup to the view template.

---

## 🛡️ Security & Panel Defense Talking Points

> [!tip] 🎤 High-Yield Defense Q&A for this File
> 
> **Q: How does the navigation bar adapt dynamically between students, employers, and administrators?**
> * **Answer:** *"In `includes/navbar.php`, the controller inspects the session role via `get_logged_user()`. If the user is an employer, it renders links to post vacancies and review candidates. If the user is an admin, it surfaces governance links to audit queues and reports. If the user is a student, it provides job browsing and application tracking. Unauthorized links are never rendered in the client DOM."*
