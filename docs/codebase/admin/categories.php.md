---
title: "admin/categories.php — Job Family Taxonomy & Non-Destructive Archive Controller"
type: "code-walkthrough"
layer: "controller / admin-suite"
original_file: "admin/categories.php"
tags:
  - code-walkthrough
  - controller
  - category-taxonomy
  - soft-delete-archiving
  - admin-only-restore
  - icon-sanitization
  - visual-profiles
aliases:
  - AdminCategoriesController
  - admin/categories.php
related:
  - "[[includes/data-helper.php.md]]"
  - "[[includes/auth-check.php.md]]"
  - "[[includes/services/job-service.php.md]]"
  - "[[includes/templates/admin-categories-view.php.md]]"
---

# 🏷️ `admin/categories.php` — Job Family Taxonomy & Non-Destructive Archive Controller

> [!abstract] 📌 Executive Summary
> `admin/categories.php` governs the **institutional job family categorization taxonomy, non-destructive archives, and visual brand profiles**. Gated by `require_auth(['admin'])`, it manages category lifecycles with **zero hard deletions**. Instead of destroying relational classification history, deletions trigger `archive_category()` (`is_archived = 1`, `archived_at = NOW()`), and only university administrators hold the cryptographic and role authority to execute `restore_category()`. Features dual **Active** and **Archived** filter tabs (`?tab=archived`), input sanitization on icon classes, and integration with `admin-categories-view.php`.

---

## 🏛️ Placement in System Architecture

```mermaid
flowchart TD
    Req["Administrator accesses /admin/categories.php"] --> AuthGate{"require_auth(['admin']) passed?"}
    
    AuthGate -- "No" --> DenyAuth(["Redirect to login.php"])
    AuthGate -- "Yes" --> CheckMethod{"HTTP Request Method?"}
    
    CheckMethod -- "POST (Taxonomy Mutation)" --> CheckCSRF{"verify_csrf_token()"}
    CheckCSRF -- "Invalid" --> CSRFError["Set $error = 'Invalid token'"]
    
    CheckCSRF -- "Valid" --> DispatchAction{"Evaluate $_POST['action']"}
    
    DispatchAction -- "create" --> ValidateCreate["Sanitize icon & color token<br/>Call create_category()"]
    DispatchAction -- "update" --> ValidateUpdate["Verify id > 0<br/>Call update_category()"]
    DispatchAction -- "archive / delete" --> ExecArchive["archive_category($id)<br/>(is_archived = 1, archived_at = NOW())"]
    DispatchAction -- "restore" --> ExecRestore["restore_category($id, $user)<br/>(Admin ONLY privilege check)"]
    
    ExecArchive --> RedirectArchive["set_flash('success') & Redirect to categories.php"]
    ExecRestore --> RedirectRestore["set_flash('success') & Redirect to categories.php?tab=archived"]
    
    CheckMethod -- "GET (Display Taxonomy)" --> CheckTab{"Tab Query Param?"}
    CheckTab -- "tab=archived" --> LoadArchived["get_categories(true, true)<br/>(Filter is_archived = 1)"]
    CheckTab -- "tab=active (default)" --> LoadActive["get_categories(false, false)<br/>(Filter is_archived = 0)"]
    
    LoadArchived & LoadActive --> DelegateView["Require includes/templates/admin-categories-view.php"]
    DelegateView --> StreamOutput(["Render Taxonomy Grid, Active/Archived Tabs & Restore Actions"])
    
    classDef gate fill:#fef3c7,stroke:#d97706,stroke-width:2px,color:#92400e;
    classDef success fill:#dcfce7,stroke:#16a34a,stroke-width:2px,color:#166534;
    classDef danger fill:#fee2e2,stroke:#dc2626,stroke-width:2px,color:#991b1b;
    classDef step fill:#f1f5f9,stroke:#64748b,stroke-width:1px,color:#0f172a;
    
    class AuthGate,CheckMethod,CheckCSRF,DispatchAction,CheckTab gate;
    class StreamOutput,RedirectArchive,RedirectRestore success;
    class DenyAuth,CSRFError danger;
    class Req,ValidateCreate,ValidateUpdate,ExecArchive,ExecRestore,LoadArchived,LoadActive,DelegateView step;
```

---

## 🔍 Line-by-Line Technical Analysis

### Lines 120–155: Non-Destructive Archive & Admin-Only Restore Actions
```php
} elseif ($action === 'archive' || $action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        archive_category($id);
        set_flash('success', "Category #{$id} has been moved to Archive. Existing jobs retain their classification.");
        header('Location: categories.php');
        exit;
    }
    $error = 'Invalid category ID specified for archiving.';
} elseif ($action === 'restore') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        restore_category($id, $user);
        set_flash('success', "Category #{$id} has been restored to active taxonomy.");
        header('Location: categories.php?tab=archived');
        exit;
    }
    $error = 'Invalid category ID specified for restoration.';
}
```
- **Zero Hard Deletes**: The legacy `DELETE FROM categories` query has been fully removed. Deleting a category executes `archive_category()`, moving the classification into the archive tab.
- **Admin Privilege Verification**: `restore_category($id, $user)` enforces that only callers with role `'admin'` can un-archive categories.

---

## 🛡️ Defense Talking Points & Security Disclosures

> [!tip] 🎓 Panelist Q&A Defense Sheet
> 
> **Q: Why does deleting a category perform an archive instead of a permanent SQL DELETE?**
> **A:** Deleting a category permanently from MySQL would cause foreign key cascade failures on `jobs.category_id` or orphan historical listings. By setting `is_archived = 1`, existing jobs retain their historical category titles and reports remain consistent, while hiding the category from new postings.
> 
> **Q: Who can restore an archived category?**
> **A:** Only administrators. `restore_category()` evaluates `$user['role'] === 'admin'`, preventing unauthorized un-archiving.
