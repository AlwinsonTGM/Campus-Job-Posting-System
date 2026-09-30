---
title: "root/update-detail.php — Career Center Article Reader Controller"
type: "code-walkthrough"
layer: "controller / editorial"
original_file: "update-detail.php"
tags:
  - code-walkthrough
  - controller
  - article-reader
  - deep-linking
  - related-content
aliases:
  - UpdateDetailController
  - update-detail.php
related:
  - "[[includes/data-helper.php.md]]"
  - "[[includes/auth-check.php.md]]"
  - "[[includes/services/system-service.php.md]]"
  - "[[root/updates.php.md]]"
  - "[[includes/templates/update-detail-view.php.md]]"
---

# 📖 `update-detail.php` — Career Center Article Reader Controller

> [!abstract] 📌 Executive Summary
> `update-detail.php` serves as the **individual article reader controller** for campus announcements, career advisories, and administrative circulars. It accepts an article identifier via `$_GET['id']`, retrieves the matching record via `get_career_update_by_id()`, formats timestamps, queries related recommendations excluding the active article (`get_latest_career_updates(3, $article['id'])`), calculates canonical sharing URLs, and delegates rendering to `includes/templates/update-detail-view.php`.

---

## 🏛️ Placement in System Architecture

```mermaid
flowchart TD
    Req["Visitor requests /update-detail.php?id=XYZ"] --> ExtractParam["Extract $article_id = $_GET['id']"]
    
    ExtractParam --> CheckParam{"$article_id is provided?"}
    
    CheckParam -- "Yes" --> QueryDB["Fetch get_career_update_by_id($article_id)"]
    CheckParam -- "No" --> SetNotFound["$article = null"]
    
    QueryDB --> CheckFound{"Article Record Exists?"}
    
    CheckFound -- "Yes" --> FormatArticle["Format Date & Time:<br/>- F j, Y & g:i A<br/>Fetch related: get_latest_career_updates(3, $article['id'])"]
    CheckFound -- "No" --> SetNotFound
    
    SetNotFound --> FallbackState["Set $page_title = 'Article Not Found'<br/>Fetch default: get_latest_career_updates(3)"]
    
    FormatArticle --> GenShareURL["Generate $share_url via HTTP_HOST & REQUEST_URI"]
    FallbackState --> GenShareURL
    
    GenShareURL --> DelegateView["Require includes/templates/update-detail-view.php"]
    DelegateView --> StreamOutput(["Render Full Article Reader & Related Sidebar"])
    
    classDef gate fill:#fef3c7,stroke:#d97706,stroke-width:2px,color:#92400e;
    classDef success fill:#dcfce7,stroke:#16a34a,stroke-width:2px,color:#166534;
    classDef step fill:#f1f5f9,stroke:#64748b,stroke-width:1px,color:#0f172a;
    
    class CheckParam,CheckFound gate;
    class StreamOutput success;
    class Req,ExtractParam,QueryDB,SetNotFound,FormatArticle,FallbackState,GenShareURL,DelegateView step;
```

---

## 🔍 Line-by-Line Technical Analysis

### Lines 1–15: Parameter Extraction & Article Lookup
```php
<?php
/**
 * Campus Job Posting System - Career Center Article Detail Reader
 * Archetype F: Public & Information Reader (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$article_id = $_GET['id'] ?? null;
$article = null;

if ($article_id !== null) {
    $article = get_career_update_by_id($article_id);
}
```
- Retrieves the requested article identifier from the URL query string.
- Executes `get_career_update_by_id($article_id)` against the persistence layer.

---

### Lines 16–25: Branching & Related Content Synthesis
```php
if ($article) {
    $page_title = $article['title'] . ' | Career Center';
    $pub_time = strtotime($article['published_at'] ?? 'now');
    $formatted_date = date('F j, Y', $pub_time);
    $formatted_time = date('g:i A', $pub_time);
    $latest_articles = get_latest_career_updates(3, $article['id']);
} else {
    $page_title = 'Article Not Found | Career Center';
    $latest_articles = get_latest_career_updates(3);
}
```
- **If Found**:
  - Dynamically builds `$page_title` using the article's headline.
  - Converts the ISO timestamp into user-friendly localized formats (e.g. `September 29, 2026` at `6:45 PM`).
  - Calls `get_latest_career_updates(3, $article['id'])`, specifically passing `$article['id']` as the exclusion parameter to prevent recommending the current article in the sidebar.
- **If Not Found (Graceful 404 Degradation)**:
  - Sets the page title to *Article Not Found* and fetches the 3 most recent articles so the reader is not left with a dead end.

---

### Lines 27–32: Canonical Share URL & View Handover
```php
// Preload view data — no service/DB calls in the template
$share_url = 'http://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');

// Last line: view template
require __DIR__ . '/includes/templates/update-detail-view.php';
```
- Constructs the full canonical sharing link for clipboard or social sharing.
- Invokes `update-detail-view.php`.

---

## 🛡️ Defense Talking Points & Security Disclosures

> [!tip] 🎓 Panelist Q&A Defense Sheet
> 
> **Q: What happens if an attacker inputs SQL injection syntax or an invalid ID in the `?id=` parameter?**
> **A:** In `includes/services/system-service.php`, `get_career_update_by_id()` binds parameters via PDO prepared statements (`execute([':id' => $id])`) with `PDO::ATTR_EMULATE_PREPARES = false`. SQL injection characters cannot alter query structure. If no record matches, `$article` evaluates to `null`, triggering the graceful fallback without exposing system errors.
> 
> **Q: Why does `get_latest_career_updates()` accept a second parameter?**
> **A:** The second parameter is an `exclude_id`. It ensures that when reading an article, that same article does not appear under "Related Updates" in the sidebar, adhering to standard UX design principles.
