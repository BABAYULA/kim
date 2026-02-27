# Tech Context

## Languages & Runtime
- **PHP 8.0+** with WordPress hooks and APIs
- **MySQL 5.7+** (or MariaDB equivalent) for 7 custom tables
- **JavaScript ES6+** for frontend and admin interactivity (Vanilla JS — NO React/Vue/jQuery)
- **CSS3** (Grid/Flexbox, BEM naming) for styling

## Frameworks & Libraries
- **WordPress 6.x** plugin environment
- **Composer** for PHP dependency management (PSR-4 autoloading, namespace `IAT\`)
- **NPM** for JS build tools
- **Webpack 5** (configured via `webpack.config.js`) for asset bundling
- **Leaflet.js 1.9+** for map display and zone visualization
- **PHPUnit 9.5+** for backend unit/integration tests

## Development Tools
- Local WP development environment (e.g., LocalWP, Docker, MAMP)
- Git for version control; typical branching strategy (feature/bugfix/release)
- PHP_CodeSniffer with WordPress coding standards
- PHPStan for static analysis
- AI coding assistants following unified `CODING_RULES.md`

## Configuration Files
- `composer.json` — PHP deps, PSR-4 autoloading (`IAT\` → `includes/`)
- `package.json` — NPM/Webpack build scripts, Leaflet dependency
- `phpunit.xml` — Test suite configuration
- `.gitignore` — Comprehensive exclusions (vendor, node_modules, env, build, IDE)
- `CODING_RULES.md` — Unified rules for all AI tools
- `.cursorrules`, `.gemini/styleguide.md`, `.windsurfrules`, `.clinerules` — AI tool pointers

## Build & Test Setup
- `composer install` to fetch PHP libraries
- `npm install` for JS dependencies
- `npm run build` for production assets (Webpack)
- `npm run dev` for watch mode during development
- `vendor/bin/phpunit` for PHP tests
- `vendor/bin/phpcs` for code standards checking
- `vendor/bin/phpstan analyse` for static analysis

## Technical Constraints
- Must run inside a WordPress plugin context; cannot use Laravel, Symfony, etc.
- No external payment gateway in v1; architecture must allow plugging in later
- External APIs have rate limits; design for rotation and caching
- Hosting environment likely shared WP hosting; resource usage should be modest
- All code in English; bilingual (Turkish/English) readiness for v2

## Deployment Environment
- Production WordPress instance with FTP/SSH access for plugin upload
- WP-Cron or server cron job for scheduled tasks
- SMTP configuration (WP Mail SMTP) for email delivery
- ZIP packaging via `npm run zip`

## Security & Compliance
- reCAPTCHA v3 integration requires separate keys per site
- Data privacy: capture minimal PII; respect GDPR
- Store API keys securely and NEVER commit to git
- All queries via `$wpdb->prepare()` — no raw SQL with user input

## Data Files
- `data/pricing-matrix.json` — Machine-readable pricing (79 routes + 11 intrazonal)
- `data/zones-metadata.json` — Zone codes, types, and descriptions (13 zones)
- `fiyat_listesi.md` — Human-readable pricing matrix (source of truth)
- `koordinatlar.md` — GeoJSON zone coordinates (source of truth)
