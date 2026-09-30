---
title: "includes/ai/security.php — AI Input Sanitization & Control Character Filtering"
type: "code-walkthrough"
layer: "infrastructure / ai / security"
original_file: "includes/ai/security.php"
tags:
  - code-walkthrough
  - ai-security
  - sanitization
  - prompt-filtering
  - defense-core
aliases:
  - AISecurity
  - ai-security.php
related:
  - "[[includes/ai-config.php.md]]"
  - "[[includes/ai/core.php.md]]"
  - "[[api/robot-chat.php.md]]"
---

# 🛡️ `includes/ai/security.php` — AI Input Sanitization & Control Character Filtering

> [!abstract] 📌 Executive Summary
> `includes/ai/security.php` provides the first line of defense for the platform's AI Assistant chatbot (`Campus Bot`). It extracts, sanitizes, and normalizes raw text input before any prompt reaches internal intent classifiers or external Large Language Model (LLM) APIs.
> 
> The module enforces a strict **800-character ceiling**, eliminates null-byte attacks (`\x00`), strips HTML tags to neutralize injection vectors, and purges non-printable control characters that could corrupt upstream JSON HTTP payloads.

---

## 🏛️ Placement in AI Execution Pipeline

```mermaid
flowchart TD
    RawInput["Raw User Input via POST<br/>api/robot-chat.php"] --> CallSec["sanitize_ai_prompt($raw_input, 800)<br/>(includes/ai/security.php)"]

    subgraph SecurityChecks ["Sanitization & Boundary Enforcement"]
        CallSec --> TypeCheck{"Is input a string?"}
        TypeCheck -- "NO" --> ReturnEmpty["Return empty string ''"]
        TypeCheck -- "YES" --> StripTags["strip_tags(trim($raw_input))"]
        StripTags --> RegexFilter["Filter Null-Bytes & Control Chars<br/>[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]"]
        RegexFilter --> LengthCheck{"mb_strlen > 800?"}
        LengthCheck -- "YES" --> Truncate["mb_substr($clean, 0, 800, 'UTF-8')"]
        LengthCheck -- "NO" --> PassClean["Keep full string"]
    end

    Truncate & PassClean --> CleanPrompt(["Clean, Safe Prompt<br/>Passed to Intent Detector & LLM Core"])

    %% Semantic styling
    classDef input fill:#EFF6FF,stroke:#2563EB,stroke-width:2px,color:#1E40AF;
    classDef step fill:#F3F4F6,stroke:#6B7280,stroke-width:1px,color:#1F2937;
    classDef check fill:#FEF3C7,stroke:#D97706,stroke-width:2px,color:#92400E;
    classDef alert fill:#FEE2E2,stroke:#DC2626,stroke-width:1px,color:#991B1B;
    classDef done fill:#DCFCE7,stroke:#16A34A,stroke-width:2px,color:#166534;

    class RawInput input;
    class CallSec,StripTags,RegexFilter,Truncate,PassClean step;
    class TypeCheck,LengthCheck check;
    class ReturnEmpty alert;
    class CleanPrompt done;
```

---

## 🔍 Function-by-Function Walkthrough

### `sanitize_ai_prompt(mixed $raw_input, int $max_length = 800): string`

```php
function sanitize_ai_prompt(mixed $raw_input, int $max_length = 800): string {
    if (!is_string($raw_input)) {
        return '';
    }
    $clean = strip_tags(trim($raw_input));
    // Remove null bytes and non-printable control characters (except newline, tab)
    $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $clean) ?? '';
    if (function_exists('mb_strlen')) {
        if (mb_strlen($clean, 'UTF-8') > $max_length) {
            $clean = mb_substr($clean, 0, $max_length, 'UTF-8');
        }
    } else {
        if (strlen($clean) > $max_length) {
            $clean = substr($clean, 0, $max_length);
        }
    }
    return $clean;
}
```

#### Detailed Line Breakdown:
1. **Type Assertion (`is_string($raw_input)`)**: Guarantees non-string payloads (e.g. arrays or objects sent via malformed JSON bodies) are rejected immediately as empty strings rather than causing PHP type errors.
2. **HTML Tag Stripping (`strip_tags(trim($raw_input))`)**: Eliminates `<script>`, `<iframe>`, or HTML formatting tags, ensuring prompts are evaluated purely as plain text.
3. **Control Character Regex (`/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/`)**:
   - `\x00`: Null-byte string termination attack mitigation.
   - `\x01-\x08`: SOH, STX, ETX, EOT, ENQ, ACK, BEL, BS.
   - `\x0B\x0C`: Vertical tab and form-feed bytes.
   - `\x0E-\x1F`: Shift Out, Shift In, DLE, DC1-4, NAK, SYN, ETB, CAN, EM, SUB, ESC, FS, GS, RS, US.
   - `\x7F`: Delete byte (DEL).
   - *Intentionally preserved*: `\x09` (Horizontal Tab) and `\x0A` (Newline `\n`) to preserve readable multi-line questions.
4. **Length Clamping (`mb_substr(..., 800)`)**: Prevents denial-of-service (DoS) via token exhaustion or massive context window stuffing attacks by enforcing an 800-character upper limit.

---

## 🎓 Panel Defense Q&A

### Q1: "Why filter out control characters before sending prompts to the LLM?"
> **Answer:** "Unfiltered control characters (such as null-bytes or terminal control escapes) can break upstream JSON serializers (`json_encode`), corrupt HTTP REST payload formatting sent to NVIDIA NIM or Google Gemini, or trigger undefined behavior in the tokenizer. Filtering ensures payload predictability and prevents protocol injection."

### Q2: "Why cap user prompts at 800 characters?"
> **Answer:** "800 characters corresponds to approximately 160–200 words, which is more than sufficient for campus assistantship inquiries and portal navigation questions. Enforcing this boundary directly protects our external API token budget and prevents attackers from submitting massive prompts that consume inference credits or attempt jailbreaks via complex instruction flooding."
