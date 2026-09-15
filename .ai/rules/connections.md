---
paths:
  - 'app/Services/Connections/**'
---

# Connections

## Verify API keys on connect; disconnect deletes the credential
`ConnectionController::store` verifies the API key before saving via `ApiCredentialVerifier` (app/Services/Connections), which pings each service's `validate_url` from config/services.php (fal→fal.run/users/me Key header; wavespeed→/api/v3/balance checking body `code===200`; claude→anthropic /v1/models with x-api-key+anthropic-version; chatgpt→openai /v1/models; deepseek→/user/balance). When adding an ApiService case, add a `validate_url` to its config/services.php block or the verifier will throw. Disconnect (DELETE /connections/{service}) deletes the row; `TeamApiCredential` unique per (team, service).
