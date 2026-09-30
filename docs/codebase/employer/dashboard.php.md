---
title: "employer/dashboard.php — Employer Workspace, Requisition Archiving & Multi-Tab Controller"
type: "code-walkthrough"
layer: "controller / employer-suite"
original_file: "employer/dashboard.php"
tags:
  - code-walkthrough
  - controller
  - employer-portal
  - requisition-archiving
  - admin-only-restore
  - applicant-preservation
  - department-metrics
aliases:
  - EmployerDashboardController
  - employer/dashboard.php
related:
  - "[[includes/data-helper.php.md]]"
  - "[[includes/auth-check.php.md]]"
  - "[[includes/services/job-service.php.md]]"
  - "[[includes/templates/employer-dashboard-view.php.md]]"
---

# 🏢 `employer/dashboard.php` — Employer Workspace, Requisition Archiving & Multi-Tab Controller

> [!abstract] 📌 Executive Summary
> `employer/dashboard.php` serves as the **operational workspace and requisition management console** for campus departments, university institutes, and accredited partner employers. Gated by `require_auth(['employer', 'admin'])`, it manages job requisitions with **non-destructive archiving**:
> 1. **Dual Requisition Tabs**: Toggles between **Active** and **Archived** requisitions (`?tab=archived`).
> 2. **Candidate Evaluation Preservation**: Moving a posting to Archive via `archive_job()` immediately halts new candidate applications while **preserving 100% of candidate applications, evaluation scores, and interview records**.
> 3. **Admin-Only Restore Gate**: Employers can archive their own requisitions, but **only university administrators are authorized to restore archived requisitions** back to active status, enforcing institutional hiring quotas.

---

## 🏛️ Placement in System Architecture

```mermaid
flowchart TD
    Req["Employer accesses /employer/dashboard.php"] --> AuthGate{"require_auth(['employer', 'admin']) passed?"}
    
    AuthGate -- "No" --> RejectAuth(["Redirect to login.php or 403 Forbidden"])
    AuthGate -- "Yes" --> CheckMethod{"HTTP Request Method?"}
    
    CheckMethod -- "POST (Requisition Action)" --> CheckCSRF{"verify_csrf_token()"}
    CheckCSRF -- "Valid" --> DispatchAction{"Evaluate $_POST['action']"}
    
    DispatchAction -- "archive_job" --> ExecArchive["archive_job($job_id, $user)<br/>(UPDATE jobs SET is_archived = 1, archived_at = NOW())"]
    ExecArchive --> FlashArchive["set_flash('warning', 'Requisition archived. Applicant evaluations safely preserved.')"]
    
    DispatchAction -- "restore_job" --> CheckAdminRole{"Is user['role'] === 'admin'?"}
    CheckAdminRole -- "No (Employer)" --> DenyRestore["set_flash('danger', 'Restricted: Only Admins can restore')"]
    CheckAdminRole -- "Yes (Admin)" --> ExecRestore["restore_job($job_id, $user)<br/>(UPDATE jobs SET is_archived = 0, archived_at = NULL)"]
    
    CheckMethod -- "GET (View Dashboard)" --> ResolveTab{"Query tab param?"}
    ResolveTab -- "tab=archived" --> LoadArchived["Fetch archived dept jobs ($is_archived_view = true)"]
    ResolveTab -- "tab=active" --> LoadActive["Fetch active dept jobs ($is_archived_view = false)"]
    
    LoadArchived & LoadActive --> DelegateView["Require includes/templates/employer-dashboard-view.php"]
    DelegateView --> StreamOutput(["Render Requisition Ledger, Active/Archived Tabs & Actions"])
    
    classDef gate fill:#fef3c7,stroke:#d97706,stroke-width:2px,color:#92400e;
    classDef success fill:#dcfce7,stroke:#16a34a,stroke-width:2px,color:#166534;
    classDef danger fill:#fee2e2,stroke:#dc2626,stroke-width:2px,color:#991b1b;
    classDef step fill:#f1f5f9,stroke:#64748b,stroke-width:1px,color:#0f172a;
    
    class AuthGate,CheckMethod,CheckCSRF,DispatchAction,CheckAdminRole,ResolveTab gate;
    class StreamOutput,FlashArchive success;
    class RejectAuth,DenyRestore danger;
    class Req,ExecArchive,ExecRestore,LoadArchived,LoadActive,DelegateView step;
```

---

## 🔍 Line-by-Line Technical Analysis

### Lines 22–50: Requisition Archiving & Admin-Only Restore Mutations
```php
if ($action === 'archive_job') {
    if ($job_id > 0 && archive_job($job_id, $user)) {
        set_flash('warning', "Job requisition #{$job_id} has been moved to Archive. All applicant records and evaluations have been safely preserved.");
    } else {
        set_flash('danger', 'Failed to archive requisition or unauthorized access.');
    }
    header('Location: dashboard.php');
    exit;
} elseif ($action === 'restore_job') {
    // Enforce: Admin ONLY has restore privileges
    if (($user['role'] ?? '') !== 'admin') {
        set_flash('danger', 'Access Restricted: Only University Administrators are authorized to restore archived requisitions.');
        header('Location: dashboard.php');
        exit;
    }

    if ($job_id > 0 && restore_job($job_id, $user)) {
        set_flash('success', "Job requisition #{$job_id} has been restored to active status!");
    } else {
        set_flash('danger', 'Failed to restore requisition.');
    }
    header('Location: dashboard.php?tab=archived');
    exit;
}
```
- **Zero Candidate Loss**: Archiving preserves candidate applications and supervisor evaluation notes.
- **Quota Protection**: Employers cannot reopen closed postings without administrative review.

---

## 🛡️ Defense Talking Points & Security Disclosures

> [!tip] 🎓 Panelist Q&A Defense Sheet
> 
> **Q: Why are employers allowed to archive job postings but prevented from restoring them?**
> **A:** Campus departments are authorized to close or archive their job requisitions when hiring rounds finish. However, restoring an archived posting can impact institutional assistantship budgets and quota allocations. Restricting restoration to University Administrators ensures university-wide labor cap oversight.
> 
> **Q: What happens to student applications when a requisition is moved to Archive?**
> **A:** All applicant dossiers, interview notes, and status records in `applications` remain completely untouched in MySQL. Students can still see their application status in `my-applications.php`.
