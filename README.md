# HAG Brokers — Digital Freight & Commercial Brokerage Platform

منصة HAG Brokers للوساطة التجارية والشحن البحري وإدارة الفرص والصفقات.
**HAG Brokers** is a proprietary commercial Laravel platform for maritime freight quotations, brokerage opportunities, documents, workflow approval, and planned revenue operations.

> **Status:** Active development, not production-ready. Phases 9A and 9B provide the revenue foundation, deal creation, agreement approval, pending commissions, financial audit records, and initial access controls. Payment collection and customer billing are not active.

## Features

- Freight quotations, carrier offers, internal approval and pricing snapshots
- Final quotation PDF generation, approval, queued Mailpit-safe email flow (disabled by default)
- Opportunities for commercial and vessel/yacht brokerage
- Customers, carriers, shipments, ports, documents and versioning
- Admin dashboard, bilingual Arabic / English UI, notifications
- Phase 9A: deal, agreement and commission database foundation with deterministic fee calculations
- Phase 9B: approved quotation to deal conversion, agreement drafts and approval, pending commissions, audit logging and financial authorization
- Future development: financial security hardening, management interfaces, invoicing, payments and revenue reporting

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

## Internal development and security

HAG Brokers is proprietary software owned by HAG Brokers, subject to applicable law and third-party rights. It is not an open-source project. Access, development, reuse and distribution require company authorization.

See [Internal Development Guidelines](CONTRIBUTING.md), [Code of Conduct](CODE_OF_CONDUCT.md), [Security](SECURITY.md), and [Proprietary License](LICENSE). Never publish source code, credentials, financial records or customer data without authorization.

## Brand and repository name

Brand: **HAG Brokers**. GitHub repository: `AsmarWeb/hag-brokers`. The repository is intended for private, authorized company development.
