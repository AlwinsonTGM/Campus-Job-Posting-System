---
title: "student/my-applications.php — Application Status Tracker & Non-Destructive Withdrawal Controller"
type: "code-walkthrough"
layer: "controller / student-suite"
original_file: "student/my-applications.php"
tags:
  - code-walkthrough
  - controller
  - application-tracker
  - non-destructive-withdrawal
  - soft-delete
  - re-application-support
  - idor-defense
aliases:
  - MyApplicationsController
  - student/my-applications.php
related:
  - "[[includes/data-helper.php.md]]"
  - "[[includes/auth-check.php.md]]"
  - "[[includes/services/application-service.php.md]]"
  - "[[includes/services/job-service.php.md]]"
  - "[[includes/templates/student-my-applications-view.php.md]]"
---

# 📊 `student/my-applications.php` — Application Status Tracker & Non-Destructive Withdrawal Controller

> [!abstract] 📌 Executive Summary
> `student/my-applications.php` serves as the **student candidate lifecycle management and tracking controller**. Gated by `require_auth(['student', 'admin'])`, it provides complete visibility into application milestones (*Submitted* $ightarrow$ *Under Review* $ightarrow$ *Interview Scheduled* $ightarrow$ *Hired/Declined*). Crucially, it manages **non-destructive application withdrawal**:
> 1. **Soft-Delete Withdrawal (`withdraw_application()`)**: Transitions records to `status = 'withdrawn'` rather than executing SQL `DELETE`, preserving historical submission proofs in MySQL.
> 2. **Dedicated Withdrawn Filter**: Allows students to inspect historical applications via `?status=withdrawn`.
> 3. **Re-Application Rights**: Withdrawing frees the student's slot. If the requisition remains open, the student can submit an updated application, handled via idempotent `ON DUPLICATE KEY UPDATE` in `job-service.php`.

---

## 🏛️ Placement in System Architecture

```mermaid
flowchart TD
    Req["Student accesses /student/my-applications.php"] --> AuthGate{"require_auth(['student', 'admin']) passed?"}
    
    AuthGate -- "No" --> RedirectAuth(["Redirect to login.php"])
    AuthGate -- "Yes" --> ExtractUser["Get logged user entity"]
    
    ExtractUser --> CheckAction{"Evaluate POST Mutation Trigger"}
    
    CheckAction -- "POST withdraw_id" --> CheckCSRF{"verify_csrf_token()"}
    CheckCSRF -- "Valid" --> VerifyAppIDOR{"Does application exist AND<br/>target_app.student_id === user.id?"}
    VerifyAppIDOR -- "No" --> FlashDanger["set_flash('danger', 'Unauthorized')"]
    VerifyAppIDOR -- "Yes" --> CheckStatus{"Is status still 'pending'?"}
    CheckStatus -- "No" --> FlashWarn["set_flash('warning', 'Cannot withdraw in-progress app')"]
    CheckStatus -- "Yes" --> ExecWithdraw["withdraw_application($withdraw_id, $user['id'])<br/>(UPDATE applications SET status = 'withdrawn')"]
    ExecWithdraw --> FlashSuccess["set_flash('info', 'Application withdrawn. Record preserved.')"]
    
    CheckAction -- "GET (Filter & View)" --> ResolveFilter["Read status filter (pending, review, interview, accepted, declined, withdrawn)"]
    ResolveFilter --> FetchApps["Fetch get_applications($user['id'])"]
    FetchApps --> FilterApps["Filter records matching status"]
    FilterApps --> DelegateView["Require includes/templates/student-my-applications-view.php"]
    DelegateView --> StreamOutput(["Render Milestone Stepper, Withdrawn Filter Chip & Actions"])
    
    classDef gate fill:#fef3c7,stroke:#d97706,stroke-width:2px,color:#92400e;
    classDef success fill:#dcfce7,stroke:#16a34a,stroke-width:2px,color:#166534;
    classDef danger fill:#fee2e2,stroke:#dc2626,stroke-width:2px,color:#991b1b;
    classDef step fill:#f1f5f9,stroke:#64748b,stroke-width:1px,color:#0f172a;
    
    class AuthGate,CheckAction,CheckCSRF,VerifyAppIDOR,CheckStatus gate;
    class StreamOutput,FlashSuccess success;
    class RedirectAuth,FlashDanger,FlashWarn danger;
    class Req,ExtractUser,ExecWithdraw,ResolveFilter,FetchApps,FilterApps,DelegateView step;
```

---

## 🔍 Line-by-Line Technical Analysis

### Lines 25–45: Non-Destructive Application Withdrawal
```php
if (isset($_POST['withdraw_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security validation failed (invalid CSRF session token).');
        header('Location: my-applications.php');
        exit;
    }
    $withdraw_id = (int)$_POST['withdraw_id'];
    $target_app = get_application_by_id($withdraw_id);
    if (!$target_app || (int)$target_app['student_id'] !== (int)($user['id'] ?? 0)) {
        set_flash('danger', 'Unauthorized or non-existent application.');
        header('Location: my-applications.php');
        exit;
    }
    if (!in_array(strtolower($target_app['status'] ?? ''), ['pending', 'pending review'])) {
        set_flash('warning', 'Applications that are under review, scheduled for interview, or accepted cannot be withdrawn.');
        header('Location: my-applications.php');
        exit;
    }
    withdraw_application($withdraw_id, $user['id'] ?? null);
    set_flash('info', 'Application has been successfully withdrawn. Your submission history remains safely archived.');
    header('Location: my-applications.php');
    exit;
}
```
- **Soft-Delete Architecture**: Executes `withdraw_application()` which updates `status = 'withdrawn'`.
- **Zero Data Loss**: Eliminates hard `DELETE FROM applications`.

---

## 🛡️ Defense Talking Points & Security Disclosures

> [!tip] 🎓 Panelist Q&A Defense Sheet
> 
> **Q: Why was application deletion converted to a withdrawn status?**
> **A:** Permanent SQL deletion removes all proof of a student's submission, timestamps, and attached study load. Changing deletion to a soft `withdrawn` status retains the institutional record for university auditing while releasing the student's slot.
> 
> **Q: If a student withdraws an application, can they re-apply to the same position?**
> **A:** Yes. `ApplicationService::checkEligibility()` and `hasStudentApplied()` ignore applications in `withdrawn` status. When the student re-applies, `create_application()` executes an `ON DUPLICATE KEY UPDATE`, refreshing the submission and resetting `status = 'pending'`.
