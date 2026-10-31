# HAG Brokers — Internal Development Guidelines

HAG Brokers is proprietary commercial software.

Copyright (c) 2026 HAG Brokers. All Rights Reserved.

## Authorized contributors

Development access is limited to employees, contractors and
business partners explicitly authorized by HAG Brokers.

Access to the repository does not grant ownership or permission
to redistribute, sublicense, publish or commercially reuse the code.

## Development workflow

1. Work through an authorized company GitHub account.
2. Create a feature or fix branch from the latest `main`.
3. Keep changes narrowly scoped and include relevant tests.
4. Run the isolated SQLite test suite before submitting changes.
5. Submit a Pull Request for review and approval.
6. Document database migrations, security impact and deployment requirements.
7. Do not push directly to `main`.
8. Never commit secrets, customer data or production credentials.
9. Never run migrations or seeders against production without authorization,
   a verified backup and a documented deployment plan.

## Confidentiality

Do not share company source code, architecture, customer records,
pricing, agreements, financial information or infrastructure details
outside authorized company channels.

## Third-party dependencies

Any new dependency must be reviewed for security, maintenance,
commercial compatibility and license obligations.

## Security reporting

Report security concerns privately using the procedures in SECURITY.md.

## Intellectual property

Contributions and ownership are governed by applicable employment,
contractor or other written agreements with the Company.
Repository access alone does not transfer intellectual property rights.

## License

See LICENSE for the proprietary software terms.

All rights reserved.
