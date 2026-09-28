# Campus Job Posting System

Institutional employment and career mobility portal connecting students, campus employers, and career services administrators.

## Language

### Authentication & Access

**SessionGuard**:
The authority responsible for request authentication gating, role authorization, and return-to destination resolution.
_Avoid_: AuthManager, LoginFilter, SessionHandler

**Verification Quarantine**:
The restricted session state where a registered or unverified account is held pending six-digit institutional email OTP confirmation before receiving active session privileges.
_Avoid_: PendingAuth, UnverifiedSession, TempUser

**Return-To Target**:
The validated relative destination URL preserved across authentication or verification redirects so users resume their original workflow.
_Avoid_: RedirectUri, NextUrl, Callback

**Role Dashboard**:
The canonical landing route assigned to an authenticated account role (`student`, `employer`, `admin`).
_Avoid_: Home, LandingPage, DefaultRoute
