---
title: "includes/ai/rate-limit.php — Sliding Window AI Rate Limiting & Throttling"
type: "code-walkthrough"
layer: "infrastructure / ai / rate-limiting"
original_file: "includes/ai/rate-limit.php"
tags:
  - code-walkthrough
  - rate-limiting
  - throttling
  - sliding-window
  - defense-core
aliases:
  - AIRateLimit
  - ai-rate-limit.php
related:
  - "[[includes/ai-config.php.md]]"
  - "[[api/robot-chat.php.md]]"
  - "[[admin/ai-settings.php.md]]"
---

# ⏱️ `includes/ai/rate-limit.php` — Sliding Window AI Rate Limiting & Throttling

> [!abstract] 📌 Executive Summary
> `includes/ai/rate-limit.php` prevents API token exhaustion and spam flooding of the chatbot assistant. It implements a **60-second in-session sliding window rate limiter**.
> 
> By default, the system caps requests to **10 prompts per minute** (configurable dynamically via `.env` or the Admin AI Settings console). When a user exceeds the threshold, requests are rejected with an exact `retry_after` countdown in seconds.

---

## 🏛️ Placement in AI Throttling Lifecycle

```mermaid
flowchart TD
    Req["Chat Prompt Received<br/>(api/robot-chat.php)"] --> RateCheck["check_ai_rate_limit()<br/>(includes/ai/rate-limit.php)"]

    subgraph SlidingWindowEngine ["60-Second Sliding Window Algorithm"]
        RateCheck --> ReadEnv["Read AI_RATE_LIMIT_PER_MINUTE<br/>(Default: 10, Clamp 1..60)"]
        ReadEnv --> CleanStale["Purge timestamps older than 60s<br/>from $_SESSION['ai_rate_limit_timestamps']"]
        CleanStale --> CountActive["Count remaining timestamps in window"]
        CountActive --> Gate{"Count < limit_per_minute?"}
        
        Gate -- "NO (Quota Exceeded)" --> Block["Compute retry_after = (60 - elapsed)<br/>allowed = false"]
        Gate -- "YES (Within Limit)" --> RecordHit["Append current timestamp to session<br/>Compute remaining = limit - count<br/>allowed = true"]
    end

    Block --> Return429(["Return HTTP 429 / Throttled JSON<br/>Notify user to wait N seconds"])
    RecordHit --> ProceedAI(["Proceed to Intent Detection & LLM API"])

    %% Semantic styling
    classDef req fill:#EFF6FF,stroke:#2563EB,stroke-width:2px,color:#1E40AF;
    classDef step fill:#F3F4F6,stroke:#6B7280,stroke-width:1px,color:#1F2937;
    classDef check fill:#FEF3C7,stroke:#D97706,stroke-width:2px,color:#92400E;
    classDef alert fill:#FEE2E2,stroke:#DC2626,stroke-width:1px,color:#991B1B;
    classDef done fill:#DCFCE7,stroke:#16A34A,stroke-width:2px,color:#166534;

    class Req req;
    class ReadEnv,CleanStale,CountActive,RecordHit step;
    class Gate check;
    class Block alert;
    class Return429 alert;
    class ProceedAI done;
```

---

## 🔍 Function-by-Function Walkthrough

### 1. `get_ai_rate_limit_status(?int $limit_per_minute = null): array`
Inspects rate-limit health **without** recording an increment (idempotent inspection):

```php
function get_ai_rate_limit_status(?int $limit_per_minute = null): array {
    if ($limit_per_minute === null) {
        $limit_per_minute = (int)get_ai_env('AI_RATE_LIMIT_PER_MINUTE', 10);
    }
    $limit_per_minute = max(1, min(60, $limit_per_minute));

    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }

    $now = time();
    $window = 60; // 60 seconds

    if (!isset($_SESSION['ai_rate_limit_timestamps']) || !is_array($_SESSION['ai_rate_limit_timestamps'])) {
        $_SESSION['ai_rate_limit_timestamps'] = [];
    }

    // Clean timestamps older than window
    $_SESSION['ai_rate_limit_timestamps'] = array_filter(
        $_SESSION['ai_rate_limit_timestamps'],
        fn($ts) => ($now - $ts) < $window
    );

    $count = count($_SESSION['ai_rate_limit_timestamps']);
    $oldest = reset($_SESSION['ai_rate_limit_timestamps']) ?: $now;
    $retry_after = ($count >= $limit_per_minute) ? max(1, $window - ($now - $oldest)) : 0;
    $remaining = max(0, $limit_per_minute - $count);

    return [
        'allowed' => $count < $limit_per_minute,
        'retry_after' => $retry_after,
        'remaining' => $remaining,
        'limit_max' => $limit_per_minute
    ];
}
```

* **Sliding Window Maintenance**: Stale timestamps ($now - ts \ge 60$) are discarded on every evaluation using `array_filter`.
* **Dynamic Retry Calculation**: When blocked, `retry_after` determines the precise seconds remaining until the oldest timestamp exits the 60-second window.

---

### 2. `check_ai_rate_limit(?int $limit_per_minute = null): array`
Enforces the rate limit and registers an invocation hit:

```php
function check_ai_rate_limit(?int $limit_per_minute = null): array {
    $status = get_ai_rate_limit_status($limit_per_minute);

    if (!$status['allowed']) {
        return $status;
    }

    $now = time();
    $_SESSION['ai_rate_limit_timestamps'][] = $now;

    return [
        'allowed' => true,
        'retry_after' => 0,
        'remaining' => max(0, $status['remaining'] - 1),
        'limit_max' => $status['limit_max']
    ];
}
```

* **Atomic Session Push**: Appends `$now` only when `allowed === true`, preventing blocked spam requests from extending the lockout penalty.

---

## 🎓 Panel Defense Q&A

### Q1: "Why use a sliding window instead of a fixed 1-minute bucket (e.g. tracking requests in minute 01, 02, etc.)?"
> **Answer:** "A fixed bucket algorithm suffers from the **Boundary Burst Vulnerability**, where a user sends 10 requests at 11:59:59 and another 10 requests at 12:00:01, yielding 20 requests in 2 seconds. Our sliding window stores exact request timestamps in `$_SESSION['ai_rate_limit_timestamps']`, ensuring that at any 60-second interval, no more than 10 requests can ever execute."

### Q2: "Can an attacker bypass this rate limiter by clearing their browser session cookies?"
> **Answer:** "Clearing cookies creates a new session ID, but in our production setup:
> 1. The chatbot API (`api/robot-chat.php`) requires an authenticated user session (`SessionGuard::requireLogin()`).
> 2. Unauthenticated anonymous visitors are blocked before this rate limiter is ever invoked.
> 3. An attacker would need to register and verify new institutional accounts to obtain new sessions, which is throttled by our registration verification controls."
