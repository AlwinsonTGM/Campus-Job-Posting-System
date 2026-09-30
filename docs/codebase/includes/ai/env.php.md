---
title: "includes/ai/env.php — Environment Configuration & Secrets Management"
type: "code-walkthrough"
layer: "infrastructure / ai / configuration"
original_file: "includes/ai/env.php"
tags:
  - code-walkthrough
  - env-loader
  - secrets-management
  - configuration
  - defense-core
aliases:
  - AIEnv
  - ai-env.php
related:
  - "[[includes/ai-config.php.md]]"
  - "[[admin/ai-settings.php.md]]"
  - "[[includes/ai/core.php.md]]"
---

# 🔐 `includes/ai/env.php` — Environment Configuration & Secrets Management

> [!abstract] 📌 Executive Summary
> `includes/ai/env.php` provides a lightweight, zero-dependency parser for the root `.env` configuration file. It isolates sensitive API credentials (such as `NVIDIA_API_KEY` and `GEMINI_API_KEY`) outside the web root and source code.
> 
> The module implements an in-memory static cache (`static $env_cache`) ensuring the disk file is read only once per PHP request lifecycle, while synchronizing loaded keys across PHP's native `putenv()` and `$_ENV` superglobals.

---

## 🏛️ Environment Lifecycle & Loading Architecture

```mermaid
flowchart TD
    AppCall["Code calls get_ai_env('NVIDIA_API_KEY')"] --> CheckCache{"Is $env_cache populated?"}

    CheckCache -- "YES (Cache Hit)" --> ReturnVal["Return cached value in memory<br/>(Zero disk I/O)"]
    CheckCache -- "NO (Cold Start)" --> LocateEnv["Locate .env file<br/>Check project root / parent paths"]

    LocateEnv --> ReadLines["Read lines with FILE_IGNORE_NEW_LINES<br/>and FILE_SKIP_EMPTY_LINES"]
    ReadLines --> ParseLoop["Iterate lines: skip # and ; comments<br/>Split at first '=' delimiter<br/>Strip quotes from values"]

    ParseLoop --> SyncEnv["Populate $env_cache<br/>Call putenv('KEY=VAL')<br/>Set $_ENV['KEY'] = VAL"]
    SyncEnv --> ReturnVal

    ReturnVal --> QueryKey{"Is requested key found?"}
    QueryKey -- "YES" --> Result(["Return Key Value"])
    QueryKey -- "NO" --> FallbackDefault(["Return Provided Default Value"])

    %% Semantic styling
    classDef call fill:#EFF6FF,stroke:#2563EB,stroke-width:2px,color:#1E40AF;
    classDef step fill:#F3F4F6,stroke:#6B7280,stroke-width:1px,color:#1F2937;
    classDef check fill:#FEF3C7,stroke:#D97706,stroke-width:2px,color:#92400E;
    classDef done fill:#DCFCE7,stroke:#16A34A,stroke-width:2px,color:#166534;

    class AppCall call;
    class LocateEnv,ReadLines,ParseLoop,SyncEnv,ReturnVal step;
    class CheckCache,QueryKey check;
    class Result,FallbackDefault done;
```

---

## 🔍 Function-by-Function Walkthrough

### 1. `load_env(?string $env_path = null): array`
Zero-dependency `.env` parser with memoized caching:

```php
function load_env(?string $env_path = null): array {
    static $env_cache = null;
    if ($env_cache !== null) {
        return $env_cache;
    }

    if ($env_path === null) {
        $env_path = dirname(__DIR__, 2) . '/.env';
        if (!file_exists($env_path)) {
            $env_path = dirname(__DIR__) . '/.env';
        }
    }

    $env_cache = [];
    if (!file_exists($env_path)) {
        return $env_cache;
    }

    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return $env_cache;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
            continue;
        }

        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));

        // Unquote single/double quotes
        if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
            (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
            $val = substr($val, 1, -1);
        }

        $env_cache[$key] = $val;
        if (!getenv($key)) {
            putenv("$key=$val");
        }
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $val;
        }
    }

    return $env_cache;
}
```

* **Comment Stripping**: Seamlessly skips `#` and `;` comment prefixes commonly found in Unix `.env` files.
* **Quote Normalization**: Strips surrounding single (`'`) or double (`"`) quotes from environment values containing whitespace.
* **Triple Synchronization**: Writes parsed pairs to `$env_cache`, `putenv()`, and `$_ENV` simultaneously to support any third-party or legacy library query style.

---

### 2. `get_ai_env(string $key, mixed $default = null): mixed`
Safe accessor with graceful cascading fallbacks:

```php
function get_ai_env(string $key, mixed $default = null): mixed {
    $env = load_env();
    if (isset($env[$key]) && $env[$key] !== '') {
        return $env[$key];
    }
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    return $default;
}
```

---

## 🎓 Panel Defense Q&A

### Q1: "Why did you build a custom `.env` loader instead of using `vlucas/phpdotenv`?"
> **Answer:** "In institutional and academic environments, deploying heavy external dependencies can introduce dependency management friction and require Composer installations on every testing machine. Our zero-dependency loader accomplishes the exact parsing, quote-stripping, and comment-filtering required in under 50 lines of pure PHP, operating with zero external overhead."

### Q2: "How is `.env` protected from unauthorized web downloads?"
> **Answer:** "Apache web servers running standard configurations automatically deny access to all hidden dotfiles (files beginning with `.`). Furthermore, in our repository, sensitive keys are excluded via `.gitignore`, and the root `.htaccess` explicitly prohibits access to configuration and environment files."
