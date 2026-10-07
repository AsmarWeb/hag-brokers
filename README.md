# Trans Marine Backend

Laravel backend for the Trans Marine sea freight operations platform.

This project powers the customer-facing freight operations workflow, including:
- customer and user management
- shipments and tracking
- quotations
- notifications
- vessel and port data
- document management
- admin operations dashboard

## Tech stack
- PHP 8.2
- Laravel 12
- MySQL / SQLite for local development
- Laravel Sanctum
- Laravel Breeze
- Laratrust
- Docker + Docker Compose
- Vite + Tailwind CSS

## Repository layout
- src/ - main Laravel application
- docker/ - PHP and Nginx container config
- flutter/ - client/mobile app assets and structure
- docs/ - project documentation (to be extended)

## Local development

### Prerequisites
- Docker
- Docker Compose

### Start the app
```bash
docker compose up --build
