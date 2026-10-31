# HAG Brokers — Phase 9A / Revenue Foundation

Scope: additive **schema + models + deterministic fee calculation + tests only**. No payment endpoints, live transfers, or new staff permissions are activated in this patch.

## Hybrid collection

- `commission_only`: HAG invoices its own brokerage fee; third parties settle main contract amounts separately.
- `full_service`: HAG is the contracting seller of the service, invoices the customer total and records supplier cost and expected spread; only when contracts and trade licence permit.

`deal_amount` is NOT necessarily HAG revenue. `platform_revenue` represents an **expected** amount and MUST NOT be treated as earned/recognized revenue without a contractual trigger and payment/accounting reconciliation.

Money fields are decimals for SQL snapshots. `CommissionCalculator` operates on string amounts and integers and currently supports **two-decimal currencies only** (e.g. AED/USD), *not* zero/three-decimal currencies. Do not use floats for calculations.

## Existing system notes

- `Opportunity` already has `commission_type` and `commission_rate`; do not reinterpret these fields without verifying business meaning.
- `Quotation` has approved offer and margin snapshot. A future `CreateDealFromQuotation` service must verify quotation approval and use the frozen snapshot.
- A deal may link to exactly one source (`quotation_id` XOR `opportunity_id`): **enforce in the Phase 9B application service and preferably a database CHECK after verifying SQLite/MySQL compatibility**.
- Draft agreements are editable; approved agreements and fee snapshots must be immutable. Implement state transitions in a service with row locks and audit trail before exposing write APIs.
- No API routes were created: `src/routes/api.php` in the repository is commented out. Auth, policies, customer isolation, idempotency and ledger accounting must be implemented before activating endpoints.
- Add invoice/payment tables in Phase 9C after reviewing invoice and VAT requirements; don't pretend this 9A foundation processes money.

## Validation without production data

```bash
cd "$HOME/Downloads/trans-marine-docker-kit"
docker compose exec -T -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: -e DB_URL= app php artisan test
```

**Never run `migrate`, `migrate:fresh`, `db:seed`, or connect to the production database as part of applying this patch.** Migration files are only definitions until deliberately executed after backup and review.
