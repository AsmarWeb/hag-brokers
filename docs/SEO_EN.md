# Public SEO and opportunity slugs

Read SEO_AR.md for the full setup and deployment guide. This phase adds stable, unique, Unicode-friendly slugs to public opportunity routes; admin/company IDs and database relationships remain unchanged. Legacy GET ID links redirect 301 only after public eligibility is verified. Private/closed/expired/suspended listings remain 404 and are excluded from dynamic sitemaps.

One additive migration creates slug and seo_updated_at, adds a unique index and backfills missing slugs through query-builder updates without observers, notifications, timestamp or financial changes. Existing lastmod values are not invented. Title changes retain their original URLs. Notes do not change the public modification timestamp.

Language-qualified URLs use ?lang=ar and ?lang=en, with self-canonical and reciprocal hreflang/x-default. Listing content itself is not automatically translated. Metadata includes escaped descriptions, social cards and safe JSON-LD for real page/organization/breadcrumb information, without invented ratings or Product/Offer markup.

/sitemap.xml is a dynamic index linking /sitemaps/pages.xml and paginated /sitemaps/opportunities-N.xml parts, each up to 1,000 listings / 2,000 language URLs. Public eligibility is evaluated live. The static public/robots.txt is backed up and removed so Laravel's dynamic route can announce the correct configured sitemap domain. The installer refuses to remove a locally modified robots.txt.

Keep SEO_INDEXING_ENABLED=false on local/staging. On the real HTTPS production domain, configure APP_URL, optional SEO_CANONICAL_URL, SEO_INDEXING_ENABLED=true, optional real SEO_SOCIAL_IMAGE and public Google/Bing verification codes. No private API keys are required. seo:check is read-only; external ownership verification and sitemap submission are manual, not performed by the installer.

After installing: config:clear, route:clear, migrate (never fresh/reset/refresh), targeted SEO/marketplace tests, seo:check, view:cache and npm run build. The installer only edits reviewed source paths and creates backups; it does not run migrations or restore data.

Use Google Search Console, Bing Webmaster Tools, PageSpeed Insights, Lighthouse and Rich Results Test on the real published domain. A sitemap or slug does not guarantee crawling, indexing, ranking or customers. Content quality, legitimate references, performance and actual business value remain ongoing work.

See official Google documentation linked in SEO_AR.md. Tests use isolated SQLite and mocked/local HTTP requests; production hosting, MySQL, Googlebot, domain verification and live performance were not exercised here.
