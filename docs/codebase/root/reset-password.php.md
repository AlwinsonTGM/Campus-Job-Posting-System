---
title: "root/reset-password.php — Token Validation & Password Reset Target Controller"
type: "code-walkthrough"
layer: "controller / authentication"
original_file: "reset-password.php"
tags:
  - code-walkthrough
  - reset-password
  - token-validation
  - password-strength
  - single-use-token
  - defense-core
aliases:
  - ResetPasswordController
  - reset-password.php
related:
  - "[[root/forgot-pass.php.md]]"
  - "[[includes/services/user-service.php.md]]"
  - "[[root/login.php.md]]"
  - "[[includes/templates/reset-password-view.php.md]]"
---

# 🔐 `reset-password.php` — Token Validation & Password Reset Controller

> [!abstract] 📌 Executive Summary
> `reset-password.php` is the **landing target for password reset action links** emailed by `forgot-pass.php`. It handles:
> 1. **Cryptographic Token Verification**: Validates the incoming `?token=` parameter against the `password_resets` table to ensure it is authentic and unexpired.
> 2. **Password Strength Enforcement (`validate_password_strength`)**: Enforces minimum 8 characters, mixed case, numbers, and symbols.
> 3. **Single-Use Token Consumption (`consume_password_reset`)**: Atomically updates the user's password hash (`password_hash`) and permanently deletes the token to prevent replay attacks.
> 4. **Success State & View Delegation**: Renders `includes/templates/reset-password-view.php`.

---

## 🏛️ Placement in System Architecture

```mermaid
flowchart TD
    LinkClick["User clicks reset link in email<br/>(reset-password.php?token=XXX)"] --> CheckToken{"get_password_reset_by_token($token)"}

    CheckToken -- "Invalid / Expired" --> ShowInvalid(["Display Error: Link expired or invalid<br/>Provide link to forgot-pass.php"])

    CheckToken -- "Valid Token" --> CheckMethod{"HTTP Request Method?"}
    
    CheckMethod -- "GET" --> RenderForm["Render New Password Form<br/>(includes/templates/reset-password-view.php)"]

    CheckMethod -- "POST" --> ValStrength{"validate_password_strength($newPassword)"}
    
    ValStrength -- "Weak Password" --> ShowStrengthErr["Set error: 'Password must meet complexity rules'"]
    ShowStrengthErr --> RenderForm

    ValStrength -- "Strong Password" --> CheckMatch{"$new === $confirm?"}
    CheckMatch -- "NO" --> ShowMatchErr["Set error: 'Passwords do not match'"]
    ShowMatchErr --> RenderForm

    CheckMatch -- "YES" --> ConsumeToken["consume_password_reset($resetId, $userId, $newPassword)"]
    ConsumeToken --> DeleteToken["1. UPDATE users SET password = hash<br/>2. DELETE FROM password_resets WHERE id = $resetId"]
    DeleteToken --> DoneScreen(["Render Success State: 'Password Updated'<br/>Redirect to login.php"])

    %% Semantic styling
    classDef check fill:#FEF3C7,stroke:#D97706,stroke-width:2px,color:#92400E;
    classDef step fill:#EFF6FF,stroke:#2563EB,stroke-width:1px,color:#1E40AF;
    classDef done fill:#DCFCE7,stroke:#16A34A,stroke-width:2px,color:#166534;
    classDef fail fill:#FEE2E2,stroke:#DC2626,stroke-width:2px,color:#991B1B;

    class CheckToken,CheckMethod,ValStrength,CheckMatch check;
    class RenderForm,ConsumeToken,DeleteToken step;
    class DoneScreen done;
    class ShowInvalid,ShowStrengthErr,ShowMatchErr fail;
```

---

## 🔍 Detailed Line-by-Line Breakdown

### 1. Token Inspection (Lines 9–10)
```php
$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
$reset = $token !== '' ? get_password_reset_by_token($token) : null;
```
* Safely inspects both GET and POST requests.
* Calls `get_password_reset_by_token()` which queries the database and verifies that `expires_at >= NOW()`.

---

### 2. Password Complexity & Match Validations (Lines 15–29)
```php
$strength = validate_password_strength($new);

if (!$reset) {
    $error = 'This reset link is invalid or has expired. Please request a new one.';
} elseif (!$strength['valid']) {
    $error = $strength['error'];
} elseif ($new !== $confirm) {
    $error = 'New password and confirm password do not match.';
}
```
* Server-side mirror of the client-side 5-point password strength meter.

---

### 3. Single-Use Token Consumption (Lines 30–35)
```php
if (consume_password_reset((int)$reset['id'], (int)$reset['user_id'], $new)) {
    $done = true;
} else {
    $error = 'Could not update password. Please try again.';
}
```
* **Replay Attack Defense**: `consume_password_reset()` runs an atomic transaction that updates the user's password hash and **deletes the reset record**. Once consumed, clicking the link a second time yields `"This reset link is invalid or has expired"`.

---

## 🛡️ Security & Panel Defense Talking Points

> [!tip] 🎤 High-Yield Defense Q&A for this File
> 
> **Q: How does `reset-password.php` protect against Password Reset Replay Attacks?**
> * **Answer:** *"In `consume_password_reset()` (Line 30), reset tokens are strictly single-use. As soon as the new password hash is committed, the token is permanently deleted from the `password_resets` table. If an eavesdropper intercepts the email or reuses browser history, the link is immediately rejected."*
