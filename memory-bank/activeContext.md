# Active Context

**Current Focus:**
- Formalizing the architecture and infrastructure before writing code.
- Defining database schema, core modules, and administrative workflows.
- Setting up repository with proper branching and CI conventions.
- Ensuring clarity around security requirements (reCAPTCHA, rate limiting, nonces).

**Recent Changes:**
- Implementation plan documented with detailed module/file breakdowns.
- README fleshed out with features, tech stack, and build instructions.
- `.clinerules` added to guide memory bank usage.

**Next Steps:**
1. Create and populate the memory-bank core documents (completed now).
2. Verify presence of actual source files (`includes/`, assets, admin etc.) and identify gaps.
3. Establish development environment: PHP 8+ local WP install, MySQL, Node for assets.
4. Scaffold CI/CD pipeline: composer install, npm build, phpunit tests, packaging plugin.
5. Draft deployment strategy: WP plugin zip generation, testing on staging, migration plans.

**Active Decisions:**
- Keep frontend JS vanilla ES6 for simplicity but allow React later if needed.
- Use WP Cron for auto-confirm; consider actual cron for reliability in production.
- Cache geocoding results aggressively to minimize API calls.

**Learnings/Patterns:**
- Clear separation between admin/backend logic and frontend presentation improves maintainability.
- Single responsibility classes for each domain (geocoding, zones, bookings) reduces coupling.
- External APIs require robust fallback and error tracking.
