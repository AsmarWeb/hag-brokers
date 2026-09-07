# Trans Marine — Laravel Docker Environment

Docker-first Laravel API environment for the Trans Marine mobile application.

## Services

- PHP 8.4 + Laravel 12
- Nginx
- MySQL 8.4
- Redis 7
- Queue worker
- Scheduler
- Mailpit
- Laravel Sanctum

## Start

```bash
docker compose up --build
```

The first boot creates a fresh Laravel 12 application in `./src`, installs Sanctum/API support, overlays the Trans Marine models/controllers/routes/migrations, generates the app key, and runs migrations.

API base URL:

```text
http://localhost:8080/api/v1
```

Mailpit:

```text
http://localhost:8025
```

MySQL from the host:

```text
127.0.0.1:3307
```

## Useful commands

```bash
docker compose logs -f app
docker compose exec app php artisan migrate
docker compose exec app php artisan route:list
docker compose exec app php artisan test
docker compose down
docker compose down -v   # deletes MySQL/Redis volumes
```

## Production notes

This package is Docker-ready and is the base for Phase 2B production hardening. Before production deployment, set real secrets, use managed/secured MySQL and Redis where appropriate, configure object storage for documents, enable TLS, restrict CORS, add rate limiting, configure broadcasting/WebSockets, and complete the remaining authorization, validation, resources, services, audit logging, and automated tests.
# trans-marine-backend
# trans-marine-backend
