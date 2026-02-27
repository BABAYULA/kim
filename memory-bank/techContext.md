# Tech Context

## Languages & Runtime
- **PHP 8.0+** with WordPress hooks and APIs
- **MySQL 5.7+** (or MariaDB equivalent) for custom tables
- **JavaScript ES6+** for frontend and admin interactivity
- **CSS/SCSS** for styling via Webpack

## Frameworks & Libraries
- **WordPress 6.x** plugin environment
- **Composer** for PHP dependency management
- **NPM/Yarn** for JS build tools
- **Webpack** (configured via `webpack.config.js`) for asset bundling
- **PHPUnit** for backend unit tests
- Optional: **Jest/Cypress** for frontend testing

## Development Tools
- Local WP development environment (e.g., LocalWP, Docker, MAMP)
- Git for version control; typical branching strategy (feature/bugfix/release)
- ESLint/Prettier for JS linting
- PHP_CodeSniffer (PHP-CS-Fixer) with WordPress rules
- VSCode or preferred IDE with PHP and JS extensions

## Dependencies
- PHP packages specified in `composer.json` (e.g., `symfony/polyfill`, `phpunit/phpunit`)
- JS packages in `package.json` (e.g., `webpack`, `babel`, `jquery` if used)

## Build & Test Setup
- `composer install` to fetch PHP libraries
- `npm install` for JS dependencies
- `npm run build` for production assets
- `npm run dev` for watch mode during development
- `vendor/bin/phpunit` for PHP tests

## Technical Constraints
- Must run inside a WordPress plugin context; cannot use Laravel, Symfony, etc.
- No external payment gateway initially; architecture must allow plugging in later.
- External APIs have rate limits; design for rotation and caching.
- Hosting environment likely shared WP hosting; resource usage should be modest.

## Deployment Environment
- Production WordPress instance with FTP/SSH access for plugin upload
- WP-Cron or server cron job for scheduled tasks
- SMTP configuration (WP Mail SMTP) for email delivery

## Security & Compliance
- ReCAPTCHA v3 integration requires separate keys per site
- Data privacy: capture minimal PII; respect GDPR (optional translations)
- Store API keys securely and not commit to git

## Documentation & Automation
- `README.md` for high-level project overview and setup
- Implementation plan files for module breakdown
- Cline workflows/rules in `prompts/` are optional and can be removed
- CI configuration (GitHub Actions/Bitbucket pipeline) to automate tests/builds
