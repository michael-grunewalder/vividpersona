---
paths:
  - 'app/Http/Controllers/Auth/**'
---

# Auth

## 6-digit email confirmation flow
Account activation uses a custom 6-digit code, not Laravel's signed-URL verification. User implements MustVerifyEmail and overrides sendEmailVerificationNotification() to issue a hashed code with a 10-minute expiry and notify EmailVerificationCode. Signups auto-login and redirect to verification.notice; the built-in 'verified' middleware funnels every unverified request back to the code page, which offers resend. Codes are stored hashed (Hash::check on verify).
