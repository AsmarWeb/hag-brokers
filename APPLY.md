# Apply the Phase 9A patch safely

This archive is designed to be extracted into the **repository root**, not into `src/`. It includes a replacement README and new community files. It intentionally does not modify `.env`, `docker-compose.yml`, existing migrations, or actual databases.

**Before extraction** verify branch and clean state. From `~/Downloads/trans-marine-docker-kit`:

```bash
git status -sb
git fetch origin
git checkout main
git pull --ff-only origin main
git checkout -b feat/phase9a-revenue-foundation
```

Then copy/extract the package root into this repository root. If any files already exist (especially README/LICENSE), inspect them first, keep backup copies, and merge content instead of blindly overwriting. The supplied patch includes only new source files besides README.

Validation:

```bash
find src/app/Models/Deal*.php src/app/Services/Revenue/CommissionCalculator.php src/database/migrations/2026_11_01_*.php src/tests/Unit/CommissionCalculatorTest.php src/tests/Feature/DealSchemaTest.php -type f -exec php -l {} \;
docker compose exec -T -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: -e DB_URL= app php artisan test
git diff --check
git status -sb
```

Do not commit unless all tests succeed. Never run migrate on a persistent database during review.

The MIT license text is a recommendation that needs project owner approval for ownership and third-party licensing obligations, including contributions and incorporated code. A company trademark is distinct from software copyright licensing.
