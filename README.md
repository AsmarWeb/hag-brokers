# HAG Brokers — Digital Freight & Commercial Brokerage Platform

منصة HAG Brokers للوساطة التجارية والشحن البحري وإدارة الفرص والصفقات.
**HAG Brokers** is an open-source Laravel foundation for maritime freight quotations, brokerage opportunities, documents, workflow approval, and planned revenue operations.

> **Status:** Active development, not production-ready. Phase 9A adds revenue schema and deterministic calculation tools; it does not activate payments or customer billing.

## Features

- Freight quotations, carrier offers, internal approval and pricing snapshots
- Final quotation PDF generation, approval, queued Mailpit-safe email flow (disabled by default)
- Opportunities for commercial and vessel/yacht brokerage
- Customers, carriers, shipments, ports, documents and versioning
- Admin dashboard, bilingual Arabic / English UI, notifications
- Phase 9A: additive deal/agreements/commissions schema and fee calculator; payments are planned

## Stack

PHP / Laravel 12, SQLite for tests, configurable MySQL for deployment, Sanctum, Laratrust, Docker Compose, Redis, Mailpit, Blade/Tailwind/Vite.

## Repository layout

- `src/` — Laravel application and tests
- `docker/`, `docker-compose.yml` — local containers
- `flutter/` — client/mobile scaffolding, not a released app
- `docs/` — implementation and architecture notes

## Development

1. Install Docker and Docker Compose.
2. Clone the repository and configure a **local-only** `src/.env` using `src/.env.example`; never commit secrets.
3. Review `docker-compose.yml` and `docker/php/bootstrap.sh` before running containers.
4. Start with `docker compose up -d --build`.
5. Run tests in a disposable SQLite database:

```bash
docker compose exec -T -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: -e DB_URL= app php artisan test
```

**Do not run migrations or seeders on existing databases without backups and a review.** The payment gateway and revenue collection flow are not live. The final-quotation delivery feature remains disabled by default (`HAG_FINAL_QUOTATION_DELIVERY_ENABLED=false`).

## Community and security

See [Contributing](CONTRIBUTING.md), [Code of Conduct](CODE_OF_CONDUCT.md), [Security](SECURITY.md), and [License](LICENSE). Please do not publish credentials or customer data in issues.

## Brand and repository name

Brand: **HAG Brokers**. Repository slug may be renamed to `hag-brokers` in GitHub Settings → General → Repository name; update remote URLs and deployment integration references after the rename.
