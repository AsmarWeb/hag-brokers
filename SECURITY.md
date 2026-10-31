# Security Policy — HAG Brokers

## Supported versions
HAG Brokers is under active development. Only the current `main` branch receives best-effort security fixes; no production support or SLA is promised.

## Reporting a vulnerability
**Do not open public issues describing exploitable vulnerabilities or post secrets.** Contact `mr.asmar.web@gmail.com` with a concise report, reproduction steps, affected version/commit and impact. Please avoid testing against real users or production data without authorization.

Maintainers will review reports when available. Do not assume an agreed disclosure timeline until confirmed directly.

## Security rules
- No customer records, credentials, payment keys, tokens or `.env` files in issues/PRs.
- The repository does not currently process live payments.
- Never run destructive database commands against existing data; use disposable SQLite tests.
- Keep Mailpit, Stripe test mode, and other developer services isolated from production.
